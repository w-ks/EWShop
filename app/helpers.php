<?php

if (!function_exists("dd")) {
    function dd(...$args)
    {
        http_response_code(500);
        foreach ($args as $x) {
            var_dump($x);
            echo "<br><br>";
        }
        die(1);
    }
}

//得到当前URL，动态获取域名

function getCurURL()
{

    $url = 'http://';

    if (isset($_SERVER["SERVER_HTTPS"]) && $_SERVER["SERVER_HTTPS" == 'on']) {
        $url = 'https://';
    }

    //判断端口
    if ($_SERVER['SERVER_PORT'] != '80') {
        // https://127.0.0.1:8012
        $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER["SERVER_PORT"];
    } else {
        $url .= $_SERVER["SERVER_NAME"];
    }

    return $url;
}


function ew_login($utype)
{
    return @md5($_SESSION["id"] . $_SERVER["HTTP_HOST"]) == @$_SESSION[$utype . "_token"] ? 1 : 0;
}

function getclientip()
{

    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。

    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {

        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {

        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {

        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {

        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $res =  preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';

    return $res;

    //dump(phpinfo());//所有PHP配置信息

}
