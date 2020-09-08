<?php
namespace app\admin\controller;
use app\common\model\AdminUser;
use library\Rsa;
use think\captcha\Captcha;
use think\Validate;
use library\Auth;
class Index extends Base {


    public function Index(){
        $this->redirect('index/login');
    }

    public function login() {
        $url = $this->request->get('url', 'index/view');
        if ( $this->request->isPost() ) {
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $username  = trim($this->request->post('username'));
            $password  = $this->request->post('password');
            $captcha  = $this->request->post('captcha');

            $keeplogin = $this->request->post('keeplogin');
            $rsa = new Rsa();
            $password = $rsa->jsDeCrypt($password);
            $rule = [
                ['username', 'require|length:2,16', '账号不能为空|账号长度为2-16位字符'],
                ['password', 'require|length:6,16', '密码不能为空|密码长度为6-16位字符'],
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
            ];

            if(!captcha_check($captcha)){
                $this->error("验证码不正确", 'index/login');
            };

            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError(), 'index/login');
            }
            $adminUser = new AdminUser();
            $info = $adminUser->getAdminUserByUsername($username);

            if (empty($info)) {
                $this->error("账号不存在", 'index/login');
            }

            // 判断账号状态
            $info['isable'] == 0 && $this->error("账号禁止登录", 'index/login');

            $result = Auth::login($username, $password, $keeplogin ? 86400 : 0);
            if ($result['code'] == 200) {
                $this->success('登录成功', 'index/view');
            } else {
                $this->error('登录失败', 'index/login');
            }
        }

        if ( Auth::instance()->isLogin() ) {
            $this->redirect($url);
        }
        // 根据客户端的cookie,判断是否可以自动登录
        if ( Auth::instance()->autologin() ) {
            $this->redirect($url);
        }
        return $this->view->fetch();
    }


    public function view() {

        return $this->view->fetch();
    }

    public function console(){
        return $this->view->fetch();
    }

    public function logout() {
        Auth::logout();
        return $this->redirect('login');
    }

    //验证码
    public function verify(){
        $captcha = new Captcha();
        return $captcha->entry();
    }
}