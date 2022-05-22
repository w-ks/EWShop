<?php

namespace admin;

use models\BaseDao;

class Page extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'page');
        parent::__construct();
    }

    /**
     * 单页列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "单页");

        //获取全部单页，并能按ord排序
        $data = $db->select("page",["id","name","ord"], ["ORDER" => ['ord' => 'ASC', "id" => "DESC"]]);

        $this->assign("data", $data);
        // dd($this->data);
        $this->display("page/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);

            if ($db->insert('page', $_POST)) {
                $this->success("/admin/page", "添加成功！");
            } else {
                $this->error("/admin/page/add", "添加失败！");
            }
        }

        $this->assign("title", "添加单页");

        $this->display("page/add");
    }

    function mod($num)
    {
        $this->assign("title", "修改单页");
        $db = new BaseDao();

        $data = $db->get("page", "*", ['id' => $num]);

        $this->assign("page", $data);

        $this->display("page/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if ($db->update("page", $_POST, ['id' => $id])) {
            $this->success("/admin/page", "修改成功！");
        } else {
            $this->error("/admin/page/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        if ($db->delete("page", ["id" => $id])) {
            $this->success("/admin/page", "删除成功");
        } else {
            $this->error("/admin/page", "删除失败");
        }
    }

    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("page", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if($num>0){
            $this->success("/admin/page","重新排序成功");
        }else{
            $this->error("/admin/page","重新排序失败");

        }

    }
}
