<?php

namespace App\Http\Controllers;

use App\Address;
use App\Cart;
use App\Sms;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    //商铺列表
    public function shops()
    {
        $shops=DB::table('shops')->get();
//        dd($shops);
        return $shops;
    }

    //指定商家列表
    public function shop(Request $request){
        $id=$request->id;
        $shop=DB::table('shops')->find($id);
        //转为数组
        $shop=get_object_vars($shop);

        $shop['evaluate']=[
            ["user_id"=> 12344,
            "username"=> "w******k",
            "user_img"=> "http://www.homework.com/images/slider-pic4.jpeg",
            "time"=> "2017-2-22",
            "evaluate_code"=> 1,
            "send_time"=> 30,
            "evaluate_details"=> "不怎么好吃"],
            [
                "user_id"=> 12344,
                "username"=> "w******k",
                "user_img"=> "http://www.homework.com/images/slider-pic4.jpeg",
                "time"=> "2017-2-22",
                "evaluate_code"=> 4.5,
                "send_time"=> 30,
                "evaluate_details"=> "很好吃"
            ]
        ];
        $food_cats=DB::table('food_cats')->where('shop_id',$id)->get();

        foreach ($food_cats as $food_cat){
//            dd($food_cat);
            $foodcatid=$food_cat->id;
//            dd($foodcatid);
            $meals=DB::table('meals')->where([['shop_id',$id],['food_cat_id',$foodcatid]])->get();
//            dd($meals);
            foreach ($meals as $meal){
//                dd($meals);
                $meal->goods_id=$meal->id;
                $meal->goods_name=$meal->meal_name;
                $meal->goods_img=$meal->meal_img;
                $meal->goods_price=$meal->meal_price;
                $food_cat->goods_list[]=$meal;
            }
//            dd($food_cat);
            $shop['commodity']=$food_cats;
        }


        return $shop;

    }

    //发送短信
    public function sendSms(Request $request)
    {
        $params = array();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAI3DqhEd5hqwsu";
        $accessKeySecret = "UlU3TruoxjeIk5uJdGqDwnTXb4sCjr";

        // fixme 必填: 短信接收号码


//        $tel=;
        $params["PhoneNumbers"] = $request->tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "仰虹怀瑾之味";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_133845002";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $code=mt_rand(100000,999999);
        $params['TemplateParam'] = Array(
            "code" =>$code ,
//            "product" => "阿里通信"
        );


        // fixme 可选: 设置发送短信流水号
//        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new Sms();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
//        dd($content) ;
        if ($content->Message == 'OK'){
            //发送成功
            Redis::setex('code_'.$request->tel,30000,$code);
//            echo '{
//              "status": "true",
//              "message": "获取验证码成功"
//                 }';
            return ['status'=>'true','message'=>'获取验证码成功'];
        }else{
            //发送失败
//            echo '{
//              "status": "false",
//              "message": "获取验证码失败,请检查电话号码稍后再试!"
//                }';
            return ['status'=>'false','message'=>'获取验证码失败,请检查电话号码稍后再试!'];
        }
    }

    //注册页面
    public function regist(Request $request)
    {
        //验证
//        $res=DB::table('users')->where('name',$request->username)->first();
        $validator=Validator::make($request->all(),
            [
                'username'=>'required|unique:users,name',
                'password'=>'required',
                'sms'=>'required',
                'tel'=>['regex:/^1(3|4|5|7|8)\d{9}$/','unique:users','required'],
            ],
            [
                'username.required'=>'用户名不能为空!',
                'username.unique'=>'该用户名已存在!',
                'tel.required'=>'手机号码不能为空!',
                'tel.unique'=>'该手机号码已存在!',
                'password.required'=>'密码不能为空!',
                'sms.required'=>'验证码不能为空!',
                'tel.regex'=>'电话号码不合法!'
            ]);
        //验证失败
        if ($validator->fails()){
        //失败获取错误信息
            $errors=$validator->errors();
            //返回错误信息
            return ['status'=>'false','message'=>$errors->first()];
        }

//        //验证 验证码
        $code=Redis::get('code_'.$request->tel);
//        return $code;

        if ($code!=$request->sms){
            return ['status'=>'false','message'=>'验证码填写错误!'];
        }
        //            保存数据到user表
        User::create(
            [
                'name'=>$request->username,
                'password'=>bcrypt($request->password),
                'tel'=>$request->tel,
            ]);
        //注册成功
        return ['status'=>'true','message'=>'注册成功!!!'];
    }

    //登陆验证
    public function loginCheck(Request $request){
//        return $request->input();
        //验证登陆
        if (Auth::attempt(['name'=>$request->name,'password'=>$request->password])){
            //登陆成功
            return ['status'=>'true','message'=>'登陆成功','user_id'=>Auth::user()->id,'username'=>Auth::user()->name];
        }
        //登录失败
        else{
            return ['status'=>'false','message'=>'登录失败'];
        }

    }

    //修改密码
    public function changePassword(Request $request)
       {
//           return $request->input();
           //旧密码输入正确
           if (Hash::check($request->oldPassword,Auth::user()->password)){
               DB::table('users')->where('id',Auth::user()->id)->update(
                   [
                       'password' => bcrypt($request->newPassword),
                   ]
               );
               return ['status'=>'true','message'=>'修改密码成功!!'];
           }
           return ['status'=>'false','message'=>'旧密码错误!!'];
       }

    //忘记密码
    public function forgetPassword(Request $request){

            //查询电话号码
            $res=DB::table('users')->where('tel',$request->tel)->first();
//            dd($res);
            if (!$res){
                return ['status'=>'false','message'=>'该电话号码的用户不存在'];
            }
            //验证
            $validator=Validator::make($request->all(),
                [
                    'tel'=>['regex:/^1(3|4|5|7|8)\d{9}$/','required',],
                    'password'=>'required',
                    'sms'=>'required',
                ],
                [
                    'tel.required'=>'手机号码不能为空!',
                    'password.required'=>'密码不能为空!',
                    'sms.required'=>'验证码不能为空!',
                    'tel.regex'=>'电话号码不合法!',
                ]);
            //验证失败
            if ($validator->fails()){
                //失败获取错误信息
                $errors=$validator->errors();
                //返回错误信息
                return ['status'=>'false','message'=>$errors->first()];
            }
            //验证 验证码
            $code=Redis::get('code_'.$request->tel);
            //验证失败
            if($code!=$request->sms){
                return ['status'=>'false','message'=>'验证码填写错误!'];
            }
            //验证成功  进行修改
            DB::table('users')->where('tel',$request->tel)->update(
                [
                    'password'=>bcrypt($request->password),
                ]
            );
            //修改成功
            return ['status'=>'true','message'=>'修改密码成功!!!'];
        }

    //地址列表
    public function addressList()
    {
//        if (Auth::user()){
            $addresses=DB::table('addresses')->get();
            return $addresses;
//        } else{
//            return ['status'=>'false','message'=>'未登录,请先登录!'];
//        }
    }

    //添加地址
    public function addAddress(Request $request)
    {
//        return $request->input();
        //验证
        $validator=Validator::make($request->all(),
            [
                'tel'=>['regex:/^1(3|4|5|7|8)\d{9}$/','required'],
            ],
            [
                'tel.required'=>'手机号码不能为空!',
                'tel.regex'=>'电话号码不合法!'
            ]);
        //验证失败
        if ($validator->fails()){
            //失败获取错误信息
            $errors=$validator->errors();
            //返回错误信息
            return ['status'=>'false','message'=>$errors->first()];
        }

        Address::create(
            [
                'name'=>$request->name,
                'provence'=>$request->provence,
                'city'=>$request->city,
                'area'=>$request->area,
                'detail_address'=>$request->detail_address,
                'tel'=>$request->tel,
                'user_id'=>Auth::user()->id,
            ]
        );
        //添加成功
        return ['status'=>'true','message'=>'添加地址成功!!!'];
    }

    //指定地址
    public function address(Request $request)
    {
        $id=$request->id;
        $address=DB::table('addresses')->where('id',$id)->first();
        return json_encode($address);
    }
    
    //修改地址
    public function editAddress(Request $request)
    {
//        return $request->input();
        //验证
        $validator=Validator::make($request->all(),
            [
                'tel'=>['regex:/^1(3|4|5|7|8)\d{9}$/','required'],
            ],
            [
                'tel.required'=>'手机号码不能为空!',
                'tel.regex'=>'电话号码不合法!'
            ]);

        //验证失败
        if ($validator->fails()){
            //失败获取错误信息
            $errors=$validator->errors();
            //返回错误信息
            return ['status'=>'false','message'=>$errors->first()];
        }

        DB::table('addresses')->where('id',$request->id)->update(
            [
                'name'=>$request->name,
                'provence'=>$request->provence,
                'city'=>$request->city,
                'area'=>$request->area,
                'detail_address'=>$request->detail_address,
                'tel'=>$request->tel,
            ]
        );

        //修改成功
        return ['status'=>'true','message'=>'修改地址成功!!!'];
    }

    //添加到购物车
    public function addCart(Request $request)
    {
//        return $request->input();
        $goodList=$request->input()['goodsList'];
        $goodCount=$request->input()['goodsCount'];
        //在添加到数据表之前把之前的数据删除,增加用户体验
        Cart::where('user_id',Auth::user()->id)->delete();
        $key=0;
        foreach($goodList as $good){
            Cart::create(
                [
                    'user_id'=>Auth::user()->id,
                    'goods_id'=>$good,
                    'count'=>$goodCount[$key],
                ]
            );
            ++$key;
    }
        return ['status'=>'true','message'=>'添加购物车成功!'];
    }

    //购物车查询
    public function cart()
    {
        $carts=Cart::where('user_id',Auth::user()->id)->get();
        $info=[];
//        dd($carts);
        $info['totalCost']=0;
        foreach ($carts as $cart){
            $meal=DB::table('meals')->where('id',$cart->goods_id)->first();
            $meal->goods_id=$meal->id;
            $meal->goods_name=$meal->meal_name;
            $meal->goods_img=$meal->meal_img;
            $meal->amount=$cart->count;
            $meal->goods_price=$meal->meal_price;
            $info['goods_list'][]=$meal;
            $info['totalCost'] += $meal->meal_price*$meal->amount;
        }
            return $info;
    }
    
}
