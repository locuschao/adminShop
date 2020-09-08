<?php
//用户地址表
namespace app\common\model;
class UserAddress extends Base{
    // 表名
    protected $name = 'user_address';

    //获取所有用户地址
    public function getUserAddressList($user_id,$offset,$limit){
        $user_id = intval($user_id);
        if($user_id <= 0){
            return array();
        }
        $where = array();
        $where['user_id'] = $user_id;
        $address = $this->where($where)->limit("$offset,$limit")->select();
        return empty($address)?array():$address->toArray();
    }

    //获取用地址数量
    public function getUserAddressCount($user_id){
        $user_id = intval($user_id);
        if($user_id <= 0){
            return array();
        }
        $where = array();
        $where['user_id'] = $user_id;
        return $this->where($where)->count();
    }

    public function getDetail(array $where){
        $data =  $this->where($where)->find();
        return empty($data)?array():$data->toArray();
    }
}