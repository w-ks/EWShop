<?php

namespace admin;

use models\BaseDao;

class Link extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'link');
        parent::__construct();
    }

    /**
     * 友情链接列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "友情链接");

        //获取全部友情链接，并能按ord排序
        $data = $db->select("link", "*", ["ORDER" => ['ord' => 'ASC', "id" => "DESC"]]);

        $this->assign("data", $data);

        $this->display("link/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);

            if ($db->insert('link', $_POST)) {
                $this->success("/admin/link", "添加成功！");
            } else {
                $this->error("/admin/link/add", "添加失败！");
            }
        }

        $this->assign("title", "添加友情链接");

        $this->display("link/add");
    }

    function mod($num)
    {
        $this->assign("title", "修改友情链接");
        $db = new BaseDao();

        $data = $db->get("link", "*", ['id' => $num]);

        $this->assign("link", $data);

        $this->display("link/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if ($db->update("link", $_POST, ['id' => $id])) {
            $this->success("/admin/link", "修改成功！");
        } else {
            $this->error("/admin/link/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        if ($db->delete("link", ["id" => $id])) {
            $this->success("/admin/link", "删除成功");
        } else {
            $this->error("/admin/link", "删除失败");
        }
    }

    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("link", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if($num>0){
            $this->success("/admin/link","重新排序成功");
        }else{
            $this->error("/admin/link","重新排序失败");

        }

    }
}
