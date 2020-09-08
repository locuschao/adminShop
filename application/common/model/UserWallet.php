<?php
// +----------------------------------------------------------------------
// | 用户钱包
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 19:38
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;

class UserWallet extends Model
{
    protected $name = 'user_wallet';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    //钱包状态
    const WALLET_STATUS_NORMAL = 0; //正常
    const WALLET_STATUS_FROZEN = 1; //异常
    const WALLET_STATUS_CANCEL = 2; //注销


}