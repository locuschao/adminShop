<?php
namespace app\common\model;
class Goods extends Base{
    protected $name = 'goods';
    protected $resultSetType = 'collection';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取多条
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('goods_id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    //获取详情
    public function getDetail(array $condition,$field=""){
        $field = empty($field)?"*":$field;
        $list = $this->where($condition)->field($field)->find();
        if(empty($list)){
            return array();
        }
        return $list->toArray();
    }

    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     * @throws \think\exception\DbException
     * Author wuyh
     */
    public function tableData($params)
    {
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        } else {
            $limit = config('paginate.list_rows');
        }

        $condition = $this->_tableCondition($params);
        $list = $this->with('defaultImage')->where($condition['where'])->paginate($limit);
        $data = $this->_tableFormat($list->getCollection());

        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;

        return $re;
    }

    /**
     * 格式化表格数据
     * @param $list
     * @return mixed
     * Author wuyh
     */
    private function _tableFormat($list)
    {
        return $list;
    }

    /**
     * 表格查询条件
     * @param $params
     * @return mixed
     * Author wuyh
     */
    private function _tableCondition($params)
    {
        $map = [];
        if (isset($params['goods_name']) && $params['goods_name'] != "") $map['goods_name'] = ['like', $params['goods_name'] . '%'];
        if (isset($params['no_ids']) && $params['no_ids'] != "") $map['goods_id'] = ['not in',explode(',',$params['no_ids'])];
        if (isset($params['is_on_sale']) && $params['is_on_sale'] != "") $map['is_on_sale'] = $params['is_on_sale'];
        if (isset($params['check']) && $params['check'] != "") $map['check'] = $params['check'];

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'id desc';
        return $result;
    }

    /**
     * 关联默认主图
     * @return \think\model\relation\HasOne
     * Author wuyh
     */
    public function defaultImage()
    {
        return $this->hasOne('GoodsImage', 'goods_id', 'goods_id')->field('goods_id,src')->bind(['image_url' => 'src']);
    }

    /**
     * 关联图片
     * @return \think\model\relation\HasMany
     */
    public function images()
    {
        return $this->hasMany('GoodsImage', 'goods_id', 'goods_id');
    }


    /**
     * 新增或更新
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function toAdd($params)
    {
        $validate = new \app\common\validate\Goods();
        $result = $validate->scene('save')->check($params);

        if (!$result) return ['code' => 0, 'msg' => $validate->getError()];

        $ret = (isset($params['goods_id']) && !empty($params['goods_id'])) ? $this->allowField(true)->save($params, $params['goods_id']) : $this->allowField(true)->save($params);
        if (!$ret) return ['code' => 0, 'msg' => $this->getError()];

        return ['code' => 1, 'msg' => 'SUCCESS'];
    }

    protected $goodsField = "a.goods_id,a.goods_name,a.goods_remark,a.label,a.market_price,a.shop_price,a.sales_sum,a.store_count,b.src as image,a.is_recommend,a.is_new";

    //获取所有商品列表
    public function getGoodsListByCondition($where,$orderby){
        $list = $this
            ->alias('a')
            ->field($this->goodsField)
            ->where($where)
            ->join('cc_goods_image b','a.goods_id=b.goods_id','left')
            ->group(" a.goods_id")
            ->order($orderby)
            ->select();
        return  empty($list)?array():$list->toArray();
    }

    //连表获取商品详数量
    public function getGoodsListByWhereCount($where){
        $total = $this
            ->alias('a')
            ->field($this->goodsField)
            ->where($where)
            ->join('cc_goods_image b','a.goods_id=b.goods_id','left')
            ->group(" a.goods_id")
            ->count();
        return  $total;
    }

    //商品列表分页
    public function getGoodsListByWhere($where,$offset,$limit,$orderby){
        $list = $this
            ->alias('a')
            ->field($this->goodsField)
            ->where($where)
            ->join('cc_goods_image b','a.goods_id=b.goods_id','left')
            ->limit("$offset,$limit")
            ->group(" a.goods_id")
            ->order($orderby)
            ->select();
        return  empty($list)?array():$list->toArray();
    }



}