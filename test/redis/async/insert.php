<?php

//构建
$redis = new Redis();
$redis->connect("localhost");
$redis->auth("123456");

// 转成字符串
$str = serialize($_POST);

$key = "question";

$res = $redis->lPush($key, $str);

if ($res) {
    echo "我们已经收到您的信息了";
}

