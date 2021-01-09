<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    dd('hello API');
    return view('welcome');
});


Route::prefix('/')->name('api.')->group(function(){
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/', 'Api\CompanyController@index')->name('index');
        Route::post('/store', 'Api\CompanyController@store')->name('store');
    });
});
