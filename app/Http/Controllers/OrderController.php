<?php

namespace App\Http\Controllers;

use App\Address;
use App\Email;
use App\Order_sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //添加订单
    public function addorder(Request $request)
    {
//        return $request;
        $address_id=$request->address_id;
        $address=DB::table('addresses')->where('id',$address_id)->first();

        $cart=DB::table('carts')->where('user_id',Auth::user()->id)->first();
//        dd($cart);
        $goods=DB::table('meals')->where('id',$cart->goods_id)->first();
//        dd($goods);
        $shopinfo=DB::table('shops')->where('id',$goods->shop_id)->first();
//        dd($shopinfo);
        //总金额
        $cost=0;
        //获取到购物车所有的信息
        $cars=DB::table('carts')->where('user_id',Auth::user()->id)->get();
//        dd($cars);
        //循环得到商品id
        foreach ($cars as $car){
            $meal=DB::table('meals')->where('id',$car->goods_id)->first();
            $money=$meal->meal_price*$car->count;
            $cost+=$money;
        }
        //订单编号
        $order_code=mt_rand(0000,9999).time();
        //  开启事务
        DB::transaction(function () use ($shopinfo,$address,$cost,$cars,$order_code){
            //添加数据到orders表
            DB::table('orders')->insert(
                [
                    'order_code'=>$order_code,
                    'order_birth_time'=>date('Y-m-d H:i:s',time()),
                    'shop_id'=>$shopinfo->id,
                    'shop_name'=>$shopinfo->shop_name,
                    'shop_img'=>$shopinfo->shop_img,
                    'province'=>$address->provence,
                    'city'=>$address->city,
                    'area'=>$address->area,
                    'detail_address'=>$address->detail_address,
                    'receiver'=>$address->name,
                    'tel'=>$address->tel,
                    'order_status'=>0,
                    'user_id'=>Auth::user()->id,
                    'order_price'=>$cost,

                ]);
            //得到订单id
            $last_order_id=DB::getPdo()->lastInsertid();

            //添加订单商品
            foreach ($cars as $car){
                $meal=DB::table('meals')->where('id',$car->goods_id)->first();
            DB::table('order_goods')->insert(
                [
                    'order_id'=>$last_order_id,
                    'goods_id'=>$meal->id,
                    'count'=>$car->count,
                    'goods_name'=>$meal->meal_name,
                    'goods_price'=>$meal->meal_price,
                    'goods_img'=>$meal->meal_img,
                ]
            );
            }
        });

        //发送短信
//        dd([$shopinfo->shop_name,$address->tel,$order_code]);

        $name=$shopinfo->shop_name;
        $tel=$address->tel;
        $shop=DB::table('members')->where('shop_id',$shopinfo->id)->first();
        $email=$shop->email;
        //获取订单id
        $order_id=DB::table('orders')->where('order_code',$order_code)->first();
        Order_sms::sendSms($name,$tel,$order_code);
        Email::email($email,$name);
        return [
            'status'=>'true',
            'message'=>'添加订单成功',
            'order_id'=>$order_id->id,
        ];
    }

    //获得指定订单接口
    public function order(Request $request)
    {
//        $order = DB::table('orders')->where('id',)->first();
        $id=$request->id;
        $orders=DB::table('orders')->where('id',$id)->first();
        $ordergoods=DB::table('order_goods')->where('order_id',$id)->get();
        $orders->goods_list=$ordergoods;
        $orders->order_address=$orders->province.$orders->city.$orders->area.$orders->detail_address;
        return json_encode($orders);
    }

    //获得订单列表接口
    public function orderList()
    {
        $orders=DB::table('orders')->where('user_id',Auth::user()->id)->get();

        foreach ($orders as $order){
            $ordergoods=DB::table('order_goods')->where('order_id',$order->id)->get();
//            dd($ordergoods);
            $order->goods_list=$ordergoods;

        }
        return $orders;
    }
}
