<?php

// 代码永远执行
// set_time_limit(0);   

//构建
$redis = new Redis();
$redis->connect("localhost");
$redis->auth("123456");

$key = 'question';

//PDO模型
$pdo = new PDO('mysql:host=localhost;dbname=ew_shop;charset=utf8', 'root', '123456');

//如果队列中存在数据
while (1) {
    if ($redis->lLen($key) > 0) {
        // 读取数据
        $data = $redis->rPop($key);
        //数据转换
        $arr = unserialize($data);
        // 插入数据 预处理占位
        $stmt = $pdo->prepare('insert into users (name,passwd,email) values(:username,:password,:email)');

        $arr['password']= md5(md5('ew_'.$arr['password']));
        
        //执行
        $stmt->execute($arr);

        //查看错误信息
        // var_dump($stmt->errorInfo());die;

        //获取受影响的行数
        $res = $stmt->rowCount();

        var_dump($res);
    } else {
        sleep(5);
    }
}
