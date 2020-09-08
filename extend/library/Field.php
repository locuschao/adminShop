<?php
namespace library;

class Field
{
    static protected $fields = array(
     'v1.User:test'=>array('md5Key','token'),//测试返回
     'wx:login'=>array('openid','nickname','oauth','province','city','country','head_url','sex','token','sessionKey','user_id'),//小程序登录获取openid
     'wx:token'=>array('access_token','expires_in'),//小程序登录获取access_token
     'wx:getPhone'=>array('phone'),//小程序登录获取access_token
        //第一版
     'v1.AdPos:homeBanner'=>array('id','name','brief','image','url','type'),//首页banner
     'v1.Goods:homeNav'=>array('cate_id','cate_name'),//首页分类
     'v1.Goods:homeList'=>array('goods_id','goods_name','goods_remark','label','shop_price','market_price','image','sales_sum','store_count','color','text','has_next','is_live_goods'),//首页列表
     'v1.Goods:goodsDetail'=>array('goods_id','goods_name','goods_remark','label','color','text','image','market_price','shop_price','is_free_shipping','template_id','shipping_price','spec','spec_name','spec_value','spec_value_id','spec_value_text','goods_content','sales_sum','store_count','is_able','live_id','live_img','is_collect'),//商品详情页
     'v1.Goods:goodsSkuStatus'=>array('spec_name',"spec_value",'spec_value_id','spec_value_text','is_able'),//商品sku状态
     'v1.Goods:goodsAttrDetail'=>array('attr_name','attr_value'),//商品产品参数
     'v1.Goods:goodsSkuDetail'=>array('item_id','price','store_count','spec_img'),//商品sku
     'v1.Goods:goodsKeyword'=>array('goods_id','goods_name','goods_remark','market_price','label','image','shop_price','color','text','sales_sum','store_count'),//商品搜索
     'v1.Cate:cateList'=>array('cate_id','cate_name','image'),//一级导航
     'v1.Cate:cateListNav'=>array('cate_id','cate_name'),//二级导航
     'v1.Cate:cateGoodsList'=>array('goods_id','goods_name','goods_remark','label','shop_price','market_price','image','sales_sum','store_count','color','text','has_next','is_live_goods','is_recommend','is_new'),//商品分类类表
     'v1.center:userAdderssList'=>array('id','consignee','address','mobile','is_default','province','city','area','town','has_next'),//用户地址列表
     'v1.center:getUserAddress'=>array('id','consignee','address','mobile','is_default','province','city','area','town',),//获取单个地址
     'v1.center:selectAdderss'=>array('id','consignee','address','mobile','is_default','province','city','area','town',),//获取单个地址
     'v1.common:region'=>array('id','name','child'),//省市区
     'v1.common:express'=>array('shipping_id','shipping_name'),//快递列表
     'v1.center:about'=>array('content'),//关于公司
     'v1.center:orderList'=>array('has_next','image','status','id','order_id','goods_id','goods_name','goods_num','goods_price','create_time','is_send','is_close_order'),//关于公司
     'v1.order:pay'=>array('order_id','payParams','pay_status','appId','timeStamp','nonceStr','package','signType','paySign'),//支付
     'v1.order:repay'=>array('order_id','payParams','pay_status','appId','timeStamp','nonceStr','package','signType','paySign'),//重新支付
     'v1.order:waitOrderDetail'=>array('order_id','consignee','mobile','address','shipping_id','goods_price','shipping_price','goods_price','order_amount','total_amount','goods_name','spec_key_name','goods_price_unit','goods_num','image','timer'),//等待支付的详情页
     'v1.order:finishOrderDetail'=>array('order_id','consignee','mobile','address','image','pay_time','goods_name','is_send'),//支付完成详情页

        //第二版
     'v1.home:banner'=>array('id','image','goods_id','title'),//轮播图
     'v1.home:recom'=>array('id','image','goods_id','title','is_new','label','color','text'),//推荐
     'v1.home:goodsList'=>array('goods_id','goods_name','image','label','color','text','market_price','shop_price','sales_sum','store_count','is_recommend','is_new','is_live_goods'),//商品上新
     'v1.activity:goodsDetailActivity'=>array('goods_id','start_time','end_time','coupon_id','title','type','full_money','money','day','is_get'),//商品详情页优惠福利
     'v1.liveActivity:activity'=>array('id','title','full_money','money','number','type','sign','live','share'),//直播间单次活动列表
     'v1.liveActivity:getSingleAward'=>array('id','title','full_money','money','number','type'),//获取单次互动奖励
     'v1.coupon:ableCoupon'=>array('id','title','money','full_money'),//支付可用优惠
     'v1.order:detail'=>array('order_id','consignee','mobile','address','shipping_id','goods_price','shipping_price','goods_price','order_amount','total_amount','goods_name','spec_key_name','goods_price_unit','goods_num','image','timer','coupon_amount','remark','status','order_status'),//订单详情
     'v1.coupon:giveCoupon'=>array('title','type','full_money','money'),//支付赠送的优惠券


      //直播
     'v1.Live:liveList'=>array('id','title','anchor_id','memo','img_url','status','start_time','goods','goods_id','goods_name','shop_price','images','has_video'),//直播列表
     'v1.Live:intoLive'=>array('id','title','start_time','end_time','anchor_id','img_url','watch_num','memo','goods','goods_id','goods_name','shop_price','images','nickname','username','head_url', 'status'),//进入直播间
     'v1.Live:startLive'=>array('goods_name','live_price','shop_price','status','goods_id'),//开始直播
     'v1.Live:stopLive'=>array('start_time','end_time','watch_num'),//结束直播
     'v1.Live:getUserSig'=>array('user_sig','user_id'),//获取userSig


      'v1.Mall.Center:articleCollect' => ['id','user_id','article_id','status','type','title','keywords','content','thum'],//收藏的文章
      'v1.Mall.Center:goodsCollect' => ['id','user_id','goods_id','status','goods','cat_id','keywords','goods_name','images','shop_price','cost_price','is_hot','is_new','is_virtual','src','is_seleted','goods_type','is_recommend','market_price','cover_image'],//收藏的商品
        'v1.Mall.order:getList' => ['id','order_sn','user_id','consignee','province','city','area','town','address','mobile','shipping_id','shipping_name','goods_price','template_id','shipping_price','order_amount','total_amount','amount','create_time','paytype','status','pay_time','attach','insertymd','live_id','order_status','shipping_status','aftersales','refund','type','status','reason','mark','cover_image','goods_num','goods_name'], //订单列表
        'v1.Live.LiveSubscribe:getList' => ['id','live_id','user_id','status','create_at','title','start_time','end_time','anchor_id','img_url','memo','has_video'], //直播订阅列表
        'v1.Mall.Center:index' => ['base_info','nickname','head_url','mobile','conpoun','collect','goods','article','follow','fans','user_id','cash_money','cash_money','give_money','wait_pay','wait_receive','all_completed','wait_send','total','frozen_money'], //个人中心首页
        'v1.Mall.Center:myFollow' => ['id','user_id','user_id2','is_remind','status','follow_user_head_url','follow_user_nickname','article_count'],//我的关注
        'v1.Mall.Wallet:withdrawLog' => ['id','user_id','code','link_code','business_type','money_type','operation_type','operation_money','original_money','latest_money','msg','create_at','from_uid','to_uid','status','business_type_text','money_type_text','withdraw_status_text','withdraw_type'], //钱包明细
        'v1.Mall.User:couponList' => ['id','remark','goods_id','live_id','coupon_id','coupon_sn','user_id','is_use_type','status','get_time','use_time','start_time','start_time','expire_time','title','full_money','money','is_withdraw','coupon_desc'], //用户优惠券
        'v1.Mall.User:giftCodeList' => ['id','user_id','number','title','update_time'],


        //文章及消息
        'v1.Mall.Article:bannel' => ['id','recommend_img'], //文章首页bannel
        'v1.Mall.Article:list' => ['id','thum','title','description','is_collect'], //文章列表
        'v1.Mall.Article:detail' => ['id','title','description','nickname','user_id','thum','add_time','content','goods_id','goods_name','market_price','shop_price','is_hot','is_new','src','is_collect','is_follow'], //文章详情
        'v1.Mall.Article:shared' => ['coupon_id','coupon_title','coupon_type','full_money','money'], //文章分享
        'v1.Mall.Message:list' => ['id','type','title','content','img','is_read','jump_id','create_time'], //消息列表
    );

    /**
     * 获取接口返回节点
     * @param $key
     * @return array
     */
    static public function get($key)
    {
        return ! empty(self::$fields[$key]) ? self::$fields[$key] : [];
    }
}