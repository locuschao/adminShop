<?php
/**
 * [微信小程序相关功能]
 */
namespace app\api\controller\live;
use app\common\model\LiveUser as User;
use library\Aes;
use library\Des;

class Wx extends Base {

      //登录
      public function login(){
          $code = $this->_param('code');
          $encryptedData = $this->_param('encryptedData');
          $iv = $this->_param('iv');
          $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$this->appid&secret=$this->appSecret&js_code=$code&grant_type=authorization_code";
          $result = http_get($url);
          if(isset($result['openid'])&&!empty($result['openid'])){
              
              $des = new Des();
              //请求成功
              $aes_data = Aes::decryptData($encryptedData, $result['session_key'], $iv,$this->appid);
              $aes_data = json_decode($aes_data,true);
              if($aes_data['code']==200){
                  $userInfo = $aes_data['data'];
                  $time = time();
                  $userModel = new User();
                  $user = $userModel->where(array('openid'=>$result['openid']))->find();
                  $user_data = array();
                  $user_data['nickname'] = removeEmojiChar($userInfo['nickName']);
                  $user_data['openid'] = $userInfo['openId'];
                  $user_data['unionid'] = empty($userInfo['unionId'])?'':$userInfo['unionId'];
                  $user_data['oauth'] = 'weixin';
                  $user_data['province'] = $userInfo['province'];
                  $user_data['city'] = $userInfo['city'];
                  $user_data['country'] = $userInfo['country'];
                  $user_data['head_url'] = $userInfo['avatarUrl'];
                  $user_data['sex'] = $userInfo['gender'];
                  $user_data['login_time'] = $time;
                  if(empty($user)){
                      //插入数据库
                      $user_data['reg_time'] = $time;
                      $user_id = $userModel->insertGetId($user_data);
                      if($user_id){
                          $username = autoCreateUsername().$user_id;
                          $password = autoCreatePassword('123456');
                          $userModel->update(array('username'=>$username,'password'=>$password),array('id'=>$user_id));
                          $user_data['token'] = $des->encrypt($userInfo['openId']."|".$user_id);
                          $user_data['sessionKey'] = $result['session_key'];
                          $user_data['user_id'] = $user_id;
                          $this->_response['data'] = $user_data;
                          $this->_success('wx:login');
                      }else{
                          $this->_error('DATA_ACTION_ERROR');
                      }
                  }else{
                      $user = $user->toArray();
                      $update = $userModel->update($user_data,array('openid'=>$userInfo['openId']));
                      if(false === $update){
                          $this->_error('DATA_ACTION_ERROR');
                      }else{
                          $user_data['token'] = $des->encrypt($userInfo['openId']."|".$user['id']);
                          $user_data['sessionKey'] = $result['session_key'];
                          $user_data['user_id'] = $user['id'];
                          $this->_response['data'] = $user_data;
                          $this->_success('wx:login');
                      }
                  }
              }else{
                  $this->_error($aes_data['code'],'解析失败');
              }
          }else{
              //请求失败
              $this->_error($result['errcode'],$result['errmsg']);
          }
      }

      //获取电话号码
      public function getPhone(){
          $sessionKey = $this->_param('sessionKey');
          $user_id = $this->_param('user_id');
          $encryptedData = $this->_param('encryptedData');
          $iv = $this->_param('iv');
          $aes_data = Aes::decryptData($encryptedData, $sessionKey, $iv,$this->appid);
          $aes_data = json_decode($aes_data,true);
          if($aes_data['code']==200){
              $userInfo = $aes_data['data'];
              $userModel = new User();
              $update = $userModel->update(array(''=>$userInfo['phoneNumber']),array('id'=>$user_id));
              if(false === $update){
                  $this->_error('DATA_ACTION_ERROR');
              }else{
                  $user_data['phone'] = $userInfo['phoneNumber'];
                  $this->_response['data'] = $user_data;
                  $this->_success('wx:getPhone');
              }
          }else{
              $this->_error($aes_data['code'],'解析失败');
          }
      }


      //小程序授权获取access_token
      public function token(){
          $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appSecret";
          $result = http_get($url);
          if(isset($result['access_token']) && !empty($result['access_token'])){
              //请求成功
              $this->_response['data'] = array(
                  'access_token'=>$result['access_token'],
                  'expires_in'=>$result['expires_in']
              );
              $this->_success('wx:token');
          }else{
              //请求失败
              $this->_error($result['errcode'],$result['errmsg']);
          }
      }
}
