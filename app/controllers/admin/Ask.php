<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;

class Ask extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'ask');

        parent::__construct();
    }

    /**
     * 咨询列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "咨询列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        // order id desc
        $prosql["ORDER"] = ["id" => "DESC"];
        $where = [];
        $name = "";
        $asktext = '';
        $uname = '';

        $state = "";

        if (isset($_GET['state']) && $_GET['state'] != "") {
            $where["ask.state"] = $_GET["state"];
            $state = '&state=' . $_GET["state"];
        }


        if (!empty($_GET['asktext'])) {
            $where['ask.asktext[~]'] = $_GET['asktext'];
            $asktext = '&asktext=' . $_GET['asktext'];
        }

        // name = get_name
        if (!empty($_GET['uname'])) {
            $where['ask.uname[~]'] = $_GET['uname'];
            $uname = '&uname=' . $_GET['uname'];
        }

        // name = get_name
        if (!empty($_GET['name'])) {
            $where['product.name[~]'] = $_GET['name'];
            $name = '&name=' . $_GET['name'];
        }


        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count('ask', ['[>]product' => ['pid' => 'id']], '*', $where);  // 总咨询个数
        $itemsPerPage = PAGESNUM;     // 每页几个咨询
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/ask?num=(:num)' . $state . $name . $asktext . $uname;        //url, 例如/admin/ask?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部咨询，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_ask` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select(
            "ask",
            ['[>]product' => ['pid' => 'id']],
            ['ask.id', 'ask.pid', 'ask.uid', 'ask.uip', 'ask.atime', 'ask.uname', 'ask.replytext', 'ask.replytime', 'ask.state', 'ask.asktext', 'product.name', 'product.logo'],
            $prosql
        );

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;


        $this->display("ask/index");
    }


    function reply($id)
    {
        $this->assign("title", "恢复咨询");
        $db = new BaseDao();
        $data = $db->get(
            "ask",
            ['[>]product' => ['pid' => 'id']],
            ['ask.id', 'ask.pid', 'ask.uid', 'ask.uip', 'ask.atime', 'ask.uname', 'ask.replytext', 'ask.replytime', 'ask.state', 'ask.asktext', 'product.name', 'product.logo'],
            ['ask.id' => $id]
        );

        $this->assign($data);

        $this->display("ask/reply");
    }

    function doreply()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if (!empty($_POST['replytext'])) {
            $_POST['replytime'] = time();
            $_POST['state'] = 1;
        } else {
            $_POST['replytime'] =  $_POST['state'] = 0;
        }

        $_POST['atime'] = empty($_POST["atime"]) ? time() : strtotime($_POST['atime']);

        if ($db->update("ask", $_POST, ['id' => $id])) {
            $this->success("/admin/ask?state=1", "回复成功！");
        } else {
            $this->error("/admin/ask/reply/" . $id, "回复失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        if ($db->delete("ask", ["id" => $id])) {
            $this->success("/admin/ask" , "删除成功");
        } else {
            $this->error("/admin/ask", "删除失败");
        }
    }

    function alldel()
    {

        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->delete("ask", ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/ask", $num . "个咨询删除成功！");
        } else {
            $this->error("/admin/ask", "删除失败...");
        }
    }
}
