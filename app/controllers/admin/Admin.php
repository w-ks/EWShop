<?php

namespace admin;

use controllers\BaseControl;

class Admin extends BaseControl
{

    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(TEMPDIR . "/app/views/admin/");

        $this->twig = new \Twig\Environment(
            $loader,
            // [ 'cache' => '/path/to/compilation_cache',]
        );

        $this->assign("session", $_SESSION);

        if(!ew_login("admin")){
            $this->error("/admin/login","你还没有登录，请先登录....");
        }
        
    }

    protected function display($template)
    {
        $url = getCurURL();

        $this->assign('url', $url . '/app/views/admin/resource');    // 自己模版下的CSS、JS、images
        $this->assign('public', $url . '/app/views/public');    // 所有模版公共的前端CSS、JS、images
        $this->assign('res', $url . '/uploads');    // 文件上传资源

        echo $this->twig->render($template . '.html', $this->data);
    }
}
