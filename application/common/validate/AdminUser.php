<?php
// +----------------------------------------------------------------------
// | 后台管理员验证
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-03-05
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class AdminUser extends Validate
{
    protected $rule = [
        'id' => 'integer',
        'name' => 'require|max:50',
        'account' => 'require|max:50',
        'old_password'=>'require',
        'password'=>'require|length:6,16|confirm',
        'level' => 'integer|in:1,2',
        'role' => 'require|integer|gt:0',
        'mobile' => 'require|mobile',
        'email' => 'email',
        'isable' => 'integer|in:0,1'

    ];

    protected $message = [
        'id.integer' => '直播id错误',
        'name.require' => '名称必填',
        'account.require' => '账号必选',
        'password.require' => '密码必填',
        'password.length' => '密码长度在6到16位之间',
        'password.confirm' => '两次密码不一致',
        'level.integer' => '等级错误',
        'role.integer' => '角色错误',
		'mobile.require' => '手机必填',
        'mobile.mobile' => '手机格式错误',
        'emial.email' => '邮箱格式错误',
		'isable.integer' => '状态错误'
    ];

    protected $scene = [
        'add' => ['name', 'account', 'password', 'mobile'],
        'update' => ['id','anchor_id','status'],
        'reset_passwd' => ['old_password', 'id', 'password'],
    ];
}