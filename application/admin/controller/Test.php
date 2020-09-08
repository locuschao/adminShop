<?php
//测试
namespace app\admin\controller;
use library\Rsa;
class Test{
    public function aa(){
        $rsa = new Rsa();
        $data = $rsa->encrypt('123456');
        $data = $rsa->decrypt($data);
        print_r($data);die;
    }

    public function bb(){
        $password = "123456";
        $rsa = new Rsa();
        $password = $rsa->jsDeCrypt($password);
        print_r($password);die;
    }

    public function cc(){
        require_once EXTEND_PATH."/WxPay/lib/WxPay.Api.php";
        try{
            $transaction_id = $_REQUEST["transaction_id"];
            $total_fee = $_REQUEST["total_fee"];
            $refund_fee = $_REQUEST["refund_fee"];
            $input = new \WxPayRefund();
            $input->SetTransaction_id($transaction_id);
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);

            $config = new \WxPayConfig();
            $input->SetOut_refund_no("sdkphp".date("YmdHis"));
            $input->SetOp_user_id($config->GetMerchantId());
            printf_info(WxPayApi::refund($config, $input));
        } catch(\Exception $e) {
            print_r($e);
        }
    }

    public function dd(){
        $client = new \GuzzleHttp\Client();
    }
}