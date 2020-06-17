<?php
namespace app\chat\controller;
use app\chat\model\OwnGroupUser;
use think\Controller;
use app\chat\logic\User as UserLogic;
use app\chat\logic\OwnGroup as OwnGroupLogic;

class Chat extends Controller
{
    /**
     * 找朋友
     */
    public function findFriends()
    {
        $keywords=input("keywords");
        $pageId=input("pageId");
        $whereUser[]=["nickname","like","%$keywords%"];
        $userLogic=new UserLogic();
        $list=$userLogic->getUsers($whereUser,$pageId);
        $listCount=$userLogic->getUsersCount($whereUser);
        $data["count"]=$listCount;
        $data["pageId"]=$pageId;
        $data["data"]=$list;
        $returnData["msg"]="成功";
        $returnData["data"]=$data;
        return json($returnData,200);
    }

    /**
     * 申请加朋友
     */
    public function addFriend()
    {
        $user_id=session("user_id");
//        $user_id=4;
        $objId=input("objId");
        $ownGroupName=input("ownGroupName");
        $isGroup=0;
        $yzxx=input("yzxx");
        $data["user_id"]=$user_id;
        $data["obj_id"]=$objId;
        $data["is_group"]=$isGroup;
        $applyInfo=model("Application")->where($data)->find();
        $ownGroupLogic=new OwnGroupLogic();
        $ownGroupId=$ownGroupLogic->getIdByName($ownGroupName,$user_id,true);
        if ($applyInfo){
            if($applyInfo['status']!=1){
                $applyInfo->yzxx=$yzxx;
                $applyInfo->own_group_id=$ownGroupId;
                $applyInfo->status=0;
                $applyInfo->create_time=time();
                $res_apply=$applyInfo->isUpdate(true)->save();
            }else{
                $returnData["msg"]="他（她）已是你的朋友了";
                $data["data"]=[];
                return json($returnData,400);
            }
        }else{
            $data["yzxx"]=$yzxx;
            $data["own_group_id"]=$ownGroupId;
            $data["create_time"]=time();
            $data["status"]=0;
            $res_apply=model("Application")->isUpdate(false)->save($data);
        }
        if ($res_apply){
            $returnData["msg"]="成功";
            $returnData["data"]=[];
            return json($returnData,200);
        }else{
            $returnData["msg"]="失败";
            $returnData["data"]=[];
            return json($returnData,400);
        }
    }

    /**
     * 获取申请列表
     */
    public function getApplyFriendList()
    {
        $user_id=session("user_id");
        $pageId=input("pageId");
        $where_list["is_group"]=0;
        $where_list["obj_id"]=$user_id;
        $list=model("Application")->where($where_list)->page($pageId)->limit(10)->order("create_time desc")->select();
        $listCount=model("Application")->where($where_list)->count();
        $data["count"]=$listCount;
        $data["pageId"]=$pageId;
        $data["data"]=$list;
        $returnData["msg"]="成功";
        $returnData["data"]=$data;
        return json($returnData,200);
    }

    /**
     * 同意添加朋友申请
     */
    public function agreeFriend()
    {
        $user_id=session("user_id");
        $user_id=2;
        $id=input("id");
        $status=input("status");
        $ownGroupName=input("ownGroupName");
        $remarkName=input("remark_name");
        $where["id"]=$id;
        $where["obj_id"]=$user_id;
        $applyInfo=model("Application")->where($where)->find();
        if (!$applyInfo){
            $returnData["msg"]="参数错误";
            $returnData["data"]=[];
            return json($returnData,500);
        }
        $applyInfo->startTrans();
        $time=time();
        $resApply=$applyInfo->isUpdate(true)->save(["status"=>$status,"upda_time"=>$time]);
        if ($status==1){
            $whereOwnGroup["user_id"]=$user_id;
            $ownGroupLogic=new OwnGroupLogic();
            $ownGroupId=$ownGroupLogic->getIdByName($ownGroupName,$user_id,true);
            $dataOwnGroupUser[0]["group_id"]=$ownGroupId;
            $dataOwnGroupUser[0]["user_id"]=$applyInfo->user_id;
            $dataOwnGroupUser[0]["remark_name"]=$remarkName;
            $dataOwnGroupUser[0]["create_time"]=$time;
            $dataOwnGroupUser[1]["group_id"]=$applyInfo->own_group_id;
            $dataOwnGroupUser[1]["user_id"]=$applyInfo->obj_id;
            $dataOwnGroupUser[1]["create_time"]=$time;
            $resOwnGroupUser=model("OwnGroupUser")->isUpdate(false)->saveAll($dataOwnGroupUser);
            $dataFriend["user_id"]=$applyInfo["user_id"];
            $dataFriend["obj_id"]=$applyInfo["obj_id"];
            $dataFriend["create_time"]=$time;
            $resFriend=model("Friend")->isUpdate(false)->save($dataFriend);
        }else{
            $resOwnGroupUser=true;
            $resFriend=true;
        }
        if ($resApply&&$resOwnGroupUser&&$resFriend){
            $applyInfo->commit();
            $returnData["msg"]="成功";
            $returnData["data"]=[];
            return json($returnData,200);
        }else{
            $applyInfo->rollback();
            $returnData["msg"]="失败";
            $returnData["data"]=[];
            return json($returnData,400);
        }
    }

    /**
     * 删除朋友
     */
    public function deleFriend()
    {
        $user_id=session("user_id");
        $ownGroupId=input("ownGroupId");
        $objId=input("objId");
        model("OwnGroupUser")->startTrans();
        $whereOwnGroupUser["user_id"]=$objId;
        $whereOwnGroupUser["group_id"]=$ownGroupId;
        $resOwnGroupUserInfo=model("OwnGroupUser")->where($whereOwnGroupUser)->delete();
        $whereFriend["user_id"]=$user_id;
        $whereFriend["obj_id"]=$objId;
        $whereFriend1["user_id"]=$objId;
        $whereFriend1["obj_id"]=$user_id;
        $resFriend=model("Friend")->where($whereFriend)->whereOr($whereFriend1)->delete();
        if ($resOwnGroupUserInfo&&$resFriend){
            model("OwnGroupUser")->commit();
            $returnData["msg"]="成功";
            $returnData["data"]=[];
            return json($returnData,200);
        }else{
            model("OwnGroupUser")->rollback();
            $returnData["msg"]="失败";
            $returnData["data"]=[];
            return json($returnData,400);
        }
    }

    /**
     * 创建群聊
     */
    public function createGroup()
    {
        $user_id=session("user_id");
        $groupName=input("groupName");
        $touXiang=input("touXiang");
        $desc=input("desc");
        $whereGroup["group_name"]=$groupName;
        $groupInfo=model("Group")->where($whereGroup)->find();
        if ($groupInfo){
            $returnData["msg"]="该群聊名称已存在";
            $returnData["data"]=[];
            return json($returnData,400);
        }
        $dataGroup["user_id"]=$user_id;
        $dataGroup["group_name"]=$groupName;
        $dataGroup["create_time"]=time();
        $dataGroup["touxiang"]=$touXiang;
        $dataGroup["desc"]=$desc;
        $resGroup=model("Group")->isUpdate(false)->save($dataGroup);
        if ($resGroup){
            $returnData["msg"]="成功";
            $returnData["data"]=[];
            return json($returnData,200);
        }else{
            $returnData["msg"]="失败";
            $returnData["data"]=[];
            return json($returnData,400);
        }
    }

    /**
     * 找群聊
     */
    public function findGroups()
    {
        $groupName=input("groupName");
        $isFuzzy=input("isFuzzy");
        $pageId=input("pageId");
        if ($isFuzzy){
            $whereGroup=["group_name","like","%$groupName%"];
        }else{
            $whereGroup["group_name"]=$groupName;
        }
        $list=model("Group")->where($whereGroup)->page($pageId)->limit(10)->select();
        $listCount=model("Group")->where($whereGroup)->count();
        $data["count"]=$listCount;
        $data["pageId"]=$pageId;
        $data["data"]=$list;
        $returnData["msg"]="成功";
        $returnData["data"]=$data;
        return json($returnData,200);
    }

    /**
     * 申请加群聊
     */
    public function addGroup()
    {

    }

    /**
     * 同意添加群聊申请
     */
    public function agreeGroup()
    {

    }

    /**
     * 退出群聊
     */
    public function exitGroup()
    {

    }

    /**
     * 踢出群聊
     */
    public function dismissGroupUser()
    {

    }

    /**
     * 删除群聊
     */
    public function deleGroup()
    {

    }

    /**
     * 获取自己的信息
     */
    public function getMineInfo()
    {

    }

    /**
     * 创建分组（自己的分组）
     */
    public function createOwnGroup()
    {

    }

    /**
     * 移动朋友到我的分组
     */
    public function moveUserToGroup()
    {

    }

    /**
     * 获取我的分组
     */
    public function getOwnGroup()
    {

    }

    /**
     * 修改我的分组
     */
    public function editOwnGroup()
    {

    }

    /**
     * 删除我的分组
     */
    public function deleOwnGroup()
    {

    }

    /**
     * 获取我的分组的用户
     */
    public function getOwnGroupUser()
    {

    }

    /**
     * 获取群聊
     */
    public function getGroup()
    {

    }

    /**
     * 获取群聊的用户
     */
    public function getMembers()
    {
        $data_str='{
          "code": 0
          ,"msg": ""
          ,"data": {
            "owner": {
              "username": "贤心"
              ,"id": "100001"
              ,"avatar": "http://tp1.sinaimg.cn/1571889140/180/40030060651/1"
              ,"sign": "这些都是测试数据，实际使用请严格按照该格式返回"
            }
            ,"list": [{
              "username": "Z_子晴123"
              ,"id": "108101"
              ,"avatar": "http://tva3.sinaimg.cn/crop.0.0.512.512.180/8693225ajw8f2rt20ptykj20e80e8weu.jpg"
              ,"sign": "微电商达人"
            },{
              "username": "Lemon_CC"
              ,"id": "102101"
              ,"avatar": "http://tp2.sinaimg.cn/1833062053/180/5643591594/0"
              ,"sign": ""
            },{
              "username": "马小云"
              ,"id": "168168"
              ,"avatar": "http://tp4.sinaimg.cn/2145291155/180/5601307179/1"
              ,"sign": "让天下没有难写的代码"
            },{
              "username": "徐小峥"
              ,"id": "666666"
              ,"avatar": "http://tp2.sinaimg.cn/1783286485/180/5677568891/1"
              ,"sign": "代码在囧途，也要写到底"
            },{
              "username": "罗玉凤"
              ,"id": "121286"
              ,"avatar": "http://tp1.sinaimg.cn/1241679004/180/5743814375/0"
              ,"sign": "在自己实力不济的时候，不要去相信什么媒体和记者。他们不是善良的人，有时候候他们的采访对当事人而言就是陷阱"
            },{
              "username": "长泽梓Azusa"
              ,"id": "100001222"
              ,"sign": "我是日本女艺人长泽あずさ"
              ,"avatar": "http://tva1.sinaimg.cn/crop.0.0.180.180.180/86b15b6cjw1e8qgp5bmzyj2050050aa8.jpg"
            },{
              "username": "大鱼_MsYuyu"
              ,"id": "12123454"
              ,"avatar": "http://tp1.sinaimg.cn/5286730964/50/5745125631/0"
              ,"sign": "我瘋了！這也太準了吧  超級笑點低"
            },{
              "username": "谢楠"
              ,"id": "10034001"
              ,"avatar": "http://tp4.sinaimg.cn/1665074831/180/5617130952/0"
              ,"sign": ""
            },{
              "username": "柏雪近在它香"
              ,"id": "3435343"
              ,"avatar": "http://tp2.sinaimg.cn/2518326245/180/5636099025/0"
              ,"sign": ""
            },{
              "username": "林心如"
              ,"id": "76543"
              ,"avatar": "http://tp3.sinaimg.cn/1223762662/180/5741707953/0"
              ,"sign": "我爱贤心"
            },{
              "username": "佟丽娅"
              ,"id": "4803920"
              ,"avatar": "http://tp4.sinaimg.cn/1345566427/180/5730976522/0"
              ,"sign": "我也爱贤心吖吖啊"
            }]
          }
        }';
        return json_decode($data_str);
    }

    public function getList()
    {
        $data_str = '{
          "code": 0
          ,"msg": ""
          ,"data": {
            "mine": {
              "username": "纸飞机"
              ,"id": "100000"
              ,"status": "online"
              ,"sign": "在深邃的编码世界，做一枚轻盈的纸飞机"
              ,"avatar": "http://cdn.firstlinkapp.com/upload/2016_6/1465575923433_33812.jpg"
            }
            ,"friend": [{
              "groupname": "前端码屌"
              ,"id": 1
              ,"online": 2
              ,"list": [{
                "username": "贤心"
                ,"id": "100001"
                ,"avatar": "http://tp1.sinaimg.cn/1571889140/180/40030060651/1"
                ,"sign": "这些都是测试数据，实际使用请严格按照该格式返回"
              },{
                "username": "Z_子晴"
                ,"id": "108101"
                ,"avatar": "http://tva3.sinaimg.cn/crop.0.0.512.512.180/8693225ajw8f2rt20ptykj20e80e8weu.jpg"
                ,"sign": "微电商达人"
              },{
                "username": "Lemon_CC"
                ,"id": "102101"
                ,"avatar": "http://tp2.sinaimg.cn/1833062053/180/5643591594/0"
                ,"sign": ""
              },{
                "username": "马小云"
                ,"id": "168168"
                ,"avatar": "http://tp4.sinaimg.cn/2145291155/180/5601307179/1"
                ,"sign": "让天下没有难写的代码"
              },{
                "username": "徐小峥"
                ,"id": "666666"
                ,"avatar": "http://tp2.sinaimg.cn/1783286485/180/5677568891/1"
                ,"sign": "代码在囧途，也要写到底"
              }]
            },{
              "groupname": "网红"
              ,"id": 2
              ,"online": 3
              ,"list": [{
                "username": "罗玉凤"
                ,"id": "121286"
                ,"avatar": "http://tp1.sinaimg.cn/1241679004/180/5743814375/0"
                ,"sign": "在自己实力不济的时候，不要去相信什么媒体和记者。他们不是善良的人，有时候候他们的采访对当事人而言就是陷阱"
              },{
                "username": "长泽梓Azusa"
                ,"id": "100001222"
                ,"sign": "我是日本女艺人长泽あずさ"
                ,"avatar": "http://tva1.sinaimg.cn/crop.0.0.180.180.180/86b15b6cjw1e8qgp5bmzyj2050050aa8.jpg"
              },{
                "username": "大鱼_MsYuyu"
                ,"id": "12123454"
                ,"avatar": "http://tp1.sinaimg.cn/5286730964/50/5745125631/0"
                ,"sign": "我瘋了！這也太準了吧  超級笑點低"
              },{
                "username": "谢楠"
                ,"id": "10034001"
                ,"avatar": "http://tp4.sinaimg.cn/1665074831/180/5617130952/0"
                ,"sign": ""
              },{
                "username": "柏雪近在它香"
                ,"id": "3435343"
                ,"avatar": "http://tp2.sinaimg.cn/2518326245/180/5636099025/0"
                ,"sign": ""
              }]
            },{
              "groupname": "我心中的女神"
              ,"id": 3
              ,"online": 1
              ,"list": [{
                "username": "林心如"
                ,"id": "76543"
                ,"avatar": "http://tp3.sinaimg.cn/1223762662/180/5741707953/0"
                ,"sign": "我爱贤心"
              },{
                "username": "佟丽娅"
                ,"id": "4803920"
                ,"avatar": "http://tp4.sinaimg.cn/1345566427/180/5730976522/0"
                ,"sign": "我也爱贤心吖吖啊"
              }]
            }]
            ,"group": [{
              "groupname": "前端群"
              ,"id": "101"
              ,"avatar": "http://tp2.sinaimg.cn/2211874245/180/40050524279/0"
            },{
              "groupname": "Fly社区官方群"
              ,"id": "102"
              ,"avatar": "http://tp2.sinaimg.cn/5488749285/50/5719808192/1"
            }]
          }
        }';
        return json_decode($data_str);
    }

}