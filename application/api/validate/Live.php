<?php
// +----------------------------------------------------------------------
// | 直播验证
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class Live extends Validate
{
    protected $rule = [
        'id' => 'integer',
        'title' => 'require',
        'start_time' => 'require|integer|gt:0',
        'end_time' => 'integer',
        'img_url' => 'require',
        'anchor_id' => 'require|integer|gt:0',
        'goods_ids' => 'require',
        'status' => 'integer|in:0,1,2,3'
    ];

    protected $message = [
        'id.integer' => '直播id错误',
        'title.require' => '标题必填',
        'start_time.require' => '开播时间必选',
        'img_url.require' => '图片必填',
        'anchor_id.require' => '主播必选',
        'goods_ids.require' => '商品必选',
		'status.integer' => '状态错误'
    ];

    protected $scene = [
        'save' => ['title', 'start_time', 'end_time', 'img_url', 'anchor_id', 'goods_ids'],
        'start' => ['id','anchor_id','status'],
        'stop' => ['id','status','end_time'],
    ];
}
