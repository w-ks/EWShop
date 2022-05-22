<?php

namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;

class Order extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'order');

        parent::__construct();
    }

    /**
     * 订单列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "订单列表");

        //如果不存在$_GET['num'], $num=1
        $num = $_GET['num'] ?? 1;

        //where 搜索条件
        // order id desc
        $prosql["ORDER"] = ["id" => "DESC"];
        $where = [];
        $utname = "";
        $uphone = '';
        $id = '';

        $state = "";

        if (isset($_GET['state']) && $_GET['state'] != "") {
            $where["state"] = $_GET["state"];
            $state = '&state=' . $_GET["state"];
        }

        if (!empty($_GET['utname'])) {
            $where['utname[~]'] = $_GET['utname'];
            $utname = '&utname=' . $_GET['utname'];
        }

        if (!empty($_GET['uphone'])) {
            $where['uphone[~]'] = $_GET['uphone'];
            $uphone = '&uphone=' . $_GET['uphone'];
        }
        if (!empty($_GET['id'])) {
            $where['id[~]'] = $_GET['id'];
            $id = '&id=' . $_GET['id'];
        }



        $this->assign("get", $_GET);

        //分页
        $totalItems = $db->count('order', $where);  // 总订单个数
        $itemsPerPage = PAGESNUM;     // 每页几个订单
        $currentPage = $num;          // 当前页
        $urlPattern = '/admin/order?num=(:num)' . $state . $utname . $uphone . $id;        //url, 例如/admin/order?num=2&cid=1&name=细说PHP

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);

        //获取全部订单，条件查询，
        //例如SELECT `id`,`name`,`atime`,`clicknum` FROM `ew_order` WHERE `cid` = '1' AND (`name` LIKE '%细说PHP%') ORDER BY `id` DESC LIMIT 10 OFFSET 10
        $data = $db->select("order", "*", $prosql);


        foreach ($data as $k => $v) {
            $data[$k]['orderdata'] = $db->select('orderdata', '*', ['oid' => $v['id']]);
        }

        $this->assign('paywayarr', ['1' => '支付宝', '2' => '转账付款', '3' => '货到付款']);


        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        // 将prosql传给session,方便删除时回到当前页面,而不是主页
        $_SESSION["prosql"] = $prosql;


        $this->display("order/index");
    }

    function state($state)
    {
        $db = new BaseDao();
        $order_id = $_GET['id'];

        switch ($state) {
                //待发货
            case '2':
                $order = $db->get("order", "*", ['id' => $order_id]);

                //如果支付方式是3货到付款，直接到4，否则是2确认付款
                $_POST['state'] = $order['payway'] == '3' ? '4' : '2';

                $_POST['ptime'] = time();

                if ($db->update('order', $_POST, ['id' => $order_id])) {
                    $this->success("/admin/order/mod/" . $order_id, "订单付款成功");
                } else {
                    $this->error("/admin/order/mod/" . $order_id, "订单付款失败...");
                }

                break;
                //已发货                
            case '3':
                if (isset($_POST['do_submit'])) {
                    $order = $db->get("order", "*", ['id' => $order_id]);
                    $_POST['stime'] = time();

                    if ($order['payway'] == '3') {
                        $_POST['state'] = 3;
                    } else {
                        $_POST['state'] = 4;
                    }

                    unset($_POST['do_submit']);


                    if ($db->update('order', $_POST, ['id' => $order_id])) {
                        $orderdata = $db->select('orderdata', ['id', 'pid', 'pnum'], ['oid' => $order_id]);

                        foreach ($orderdata as $v) {
                            $db->update('product', ['sellnum[+]' => $v['pnum']], ['id' => $v['pid']]);
                        }
                        $this->topsuccess("/admin/order", "发货成功");
                    } else {
                        $this->error("/admin/order", "发货失败...");
                    }
                }


                $this->assign('order_id', $order_id);
                $this->assign('wllist', ['顺丰快递', '申通快递', '中通快递', '圆通快递', '京东物流', 'EMS快递']);
                $this->display('order/send');

                break;
        }
    }



    function mod($order_id)
    {
        $this->assign("title", "订单详情修改");
        $db = new BaseDao();
        $info = $db->get('order', '*', ['id' => $order_id]);
        $product_list = $db->select('orderdata', '*', ['oid' => $order_id]);

        $this->assign($info);
        $this->assign('product_list', $product_list);
        $this->assign('paywayarr', ['1' => '支付宝', '2' => '转账付款', '3' => '货到付款']);

        $tmpay = $db->select('payway', '*', ['state' => 1, 'ORDER' => ['ord' => "ASC", 'id' => 'ASC']]);

        $payway = [];

        foreach ($tmpay as $v) {
            $payway[$v['mark']] = $v;
        }

        $this->assign('cache_payway', $payway);


        $this->display("order/mod");
    }

    function doupdate()
    {

        $order_id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        $_POST['money'] = $_POST['productmoney'] + $_POST['wlmoney'];

        unset($_POST['do_submit']);

        if ($db->update("order", $_POST, ['id' => $order_id])) {
            $this->success("/admin/order/mod/" . $order_id, "订单修改成功！");
        } else {
            $this->error("/admin/order/mod/" . $order_id, "订单修改失败！");
        }
    }

    function del($order_id)
    {
        $db = new BaseDao();

        //删除订单
        if ($db->delete("order", ["id" => $order_id])) {
            $orderdata = $db->select('orderdata', ['id', 'pid', 'pnum'], ['oid' => $order_id]);

            //库存加回去
            foreach ($orderdata as $v) {
                $db->update('product', ['num[+]' => $v['pnum']], ['id' => $v['pid']]);
            }

            //删除订单详情表对应的数据
            $db->delete('orderdata', ['oid' => $order_id]);


            $this->success("/admin/order", "删除成功");
        } else {
            $this->error("/admin/order", "删除失败");
        }
    }
}
