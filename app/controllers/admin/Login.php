<?php

namespace admin;

use models\BaseDao;
use Gregwar\Captcha\CaptchaBuilder;

class Login extends Admin
{

    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(TEMPDIR . "/app/views/admin/");

        $this->twig = new \Twig\Environment(
            $loader,
            // [ 'cache' => '/path/to/compilation_cache',]
        );

        $this->assign("session",$_SESSION);

    }

    function index()
    {
        $this->display("login/index");
    }

    function vcode()
    {
        $builder = new CaptchaBuilder;
        $builder->build();

        $_SESSION['code'] = strtoupper($builder->getPhrase());

        header('Content-type: image/jpeg');
        $builder->output();
    }

    function dologin()
    {
        
        if (strtoupper($_POST['code']) != $_SESSION['code']) {
            $this->error("/admin/login","验证码输入有误....");
            exit;
        }

        $name = $_POST['name'];

        $pw = md5(md5('ew_'.$_POST['pw']));

        $db = new BaseDao;

        $user = $db->get("admin",['id','name'],['name'=>$name,'pw'=>$pw]);

        if($user){
            $db->update("admin",["ltime"=>time()],["id"=>$user["id"]]);
            $_SESSION = $user;

            $_SESSION["admin_token"] = md5($user["id"].$_SERVER["HTTP_HOST"]);

            $this->success("/admin","用户登陆成功");

        }else{
            $this->error("/admin/login","用户名或密码有误....");
        }

    }

    function logout(){

        $_SESSION=array();

        if(isset($_COOKIE[session_name()])){
            setcookie(session_name(),"",time()-3600,"/");
        }

        session_destroy();

        $this->success("/admin/login","管理员退出");

    }



}
