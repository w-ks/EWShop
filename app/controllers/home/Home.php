<?php

namespace home;

use controllers\BaseControl;
use models\BaseDao;

class Home extends BaseControl
{
    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(TEMPDIR . "/app/views/" . TEMPNAME . "/");

        $this->twig = new \Twig\Environment(
            $loader,
            // [ 'cache' => '/path/to/compilation_cache',]
        );

        $this->init();
    }

    protected function display($template)
    {
        $url = getCurURL();

        $this->assign('url', $url . '/app/views/' . TEMPNAME . '/resource');    // 自己模版下的CSS、JS、images
        $this->assign('public', $url . '/app/views/public');    // 所有模版公共的前端CSS、JS、images
        $this->assign('res', $url . '/uploads');    // 文件上传资源

        echo $this->twig->render($template . '.html', $this->data);
    }

    function init()
    {

        $db = new BaseDao();

        //缓存，判断缓存里面有无此数据，


        //
        $this->assign("login", ew_login("user"));
        $this->assign("session", $_SESSION);

        // 获取系统设置信息  setting

        $allsetting = $db->select("setting", "*");

        $setting = array();

        foreach ($allsetting as $v) {
            $setting[$v['skey']] = $v['svalue'];
        }

        $setting["web_qqs"] = explode(",", $setting["web_qq"]);

        // dd($setting);   

        $this->assign("setting", $setting);

        // 获取商品分类信息，制作菜单

        $cats = $db->select("category", ['id', 'catname'], ['pid' => 0, 'ORDER' => ['ord' => 'ASC', 'id' => 'DESC'], "LIMIT" => 10]);
        // dd($cats);
        $this->assign("cats", $cats);

        // 广告

        $this->assign('ads', $db->select('ad', '*', ['ORDER' => ['ord' => 'ASC', 'id' => 'ASC']]));

        // 所有单页信息

        $this->assign('page', $db->select('page', ['id', 'name'], ['ORDER' => ['ord' => 'ASC']]));


        // 友情链接
        $this->assign('links', $db->select('link', "*", ['ORDER' => ['ord' => 'ASC', 'id' => 'ASC'], "LIMIT" => 10]));

        // 用户登录的处理



        // 获取购物车的信息

        if (ew_login('user')) {
            $cart_num = $db->count('cart', ['uid' => $_SESSION['id']]);
        } else {
            if(array_key_exists('cart_list',$_COOKIE)){
                $cart_list = unserialize(stripcslashes($_COOKIE['cart_list']));
                $cart_num = count($cart_list);    
            }else{
                $cart_num = 0;    
            }
            
        }

        $this->assign('cart_num', $cart_num);
        
        // 记录访客的一次访问
        $db->insert("iplog", ['ip' => getclientip(), 'atime' => time()]);

        // 在所有分类下搜索
        $this->assign("pid", 0);
    }
}
