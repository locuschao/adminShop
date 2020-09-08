<?php
// +----------------------------------------------------------------------
// | 直播间的验证
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-03-19
// +----------------------------------------------------------------------
namespace app\swoole\validate;

use think\Validate;

class Live extends Validate
{
    protected $rule = [
        'live_id' => 'integer',
        'user_id' => 'require',
        'anchor_id' => 'require',
        'nickname' => 'require|max:100',
        'user_role' => "require|integer|in:1,2",
        'fd' => "require"
    ];

    protected $message = [
        'live_id.integer' => '直播id不能为空',
        'user_id.require' => '用户ID不能为空',
        'nickname.require' => '用户ninckname不能为空',
        'nickname.100' => '用户ninckname格式错误',
        'user_role.user_role' => "用户角色不能为空",
        'user_role.integer' => "用户角色类型错误",
        'anchor_id.require' => "主播ID必填",
        'fd.require' => '连接标识必填'
    ];

    protected $scene = [
        'into_live' => ['live_id', 'user_id', 'nickname', 'user_role'],
        'start' => ['live_id','anchor_id','fd'],
    ];
}
