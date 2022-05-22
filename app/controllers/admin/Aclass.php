<?php

namespace admin;

use models\BaseDao;

class Aclass extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'aclass');
        parent::__construct();
    }

    /**
     * 文章分类列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "文章分类");

        //获取全部文章分类，并能按ord排序
        $data = $db->select("class", "*", ["ORDER" => ['ord' => 'ASC', "id" => "DESC"]]);

        $this->assign("data", $data);

        $this->display("aclass/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);

            if ($db->insert('class', $_POST)) {
                $this->success("/admin/aclass", "添加成功！");
            } else {
                $this->error("/admin/aclass/add", "添加失败！");
            }
        }

        $this->assign("title", "添加文章分类");

        $this->display("aclass/add");
    }

    function mod($num)
    {
        $this->assign("title", "修改文章分类");
        $db = new BaseDao();

        $this->assign($db->get("class", "*", ['id' => $num]));

        $this->display("aclass/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if ($db->update("class", $_POST, ['id' => $id])) {
            $this->success("/admin/aclass", "修改成功！");
        } else {
            $this->error("/admin/aclass/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        if ($db->delete("class", ["id" => $id])) {
            $this->success("/admin/aclass", "删除成功");
        } else {
            $this->error("/admin/aclass", "删除失败");
        }
    }

    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("class", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if($num>0){
            $this->success("/admin/aclass","重新排序成功");
        }else{
            $this->error("/admin/aclass","重新排序失败");

        }

    }
}
