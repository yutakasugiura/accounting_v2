<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Article;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function index()
    {
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
        if ($request['data'] === true) {
            return ['result' => 'OK'];
        } else {
            return ['result' => 'NG'];
        }
    }
}