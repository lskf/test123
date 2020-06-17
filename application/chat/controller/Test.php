<?php


namespace app\chat\controller;


use think\Controller;
use app\chat\logic\User as UserLogic;
use think\Loader;

class Test extends Controller
{
    public function test()
    {
        $user=new UserLogic();
        $res=$user->getUsers();
        return $res;
    }
}