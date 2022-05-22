<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;
use Slince\Upload\UploadHandlerBuilder;
use pclass\CatTree as CT;

class Product extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'product');

        $db = new BaseDao();
        $data = $db->select("category", ["id", "catname", "pid", "ord"]);

        $tree = CT::getList($data);

        $this->assign("cattree", $tree);

        parent::__construct();
    }

    /**
     * 商品列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "商品列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        // order id desc
        $prosql["ORDER"] = ["id" => "DESC"];
        $where = [];
        $cid = "";
        $name = "";
        $state = "";
        $filter = "";
        $orderby = "";
        if (isset($_GET['state']) && $_GET['state'] != "") {
            $where["state"] = $_GET["state"];
            $state = '&state=' . $_GET["state"];
        }


        // cid = get_cid
        if (!empty($_GET['cid']) && $_GET['cid'] != 0) {
            $where['cid'] = $_GET['cid'];
            $cid = '&cid=' . $_GET['cid'];
        }

        // name = get_name
        if (!empty($_GET['name'])) {
            $where['name[~]'] = $_GET['name'];
            $name = '&name=' . $_GET['name'];
        }

        // 
        if (!empty($_GET['filter']) && $_GET['filter'] != '') {
            list($filed, $value) = explode("|", $_GET['filter']);
            $where[$filed] = $value;
            $filter = '&filter=' . $_GET['filter'];
        }

        if (!empty($_GET['orderby']) && $_GET['orderby'] != '') {
            list($filed, $value) = explode("|", $_GET['orderby']);
            $prosql["ORDER"] = [$filed => strtoupper($value), "id" => "DESC"];
            $orderby = '&orderby=' . $_GET['orderby'];
        } else {
            $prosql["ORDER"] = ["id" => "DESC"];
        }


        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count("product", $where);  // 总商品个数
        $itemsPerPage = PAGESNUM;     // 每页几个商品
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/product?num=(:num)' . $cid . $name . $state . $filter . $orderby;        //url, 例如/admin/product?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部商品，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_product` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select("product", "*", $prosql);

        //过滤的数据
        $filter_arr = ["istuijian|1" => "推荐商品", "istj|1" => "特价商品", "wlmoney|0" => "包邮商品", "num|0" => "售空商品"];
        $this->assign("filter_arr", $filter_arr);

        $orderby_arr['clicknum|desc'] = '浏览量(多到少)';
        $orderby_arr['clicknum|asc'] = '浏览量(少到多)';
        $orderby_arr['sellnum|desc'] = '销售量(多到少)';
        $orderby_arr['sellnum|asc'] = '销售量(少到多)';
        $orderby_arr['num|desc'] = '库存量(多到少)';
        $orderby_arr['num|asc'] = '库存量(少到多)';
        $orderby_arr['collectnum|desc'] = '收藏数(多到少)';
        $orderby_arr['collectnum|asc'] = '收藏数(少到多)';
        $orderby_arr['asknum|desc'] = '咨询数(多到少)';
        $orderby_arr['asknum|asc'] = '咨询数(少到多)';
        $orderby_arr['commentnum|desc'] = '评价数(多到少)';
        $orderby_arr['commentnum|asc'] = '评价数(少到多)';

        $this->assign("orderby_arr", $orderby_arr);

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;

        $this->display("product/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $path = TEMPDIR . "/uploads/product";

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder
                ->allowExtensions(['jpg', 'png', 'jpeg', 'gif'])
                ->allowMimeTypes(['image/*'])
                ->saveTo($path)
                ->getHandler();

            $files = $handler->handle();

            //文件名
            $filename = $files["logo"]->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path . "/" . $filename, $path . "/" . $newfilename);

            unset($_POST['do_submit']);

            $_POST['logo'] = $newfilename;

            $_POST['istj'] = isset($_POST['istj']) ? 1 : 0;


            $db = new BaseDao();

            $_POST['atime'] = empty($_POST["atime"]) ? time() : strtotime($_POST['atime']);

            if ($db->insert('product', $_POST)) {
                $this->success("/admin/product", "添加成功！");
            } else {
                $this->error("/admin/product/add", "添加失败！");
            }
        }

        $this->assign("title", "发布商品");

        $this->display("product/add");
    }


    function mod($num)
    {
        $this->assign("title", "修改商品");
        $db = new BaseDao();

        $this->assign($db->get("product", "*", ['id' => $num]));

        $this->display("product/mod");
    }

    function doupdate()
    {

        if ($_FILES['logo']['error'] == 0) {
            $path = TEMPDIR . "/uploads/product";

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder
                ->allowExtensions(['jpg', 'png', 'jpeg', 'gif'])
                ->allowMimeTypes(['image/*'])
                ->saveTo($path)
                ->getHandler();

            $files = $handler->handle();

            //文件名
            $filename = $files["logo"]->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path . "/" . $filename, $path . "/" . $newfilename);

            $_POST['logo'] = $newfilename;
        }

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        $_POST['atime'] = empty($_POST["atime"]) ? time() : strtotime($_POST['atime']);
        $_POST['istj'] = isset($_POST['istj']) ? 1 : 0;

        if ($db->update("product", $_POST, ['id' => $id])) {
            $this->success("/admin/product", "修改成功！");
        } else {
            $this->error("/admin/product/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        $num = $_SESSION["prosql"]['LIMIT'][0] / 10 + 1;

        $cid = $_SESSION['prosql']['cid'] ?? 0;
        $name = $_SESSION['prosql']['name[~]'] ?? '';

        $page = "?num=" . $num . "&cid=" . $cid . "&name=" . $name;

        if ($db->delete("product", ["id" => $id])) {
            $this->success("/admin/product" . $page, "删除成功");
        } else {
            $this->error("/admin/product", "删除失败");
        }
    }

    function alldel()
    {

        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->delete("product", ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/product", $num . "篇商品删除成功！");
        } else {
            $this->error("/admin/product", "删除失败...");
        }
    }

    function state($state)
    {
        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->update("product", ["state" => $state], ["id" => $id])->rowCount();
        }

        $mess = ["下架", "上架"];

        if ($num > 0) {
            $this->success("/admin/product", $num . "个商品" . $mess[$state] . "成功！");
        } else {
            $this->error("/admin/product", "批量" . $mess[$state] . "失败...");
        }
    }

    function tuijian($tuijian)
    {
        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id) {
            $num += $db->update("product", ["istuijian" => $tuijian], ["id" => $id])->rowCount();
        }

        $mess = ["取消推荐", "批量推荐"];

        if ($num > 0) {
            $this->success("/admin/product", $num . "个商品" . $mess[$tuijian] . "成功！");
        } else {
            $this->error("/admin/product", "批量" . $mess[$tuijian] . "失败...");
        }
    }

}
