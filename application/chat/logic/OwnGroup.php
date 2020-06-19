<?php

namespace app\chat\logic;
use app\chat\model;

class OwnGroup
{
    /**
     * 根据名称获取我的分组id
     * @param $groupName    我的分组名称
     * @param $ownUserId    分组所属人id
     * @param bool $type    类型（如果不存在分组，是否创建，true创建，false不创建）
     * @return int|mixed
     */
    public function getIdByName($groupName,$ownUserId,$type=false)
    {
        $whereOwnGroup["group_name"]=$groupName;
        $whereOwnGroup["user_id"]=$ownUserId;
        $ownGroupInfo=model("OwnGroup")->where($whereOwnGroup)->find();
        if ($ownGroupInfo){
            $ownGroupId=$ownGroupInfo->id;
        }else{
            if ($type){
                $dataOwnGroup["user_id"]=$ownUserId;
                $dataOwnGroup["group_name"]=$groupName;
                $dataOwnGroup["create_time"]=time();
                $resOwnGroup=model("OwnGroup")->isUpdate(false)->save($dataOwnGroup);
                if ($resOwnGroup){
                    $ownGroupId=model("OwnGroup")->id;
                }else{
                    $ownGroupId=0;
                }
            }else{
                $ownGroupId=0;
            }
        }
        return $ownGroupId;
    }

    public function getUserById($groupId)
    {
        $lists=model("OwnGroupUser")->where(["group_id"=>$groupId])->select();
        return $lists;
    }

    public function getGroupsByUserId($userId)
    {
        $lists=model("OwnGroup")->where(["user_id"=>$userId])->select();
        return $lists;
    }
}