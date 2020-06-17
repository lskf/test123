<?php
namespace app\chat\model;

use think\Model;

class Group extends Model
{
    public function ownerInfo()
    {
        return $this->hasOne("User");
    }
}