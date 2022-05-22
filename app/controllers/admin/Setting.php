<?php

namespace admin;

use models\BaseDao;
use Slince\Upload\UploadHandlerBuilder;

class Setting extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'setting');
        parent::__construct();
    }

    /**
     * 基本信息列表页面
     */
    function index()
    {
        $db = new BaseDao();

        $this->assign("title", "基本信息");

        //获取全部基本信息，并能按ord排序
        $data = $db->select("setting", "*");

        foreach ($data as $v) {
            $this->assign($v['skey'], $v["svalue"]);
        }

        $this->display("setting/mod");
    }

    function doupdate()
    {

        if ($_FILES['web_logo']['error'] == 0) {
            $path = TEMPDIR . "/uploads";

            $builder = new UploadHandlerBuilder();
            $handler = $builder
                ->allowExtensions(['jpg', 'png', 'jpeg', 'gif'])
                ->allowMimeTypes(['image/*'])
                ->saveTo($path)
                ->getHandler();

            $files = $handler->handle();
            //文件名
            $filename = $files["web_logo"]->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['web_logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path . "/" . $filename, $path . "/" . $newfilename);

            $_POST['web_logo'] = $newfilename;

        }
        
        $db = new BaseDao();

        $num=0;

        foreach($_POST as $k => $v){
            $num += $db->update("setting",['svalue'=>$v],["skey"=>$k])->rowCount();
        }

        if($num){
            $this->success("/admin/setting","基本信息设置成功！");
        }else{
            $this->error("/admin/setting","基本信息设置失败！");
        }


    }
}
