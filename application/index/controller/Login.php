<?php


namespace app\index\controller;


use think\Controller;

class Login extends Controller
{
    public function login(){
        $user_name=input("user_name");
        $userInfo=model("User")->where(["username"=>$user_name])->find();
        session("user_id",$userInfo["id"]);
        return "成功";
    }
}