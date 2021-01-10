<?php

namespace App\Service;

use App\Format\ViewFormat;

class ImportEdinetXmlService
{
    /**
     * 有価証券報告書の企業情報を取得
     *
     * @return array
     */
    public function import(string $stock_code): array
    {
        $get_data = storage_path('app/company/'.$stock_code.'.xbrl');

        $xml = simplexml_load_file($get_data);

        foreach($xml->getDocNamespaces() as $key => $name) {
            $result = $xml->children($name);
            $xml_object[$key][] = (array)$result;
        }
        return $this->readCompanyDetails($xml_object['jpcrp_cor'][0], $stock_code);

    }

    /**
     * 詳細情報を整形
     *
     * @param array $company_details
     * @return array
     */
    private function readCompanyDetails(array $details, string $stock_code): array
    {
        //基本情報
        $company = $this->formatBasicInfo($details, $stock_code);
    
        //沿革情報
        $histories = $this->formatHistory(['html' => $details['CompanyHistoryTextBlock']]);

        //役員情報（TODO 実装）
        // $executive = $this->formatExecutive(['html' => $details['InformationAboutOfficersTextBlock']]);

        //業績推移
        $closing_years = $this->formatClosing(['html' => $details['BusinessResultsOfGroupTextBlock']]);

        return [
            'company'       => $company,
            'closing_years' => $closing_years,
            'histories'     => $histories
        ];
    }

    /**
     * 会社の基本情報
     *
     * @param array $company
     * @return array
     */
    private function formatBasicInfo(array $company, string $stock_code): array
    {
        // 年間平均給与(万円単位に変換) 
        $salary = $company['AverageAnnualSalaryInformationAboutReportingCompanyInformationAboutEmployees'];
        $averageAnnualSalary = ViewFormat::convertToTenThousand($salary);

        // 従業員の平均年齢（X年Yヶ月）
        $yearsEmployeeAge = $company['AverageAgeYearsInformationAboutReportingCompanyInformationAboutEmployees'];
        $monthsEmployeeAge = $company['AverageAgeMonthsInformationAboutReportingCompanyInformationAboutEmployees'];

        return  [
            'stock_code'          => $stock_code,
            'company'             => $company['CompanyNameCoverPage'],
            'ceo'                 => $company['TitleAndNameOfRepresentativeCoverPage'],
            'address'             => $company['AddressOfRegisteredHeadquarterCoverPage'],
            'salary'              => $averageAnnualSalary,
            'salary_unit'         => '万円',
            'employee_age_years'  => $yearsEmployeeAge,
            'employee_age_months' => $monthsEmployeeAge,
        ];
    }

    /**
     * 決算情報を整形
     *
     * @param array $closings
     * @return array
     */
    private function formatClosing(array $closings): array
    {
        //有価証券報告書・見開き１ページ目の決算情報を整形
        $closing_to_array = explode("<tr style=", $closings['html']);
        //データを取得できる形にグルーピング
        foreach ($closing_to_array as $item) {
            $array_by_columns = explode("\n", $item);
            $closingData = [];
            foreach ($array_by_columns as $closing_record) {
                if (substr($closing_record, 0, 5) === '<span') {
                    $closingData[] = rtrim(mb_substr($closing_record, 66, 300,"utf-8"), '</span>');
                }
            }

            $formatted_records[] = $closingData ?? null;
        }

        /**
         * 2列目: 決算
         *
         *  0 => "決算年月"
         *  1 => "2016年2月"
         *  2 => "2017年3月"
         *  3 => "2018年4月"
         */
        foreach ($formatted_records[2] as $year) {
            if (! strpos($year,'年月')) {
                $result['year'][] = $year;
            }
        }

        /**
         * 3列目: 売上
         *
         *  0 => "売上高"
         *  1 => "(百万円)"
         *  2 => "238,154"
         *  3 => "238,952"
         */
        foreach ($formatted_records[3] as $sales) {
            $sales_record = $this->formatCurrency($sales, $formatted_records[3][1]);
            if ($sales_record) {
                $result['sales']['value'][] = $sales_record;
            }
        }
        $result['sales']['label'] = $formatted_records[3][0];
        $result['sales']['unit'] = '億円';

        /**
         * 4列目: 利益
         *
         *  0 => "経常利益"
         *  1 => "(百万円)"
         *  2 => "238,154"
         *  3 => "238,952"
         */
        foreach ($formatted_records[4] as $sales) {
            $sales_record = $this->formatCurrency($sales, $formatted_records[4][1]);
            if ($sales_record) {
                $result['profit']['value'][] = $sales_record;
            }
        }
        $result['profit']['label'] = $formatted_records[4][0];
        $result['profit']['unit'] = '億円';

        //最新の業績を整形
        $result['latest_performance'] = [
            'closing_month'      => end($result['year']),
            'sales_type'         => $result['sales']['label'],
            'sales_performance'  => end($result['sales']['value']),
            'profit_type'        => $result['profit']['label'],
            'profit_performance' => end($result['profit']['value']),
            'unit'               => '億円'
        ];

        return $result;
    }

    /**
     * 通貨単位の変換
     *
     * @param string $amount
     * @param string $unit
     * @return int|null
     */
    private function formatCurrency(string $amount, string $unit): ?int
    {
        if (strpos($unit,'百万円')) {
            if ($amount !== $unit) {
                $amountConvertedInt = ViewFormat::convertAmount($amount);
                return ViewFormat::convertMillionToBillion($amountConvertedInt);
            } else {
                return null;
            }
        } elseif (strpos($unit,'千円')) {
            if ($amount !== $unit) {
                $amountConvertedInt = ViewFormat::convertAmount($amount);
                return ViewFormat::convertThousandToBillion($amountConvertedInt);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * 沿革情報を整形
     *
     * @param array $history
     * @return array
     */
    private function formatHistory(array $history): array
    {
        //沿革に関係ない情報を除去
        $historyToArray = explode("\n", $history['html']);

        //年と内容を抽出
        foreach ($historyToArray as $item) {
            if (substr($item, 0, 22) === '<p style="margin-right') {
                $targeEvents['year'][] = (int)substr($item, 49, 4);
            } elseif(substr($item, 0, 21) === '<p style="margin-left') {
                $targeEvents['detail'][] = rtrim(mb_substr($item, 47, 300,"utf-8"), '</p>');
            }
        }

        foreach ($targeEvents['year'] as $key => $event) {
            if ($event === 0) {
                break;
            }
            if (empty($targeEvents['detail'][$key])) {
                break;
            }
            $result[] = [
                'year'   => $event,
                'detail' => $targeEvents['detail'][$key]
            ];
        }

        return $result;
    }

    /**
     * 役員一覧を取得
     *
     * @param array $executive
     * @return array
     */
    private function formatExecutive(array $executive): array
    {
        //取得が困難
        return [];
    }
}
