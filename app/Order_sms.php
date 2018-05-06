<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_sms extends Model
{
    //发送短信
    public static function sendSms($name,$tel,$order_code)
    {
        $params = array();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAI3DqhEd5hqwsu";
        $accessKeySecret = "UlU3TruoxjeIk5uJdGqDwnTXb4sCjr";

        // fixme 必填: 短信接收号码


//        $tel=;
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "仰虹怀瑾之味";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_133979477";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项

        $params['TemplateParam'] = Array(
            "name" =>$name.$order_code ,
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
//        if ($content->Message == 'OK'){
//            //发送成功
//            echo '{
//              "status": "true",
//              "message": "发送短信成功"
//                 }';
//            return ['status'=>'true','message'=>'发送短信成功'];
//        }else{
//            //发送失败
//            echo '{
//              "status": "false",
//              "message": "发送短信失败,请检查电话号码稍后再试!"
//                }';
//            return ['status'=>'false','message'=>'发送短信失败,请检查电话号码稍后再试!'];
//        }
    }
}
