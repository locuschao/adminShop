<?php
// +----------------------------------------------------------------------
// | 直播验证
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class SysConfigGroup extends Validate
{
    protected $rule = [
        'id' => 'integer',
        'title' => 'require',
        'sort' => 'integer',
        'pid' => 'integer',
    ];

    protected $message = [
        'id.integer' => 'ID错误',
        'title.require' => '标题必填',
        'sort.integer' => '排序必须是整数',
        'pid.integer' => '组ID必须是整数',
    ];

    protected $scene = [
        'save' => ['title', 'sort'],
    ];
}
