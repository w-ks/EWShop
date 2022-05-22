<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;
use Slince\Upload\UploadHandlerBuilder;

class Article extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'article');

        $db = new BaseDao();
        $data = $db->select("class", "*", ["ORDER" => ["ord" => "ASC", "id" => "DESC"]]);
        $this->assign("aclass", $data);

        parent::__construct();
    }

    /**
     * 文章列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "文章列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        // order id desc
        $prosql["ORDER"] = ["id" => "DESC"];
        $where = [];
        $cid = "";
        $name = "";

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

        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count("article", $where);  // 总文章个数
        $itemsPerPage = PAGESNUM;     // 每页几篇文章
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/article?num=(:num)' . $cid . $name;        //url, 例如/admin/article?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部文章，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_article` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select("article", ["id", "name", "atime", "clicknum"], $prosql);

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;


        $this->display("article/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);

            $_POST['atime'] = empty($_POST["atime"])?time():strtotime($_POST['atime']);

            if ($db->insert('article', $_POST)) {
                $this->success("/admin/article", "添加成功！");
            } else {
                $this->error("/admin/article/add", "添加失败！");
            }
        }

        $this->assign("title", "发布文章");

        $this->display("article/add");
    }

    function upload()
    {
        $path = TEMPDIR . "/uploads/article";

        $builder = new UploadHandlerBuilder(); //create a builder.
        $handler = $builder
            ->allowExtensions(['jpg', 'png', 'jpeg', 'gif'])
            ->allowMimeTypes(['image/*'])
            ->saveTo($path)
            ->getHandler();

        $files = $handler->handle();
        //文件名
        $filename = $files["file"]->getUploadedFile()->getClientOriginalName();

        $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['file']->getUploadedFile()->getClientOriginalExtension();

        rename($path . "/" . $filename, $path . "/" . $newfilename);

        $url = getCurURL();

        $arr['src'] = $url . '/uploads/article/' . $newfilename;

        echo json_encode($arr);
    }


    function mod($num)
    {
        $this->assign("title", "修改文章");
        $db = new BaseDao();

        $this->assign($db->get("article", "*", ['id' => $num]));

        $this->display("article/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        $_POST['atime'] = empty($_POST["atime"])?time():strtotime($_POST['atime']);

        if ($db->update("article", $_POST, ['id' => $id])) {
            $this->success("/admin/article", "修改成功！");
        } else {
            $this->error("/admin/article/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        $num = $_SESSION["prosql"]['LIMIT'][0] / 10 + 1;

        $cid = $_SESSION['prosql']['cid'] ?? 0;
        $name = $_SESSION['prosql']['name[~]'] ?? '';

        $page = "?num=" . $num . "&cid=" . $cid . "&name=" . $name;

        if ($db->delete("article", ["id" => $id])) {
            $this->success("/admin/article" . $page, "删除成功");
        } else {
            $this->error("/admin/article", "删除失败");
        }
    }

    function alldel()
    {

        $db = new BaseDao();

        $num = 0;
        foreach ($_POST["id"] as $id ) {
            $num += $db->delete("article", ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/article", $num ."篇文章删除成功！");
        } else {
            $this->error("/admin/article", "删除失败...");
        }
    }
}
