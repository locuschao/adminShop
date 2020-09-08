<?php
namespace app\api\controller\mall;
use think\Config;
use library\Code;
use library\Field;
use think\Controller;
use think\Request;
use library\Des;

class Base extends Controller {

    // 请求参数
    protected $_params = [];

    private $_md5key = null;

    // 返回数据类型
    protected $_type = 'json';

    // 接口返回值
    protected $_response   = [];

    protected $_isDebug = true; // 自测模拟请求

    protected $appid = null;//小程序appid

    protected $appSecret = null;//小程序AppSecret

    protected $openid = null;//openid

    /**
     * 用户信息
     * @var void|null
     * @author wuyh
     */
    protected $userInfo = null;

    public function __construct()
    {

        $input = file_get_contents('php://input');

        if ( empty($input) || '[]' == $input ) {
            if ( true == Config::get('app_debug') ) {
                $this->_params  = input();
                $this->_isDebug = true;
            } else {
                $this->_error('PARAM_NOT_EMPTY');
            }
        } else {
            log_message('客户端请求数据:'.$input, 'log', Config::get('log_dir').'/mall/');
            $params = json_decode($input, true);
            if (JSON_ERROR_NONE == json_last_error()) {
                $this->_params = $params;

            }
        }
        $this->appid = !empty($this->appid)?$this->appid:Config::get('appid');
        $this->appSecret = !empty($this->appSecret)?$this->appSecret:Config::get('appSecret');
        $this->_md5key = Config::get('md5key');
        $this->_verify();

        //用户信息
        $this->userInfo = $this->getUserInfo();
        $this->_config();
    }

    /**
     * 获取参数
     * @param $key
     * @param $default
     * @return mixed|null
     */
    protected function _param($key, $default = null)
    {
        return isset($this->_params[$key]) ? (is_array($this->_params[$key])?$this->_params[$key]:trim($this->_params[$key])) : ($default ? $default : null);
    }

    /**
     * 签名验证
     */
    private function _verify() {
        $sign= Request::instance()->header('sign');
        ksort($this->_params);
        reset($this->_params);
        $md5str = md5(http_build_query($this->_params) . $this->_md5key);
        log_message('客户端sign:'.$sign.' 服务端sign:'.$md5str, 'log', Config::get('log_dir').'/mall/');
        if ( !$this->_isDebug  && (empty($sign) || $sign != $md5str )) {
            $this->_error('SIGN_NOT_MATCH');
        }
    }

    /**
     * 错误响应输出
     * @param $codeKey
     * @param $errorMsg
     * @return \think\response
     * @throws \Exception
     */
    protected function _error($codeKey, $errorMsg = '')
    {
        if (empty($codeKey)) {
            exception('错误信息不能为空', 400);
        }
        $this->_response = Code::get($codeKey, $errorMsg);

        return $this->_formatResponse();
    }

    /**
     * 格式化输出
     * @return \think\response
     */
    protected function _formatResponse()
    {
        if ($this->_type == 'xml') {
            $object = xml($this->_response);
        } else {
            $object = json($this->_response);
        }
        echo $object->getContent(); exit;
    }

    /**
     * @param string $fieldKey
     * @param string $codeKey
     * @return \think\response
     * @throws \Exception
     *
     */
    protected function _success($fieldKey = '', $codeKey = 'SUCCESS') {
        $this->_response = array_merge($this->_response, Code::get($codeKey));

        // 获取接口输出节点
        $fields = Field::get($fieldKey);
        if (empty($fields) && isset($this->_response['data'])) {
            if (!is_string($this->_response['data'])) {
                $this->_error('ERROR_RESPONSE');
            }
        } elseif (!empty($fields) && isset($this->_response['data'])) {
            $this->_buildResponse($this->_response['data'], $fields);
        }

        $this->_multiKsort($this->_response);
        return $this->_formatResponse();
    }

    /**
     * 重新构造返回字段
     * @param array $array 响应的数组
     * @param array $field 响应的字段
     * @return void
     */
    private function _buildResponse(array &$array, array $field) {
        foreach ( $array as $key => $value ) {
            if ( is_array($value) ) {
                $this->_buildResponse($value, $field);
                $array[$key] = $value;
            } else {
                if ( ! in_array($key, $field) && ('count' != $key) &&('has_next' !=$key) && !preg_match('/^\d{4}\-\d{2}$/', $key) && !preg_match('/^\d{1,}$/', $key) ) {
                    unset($array[$key]);
                }

                // null 转为空字符串
                if (is_null($value)) {
                    $array[$key] = strval($value);
                }
            }
        }
    }

    /**
     * 多维数组按照键名排序(ksort封装)
     * @param array $array 输入的数组
     * @return void
     */
    private function _multiKsort(array &$array) {
        if ( is_array($array) ) {
            ksort($array);
            reset($array);
            foreach ( $array as &$item ) {
                if ( is_array($item) ) {
                    $this->_multiKsort($item);
                }
            }
        }
    }

    /**
     * 用户信息
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/22 12:14
     */
    public function getUserInfo()
    {
        $token = $this->_param('token');

        $des = new Des();
        $userInfo = $des->decrypt($token);

        if (empty($userInfo)) return [];
        $arr = explode("|",$userInfo);

        if (count($arr) < 2) return [];

        $userInfo = model('common/User')->cache(5)->field('password', true)->where(['id' => $arr[1]])->find();
        if (empty($userInfo)) return[];

        return $userInfo->toArray();
    }

    /**
     * 加载配置
     * @return bool
     * @author wuyh
     * @date 2020-02-28
     */
    private function _config()
    {
        $list = [];
        $list = model('SysConfig')->getConfigInfo(true);
        config('cfg',$list);
        return true;
    }
}