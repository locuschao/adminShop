<?php
// +----------------------------------------------------------------------
// | 提现验证
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/24 18:07
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class Withdraw extends Validate
{
    protected $rule = [
//        'user_id' => 'require',
//        'withdraw_no' => 'require',
        'money' => 'require|integer',
        'arrival_money' => 'require|integer',
        'poundage' => 'require|float',
        'poundage_ratio' => 'require|integer',
        'withdraw_type' => 'require|integer|in:1,2,3',
        'withdraw_way' => 'require|integer|in:1,2,3,4',
        'audit_time' => 'integer',
        'bank_id' => 'integer',
        'province' => 'require',
        'city' => 'require',
        'branch' => 'require',
        'bankcard' => 'require|ilength:10,30',
        'realname' => 'require',
        'hope_arrival_date' => 'require|in:0,1,2,3',
        'source_id' => 'require|in:0,1,2,3',
        'status' => 'require|integer|in:0,1,2,3,4',
        'audit_memo' => 'max:180',
        'admin_id' => 'require'
    ];

    protected $message = [
        'user_id.integer' => '用户必填',
        'withdraw_no.integer' => '提现单号必填',
        'money.require' => '提现金额不能为空',
        'arrival_money.require' => '实际到账金额必填',
        'poundage.require' => '提现手续费续率必填',
        'poundage_ratio.require' => '提现手续费续率必填',
        'withdraw_type.integer' => '提现类型必填',
        'withdraw_way.integer' => '提现方式必填',
        'audit_time.integer' => '确认时间必填',
        'bank_id.integer' => '银行卡ID必填',
        'bankcard.require' => '银行卡号必填',
        'bankcard.ilength' => '银行卡号格式错误',
        'province.integer' => '省份必填',
        'city.integer' => '城市必填',
        'branch.integer' => '支行必填',
        'realname.integer' => '银行卡开户名必填',
        'source_id.require' => '来源必填',
        'status.require' => '状态必填',
        'audit_memo.max' => '长度不能大于180个字',
        'admin_id.require' => '操作人ID必填'
    ];

    protected $scene = [
        //申请
        'withdraw' => [
            'user_id', 'money', 'poundage', 'poundage_ratio', 'withdraw_type',
            'withdraw_way','arrival_money'

        ],

        //审核
        'audit' => ['status', 'audit_memo', 'audit_time','admin','audit_time'],
    ];
}