<?php

namespace App\Http\Controllers\Api;

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

    public function index()
    {
        $this->importEdinetXmlService->import();
        return [
            [
                'stock_code' => 9005,
                'name'       => '東急電鉄'
            ],
            [
                'stock_code' => 9005,
                'name'       => '東急電鉄'
            ],
            [
                'stock_code' => 9005,
                'name'       => '東急電鉄'
            ]
        ];
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