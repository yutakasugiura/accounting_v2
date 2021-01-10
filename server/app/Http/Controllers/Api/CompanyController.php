<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service\ImportEdinetXmlService;

class CompanyController extends Controller
{
    private $importEdinetXmlService;

    public function __construct(ImportEdinetXmlService $importEdinetXmlService)
    {
        $this->importEdinetXmlService = $importEdinetXmlService;
    }

    /**
     * 企業データjsonを生成
     *
     * @param string $stock_code
     * @return void
     */
    public function index()
    {   
        dd('hello');
    }

    /**
     * 企業データjsonを生成
     *
     * @param string $stock_code
     * @return void
     */
    public function create(string $stock_code)
    {   
        $stock_code_length = strlen($stock_code);
        try {
            if ($stock_code_length < 20) {
                return $this->importEdinetXmlService->import($stock_code);
            }
        } catch (Exception $e) {
            return $e->getMessage() ?? ['error' => '出力失敗'];
        }
    }

    /**
     * 保存（POST）
     *
     * @param Request $Request
     * @return void
     */
    public function store(Request $request)
    {
        $this->importEdinetXmlService->import();
        dd($request->all());
        if ($request['data'] === true) {
            return ['result' => 'OK'];
        } else {
            return ['result' => 'NG'];
        }
    }
}