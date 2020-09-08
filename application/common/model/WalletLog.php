<?php
// +----------------------------------------------------------------------
// | 钱包异动模型
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/24 20:31
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;

class WalletLog extends Model
{
    protected $name = 'wallet_log';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    const MONEY_TYPE_NORMAL = 0; //正常的
    const MONEY_TYPE_FROZEN = 1; //冻结

    /**
     * 关联提现
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/25 11:29
     */
    public function Withdraw()
    {
        return $this->hasOne('Withdraw', 'withdraw_no', 'link_code')->bind([
            'status' => 'status',
            'withdraw_type' => 'withdraw_type'
        ]);
    }

}