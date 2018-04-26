<?php

namespace App\Http\Controllers;

use App\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

}
