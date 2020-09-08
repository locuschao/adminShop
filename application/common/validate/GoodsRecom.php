<?php
// +----------------------------------------------------------------------
// | 商品表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class GoodsRecom extends Validate{

    protected $rule = [
        'title' => 'require',
        'pos_id' => 'require',
        'image' => 'require',
        'start_time' => 'require|integer|gt:0',
        'end_time' => 'require|integer|gt:0',


    ];

    protected $message = [
        'pos_id.integer' => '显示位置需要填写',
        'title.require' => '标题需要填写',
        'start_time.require' => '开始时间需要填写',
        'end_time.require' => '结束时间需要填写',
        'image.require' => '图片需要上传',
    ];

    protected $scene = [
        'save' => ['title', 'pos_id', 'image', 'start_time', 'end_time'],
    ];
}