<?php

use Illuminate\Http\Request;

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

//商铺列表
Route::get('/shops','ApiController@shops');

//指定店铺列表
Route::get('/shop','ApiController@shop');

//发送短信接口
Route::get('/sms','ApiController@sendSms');

//注册接口
Route::post('/regist','ApiController@regist');

//验证登陆接口
Route::post('/loginCheck','ApiController@loginCheck');
