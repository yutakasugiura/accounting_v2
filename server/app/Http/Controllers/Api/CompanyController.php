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
}