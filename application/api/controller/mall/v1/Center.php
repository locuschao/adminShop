<?php
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\model\GoodsOrder as GoodsOrderModel;
use app\common\model\Region;
use app\common\model\ShopConfig;
use app\common\model\UserAddress;
use app\common\service\UserService;
use app\common\model\ArticleCollect;
use app\common\model\GoodsCollect;
use app\common\service\OrderService;
use app\common\service\UserWalletService;
use app\common\service\CollectionService;
use app\common\service\FollowService;
use app\common\service\CouponService;
use library\Des;
use think\Log;

class Center extends Base{

    //下单选择地址
    public function selectAdderss(){
        $token = $this->_param('token');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $userAddressModel =  new UserAddress();
        $where = array();
        $where['user_id'] = $user_id;
        $where['is_default'] = 1;
        $address = $userAddressModel ->getDetail($where);
        if(empty($address)){
            $address = $userAddressModel ->getDetail(array('user_id'=>$user_id));
        }
        $this->_response['data']['list'] = $address;
        $this->_success('v1.center:selectAdderss');
    }

    //获取用户地址列表
    public function userAdderssList(){
        $token = $this->_param('token');
        $page = $this->_param('page');
        $pagesize = $this->_param('pagesize');
        $offset = empty($page)?$page*$pagesize:($page-1)*$pagesize;
        $limit = $pagesize;
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $userAddressModel =  new UserAddress();
        $userService = new UserService();
        $count = $userAddressModel -> getUserAddressCount($user_id);
        $has_next = $count>$page*$pagesize?1:0;
        $address = $userService ->getUserAddressListCache($user_id,$offset,$limit);
        $this->_response['data']['count'] = $count;
        $this->_response['data']['has_next'] = $has_next;
        $this->_response['data']['list'] = $address;
        $this->_success('v1.center:userAdderssList');
    }

    //添加用户地址
    public function addUserAddress(){
        $token = $this->_param('token');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $consignee = $this->_param('consignee');
        $province = $this->_param('province');
        $city = $this->_param('city');
        $area = $this->_param('area');
        $town = $this->_param('town');
        $mobile = $this->_param('mobile');
        $is_default = $this->_param('is_default');
        if(empty($consignee)){
            $this->_error('收货人不能为空');
        }
        if(empty($mobile)){
            $this->_error('收货人电话号码不能为空');
        }
        $regionModel = new Region();
        $province_address = $regionModel -> getDetail(array("id"=>$province));
        $city_address = $regionModel -> getDetail(array("id"=>$city));
        $area_address = $regionModel -> getDetail(array("id"=>$area));
        $address = $province_address['name'].$city_address['name'].$area_address['name'].$town;
        $data = array(
            'user_id'=>$user_id,
            'consignee'=>$consignee,
            'province'=>$province,
            'city'=>$city,
            'area'=>$area,
            'town'=>$town,
            'mobile'=>$mobile,
            'address'=>$address,
            'is_default'=>$is_default,
        );
        $userAddressModel =  new UserAddress();
        $res = $userAddressModel -> update(array('is_default'=>0),array('user_id'=>$user_id));
        if(false === $res){
            $this->_error('DATA_ACTION_ERROR');
        }
        $id = $userAddressModel -> insertGetId($data);
        if($id<=0){
            $this->_error('DATA_ACTION_ERROR');
        }
        $userService = new UserService();
        $userService ->clearAddressCache($user_id,0,10);
        $this->_success('v1.center:addUserAddress');
    }

    //获取用户地址
    public function getUserAddress(){
        $token = $this->_param('token');
        $address_id = $this->_param('address_id');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $userAddressModel =  new UserAddress();
        $address = $userAddressModel->where(array('id'=>$address_id,'user_id'=>$user_id))->find();
        $address = empty($address)?array():$address->toArray();
        $this->_response['data']['list'] = $address;
        $this->_success('v1.center:getUserAddress');
    }

    //编辑地址用户地址
    public function editUserAddress(){
        $token = $this->_param('token');
        $address_id = $this->_param('address_id');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $consignee = $this->_param('consignee');
        $province = $this->_param('province');
        $city = $this->_param('city');
        $area = $this->_param('area');
        $town = $this->_param('town');
        $mobile = $this->_param('mobile');
        $is_default = $this->_param('is_default');
        if(empty($consignee)){
            $this->_error('收货人不能为空');
        }
        if(empty($mobile)){
            $this->_error('收货人电话号码不能为空');
        }
        $regionModel = new Region();
        $province_address = $regionModel -> getDetail(array('id'=>$province));
        $city_address = $regionModel -> getDetail(array('id'=>$city));
        $area_address = $regionModel -> getDetail(array("id"=>$area));
        $address = $province_address['name'].$city_address['name'].$area_address['name'].$town;
        $data = array(
            'user_id'=>$user_id,
            'consignee'=>$consignee,
            'province'=>$province,
            'city'=>$city,
            'area'=>$area,
            'town'=>$town,
            'mobile'=>$mobile,
            'address'=>$address,
            'is_default'=>$is_default,
        );
        $userAddressModel =  new UserAddress();
        $res = $userAddressModel -> update(array('is_default'=>0),array('user_id'=>$user_id));
        if(false === $res){
            $this->_error('DATA_ACTION_ERROR');
        }
        $update_address = $userAddressModel -> update($data,array('id'=>$address_id));

        if(false == $update_address){
            $this->_error('DATA_ACTION_ERROR');
        }
        $userService = new UserService();
        $userService ->clearAddressCache($user_id,0,10);
        $this->_success('v1.center:editUserAddress');
    }

    //默认地址
    public function defaultAddress(){
        $token = $this->_param('token');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $address_id = $this->_param('address_id');
        $is_default = $this->_param('is_default');
        $userAddressModel =  new UserAddress();
        $res = $userAddressModel -> update(array('is_default'=>0),array('user_id'=>$user_id));
        if(false === $res){
            $this->_error('DATA_ACTION_ERROR');
        }
        $update_id = $userAddressModel -> update(array('is_default'=>$is_default),array('id'=>$address_id));
        if(false === $update_id){
            $this->_error('DATA_ACTION_ERROR');
        }
        $userService = new UserService();
        $userService ->clearAddressCache($user_id,0,10);
        $this->_success('v1.center:defaultAddress');
    }

    //删除地址
    public function deleteAddress(){
        $token = $this->_param('token');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        list($openid,$user_id) = explode("|",$userInfo);
        $address_id = $this->_param('address_id');
        $userAddressModel =  new UserAddress();
        $res = $userAddressModel -> where(array('id'=>$address_id,'user_id'=>$user_id))->delete();
        if(!$res){
            $this->_error('DATA_ACTION_ERROR');
        }
        $userService = new UserService();
        $userService ->clearAddressCache($user_id,0,10);
        $this->_success('v1.center:deleteAddress');
    }

    //关于公司简介
    public function about(){
        $shopConfigModel =  new ShopConfig();
        $shopConfig = $shopConfigModel->getDetail(array('id'=>1));
        $this->_response['data']['list'] = $shopConfig;
        $this->_success('v1.center:about');
    }



    /**
     * 我收藏的文章
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/19 13:23
     */
    public function articleCollect()
    {
        if (empty($this->userInfo)) return $this->_error('PARAM_NOT_EMPTY');
        $page = $this->_param('page') ? $this->_param('page') : 1;
        $limit = $this->_param('limit') ? $this->_param('limit') : config('cfg.ORDER_PAGE_LIMIT');

        $params = [
            'user_id' => $this->userInfo['id'],
            'page' => $page,
            'limit' => $limit,
        ];

        $collectService = new CollectionService();
        $data = $collectService->userArticlesCollectList($params);
        $this->_response['data']['list'] = $data['count'];
        $this->_response['data']['list'] = $data['list'];
        $this->_success('v1.Mall.Center:articleCollect');
    }

    /**
     * 我收藏的商品
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/18 13:24
     */
    public function goodsCollect()
    {
        if (empty($this->userInfo)) return $this->_error('PARAM_NOT_EMPTY');
        $page = $this->_param('page') ? $this->_param('page') : 1;
        $limit = $this->_param('limit') ? $this->_param('limit') : config('cfg.ORDER_PAGE_LIMIT');

        $params = [
            'user_id' => $this->userInfo['id'],
            'page' => $page,
            'limit' => $limit,
        ];

        $collectService = new CollectionService();
        $data = $collectService->userGoodsCollectList($params);
        $this->_response['data']['list'] = $data['count'];
        $this->_response['data']['list'] = $data['list'];

        return  $this->_success('v1.Mall.Center:goodsCollect');
    }

    /**
     * 个人中心详情
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/23 17:15
     */
    public function index()
    {
        $orderService = new OrderService();
        $userWalletService = new UserWalletService();
        $collectionService = new CollectionService();
        $followService = new FollowService();
        $couponService = new CouponService();

        if (empty($this->userInfo)) return $this->_error('PARAM_NOT_EMPTY');

        $userId = $this->userInfo['id'];

        $data = [
            'base_info'=> [
                'nickname' => $this->userInfo['nickname'],
                'head_url' => $this->userInfo['head_url'],
                'mobile' => encrypt_phone($this->userInfo['mobile']),
            ],
            'conpoun' => $couponService->getCouponCountByUser($userId), //卡包
            'collect' => $collectionService->getCollectCountByUser($userId), //收藏
            'follow' => $followService->getFollowCountByUser($userId), //关注数
            'wallet' => $userWalletService->getUserWalletInfo($userId), //钱包
            'order_status_total' => $orderService->getOrderCountByUser($userId),
        ];

        $this->_response['data'] = $data;
        return  $this->_success('v1.Mall.Center:index');
    }


    /**
     * 我的关注
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/24 11:28
     */
    public function myFollow()
    {
        if (empty($this->userInfo)) return $this->_error('PARAM_NOT_EMPTY');

        $userFollow = new FollowService();
        $userId = $userId = $this->userInfo['id'];
        $page = $this->_param('page') ? $this->_param('page') : 1;
        $limit = $this->_param('limit') ? $this->_param('limit') : config('cfg.ORDER_PAGE_LIMIT');

        $params = [
            'user_id' => $userId,
            'page' => $page,
            'limit' => $limit,
        ];

        $data = $userFollow->userFollowList($params);
        $this->_response['data']['count'] = $data['count'];
        $this->_response['data']['list'] = $data['list'];
        return $this->_success('v1.Mall.Center:myFollow');
    }
}