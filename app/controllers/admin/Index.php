<?php

namespace admin;

use Medoo\Medoo;
use models\BaseDao;

class Index extends Admin
{
    function __construct()
    {   
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        // $this->assign("menumark",'product');
        parent::__construct();
    }




    function index(){

        $db=new BaseDao();

        $this->assign("title","后台首页");
        
        //商品的统计
        //上架的商品
        $tongji["product_up"] = $db->count("product",["state"=>1]);
        //下架的商品
        $tongji["product_down"] = $db->count("product",["state"=>0]);
        //缺货的商品
        $tongji["product_empty"] = $db->count("product",["state"=>1,'num[<]'=>1]);
        //推荐的商品
        $tongji["product_recommend"] = $db->count("product",["state"=>1,'istuijian'=>1]);

        //访客统计
        $today = strtotime(date("Y-m-d"));
        $yesterday = strtotime(date("Y-m-d",strtotime('-1 day')));


        //今日访客
        $tongji['iplog_today']=$db->count("iplog",['atime[>=]'=>$today]);

        //昨日访客
        $tongji['iplog_yesterday']=$db->count("iplog",['atime[>=]'=>$yesterday,'atime[<=]'=>$today]);

        //累积的访客
        $tongji['iplog_all']=$db->count("iplog");

        //所有注册 
        $tongji['iplog_user']=$db->count("user");

        //交易数据
        // 今日订单
        $tongji['order_today']=$db->count("order",['atime[>=]'=>$today]);
        
        $money_today = $db->select("order",["productmoney"=>Medoo::raw("IFNULL(SUM(`productmoney`),0)") ],['atime[>=]'=>$today]);
        // dd($money_today);
        $tongji['money_today']= number_format(floatval($money_today[0]["productmoney"]),2,'.','');   
        
        
        // 昨日订单
        $tongji['order_yesterday']=$db->count("order",['atime[>=]'=>$yesterday,'atime[<=]'=>$today]);

        $money_yesterday = $db->select("order",["productmoney"=>Medoo::raw("IFNULL(SUM(`productmoney`),0)") ],['atime[>=]'=>$yesterday,'atime[<=]'=>$today]);

        $tongji['money_yesterday']= number_format(floatval($money_yesterday[0]["productmoney"]),2,'.','');   
        
        // 全部订单
        $tongji['order_all']=$db->count("order");

        $money_all = $db->select("order",["productmoney"=>Medoo::raw("IFNULL(SUM(`productmoney`),0)") ]);

        $tongji['money_all']= number_format(floatval($money_all[0]["productmoney"]),2,'.','');   


        $this->assign($tongji);
        
        $this->display("index/index");
    }

}