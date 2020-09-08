<?php
/*
 * 小程序微信支付
 */
namespace app\common\service;
use think\Config;

class WxPay {

    protected $appid;
    protected $mch_id;
    protected $key;
    protected $notify_url;

    //证书
    static $SSLCERT_PATH = "";//证书path
    static $SSLKEY_PATH = "";//证书key

    function __construct() {
        $this->appid = Config::get('appid');
        $this->mch_id = Config::get('mch_id');
        $this->key = Config::get('wx_key');
        $this->notify_url = Config::get('notify_url');
    }

    //支付
    public function pay($body,$detail,$out_trade_no,$total_fee,$openid,&$error) {
        $parameters = array();
        //统一下单接口
        $result = $this->unifiedorder($body,$detail,$out_trade_no,$total_fee,$openid);
        if($result["return_code"] == "FAIL"){
            $error = $result["return_msg"];
            return $parameters;
        }
        $parameters = array(
            'appId' => $this->appid, //小程序ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr' => $this->createNoncestr(), //随机串
            'package' => 'prepay_id=' . $result['prepay_id'], //数据包
            'signType' => 'MD5'//签名方式
        );
        //签名
        $parameters['paySign'] = $this->getSign($parameters);
        return $parameters;
    }

    //退款
    public function refund($outRefundNo,$outTradeNo,$totalFee,$refundFee,$refundDesc){
        $parameters = array(
            'appid'=> $this->appid,
            'mch_id'=> $this->mch_id,
            'nonce_str'=> $this->createNoncestr(),
            'out_refund_no'=> $outRefundNo,//商户退款单号
            'out_trade_no'=> $outTradeNo,//商户订单号(生成退款订单号)
            'total_fee'=> $totalFee,//订单金额
            'refund_fee'=> $refundFee,//退款金额
            'refund_desc' => $refundDesc,//退款原因
        );
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->ToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60,true));
        return $return;
    }

    //统一下单接口
    private function unifiedorder($body,$detail,$out_trade_no,$total_fee,$openid) {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = array(
            'appid' => $this->appid, //小程序ID
            'mch_id' => $this->mch_id, //商户号
            'nonce_str' => $this->createNoncestr(), //随机字符串
            'body' => $body,
            'detail' => $detail,
            'out_trade_no'=> $out_trade_no,
            'total_fee' => $total_fee*100,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], //终端IP
            'notify_url' => $this->notify_url, //通知地址  确保外网能正常访问
            'openid' => $openid, //用户id
            'trade_type' => 'JSAPI'//交易类型
        );
        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->ToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }

    private static function postXmlCurl($xml, $url, $second = 30,$is_ssl=false)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);

        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch,CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        }else{
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }

        if($is_ssl){
            //设置证书
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, self::$SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, self::$SSLKEY_PATH);
        }
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
        }
    }


    /**
     * 输出xml字符
     **/
    public function ToXml($values)
    {
        $xml = "<xml>";
        foreach ($values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }


    //xml转换成数组
    public function xmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //作用：生成签名
    public function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }


    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


}