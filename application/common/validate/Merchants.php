<?php
// +----------------------------------------------------------------------
// | 商家表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class Merchants extends Validate{
    protected $rule = [
        'platform' => 'require',
        'contact' => 'require',
        'responser' => 'require',
        'mobile' => 'require',

    ];

    protected $message = [
        'platform.require' => '平台名称需要填写',
        'contact.require' => '联系人需要填写',
        'responser.require' => '负责人需要填写',
        'mobile.require' => '电话需要填写',
    ];

    protected $scene = [
        'save' => ['platform','contact', 'responser', 'mobile'],
    ];
}