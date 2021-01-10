<?php

namespace App\Service;

use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Weidner\Goutte\GoutteFacade as Goutte;

class ImportEdinetXmlService
{
    public function import()
    {
        //APIを叩き、StorageにXMLをzipで保存
        $code = 'S100IOBO';
        $getUri = 'https://disclosure.edinet-fsa.go.jp/api/v1/documents/'.$code.'?type=1';

        $filePath = 'private/'.$code.'.zip';

        //財務情報を取得＆整形
        $company = $this->readSecurityReport();
        dd($company);

    }

    /**
     * 有価証券報告書の企業情報を取得
     *
     * @return array
     */
    private function readSecurityReport()
    {
        $file_name = 'jpcrp030000-asr-001_E02925-000_2020-02-29_01_2020-06-01.xbrl';
        $get_data = storage_path('app/public/XBRL/PublicDoc/'.$file_name);

        $xml = simplexml_load_file($get_data);

        foreach($xml->getDocNamespaces() as $key => $name) {
            $result = $xml->children($name);
            $xml_object[$key][] = (array)$result;
        }

        $this->readCompanyDetails($xml_object['jpcrp_cor'][0]);

    }

    /**
     * 詳細情報を整形
     *
     * @param array $company_details
     * @return array
     */
    private function readCompanyDetails(array $details): array
    {
        //基本情報
        $company = [
            'company' => $details['CompanyNameCoverPage'],
            'ceo'     => $details['TitleAndNameOfRepresentativeCoverPage'],
            'address' => $details['AddressOfRegisteredHeadquarterCoverPage'],
        ];

        //沿革情報
        $history = $this->formatHistory(['html' => $details['CompanyHistoryTextBlock']]);


        //財務情報
        $closing = [
            //売上高（連結＋単体）
            'sales' => $details['NetSalesSummaryOfBusinessResults'],
            //経常利益（連結＋単体）
            'profit' => $details['OrdinaryIncomeLossSummaryOfBusinessResults'],
            //純利益（連結）
            'net_profit' => $details['ProfitLossAttributableToOwnersOfParentSummaryOfBusinessResults'],
            //従業員数（連結＋？）
            'employee' => $details['NumberOfEmployees'],
        ];

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
}
