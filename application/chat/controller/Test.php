<?php


namespace app\chat\controller;


use think\Controller;
use app\chat\model;
use app\chat\logic\User as UserLogic;
use think\Loader;

class Test extends Controller
{
    public function test()
    {
        $userId=session("user_id");
        $userId=2;
        $userLogic=new UserLogic();
        $mine=$userLogic->getUserInfoById($userId);
        $whereGroups["user_id"]=$userId;
        $groups=model("GroupUser")->with("groupInfo")->where($whereGroups)->select();
        return $groups;
//        $whereOwnGroups["user_id"]=$userId;
//        $ownGroups=model("OwnGroup")->where($whereOwnGroups)->select();
//        $list=model("OwnGroup")
    }
}