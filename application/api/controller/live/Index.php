<?php
namespace app\api\controller\live;

use think\Config;

class Index extends Base {
  public function index(){
      print_r(Config::get('appid'));die;
      echo time();die;
  }
}