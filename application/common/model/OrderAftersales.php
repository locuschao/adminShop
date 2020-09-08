<?php
// +----------------------------------------------------------------------
// | 退货单
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/20 18:17
// +----------------------------------------------------------------------

namespace app\common\model;

class OrderAftersales extends Base
{
    protected $name = 'order_aftersales';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
}