<?php


namespace app\chat\model;


use think\Model;

class GroupUser extends Model
{
    /**
     * 所属群聊
     * @return \think\model\relation\BelongsTo
     */
    public function groupInfo()
    {
        return $this->belongsTo("Group","id","group_id");
    }
}