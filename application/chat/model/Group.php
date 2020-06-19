<?php
namespace app\chat\model;

use think\Model;

class Group extends Model
{
    public function ownerInfo()
    {
        return $this->hasOne("User","id","user_id")->bind("username");
    }

    public function groupUsers()
    {
        return $this->hasMany("GroupUser","group_id","id");
    }
}