<?php


namespace app\chat\controller;


use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function find()
    {
        return $this->fetch();
    }

    public function chatlog()
    {
        return $this->fetch();
    }

    public function msgbox()
    {
        return $this->fetch();
    }
}