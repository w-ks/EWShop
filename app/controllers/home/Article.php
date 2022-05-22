<?php

namespace home;

use models\BaseDao;
use JasonGrimes\Paginator;

class Article extends Home
{
    function index($id)
    {
        $db = new BaseDao;


        $article = $db->get("article", "*", ["id" => $id]);

        $class = $db->select("class", "*", ['ORDER' => ['ord' => 'ASC', 'id' => 'ASC']]);
        $this->assign("class", $class);

        $classes = array();

        foreach ($class as $v) {
            $classes[$v['id']] = $v['catname'];
        }

        $this->assign("nowpath", " &gt; 咨询中心 &gt; <a href='/alist/{$article['cid']}'>{$classes[$article['cid']]}</a> &gt; " . $article['name']);

        $this->assign('cname', $classes[$article['cid']]);
        $this->assign('title', $article['name']);

        $this->assign($article);

        // 获取相关文章，即同分类下的文章
        $data = $db->select("article", ['id', 'name', 'atime', 'clicknum'],['cid'=>$article['cid'],'LIMIT'=>6]);

        $this->assign("data",$data);

        $this->display('article/index');

        $db->update("article",['clicknum[+]'=>1],['id'=>$id]);

    }

    function alist($cid = 0)
    {
        $db = new BaseDao;

        $class = $db->select("class", "*", ['ORDER' => ['ord' => 'ASC', 'id' => 'ASC']]);
        $this->assign("class", $class);

        $classes = array();

        foreach ($class as $v) {
            $classes[$v['id']] = $v['catname'];
        }

        $this->assign("nowpath", " &gt; 咨询中心 &gt; <a href='/alist/{$cid}'>{$classes[$cid]}</a>");

        $this->assign("cname", $classes[$cid]);

        $num = $_GET['num'] ?? 1;

        //where 搜索条件

        if ($cid != 0) {
            $where['cid'] = $cid;
        }


        //分页
        $totalItems = $db->count("article", $where);  // 总商品个数
        $itemsPerPage = PAGESNUM;     // 每页几个商品
        $currentPage = $num;          // 当前页
        $urlPattern = '/alist/' . $cid . '?num=(:num)';

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $start = ($currentPage - 1) * $itemsPerPage;
        $where['LIMIT'] = [$start, $itemsPerPage];

        //获取全部文章，条件查询
        $data = $db->select("article", ['id', 'name', 'atime', 'clicknum'], $where);

        $this->assign("fpage", $paginator);
        $this->assign("data", $data);

        $this->assign("title", "咨询中心");

        $this->display("article/alist");
    }
}
