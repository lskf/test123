<?php
namespace app\index\controller;

use think\App;
use think\Controller;

class Index extends Controller
{

    public function index()
    {
        $item_list =model("Item")->select();
        $this->assign("item_list",$item_list);
        return $this->fetch();
    }

    public function detail()
    {
        $item_id=input("item_id");
        $item_detail=model("ItemDetail")->where(["item_id"=>$item_id])->order("add_time asc")->select();
        $this->assign("item_id",$item_id);
        $this->assign("item_detail",$item_detail);
        $this->assign("user_id",session("user_id"));
        $this->assign("to_id",session("to_id"));
        return $this->fetch();
    }


}
