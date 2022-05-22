<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;

class User extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'user');

        parent::__construct();
    }

    /**
     * 用户列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "用户列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        // order id desc
        $prosql["ORDER"] = ["id" => "DESC"];
        $where = [];
        $name = "";
        $phone = '';
        $email = '';
        $orderby = '';

        //排序
        if (!empty($_GET['orderby'])) {
            $prosql["ORDER"] = [$_GET['orderby'] => 'DESC'];
            $orderby = '&orderby=' . $_GET['orderby'];
        }

        // name = get_name
        if (!empty($_GET['phone'])) {
            $where['phone[~]'] = $_GET['phone'];
            $phone = '&phone=' . $_GET['phone'];
        }

        // name = get_name
        if (!empty($_GET['email'])) {
            $where['email[~]'] = $_GET['email'];
            $email = '&email=' . $_GET['email'];
        }

        // name = get_name
        if (!empty($_GET['name'])) {
            $where['name[~]'] = $_GET['name'];
            $name = '&name=' . $_GET['name'];
        }


        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count("user", $where);  // 总用户个数
        $itemsPerPage = PAGESNUM;     // 每页几个用户
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/user?num=(:num)' . $name . $phone . $email . $orderby;        //url, 例如/admin/user?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部用户，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_user` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select("user", '*', $prosql);

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;


        $this->display("user/index");
    }


    function mod($num)
    {
        $this->assign("title", "修改用户");
        $db = new BaseDao();

        $this->assign($db->get("user", "*", ['id' => $num]));

        $this->display("user/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if(!empty($_POST['pw'])){
            $_POST['pw']=md5(md5('ew_'.$_POST['pw']));
        }else{
            unset($_POST['pw']);
        }

        $_POST['atime'] = empty($_POST["atime"]) ? time() : strtotime($_POST['atime']);

        if ($db->update("user", $_POST, ['id' => $id])) {
            $this->success("/admin/user", "修改成功！");
        } else {
            $this->error("/admin/user/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        if ($db->delete("user", ["id" => $id])) {
            $this->success("/admin/user" , "删除成功");
        } else {
            $this->error("/admin/user", "删除失败");
        }
    }

    function alldel()
    {

        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->delete("user", ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/user", $num . "个用户删除成功！");
        } else {
            $this->error("/admin/user", "删除失败...");
        }
    }
}
