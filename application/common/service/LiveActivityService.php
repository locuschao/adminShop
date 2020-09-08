<?php
/**
 * [直播活动处理]
 */
namespace app\common\service;
use app\common\model\Live;
use app\common\model\LiveItem as LiveItemModel;
use app\common\model\LiveItemDetail;
use app\common\model\LiveItemUser;
use app\common\model\Coupon;
use app\common\model\GiftActivity;
use app\common\model\UserCoupon;
use app\common\model\Live as LiveModel;

class LiveActivityService extends BaseService{

    //单次互动活动
    public function getLiveSingleList($live_id,$user_id){
        $data = array(
            'sign'=>array(),
            'live'=>array(),
            'share'=>array(),
        );

        $live_id = intval($live_id);

        $user_id = intval($user_id);

        if (empty($live_id)){
            return $data;
        }

        $liveItemModel = new LiveItemModel();
        $liveItemUserModel = new LiveItemUser();

        $where = array();
        $where['a.item_id'] = LiveItemModel::single;
        $where['a.status'] = 1;
        $where['b.status'] = 1;
        $where['b.live_id'] = $live_id;
        $field = "id,reward_type as type,ext_id";

        $list = $liveItemModel->getLiveItemListByCondition($where,$field);

        if(!empty($list)){
            foreach ($list as $value){
                switch ($value['id']){
                    case LiveItemModel::SIGN://签到
                        $live_sign = $liveItemUserModel->getUserLiveItemByCondition(array('user_id'=>$user_id,'live_id'=>$live_id,'mode_type'=>LiveItemUser::live_sign));
                        $sign = array();
                        if(empty($live_sign)){
                            $sign = $this->handleLiveActivityAward($value['type'],$value['ext_id'],$user_id,$live_id,1);
                        }
                        if(!empty($sign)){
                            //写入数据
                            $liveItemUserModel->addLiveItemUser(LiveItemUser::live_sign,array('user_id'=>$user_id,'live_id'=>$live_id,'content'=>$sign));
                        }
                        $data['sign'] = $sign;
                        break;
                    case LiveItemModel::LIVE://直播赠礼
                        $live_gift = $liveItemUserModel->getUserLiveItemByCondition(array('user_id'=>$user_id,'live_id'=>$live_id,'mode_type'=>LiveItemUser::live_gift));
                        $live = array();
                        if(empty($live_gift)){
                            $live = $this->handleLiveActivityAward($value['type'],$value['ext_id'],$user_id,$live_id);
                        }
                        $data['live'] = $live;
                        break;
                    case LiveItemModel::SHARE://分享
                        $share = $this->handleLiveActivityAward($value['type'],$value['ext_id'],$user_id,$live_id);
                        $data['share'] = $share;
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * @param $reward_type
     * @param $ext_id
     * @param $user_id
     * @param $live_id
     * @param int $is_push[是否直接发放]
     * @return array
     * [处理直播间活动]
     */
    private function handleLiveActivityAward($reward_type,$ext_id,$user_id,$live_id,$is_push=0){
        $data = array();
        $couponModel = new Coupon();
        $userCouponModel = new UserCoupon();
        $giftActivityModel = new GiftActivity();
        $GiftCodeService = new GiftCodeService();
        $liveModel = new LiveModel();
        $live = $liveModel->getDetail(array('id'=>$live_id));
         switch ($reward_type){
             case 1: //优惠券
                 $coupon = $couponModel->getDetail(array('type'=>$reward_type,'id'=>$ext_id,'status'=>1));
                 if(!empty($coupon)){
                     if($is_push){
                         $userCouponModel->addCoupon(UserCoupon::LIVE_TYPE,$coupon['id'],$user_id,$coupon['day'],$live['title'],$live_id);
                     }
                     $data['id'] =  $coupon['id'];
                     $data['title']=$coupon['title'];
                     $data['full_money']=$coupon['full_money'];
                     $data['money']=$coupon['money'];
                     $data['type']=1;
                 }
                 break;
             case 2: //红包
                 $coupon = $couponModel->getDetail(array('type'=>$reward_type,'id'=>$ext_id,'status'=>1));
                 if(!empty($coupon) && $is_push){

                     if($is_push){
                         $userCouponModel->addCoupon(UserCoupon::LIVE_TYPE,$coupon['id'],$user_id,$coupon['day'],$live['title'],$live_id);
                     }
                     $data['id'] =  $coupon['id'];
                     $data['title']=$coupon['title'];
                     $data['full_money']=$coupon['full_money'];
                     $data['money']=$coupon['money'];
                     $data['type']=2;
                 }
                 break;
             case 3://充换码
                 $gift = $giftActivityModel->getDetail(array('id'=>$ext_id,'status'=>1));
                 if(!empty($gift) && ($gift['total_num']-$gift['obtain_num']>1)){
                     if($is_push){
                         //领取礼包
                         $number = $GiftCodeService->receiveGiftCode($gift['id'],$user_id);
                         $data['number']=$number;
                     }
                     $data['id'] =  $gift['id'];
                     $data['title']=$gift['title'];
                     $data['type']=3;
                 }
                 break;
         }

         return $data;
    }

    //获取单次互动奖励
    public function getSingleAward($action,$live_id,$type,$id,$user_id){
        if(empty($action) || empty($live_id) || empty($type) || empty($id) || empty($user_id)){
            return array();
        }
        $liveItemUserModel = new LiveItemUser();
        $data = array();
        switch ($action){
            case LiveItemUser::live_share:
                $live_share = $liveItemUserModel->getUserLiveItemByCondition(array('user_id'=>$user_id,'live_id'=>$live_id,'mode_type'=>LiveItemUser::live_share));
                if(empty($live_share)){
                    $data = $this->handleLiveActivityAward($type,$id,$user_id,$live_id,1);
                }
                break;
            case LiveItemUser::live_gift:
                $live_gift = $liveItemUserModel->getUserLiveItemByCondition(array('user_id'=>$user_id,'live_id'=>$live_id,'mode_type'=>LiveItemUser::live_gift));
                if(empty($live_gift)){
                    $data = $this->handleLiveActivityAward($type,$id,$user_id,$live_id,1);
                }
                break;
        }

        //写入数据
        if(!empty($data)){
            //写入数据
            $liveItemUserModel->addLiveItemUser($action,array('user_id'=>$user_id,'live_id'=>$live_id,'content'=>$data));
        }

        return $data;
    }


    //直播间购买赠礼
    public function liveBuyGive($live_id,$user_id){
        $liveModel = new Live();
        $liveItemDetailModel = new LiveItemDetail();
        $liveInfo = $liveModel -> getDetail(array('live_id'=>$live_id,'status'=>1));
        if(empty($liveInfo)){
            return array();
        }
        $live_Item = $liveItemDetailModel->getDetail(array('live_id'=>$live_id,'type'=>LiveItemModel::BUY,'status'=>1));
        if(empty($live_Item)){
            return array();
        }
        $liveItemUserModel = new LiveItemUser();
        $live_gift = $liveItemUserModel->getUserLiveItemByCondition(array('user_id'=>$user_id,'live_id'=>$live_id,'mode_type'=>LiveItemUser::live_buy));
        if(empty($live_gift)){
            $this->handleLiveActivityAward($live_Item['reward_type'],$live_Item['ext_id'],$user_id,$live_id,1);
        }

    }
}