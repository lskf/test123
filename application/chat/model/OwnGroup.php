<?php


namespace app\chat\model;


use think\Model;

class OwnGroup extends Model
{
    public function groupUsers()
    {
        return $this->hasMany("OwnGroupUser","group_id","id");
    }
}