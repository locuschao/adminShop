<?php
//商品缓存类
namespace app\common\service;
use app\common\model\Goods as GoodsModel;
use app\common\model\GoodsImage;
use app\common\model\GoodsRecom;
use think\Cache;
class GoodsService extends BaseService {
    protected $prefix = "goods";

    //连表获取商品信息
    public function getGoodsListByWhereCache($where=array(),$offset=0,$limit=10,$orderby="sort desc"){
        $cache = md5($this->prefix.'getGoodsListCache'.serialize($where).$offset.$limit.$orderby);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsModel = new GoodsModel();
        $list = $goodsModel ->getGoodsListByWhere($where,$offset,$limit,$orderby);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取商品详情
    public function getGoodsDetailGoodsIdCache($goods_id=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0){
            return array();
        }
        $where = array('goods_id'=>$goods_id,'check'=>1);
        $cache = md5($this->prefix.'getGoodsDetailGoodsidCache'.serialize($where));

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsModel = new GoodsModel();
        $list = $goodsModel ->getDetail($where);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取商品图片
    public function getGoodsImageListByGoodsIdCache($goods_id=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0){
            return array();
        }
        $where['goods_id'] = $goods_id;
        $cache = md5($this->prefix.'getGoodsImageListByGoodsIdCache'.serialize($where));

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsImageModel = new GoodsImage();
        $list = $goodsImageModel ->fetchList($where,0,10);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取推荐banner
    public function getRecomBannerListCache($pos_id = null){
        $pos_id = intval($pos_id);
        if($pos_id <= 0){
            return array();
        }
        $time = time();
        $where = array();
        $where['status'] = 1;
        $where['start_time'] = array('<=',$time);
        $where['end_time'] = array('>=',$time);
        $where[] = array('exp',"pos_id&".$pos_id."=".$pos_id);
        $cache = md5($this->prefix.'getRecomBannerListCache'.$pos_id);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsRecomModel = new GoodsRecom();
        $list = $goodsRecomModel ->fetchList($where,0,10);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }


    /**
     * 用户是否收藏了商品
     * @param $goodsId
     * @param $userId
     * @return int|string
     * @Author: wuyh
     * @Date: 2020/3/24 9:40
     */
    public function IsUserGoodsCollect($goodsId, $userId)
    {
        if (empty($goodsId) || empty($userId)) return 0;
        return model('common/GoodsCollect')::where(['user_id' => $userId, 'goods_id' => $goodsId])->count();
    }

}
