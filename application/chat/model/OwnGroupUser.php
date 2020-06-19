<?php


namespace app\chat\model;


use think\Model;

class OwnGroupUser extends Model
{
    public function ownGroupInfo()
    {
        return $this->belongsTo("OwnGroup","id","group_id");
    }
}