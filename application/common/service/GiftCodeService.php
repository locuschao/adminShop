<?php
// +----------------------------------------------------------------------
// | 礼包码服务层
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/25 16:08
// +----------------------------------------------------------------------

namespace app\common\service;

use app\common\model\GiftCode;
use app\common\model\GiftActivity;
use think\Db;

class GiftCodeService extends BaseService
{
    /**
     * 用户兑换码
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/25 16:23
     */
    public function userGiftCodeList($params)
    {
        $res = [
            'count' => 0,
            'list' => []
        ];

        if (!isset($params['user_id']) || empty($params['user_id'])) return $res;

        $giftCode = new GiftCode();
        $map = [
            'user_id' => $params['user_id'],
        ];

        $res['count'] = $giftCode->where($map)->count();
        $list = $giftCode->where($map)
            ->with('GitActivity')
            ->page($params['page'], $params['limit'])
            ->order('update_time DESC')
            ->select();

        if (!empty($list))   $res['list'] = $list->toArray();

        return $res;
    }

    //领取礼包码
    public function receiveGiftCode($id,$user_id){
        $id = intval($id);
        $user_id = intval($user_id);
        if(empty($id) || empty($user_id)){
            return array();
        }
        $giftCodeModel = new GiftCode();
        $giftActivityModel = new GiftActivity();
        Db::startTrans();
        //查询一个充换码
        $giftCode = $giftCodeModel->getDetail(array('gid'=>$id,'status'=>0));
        if(empty($giftCode)){
            Db::rollback();
            return array();
        }
        $update = $giftCodeModel->update(array('user_id'=>$user_id,'update_time'=>time(),'status'=>1),array('gid'=>$id,'id'=>$giftCode['id']));
        if(false === $update){
            Db::rollback();
            return array();
        }
        $is_update = $giftActivityModel->where(array('id'=>$id))->setInc('obtain_num',1);
        if(false === $is_update){
            Db::rollback();
            return array();
        }
        Db::commit();
        return $giftCode['number'];
    }
}