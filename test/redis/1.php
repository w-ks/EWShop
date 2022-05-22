<?php

//构建
$redis = new Redis();
$redis->connect("localhost");
$redis->auth("123456");

//字符串操作
$a = $redis->set('name1','wuks1 abfasfa');
// Will redirect, and actually make an SETEX call

// 删除

$res = $redis->unlink('name1');

$redis->del('x', 'y');

$redis->lPush('x', 'abc');
$redis->lPush('x', 'def');
$redis->lPush('y', '123');
$redis->lPush('y', '456');

// move the last of x to the front of y.
var_dump($redis->rPopLPush('x', 'y'));
var_dump($redis->lRange('x', 0, -1));
var_dump($redis->lRange('y', 0, -1));


$count = $redis->dbSize();
echo "Redis has $count keys\n";

var_dump($redis->info());

$redis->close();