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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('scb_balance', 'ScbController@getBalance');
Route::post('scb_withdraw_tobank', 'ScbController@withdrawToBank');
Route::post('scb_withdraw_to_tmn', 'ScbController@withdrawToTmn');
Route::post('scb_get_name', 'ScbController@getNameAcountScb');
