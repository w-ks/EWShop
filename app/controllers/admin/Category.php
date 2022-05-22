<?php

namespace admin;

use models\BaseDao;

use pclass\CatTree as CT;


class Category extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'category');

        $db = new BaseDao();

        //获取全部商品分类，并能按ord排序
        $data = $db->select("category", ["id","catname","pid","ord"]);

        $tree = CT::getList($data);

        $this->assign("cattree", $tree);

        parent::__construct();
    }

    /**
     * 商品分类列表页面
     */
    function index()
    {
        // dd($this->data);

        $this->assign("title", "商品分类");

        $this->display("category/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);
            // dd($this->data);

            if ($db->insert('category', $_POST)) {
                $this->success("/admin/category", "添加成功！");
            } else {
                $this->error("/admin/category/add", "添加失败！");
            }
        }

        $this->assign("title", "添加商品分类");

        $this->display("category/add");
    }

    function mod($num)
    {
        $this->assign("title", "修改商品分类");

        $this->assign("childs",$_GET['childs']);

        $db = new BaseDao();

        $this->assign($db->get("category", "*", ['id' => $num]));

        $this->display("category/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $_POST['childs'] .= "," .$id;

        if(in_array($_POST['pid'],explode(",",$_POST["childs"]))){
            $this->error("/admin/category","不能将分类修改到自己或自己的子类中...");
            exit;
        }
        
        $db = new BaseDao();

        unset($_POST['childs']);
        if ($db->update("category", $_POST, ['id' => $id])) {
            $this->success("/admin/category", "修改成功！");
        } else {
            $this->error("/admin/category/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        $num = $db->count("category",['pid'=>$id]);

        if($num>0){
            $this->error("/admin/category","删除失败，不能删除非空分类。。。");
            exit;
        }

        if ($db->delete("category", ["id" => $id])) {
            $this->success("/admin/category", "删除成功");
        } else {
            $this->error("/admin/category", "删除失败");
        }
    }

    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("category", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/category", "重新排序成功");
        } else {
            $this->error("/admin/category", "重新排序失败");
        }
    }
}
