<?php
// +----------------------------------------------------------------------
// | 商品活动
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class GoodsActivity extends Validate{

    protected $rule = [
        'title' => 'require',
        'coupon_id' => 'require',
        'get_num' => 'require|integer|gt:0',
        'start_time' => 'require|integer|gt:0',
        'end_time' => 'require|integer|gt:0',


    ];

    protected $message = [
        'coupon_id.integer' => '请选择关联的活动',
        'get_num.integer' => '获取次数必填',
        'title.require' => '标题需要填写',
        'start_time.require' => '开始时间需要填写',
        'end_time.require' => '结束时间需要填写',
    ];

    protected $scene = [
        'save' => ['title', 'coupon_id',  'start_time', 'end_time','get_num'],
    ];
}