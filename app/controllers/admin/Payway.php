<?php

namespace admin;

use models\BaseDao;

class Payway extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'payway');
        parent::__construct();
    }

    /**
     * 支付方式列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "支付方式");

        //获取全部支付方式，并能按ord排序
        $data = $db->select("payway", "*", ["ORDER" => ['ord' => 'ASC', "id" => "ASC"]]);

        $this->assign("data", $data);

        $this->display("payway/index");
    }

    function mod($num)
    {
        $this->assign("title", "修改支付方式");
        $db = new BaseDao();

        $data = $db->get("payway", "*", ['id' => $num]);

        $this->assign("qt", ['1'=>'启用','0'=>'禁用']);

        $this->assign("payway", $data);
        // dd($this->data);
        $this->display("payway/mod");
    }

    function doupdate()
    {
        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if ($db->update("payway", $_POST, ['id' => $id])) {
            $this->success("/admin/payway", "修改成功！");
        } else {
            $this->error("/admin/payway/mod/" . $id, "修改失败！");
        }
    }


    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("payway", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if($num>0){
            $this->success("/admin/payway","重新排序成功");
        }else{
            $this->error("/admin/payway","重新排序失败");

        }

    }
}
