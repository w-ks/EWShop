<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;

class Comment extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'comment');


        parent::__construct();
    }

    /**
     * 评价列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "评价列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        $prosql["ORDER"] = ["comment.atime" => "DESC"];
        $where = [];
        $name = "";
        $content = '';
        $uname = '';


        if (!empty($_GET['content'])) {
            $where['comment.content[~]'] = $_GET['content'];
            $content = '&content=' . $_GET['content'];
        }

        // name = get_name
        if (!empty($_GET['uname'])) {
            $where['comment.uname[~]'] = $_GET['uname'];
            $uname = '&uname=' . $_GET['uname'];
        }

        // name = get_name
        if (!empty($_GET['name'])) {
            $where['product.name[~]'] = $_GET['name'];
            $name = '&name=' . $_GET['name'];
        }


        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count('comment', ['[>]product' => ['pid' => 'id']], '*', $where);  // 总评价个数
        $itemsPerPage = PAGESNUM;     // 每页几个评价
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/comment?num=(:num)'  . $name . $content . $uname;        //url, 例如/admin/comment?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部评价，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_comment` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select(
            "comment",
            ['[>]product' => ['pid' => 'id']],
            ['comment.id', 'comment.pid', 'comment.uid', 'comment.uip', 'comment.atime', 'comment.uname', 'comment.content', 'product.name', 'product.logo'],
            $prosql
        );

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;


        $this->display("comment/index");
    }


    function mod($id)
    {
        $this->assign("title", "恢复评价");
        $db = new BaseDao();
        $data = $db->get(
            "comment",
            ['[>]product' => ['pid' => 'id']],
            ['comment.id', 'comment.pid', 'comment.uid', 'comment.uip', 'comment.atime', 'comment.uname', 'comment.content', 'product.name', 'product.logo'],
            ['comment.id' => $id]
        );

        $this->assign($data);

        $this->display("comment/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if ($db->update("comment", $_POST, ['id' => $id])) {
            $this->success("/admin/comment?state=1", "修改成功！");
        } else {
            $this->error("/admin/comment/reply/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();


        if ($db->delete("comment", ["id" => $id])) {
            $this->success("/admin/comment" , "删除成功");
        } else {
            $this->error("/admin/comment", "删除失败");
        }
    }

    function alldel()
    {

        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->delete("comment", ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/comment", $num . "个评价删除成功！");
        } else {
            $this->error("/admin/comment", "删除失败...");
        }
    }
}
