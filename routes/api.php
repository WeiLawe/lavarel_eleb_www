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

//修改密码接口
Route::post('/changePassword','ApiController@changePassword');

//忘记密码接口
Route::post('/forgetPassword','ApiController@forgetPassword');

//商铺列表
Route::get('/addressList','ApiController@addressList');

//添加保存地址接口
Route::post('/addAddress','ApiController@addAddress');

//指定地址
Route::get('/address','ApiController@address');

//修改保存地址接口
Route::post('/editAddress','ApiController@editAddress');

//指定地址
Route::post('/addCart','ApiController@addCart');

//获取购物车数据接口
Route::get('/cart','ApiController@cart');

//添加订单接口
Route::post('/addorder','OrderController@addorder');

//获得指定订单接口
Route::get('/order','OrderController@order');

//获得订单列表接口
Route::get('/orderList','OrderController@orderList');