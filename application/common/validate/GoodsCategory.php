<?php
// +----------------------------------------------------------------------
// | 商品表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class GoodsCategory extends Validate{

    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '分类名称必填',
    ];

    protected $scene = [
        'save' => ['name'],
    ];
}