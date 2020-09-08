<?php
// +----------------------------------------------------------------------
// | 商品表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class coupon extends Validate{

    protected $rule = [
        'title' => 'require',
        'day' => 'require|integer|gt:0',
        'money' => 'require|integer|gt:0',


    ];

    protected $message = [
        'title.require' => '标题需要填写',
        'day.require' => '请填写有效时长',
        'money.require' => '请填写抵扣金额',
    ];

    protected $scene = [
        'save' => ['title', 'day', 'money'],
    ];
}