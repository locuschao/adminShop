<?php
// +----------------------------------------------------------------------
// | 直播表
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;
use app\common\validate\Live AS LiveValidate;
use app\common\model\LiveGoods;
use think\Db;


class Live extends Model
{
    protected $name = 'live';
    protected $resultSetType = '';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    protected $type = [
        'start_time'  =>  'timestamp',
//        'end_time'  =>  'timestamp',
    ];

    /**
     * 直播状态
     */
    const LIVE_WAIT = 0;
    const LIVE_BEGIN = 1;
    const LIVE_END = 2;


    //直播间用户角色
    const LIVE_USER_NORMAL = 1; //普通用户
    const LIVE_USER_MANAGE = 2; //主播
    const LIVE_USER_VISITOR = 3; //游客


    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     */
    public function tableData($params)
    {
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        } else {
            // $limit = config('paginate.list_rows');
            $limit = config('cfg.SYS_PAGE');
        }

        $condition = $this->_tableCondition($params);

        $list = $this
            ->alias('l')
            ->field('l.*,a.username as anchor_name')
            ->join('__USER__ a', 'a.id=l.anchor_id', 'LEFT')
            ->where($condition['where'])
            ->order($condition['order'])
            ->paginate($limit);

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
     */
    private function _tableFormat($list)
    {
        foreach($list as $k => $v) {
            if(isset($v['status'])) $list[$k]['status_name'] = config('enum.live')['status'][$v['status']];
//            if(isset($v['start_time'])) $list[$k]['start_time'] = date('Y-m-d H:i:s',$v['start_time']);
            if(isset($v['end_time']) && $v['end_time']) $list[$k]['end_time'] = date('Y-m-d H:i:s',$v['end_time']);
        }
        return $list;
    }

    /**
     * 表格查询条件
     * @param $params
     * @return mixed
     * @author wuyh
     */
    private function _tableCondition($params)
    {
        $map = [];

        if (isset($params['title']) && $params['title'] != "") $map['l.title'] = ['like', $params['title'] . '%'];
        if (isset($params['status']) && $params['status'] != "")  $map['l.status'] = $params['status'];
        if (isset($params['anchor_id']) && $params['anchor_id'] != "")  $map['l.anchor_id'] = $params['anchor_id'];
        if (isset($params['start_time']) && $params['start_time'] != "")  $map['l.start_time'] = ['>=', $params['start_time']];
        if (isset($params['end_time']) && $params['end_time'] != "")  $map['l.end_time'] = ['<=', $params['end_time']];

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'id desc';
        return $result;
    }

    /**
     * 新增或更新
     * @param $params
     * @return array
     * @throws \Exception
     * @author wuyh
     */
    public function toAdd($params)
    {
        $params['start_time'] = isset($params['start_time']) && !empty($params['start_time']) ? strtotime($params['start_time']) : 0;
        $validate = new LiveValidate();
        $result = $validate->scene('save')->check($params);

        if (!$result) return['code' => 0, 'msg' => $validate->getError()];

        $ret = (isset($params['id']) && !empty($params['id'])) ? $this->allowField(true)->save($params, $params['id']) : $this->allowField(true)->save($params);
        if (!$ret) return['code' => 0, 'msg' => $this->getError()];

        $liveGoodsModdel = new LiveGoods();
        $ret = $liveGoodsModdel->saveGoods($this->id, $params['goods_ids']);
        if (!$ret) return['code' => 0, 'msg' => $this->getError()];
        return ['code' => 1, 'msg' => 'SUCCESS'];
    }

    /**
     * 关联商品
     * @return \think\model\relation\HasMany
     * @author wuyh
     */
    public function liveGoods()
    {
        return $this->hasMany('LiveGoods','live_id', 'id')
            ->field('id,live_id,goods_id,goods_sn,shop_price,status,live_price');
    }

    /**
     * 关联主播
     * @return \think\model\relation\HasOne
     * @author wuyh
     */
    public function liveAnchor()
    {
        return $this->hasOne('LiveUser', 'id', 'anchor_id')->field('id,username,head_url,nickname');
    }

    /**
     * 结束直播
     * @param $id
     * @return bool
     * @throws \think\exception\DbException
     * @author wuyh
     */
    static function stopLive($id)
    {
        $info = static::get($id);
        if (empty($info)) return false;

        try{
            Db::startTrans();
            $info->end_time = time();
            $info->status = static::LIVE_END;
            $res = $info->save();
            if ($res === false) throw new Exception('LIVE FAIL');
            $res = model('LiveGoods')->save(['status' => LiveGoods::LIVE_END],['live_id' => $info->id]);
            if ($res === false)  throw new Exception('LIVE GOODS FAIL');
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    /**
     * 关联直播观看赠礼
     * @Author: wuyh
     * @Date: 2020/3/30 15:48
     */
    public function liveItemView()
    {
        return $this->hasMany( 'LiveItemView', 'live_id', 'id');
    }

    /**
     * 关联定时抽奖
     * @return \think\model\relation\HasMany
     * @Author: wuyh
     * @Date: 2020/3/30 16:25
     */
    public function liveItemDraw()
    {
        return $this->hasMany( 'LiveItemDraw', 'live_id', 'id');
    }

    /**
     * 关联问答
     * @return \think\model\relation\HasMany
     * @Author: wuyh
     * @Date: 2020/3/30 16:26
     */
    public function liveItemAnswer()
    {
        return $this->hasMany( 'LiveItemAnswer', 'live_id', 'id');
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }
}