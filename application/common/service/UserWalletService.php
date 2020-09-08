<?php
// +----------------------------------------------------------------------
// | 用户钱包服务层
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 18:37
// +----------------------------------------------------------------------
namespace app\common\service;

use app\common\model\UserWallet;
use app\common\model\WalletExceptionLog;
use app\common\model\Withdraw;
use app\common\model\WalletLog;
use think\Db;
use think\Exception;
use app\common\validate\Withdraw AS WithdrawValidate;


class UserWalletService extends BaseService
{
    public function __construct()
    {
        $this->model = new UserWallet();
        parent::__construct();
    }

    /**
     * 获取用户钱包信息
     * @param $userId
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 18:32
     */
    public function getUserWalletInfo($userId, $lock = false)
    {
        if (empty($userId)) return [];
        $wallet = $this->model->lock($lock)->where(['user_id' => $userId])->field('create_at,update_at', true)->find();

        if (empty($wallet)) return $this->_createWallet($userId);
        return $wallet->toArray();
    }

    /**
     * 创建用户钱包
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 18:39
     */
    protected function _createWallet($userId)
    {
        $walletData = [
            'user_id' => $userId,
            'cash_money' => '0.00',
            'frozen_money' => '0.00',
            'give_money' => '0.00',
        ];

        $walletData['crc'] = $this->md5WalletCrc($walletData);
        $ret = $this->model->save($walletData);

//        unset($walletData['crc']);
        if ($ret) return $walletData;
    }

    /**
     * 钱包加款
     * @param $account
     * @param $options
     * @param bool $updateAccount
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/23 19:17
     */
    public function cashInc($account, $options, $updateAccount = false)
    {
        if ($options['income_expenses'] <= 0) return ['code' => 0, 'msg' => '异动金额必须大于0'];
        $originalMoney = $account['cash_money'];
        $account['cash_money'] += $options['income_expenses'];

        $data = [
            'user_id' => $account['user_id'],
            'code' => create_no(config('enum.orders_prefix')['CA'], $account['user_id']),
            'link_code' => isset($options['link_code']) ? $options['link_code'] : '',
            'money_type' => isset($options['money_type']) ? $options['money_type'] : 0,
            'operation_type' => 1,
            'business_type' => $options['business_type'],
            'operation_money' => $options['income_expenses'],
            'original_money' => $originalMoney,
            'latest_money' => $account['cash_money'],
            'msg' => isset($options['msg']) ? $options['msg'] : '',
            'commercial_tenant_id' => isset($options['commercial_tenant_id']) ? $options['commercial_tenant_id'] : 0, //商户ID
            'from_uid' => isset($options['from_uid']) ? $options['from_uid'] : 0,
            'to_uid' => isset($options['to_uid']) ? $options['to_uid'] : 0,
        ];

        try {
            Db::startTrans();
            $walletLog = new WalletLog();
            if (!$ret = $walletLog->isUpdate(false)->allowField(true)->save($data)) {
                $msg = $walletLog->getError();
                throw new Exception($msg);
            }

            //更新钱包
            if ($updateAccount == true) {
                $ret = $this->updateAccount($account);
                if ($ret['code'] != 1) throw new Exception($ret['msg']);
                $account['crc'] = $this->md5WalletCrc($account);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

        return ['code' => 1, 'msg' => '更新钱包成功！', 'data' => $account];
    }

    /**
     * 钱包扣款
     * @param $account
     * @param $options
     * @param bool $updateAccount
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/23 19:17
     */
    public function cashDec($account, $options, $updateAccount = false)
    {
        if ($options['income_expenses'] <= 0) return ['code' => 0, 'msg' => '异动金额必须大于0'];
        $originalMoney = $account['cash_money'];
        $account['cash_money'] -= $options['income_expenses'];

        $data = [
            'user_id' => $account['user_id'],
            'code' => create_no(config('enum.orders_prefix')['CA'], $account['user_id']),
            'link_code' => isset($options['link_code']) ? $options['link_code'] : '',
            'money_type' => isset($options['money_type']) ? $options['money_type'] : 0,
            'operation_type' => 0,
            'business_type' => $options['business_type'],
            'operation_money' => $options['income_expenses'] * -1,
            'original_money' => $originalMoney,
            'latest_money' => $account['cash_money'],
            'msg' => isset($options['msg']) ? $options['msg'] : '',
            'commercial_tenant_id' => isset($options['commercial_tenant_id']) ? $options['commercial_tenant_id'] : 0, //商户ID
            'from_uid' => isset($options['from_uid']) ? $options['from_uid'] : 0,
            'to_uid' => isset($options['to_uid']) ? $options['to_uid'] : 0,
        ];

        try {
            Db::startTrans();
            $walletLog = new WalletLog();
            if (!$ret = $walletLog->isUpdate(false)->allowField(true)->save($data)) {
                $msg = $walletLog->getError();
                throw new Exception($msg);
            }

            //更新钱包
            if ($updateAccount == true) {
                $ret = $this->updateAccount($account);
                if ($ret['code'] != 1) throw new Exception($ret['msg']);
                $account['crc'] = $this->md5WalletCrc($account);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

        return ['code' => 1, 'msg' => '更新钱包成功！', 'data' => $account];
    }

    /**
     * 更新资金账户
     * @param $account
     * @return array
     */
    protected function updateAccount($account)
    {
        $account['crc'] = $this->md5WalletCrc($account);
        $res = $this->model->where(['user_id' => $account['user_id']])->update($account);
        if ($res === false) return ['code' => 0, 'msg' => '更新钱包失败！'];
        return ['code' => 1, 'msg' => '更新钱包成功！'];
    }

    /**
     * 生成钱包资金签名
     * @param $data
     * @param array $key
     * @return string
     * @Author: wuyh
     * @Date: 2020/3/24 22:57
     */
    private function md5WalletCrc($data, $key = [])
    {
        if (empty($key)) $key = ['cash_money', 'cash_money', 'frozen_money', 'give_money'];

        $tmp = [];
        foreach ($key as $val) {
            if (isset($data[$val])) $tmp[$val] = number_formats($data[$val], 2);
        }

        $tmp['id'] = $data['user_id'];
        ksort($tmp);
        $str = implode(',', $tmp) . config('md5_crc');
        return md5(sha1($str));
    }

    /**
     * 余额提现申请
     * @Author: wuyh
     * @Date: 2020/3/24 16:41
     */
    public function cashWithdraw($params)
    {
        $res = [
            'code' => 0,
            'msg' => '',
            'data' => ''
        ];

        if (2 != config('cfg.CASH_WITHDRAW_SWITCH')) {
            $res['msg'] = empty(config('cfg.CASH_WITHDRAW_OFF_DESC')) ? '暂时不开放提现' : config('cfg.CASH_WITHDRAW_OFF_DESC');
            return $res;
        }

        $money = $params['money'];
        $userId = $params['user_id'];
        $withdrawWay = $params['withdraw_way'];

        if (empty($money) || empty($userId)) {
            $res['msg'] = '参数错误';
            return $res;
        }

        //检查用钱包的状态
        $userWallet = $this->getUserWalletInfo($userId, true);
        $ret = $this->checkWalletStatus($userWallet);
        if ($ret['code'] != 1) {
            $res['msg'] = '钱包状态异常，请联系客服';
            return $res;
        }

        if ($money < config('cfg.MIN_WITHDRAW_MONEY')) {
            $res['msg'] = '提现最低不能少于' . config('cfg.MIN_WITHDRAW_MONEY') . '元';
            return $res;
        }

        $map = [
            'user_id' => $userId,
            'create_at' => [
                'between',
                [
                    strtotime(date('Y-m-d') . ' 00:00:00'),
                    strtotime(date('Y-m-d') . ' 23:59:59')
                ]
            ],
            'status' => ['<>', Withdraw::WITHDRAW_STATUS_REJECT]
        ];

        $withdraw = new Withdraw();
        $todayCount = $withdraw->where($map)->count();
        if (config('cfg.DAY_MAX_WITHDRAW_NUM') && $todayCount >= config('cfg.DAY_MAX_WITHDRAW_NUM') ) {
            $res['msg'] = '当天提现次数不能超过' . config('cfg.DAY_MAX_WITHDRAW_NUM');
            return $res;
        }

        $todayMoney = $withdraw->where($map)->sum('money');
        if (config('cfg.DAY_MAX_WITHDRAW_NUM') && ($todayMoney + $money) > config('cfg.DAY_WITHDRAW_MAX_MONEY')) {
            $res['msg'] = '当天提现额度不能超过' . config('cfg.DAY_WITHDRAW_MAX_MONEY');
            return $res;
        }

        $totalMoney = $withdraw->where(['user_id' => $userId, 'status' => ['<>', Withdraw::WITHDRAW_STATUS_REJECT]])->sum('money');

        if (($totalMoney + $money) > config('cfg.MAX_WITHDRAW_MONEY') && config('cfg.MAX_WITHDRAW_MONEY')) {
            $res['msg'] = '您累计提现金额超过' . config('cfg.MAX_WITHDRAW_MONEY') . '上限';
            return $res;
        }

        //提现方式是不是支持
//        $withdrawWay = explode();
//        if (in_array($withdrawWay, config('')))

        //计算手续费
        $poundageRatio = config('cfg.CASH_WITHDRAW_POUNDAGE');
        $payPoundage = $this->_cateMoney($money, $poundageRatio);


        if ($userWallet['cash_money'] < ($money + $payPoundage)) {
            $res['msg'] = '余额不足，请确定提现金额后再重试';
            return $res;
        }

        $arrivalMoney = max(($money - $payPoundage), 0);

        if ($arrivalMoney == 0) {
            $res['mg'] = '扣除手续费手，可提现金额为0';
            return $res;
        }

        $data = [
            'user_id' => $userId,
            'money' => $money,
            'arrival_money' => $arrivalMoney,
            'poundage' => $payPoundage,
            'withdraw_type' => Withdraw::WITHDRAW_TYPE_CASH,
            'withdraw_way' => $withdrawWay,
            'poundage_ratio' => $poundageRatio,
            'withdraw_no' => create_no(config('enum.orders_prefix')['CWH'])
        ];
        $withdrawValidate = new WithdrawValidate();
        $msg = $withdrawValidate->scene('withdraw')->check($data);

        if ($msg !== true) {
            $res['msg'] = $withdrawValidate->getError();
            return $res;
        }

        try {
            Db::startTrans();

            //创建提现订单
            $ret = $withdraw->allowField(true)->save($data);
            if ($ret === false) throw new Exception('申请是失败');

            //创建钱包异动
            $options = [
                'user_id' => $userId,
                'business_type' => 10001,
                'income_expenses' => $money,
                'link_code' => $data['withdraw_no'],
                'money_type' => WalletLog::MONEY_TYPE_FROZEN,
                'msg' => "余额提现",
            ];

            $ret = $this->cashDec($userWallet, $options, true);
            if ($ret['code'] == 0) throw new Exception('申请失败 - 1002');

            Db::commit();
            return ['code' => 1, 'msg' => '申请成功'];
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

        //是否要实现风控监控任务(wuyh)
    }

    /**
     * 钱包明细
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/25 10:11
     */
    public function walletLogList($params)
    {
        $res = [
            'count' => 0,
            'list' => [],
        ];

        $map = [];

        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : config('cfg.ORDER_PAGE_LIMIT');
        $withdrawLog = new WalletLog();

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $map = [
                'user_id' => $params['user_id']
            ];
        }

        // 获取数据列表
        $res['count'] = $withdrawLog->where($map)->count();

        $data = $withdrawLog->where($map)
            ->with('Withdraw')
            ->page($page, $limit)
            ->order('create_at DESC')
            ->select();

        if (!empty($data)) {
            $data = $data->toArray();
            $businesstypeList = config('enum.business_type');
            $moneyTypeList = config('enum.money_type');
            $withdrawStatusList = config('enum.withdraw_status');

            foreach ($data as &$v) {
                // 业务类型
                $v['business_type_text'] = isset($businesstypeList[$v['business_type']]) ? $businesstypeList[$v['business_type']] : '未知';
                // 金额类型
                $v['money_type_text'] = isset($moneyTypeList[$v['money_type']]) ? $moneyTypeList[$v['money_type']] : '未知';
                //提现状态
                $v['status_text'] = isset($withdrawStatusList[$v['status']]) ? $withdrawStatusList[$v['status']] : '未知';
                // 操作原因
                $v['msg'] = empty($v['msg']) ? '' : str_replace("\n", '<br />', $v['msg']);
            }

            $res['list'] = $data;
        }

        return $res;

    }

    /**
     * 提现服务费
     * @param $money
     * @param int $cate 手续费率
     * @return float|int
     * @Author: wuyh
     * @Date: 2020/3/24 18:54
     */
    private function _cateMoney($money, $cate = 100)
    {
        $cateMoney = $money * ($cate / 100);
        return $cateMoney;
    }

    /**
     * 检查钱包的状态
     * @param $account
     * @param bool $lock
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/28 10:56
     */
    public function checkWalletStatus($account, $lock = true)
    {
        $crc = $this->md5WalletCrc($account);
        $status = [];

        if ($crc == $account['crc']) {
            $status['crc'] = [
                'status' => 1,
                'msg' => 'crc正确',
            ];
        } else {
            $status['crc'] = [
                'status' => 0,
                'msg' => 'crc错误',
            ];
        }

        if ($account['status'] == UserWallet::WALLET_STATUS_NORMAL) {
            $status['status'] = [
                'status' => 1,
                'msg' => '钱包状态正常',
            ];
        } else {
            $status['status'] = [
                'status' => $account['status'],
                'msg' => '钱包状态异常',
            ];
        }

        //crc错误则冻结资金账户
        //注意 ： 如果使用事务嵌套，请将lock设为false,因为事务回滚时此项无效
        if ($status['status']['status'] == 1 && $lock == true) {
            if ($account['status'] != UserWallet::WALLET_STATUS_NORMAL)  return ['code' => 0, 'msg' => '钱包异常', 'data' => $status];
            $lockData = [
                'id' => $account['id'],
                'user_id' => $account['user_id'],
                'reason' => '【系统检测】 用户钱包存在风险！',
            ];

            $ret = $this->lockWallet($account['id'], $lockData);
            return ['code' => 0, 'msg' => '钱包异常', 'data' => $status];
        }

        return ['code' => 1, 'msg' => '钱包正常', 'data' => $status];
    }

    /**
     * 冻结钱包
     * @param $id
     * @param $options
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/28 11:10
     */
    public function lockWallet($id, $options)
    {
        $data = [
            'user_id' => $options['user_id'],
            'admin_id' => isset($options['admin_id']) ? $options['admin_id'] : 1,
            'reason' => $options['reason'],
            'url' => request()->url(),
        ];

        try {
            Db::startTrans();
            $ret = $this->model->where(['id' => $id])->update([ 'status' => UserWallet::WALLET_STATUS_FROZEN]);
            if ($ret === false)  throw new Exception('更新钱包状态失败！');

            //写日志
            $walletExceptionLog = new WalletExceptionLog();
            $ret = $walletExceptionLog->save($data);
            if ($ret === false)  throw new Exception('创建资金账户冻结日志失败！('.$walletExceptionLog->getError().')');
            Db::commit();
            return ['code' => 1, 'msg' => '钱包已冻结！'];
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 0,'msg' => $e->getMessage()];
        }
    }
}

