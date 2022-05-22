<?php

namespace admin;

use models\BaseDao;

class AdminUser extends Admin
{

    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'adminuser');
        parent::__construct();
    }

    /**
     * 管理员列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "管理员列表");

        //获取全部管理员，并能按ord排序
        $data = $db->select("admin", "*", ["ORDER" => "id"]);

        $this->assign("data", $data);

        $this->display("adminuser/index");
    }

    function add()
    {

        if (!empty($_POST['name'])) {
            $db = new BaseDao();

            $_POST['atime'] = $_POST["ltime"] = time();
            $_POST['pw'] = md5(md5('ew_' . $_POST["pw"]));

            if ($db->insert('admin', $_POST)) {
                $this->success("/admin/adminuser", "添加成功！");
            } else {
                $this->error("/admin/adminuser/add", "添加失败！");
            }
        }

        $this->assign("title", "添加管理员");

        $this->display("adminuser/add");
    }

    function mod($id)
    {
        $this->assign("title", "修改管理员");
        $db = new BaseDao();

        $data = $db->get("admin", "*", ['id' => $id]);

        $this->assign($data);

        $this->display("adminuser/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        if (!empty($_POST['pw'])) {
            $_POST['pw'] = md5(md5('ew_' . $_POST["pw"]));
        } else {
            unset($_POST['pw']);
        }


        $db = new BaseDao();

        if ($db->update("admin", $_POST, ['id' => $id])) {
            $this->success("/admin/adminuser", "修改成功！");
        } else {
            $this->error("/admin/adminuser/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        if($id==1){
            echo "this is 1";
            $this->error("/admin/adminuser","抱歉，默认管理员是不可以删除的....");
            die(1);
        }

        $db = new BaseDao();

        if ($db->delete("admin", ["id" => $id])) {
            $this->success("/admin/adminuser", "删除成功");
        } else {
            $this->error("/admin/adminuser", "删除失败");
        }
    }
}
