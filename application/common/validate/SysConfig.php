<?php
// +----------------------------------------------------------------------
// | 配置验证
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-13
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class SysConfig extends Validate
{
    protected $rule = [
        'id' => 'integer',
        'title' => 'require',
        'type' => 'require',
        'name' => 'require|max:30',
        'group_id' => 'require|integer|gt:0',
        'sub_id' => 'require|integer|gt:0',
        'extra' => 'max:255',
        'sort' => 'integer',
        'status' => 'integer',
        'value.require' => 'require',

    ];

    protected $message = [
        'id.integer' => 'ID错误',
        'title.require' => '配置标题必填',
        'type.require' => '配置类型必填',
        'name.require' => '配置标识必填',
        'group_id.require' => '配置分类必填',
        'sub_id.require' => '配置值必填',
        'extra.max' => '最多不能超过255个字符',
        'value.require' => '配置值必填',
    ];

    protected $scene = [
        'save' => ['title', 'type', 'name', 'group_id', 'sub_id', 'extra', 'sort'],
        'save_value' => ['value']
    ];
}
