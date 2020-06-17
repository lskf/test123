<?php


namespace app\chat\logic;

use think\db\Where;

class User
{
    /**
     * 获取用户列表
     * @param array $where      条件
     * @param int $pageId       页码
     * @param int $pageCount    一页的数量
     * @param string $order     排序
     * @return json
     */
    public function getUsers($where=[],$pageId=1,$pageCount=10,$order="")
    {
        $resUsers=model("User")->where($where)->hidden(["password"])->order($order)->page($pageId)->limit($pageCount)->select();
        return $resUsers;
    }

    /**
     * 获取数据条数
     * @param array $where      条件
     * @return float|int|string
     */
    public function getUsersCount($where=[])
    {
        $userCount=model("User")->where($where)->count();
        return $userCount;
    }

    /**
     * 根据id获取用户信息
     * @param int $id
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserInfoById($id)
    {
        $whereUser["id"]=$id;
        $userInfo=model("User")->hidden(["password"])->where($whereUser)->find();
        if ($userInfo){
            $returnData["data"]=$userInfo;
            $returnData["status"]=1;
        }else{
            $returnData["data"]=[];
            $returnData["status"]=2;
        }
        return $returnData;
    }
}