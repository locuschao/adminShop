<?php
// +----------------------------------------------------------------------
// | 钱包异常冻结日志
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/28 11:19
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;

class WalletExceptionLog extends Model
{
    protected $name = 'wallet_exception_log';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

}