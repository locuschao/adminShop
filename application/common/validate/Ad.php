<?php
// +----------------------------------------------------------------------
// | 广告位置
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class Ad extends Validate{

    protected $rule = [
        'id' => 'integer',
        'name' => 'require',
        'image' => 'require',
        'start_time' => 'require',
        'end_time' => 'require',
    ];


    protected $message = [
        'name.require' => '广告位必填',
        'image.require' => '图片必填',
        'start_time.require' => '广告位开始时间必填',
        'end_time.require' => '广告位结束时间必填',
    ];

    protected $scene = [
        'save' => ['name','image','start_time','end_time']
    ];
}