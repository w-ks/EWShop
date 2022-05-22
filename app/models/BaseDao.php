<?php

namespace models;

use Medoo\Medoo;

class BaseDao extends Medoo{


    function __construct()
    {
        $options = [
            'type' => 'mysql',
            'host' => HOST,
            'database' => DBNAME,
            'username' => USER,
            'password' => PASSWD,
            'prefix' => TABPREFIX,
        ];
        //重写父类构造方法，同时为了保证父类构造方法能够执行需要再次调用父类构造方法
        parent::__construct($options);
    }

}





?>