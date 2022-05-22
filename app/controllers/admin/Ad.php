<?php

namespace admin;

use models\BaseDao;
use Slince\Upload\UploadHandlerBuilder;

class Ad extends Admin
{
    function __construct()
    {
        //为了实现点击哪个菜单，哪个侧边栏亮，使用标记位
        $this->assign("menumark", 'ad');
        $this->assign("allposition", ['1' => '首页顶部广告(980*80)', '2' => '首页底部广告(980*80)', '3' => '所有页面顶部广告(980*80)', '4' => '所有页面底部广告(980*80)', '5' => '首页轮播图广告(730*300)']);
        parent::__construct();
    }

    /**
     * 广告列表页面
     */
    function index()
    {

        $db = new BaseDao();

        $this->assign("title", "广告列表");

        //获取全部广告，并能按ord排序
        $data = $db->select("ad", "*", ["ORDER" => ['ord' => 'ASC', "id" => "ASC"]]);

        $this->assign("data", $data);

        $this->display("ad/index");
    }

    function add()
    {

        if (isset($_POST['do_submit'])) {

            $path= TEMPDIR."/uploads/ad";

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder
                ->allowExtensions(['jpg', 'png','jpeg','gif'])
                ->allowMimeTypes(['image/*'])
                ->saveTo($path)
                ->getHandler();

            $files = $handler->handle();
            //文件名
            $filename = $files["logo"]->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path . "/" . $filename,$path . "/" . $newfilename);

            $db = new BaseDao();

            unset($_POST['do_submit']);

            $_POST['logo'] = $newfilename;

            if ($db->insert('ad', $_POST)) {
                $this->success("/admin/ad", "添加成功！");
            } else {
                $this->error("/admin/ad/add", "添加失败！");
            }
        }

        $this->assign("title", "添加广告");

        $this->display("ad/add");
    }

    function mod($num)
    {
        $this->assign("title", "修改广告");
        $db = new BaseDao();

        $data = $db->get("ad", "*", ['id' => $num]);

        $this->assign("ad", $data);

        $this->display("ad/mod");
    }

    function doupdate()
    {

        $id = $_POST['id'];
        unset($_POST['id']);

        $db = new BaseDao();

        if($_FILES['logo']['error']==0){
            $path= TEMPDIR."/uploads/ad";

            $builder = new UploadHandlerBuilder(); 
            $handler = $builder
                ->allowExtensions(['jpg', 'png','jpeg','gif'])
                ->allowMimeTypes(['image/*'])
                ->saveTo($path)
                ->getHandler();

            $files = $handler->handle();
            //文件名
            $filename = $files["logo"]->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path . "/" . $filename,$path . "/" . $newfilename);

            $_POST['logo']=$newfilename;

        }

        
        if ($db->update("ad", $_POST, ['id' => $id])) {
            $this->success("/admin/ad", "修改成功！");
        } else {
            $this->error("/admin/ad/mod/" . $id, "修改失败！");
        }
    }

    function del($id)
    {
        $db = new BaseDao();

        $logo = $db -> get("ad",'logo',['id'=>$id]);

        if ($db->delete("ad", ["id" => $id])) {
            $path = TEMPDIR ."/uploads/ad";

            @unlink($path . "/" .$logo);

            $this->success("/admin/ad", "删除成功");
        } else {
            $this->error("/admin/ad", "删除失败");
        }
    }

    function order()
    {

        $db = new BaseDao();
        $num = 0;
        foreach ($_POST["ord"] as $id => $ord) {
            $num += $db->update("ad", ["ord" => $ord], ["id" => $id])->rowCount();
        }

        if ($num > 0) {
            $this->success("/admin/ad", "重新排序成功");
        } else {
            $this->error("/admin/ad", "重新排序失败");
        }
    }
}
