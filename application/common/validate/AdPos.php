<?php
// +----------------------------------------------------------------------
// | 广告位置
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class AdPos extends Validate{

    protected $rule = [
        'id' => 'integer',
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '广告位必填',
    ];

    protected $scene = [
        'save' => ['name'],
    ];
}