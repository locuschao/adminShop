<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Cache;

/**
 * 输出xml字符
 * @param   $params array 参数
 *
 * @return   string      返回组装的xml
 **/
function dataToXml($params)
{
    if (!is_array($params) || count($params) <= 0) {
        return false;
    }
    $xml = "<xml>";
    foreach ($params as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";

    return $xml;
}

function random($length, $numeric = 0)
{
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}

/**
 * 写入日志
 *
 * @param string $path 日志路径
 * @param string $level
 *  log 常规日志，用于记录日志
 *  error 错误，一般会导致程序的终止
 *  notice 警告，程序可以运行但是还不够完美的错误
 *  info 信息，程序输出信息
 *  debug 调试，用于调试信息
 *  sql SQL语句，用于SQL记录，只在数据库的调试模式开启时有效
 */
function log_message($msg, $level = 'info', $path = LOG_PATH)
{
    \think\Log::init([
        'type' => 'File',
        'path' => $path
    ]);
    \think\Log::write($msg, $level);
}


/**
 * 将二维数组并根据某个字段排序排序
 * @param  string $field 要排序的字段
 * @param  string $sort 排序  0-倒序 1-正序
 * @param  string $ret 二维数组
 */
function sortDataByField($field, $sort, $ret)
{

    //排序
    for ($i = 0; $i < count($ret); $i++) {
        for ($j = 0; $j < $i; $j++) {
            if (!isset($ret[$i][$field]) || !isset($ret[$j][$field])) {
                exit("Parameter error: $field is not defined");
            }
            $a = $ret[$i][$field];
            $b = $ret[$j][$field];
            if ($sort ^ $a - $b > 0) {// ^ 异或运算符
                $temp1 = $ret[$i];
                $ret[$i] = $ret[$j];
                $ret[$j] = $temp1;
                $temp = $a;
                $ret[$i][$field] = $b;
                $ret[$j][$field] = $temp;
            }
        }
    }
    return $ret;
}

/**
 * @param $array
 * @param string $field [指定字段]
 * @return array
 * [指定索引建立数组]
 */
function arrayByField($array, $field = "")
{
    $result = array();

    if (empty($array) || !is_array($array)) return $result;

    foreach ($array as $value) {
        if (isset($value[$field]) && !empty($value[$field])) {
            $result[$value[$field]] = $value;
        }
    }

    return $result;
}


if (!function_exists('global_MkDirs')) {
    function global_MkDirs($dir, $mode = 0775)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (!global_MkDirs(dirname($dir), $mode)) return false;

        return @mkdir($dir, $mode);
    }
}


function combineAttributes($list)
{
    $index = 0;
    if (count($list) == 2) {
        $result = recurse2($list[0], $list[1]);
    } elseif (count($list) == 3) {
        $result = recurse3($list[0], $list[1], $list[2]);
    } else {
        $result = recurse1($list[$index]);
    }
    return $result;
}

function recurse1($array)
{
    $res = array();
    foreach ($array as $v) {
        $t = array();
        $t['key'] = $v['id'];
        $t['key_name'] = $v['item'];
        array_push($res, $t);
    }
    return $res;
}

/**
 * @param $array1
 * @param $array2
 * @return array
 */
function recurse2($array1, $array2)
{
    $res = array();
    foreach ($array1 as $v1) {
        foreach ($array2 as $v2) {
            $t = array();
            $t['key'] = $v1['id'] . "_" . $v2['id'];
            $t['key_name'] = $v1['item'] . "/" . $v2['item'];
            array_push($res, $t);
        }
    }
    return $res;
}

/**
 * @param $array1
 * @param $array2
 * @param $array3
 * @return array
 */
function recurse3($array1, $array2, $array3)
{
    $res = array();
    foreach ($array1 as $v1) {
        foreach ($array2 as $v2) {
            foreach ($array3 as $v3) {
                $t = array();
                $t['key'] = $v1['id'] . "_" . $v2['id'] . "_" . $v3['id'];
                $t['key_name'] = $v1['item'] . "/" . $v2['item'] . "/" . $v3['item'];
                array_push($res, $t);
            }
        }
    }
    return $res;
}

/**
 * 接口请求
 * @param $api
 * @param $data
 * @param string $method
 * @return array
 */
function api($api, $data, $method = 'GET')
{
    $apiCfg = config('adminSystem');

    $api = $apiCfg['Api']['Host'] . $api;

    //参数中增加时间戳
    $params['time'] = time();

    //生成签名
    ksort($params);
    $tmp = [];

    foreach ($params AS $key => $val) {
        $tmp[] = $key . '=' . $val;
    }

    $sign = strtoupper(md5(implode('&', $tmp) . $apiCfg['Api']['AccessSecret']));

    $headers = [
        'access-key:' . $apiCfg['Api']['AccessKey'],
        'sign:' . $sign
    ];

    $res = global_Curl($api, $data, $method, $headers);

    return $res;
}


/**
 * 统一CURL
 *
 * @param string $url 地址
 * @param array $params 参数
 * @param string $method 提交方式(GET,POST,PUT,DELETE)
 * @param string $headers 头信息
 * @return array
 */
function global_Curl($url, $params = array(), $method = 'GET', $headers = array())
{
    if (empty($url)) return false;

    $ch = curl_init();

    if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    switch ($method) {
        case 'GET':
            $params = !empty($params) ? '?' . http_build_query($params) : '';

            curl_setopt($ch, CURLOPT_URL, $url . $params);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;

        case 'POST':
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;

        case 'PUT':
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));    //如果value是一个数组，Content-Type头将会被设置成multipart/form-data，所以转成字符串
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;

        case 'DELETE':
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
    }

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


/**
 * 下拉选择框
 * @param array $data 下拉框数据源
 * @param int $selected 选择数据ID
 * @param string $textField 显示名称(支持多字段显示)
 * @param string $valField 显示值
 * @author wuyh
 */
function create_option($data, $selected = 0, $textField = 'name', $valField = 'id')
{
    $result = '';
    $fields_arr = explode(',', $textField);
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $show_text = '';
            if (is_array($val)) {
                foreach ($fields_arr as $field) {
                    $show_text .= $val[$field] . ' ';
                }
                $show_text = substr($show_text, 0, -1);
                $valField && $key = $val[$valField];
            } else {
                $show_text = $val;
            }
            $sel = '';
            if ($selected !== '' && $key == $selected) {
                $sel = 'selected';
            }
            $result .= '<option value = ' . $key . ' ' . $sel . '>' . $show_text . '</option>';
        }
    }
    echo $result;
}

/**
 * 获取无限分类的数据
 * @param $data
 * @param int $id
 * @param string $pidKey 父id的key名
 * @param string $idKey 主键的key名
 * @return array
 * @author  wuyh
 */
function getChild($data, $id = 0, $pidKey = 'pid', $idKey = 'id')
{
    $children = array();
    foreach ($data as $v) {
        if ($v[$pidKey] == $id) {
            foreach ($data as $subv) {
                if ($subv[$pidKey] == $v[$idKey]) {
                    $v['children'] = array_values(getChild($data, $v[$idKey], $pidKey, $idKey));
                    break;
                }
            }
            $children[] = $v;
        }
    }
    return $children;
}

/**
 * @param $data
 * @param int $id
 * @return array
 * 返回省市区[格式]
 */
function getChildRegion($data, $id = 0)
{
    //创建一个保存最后输出结果的数据
    $tree = [];
    //循环所有数据找到pid为传入id的数组，也就是找到id的儿子们
    foreach ($data as $key => $value) {
        //找到了儿子
        if ($value['parent_id'] == $id) {
            //将数组保存进最后结果
            $tree[$key] = $value;
            //为了减少数据量删掉了自己，因为自己不可能是自己的儿子。。
            unset($data[$key]);
            //将儿子的儿孙数组保存到儿子的child数组里（递归查找）
            $tree[$key]['child'] = array_values(getChildRegion($data, $value['id']));
        }
    }
    return $tree;
}

/**
 * 支付方式
 * @param $payType
 * @return string
 */
function payTypeHtml($payType)
{
    $html = '';
    switch ($payType) {
        case 1:
            $html = '微信';
            break;
        case 2:
            $html = "支付宝";
            break;
        default:
            $html = '未知';
    }
    return $html;
}

/**
 * 直播状态
 * @param $staus
 * @return string
 */
function payStatusHtml($status)
{
    //0待支付,1支付失败, 99支付成功
    $html = '';
    switch ($status) {
        case 0:
            $html = '<span style="color: #FFB800;">未支付</span>';
            break;
        case 1:
            $html = '<span style="color: #F581B1;">支付失败</span>';
            break;
        case 99:
            $html = '<span style="color: #20a53a;">已支付</span>';
            break;
        default:
            $html = '<span style="color: #FFB800;">未支付</span>';
    }
    return $html;
}

/**
 * 商品规格
 * @param $item
 * @return mixed|string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function goodsItem($item)
{
    $info = model('Spec')->where(['id' => $item])->find();

    return empty($info) ? '-' : $info->item;
}

/**
 * 发货状态
 * @param $status
 * @return string
 */
function shipStatusHtml($status)
{
    $html = '';
    switch ($status) {
        case 0:
            $html = '<span style="color: #FFB800;">未发货</span>';
            break;
        case 1:
            $html = '<span style="color: #F581B1;">部分发货</span>';
            break;
        case 2:
            $html = '<span style="color: #009688;">已发货</span>';
            break;
        case 3:
            $html = '<span style="color: #01AAED;">部分退货</span>';
            break;
        case 3:
            $html = '<span style="color: #01AAED;">已退货</span>';
            break;
        default:
            $html = '<span style="color: #FFB800;">未发货</span>';
    }
    return $html;
}


/**
 * 自动创建登录账号
 */
if (!function_exists('autoCreateUsername')) {
    function autoCreateUsername()
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
        $username = "";
        for ($i = 0; $i < 6; $i++) {
            $username .= $chars[mt_rand(0, strlen($chars) - 5)];
        }
        return $username;
    }
}

/**
 * 自动生成密码
 */
if (!function_exists('autoCreatePassword')) {
    function autoCreatePassword($password)
    {
        $p = md5($password);
        return md5(substr($p, 16, 8) . substr($p, 0, 8) . substr($p, 24, 8) . substr($p, 8, 8));
    }
}

/**
 * @param $unique
 * @param $session_key
 * @param $user_id
 * @return string
 * token
 */
function CreateToken($unique, $session_key, $user_id)
{
    return md5($unique . '_' . $session_key . '_' . $user_id);
}

/*
 * 获取客户端
 */
function getClientIp($type = 0, $adv = true)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * @return mixed
 * 返回微妙
 */
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return $usec;
}

/**
 * 生成订单号,每秒可产生9999个不重复的订单号(24位订单号)
 * @return string
 */
function makeOrderid()
{

    $orderid = date("YmdHis", time()) . str_pad((microtime_float() * 1000000), 6, 0, STR_PAD_RIGHT) . random_int(1000, 9999);
    // 判断是否有同个订单号的请求还没处理完，防止因为网络问题，导致同个订单并发
    $cache_key = 'orderid:' . $orderid;
    if (think\Cache::store('redis')->has($cache_key)) {
        return makeOrderid();
    } else {
        think\Cache::store('redis')->set($cache_key, $orderid, 86400);
        return $orderid;
    }
}

/**
 * @param $is_free_shipping
 * @param $template_id
 * @return int
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * 运费计算
 */
function template_freight($is_free_shipping, $template_id)
{
    if (empty($is_free_shipping)) {
        return 0;
    }
    $freightConfigModel = new \app\common\model\FreightConfig();
    $freight = $freightConfigModel->where(array('template_id' => $template_id))->order('config_id asc')->find();
    if (empty($freight)) {
        return 0;
    }
    return $freight->toArray()['first_money'];
}

/**
 * @param $str
 * @return string
 * 去掉表情图标
 */
function removeEmojiChar($str)
{
    $mbLen = mb_strlen($str);

    $strArr = [];
    for ($i = 0; $i < $mbLen; $i++) {
        $mbSubstr = mb_substr($str, $i, 1, 'utf-8');
        if (strlen($mbSubstr) >= 4) {
            continue;
        }
        $strArr[] = $mbSubstr;
    }

    return implode('', $strArr);
}

/**
 * @param $prefix [前缀]
 * @param $suffix [后缀]
 * @return string
 * [生成优惠券号]
 */
function generateCode($prefix = 3, $suffix = 4)
{
    $prefix_str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $suffix_str = "abcdefghijklmnpqrstuvwxyz";

    $code = '';
    for ($i = 0; $i < $prefix; $i++) {

        $code .= $prefix_str[mt_rand(0, strlen($prefix_str) - 1)];
    }
    $code .= date('YmdHis', time()) . str_pad((microtime_float() * 1000000), 6, 0, STR_PAD_RIGHT);
    for ($i = 0; $i < $suffix; $i++) {

        $code .= $suffix_str[mt_rand(0, strlen($suffix_str) - 1)];
    }
    return $code;
}


//验证电话号码
function check_phone($phone)
{
    $check = '/^(1(([35789][0-9])|(47)))\d{8}$/';
    if (preg_match($check, $phone)) {
        return true;
    } else {
        return false;
    }
}

//验证账号
function check_username($username)
{
    $check = '/^[a-zA-Z0-9]+$/';
    if (preg_match($check, $username)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 格式化数字
 * @param $number
 * @param int $decimals
 * @return string
 * @Author: wuyh
 * @Date: 2020/3/23 19:46
 */
function number_formats($number, $decimals = 2)
{
    return number_format($number, $decimals, '.', '');
}

/**
 * 手机加密
 * @param $phone
 * @return string
 * @Author: wuyh
 * @Date: 2020/3/23 20:35
 */
function encrypt_phone($phone)
{
    if (empty($phone)) return '';
    $new_tel = substr($phone, 0, 3) . '****' . substr($phone, 7);
    return $new_tel;
}

/**
 * 高并发下创建不重复流水号,主要用于钱包异动
 * @param string $prefix
 * @param string $uid
 * @return string
 * @Author: wuyh
 * @Date: 2020/3/24 19:26
 */
function create_no($prefix = '', $uid = '')
{
    if (empty($uid)) $uid = date('His');
    $str = $prefix . session_id() . microtime(true) . uniqid(md5(microtime(true)), true);
    $str = md5($str);
    $prefix = $prefix . date('YmdH') . $uid;
    $code = $prefix . substr(uniqid($str, true), -8, 8);
    return $code;
}

/**
 * 规范数据返回
 * @param $code
 * @param string $msg
 * @param array $data
 * @return array
 * @Author: wuyh
 * @Date: 2020/3/26 21:21
 */
function json_return($code, $msg = '', $data = [])
{
    return array('code' => $code, 'msg' => $msg, 'data' => $data);
}