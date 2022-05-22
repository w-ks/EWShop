<?php

$config = include 'config.php';

class RedisCache
{

    public static $redis = null;

    /**
     * 获取redis对象
     */

    public static function getInstance()
    {
        global $config;

        if (self::$redis == null) {
            self::$redis = new Redis();
            self::$redis->connect($config['redis']['host'],$config['redis']['port']);
            self::$redis->auth($config['redis']['auth']);
        }
        return self::$redis;
    }

    /**
     * 写入缓存
     */
    public static function set($key, $value, $time)
    {
        return self::getInstance()->setex($key, $time, $value);
    }

    /**
     * 读取缓存
     */
    public static function get($key)
    {
        return self::getInstance()->get($key);
    }

    /**
     * 检测缓存是否存在,当输入多个key时,返回存在的key个数
     */
    public static function exist($key)
    {
        return self::get($key) == false ? false : true;
    }

    /**
     * 删除缓存
     */
    public static function delete($key)
    {
        return self::getInstance()->unlink($key);
    }

    function __destruct()
    {
        self::$redis->close();
    }
}


// 缓存数据
$res = RedisCache::set("pingban", "ipad pro", 30);
$res = RedisCache::exist("pingban");

var_dump($res);
