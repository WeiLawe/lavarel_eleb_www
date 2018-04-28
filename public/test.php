<?php
$email="9122198@qq.com";
$result = preg_match('/^[\w\-\.]+@[\w\-]+(\.\w+)+$/', $email);
var_dump($result);

echo date('Y-m-d',strtotime('-1 day'));


