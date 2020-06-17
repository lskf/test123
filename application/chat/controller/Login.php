<?php


namespace app\chat\controller;


use think\Controller;

class Login extends Controller
{
    public function login()
    {
        $username=input("username");
        $password=input("password");
        $whereUser["username"]=$username;
        $whereUser["password"]=$password;
        $userInfo=model("User")->where($whereUser)->find();
        if ($userInfo){
            unset($userInfo["password"]);
            session("user_id",$userInfo["id"]);
            session("userInfo",$userInfo);
            $data["data"]=[];
            $data["msg"]="成功";
            return json($data,200);
        }else{
            $data["data"]=[];
            $data["msg"]="失败";
            return json($data,400);
        }
    }

    public function logout()
    {
        session("user_id",null);
        session("userInfo",null);
        session(null);
        $data["data"]=[];
        $data["msg"]="成功";
        return json($data,200);
    }
}