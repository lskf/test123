<?php


namespace app\index\controller;



use GatewayWorker\Lib\Gateway;
use think\Controller;
use think\Db;

class Bind extends Controller
{
    public function bind_item()
    {
        $item_id=input("item_id");
        $client_id=input("client_id");
        $res=Gateway::bindUid($client_id,$item_id);
        return json_encode($res);
    }

    public function send()
    {
        $item_id=input("item_id");
        $content=input("content");
        if (session("user_id")==1){
            $is_admin=1;
        }else{
            $is_admin=0;
        }
        $res=Gateway::sendToUid($item_id,json_encode(["content"=>$content,"is_admin"=>$is_admin]));
        Db::startTrans();
        $time=time();
        $data_item["upda_time"]=$time;
        $res_item=model("Item")->isUpdate(true)->save($data_item,["id"=>$item_id]);
        if ($is_admin==1){
            $data_detail["admin_id"]=1;
        }
        $data_detail["item_id"]=$item_id;
        $data_detail["add_time"]=time();
        $data_detail["content"]=$content;
        $res_detail=model("ItemDetail")->save($data_detail);
        if ($res_item&&$res_detail){
            Db::commit();
        }else{
            Db::rollback();
        }
    }

    public function getCliendIdList()
    {
        $res=Gateway::getAllClientIdList();
        return json_encode($res);
    }
    public function getUIdList()
    {
        $res=Gateway::getAllUidList();
        return json_encode($res);
    }
}