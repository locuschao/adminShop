<?php
// +----------------------------------------------------------------------
// | 品牌表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class GoodsBrand extends Validate{

    protected $rule = [
        'name' => 'require',
        'logo' => 'require',
    ];

    protected $message = [
        'name.require' => '品牌名称必填',
        'logo.require' => 'LOGO必填',
    ];

    protected $scene = [
        'save' => ['name','logo'],
    ];
}