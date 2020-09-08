<?php
namespace app\common\service;
use app\common\model\GoodsCategory;
use think\Cache;
class CategoryService extends BaseService {

    protected $prefix = "category";

    //获取分类缓存
    public function getCategoryListCache($level=null){
        $level = intval($level);
        if($level <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getCategoryListCache'.$level);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsCategoryModel = new GoodsCategory();
        $list = $goodsCategoryModel ->fetchList(array('level'=>$level),0,20);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }


    //获取分类的子类目
    public function getThreeCategoryNav($cate_id=null){
        $cate_id = intval($cate_id);
        if($cate_id <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getThreeCategoryNav'.$cate_id);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsCategoryModel = new GoodsCategory();
        $list = $goodsCategoryModel ->fetchList(array('pid'=>$cate_id),0,20);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }
}