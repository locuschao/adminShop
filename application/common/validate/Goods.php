<?php
// +----------------------------------------------------------------------
// | 商品表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class Goods extends Validate{

    protected $rule = [
        'goods_id' => 'integer',
        'goods_name' => 'require',
        'goods_sn' => 'require',
        'cat_id' => 'require',
        'shop_price' => 'require',

    ];

    protected $message = [
        'goods_id.integer' => '商品goods_id错误',
        'goods_name.require' => '商品名称必填',
        'goods_sn.require' => '商品货号必填',
        'cat_id.require' => '商品分类必填',
        'shop_price.require' => '商品出售价必填',
    ];

    protected $scene = [
        'save' => ['goods_id','goods_name', 'goods_sn', 'cat_id',  'shop_price'],
    ];
}