<?php


namespace app\chat\controller;


use GatewayWorker\Lib\Gateway;
use think\Controller;

class Bind extends Controller
{
    /**
     * 绑定client_id
     */
    public function bind_item()
    {
        $user_id=session("user_id");
        $client_id=input("client_id");
        Gateway::bindUid($client_id,$user_id);
    }

    /**
     * 发送信息
     * @param $send_user_id 发送的人的id
     * @param $uid 要发送的uid
     * @param $type  发送信息的分类
     * @param $data  发送的数据
     */
    public function send_message($send_user_id,$uid,$type,$data)
    {
        $data_send["send_user_id"]=$send_user_id;
        $data_send["type"]=$type;
        $data_send["data"]=$data;
        Gateway::sendToUid($uid,json_encode($data_send));
    }
}