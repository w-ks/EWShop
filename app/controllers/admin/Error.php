<?php

namespace admin;

class Error extends Admin
{
    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(TEMPDIR . "/app/views/admin/");

        $this->twig = new \Twig\Environment(
            $loader,
            // [ 'cache' => '/path/to/compilation_cache',]
        );

        $this->assign("session", $_SESSION);

    }
    function notfound(){
        $this->display("adminuser/notfound");
    }
}

