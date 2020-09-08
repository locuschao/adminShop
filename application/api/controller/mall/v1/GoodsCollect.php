<?php

namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\model\GoodsCollect AS GoodsCollectModel;
use app\common\model\Goods;
use app\common\model\GoodsExt;
use think\Exception;
use think\Db;
use library\Code;

class GoodsCollect extends Base
{
    /**
     * 收藏
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/22 12:42
     */
    function collect()
    {
        if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

        $goodsId = $this->_param('goods_id');
        $goods = Goods::where(["goods_id" => $goodsId])->find();

        Code::$code['HAS_GOODS_COLLECT'] = ['code' => '400', 'msg' => '你已经收藏过该商品'];
        Code::$code['GOODS_NO_FOUND_ERROR'] = ['code' => '400', 'msg' => '商品不存在'];
        Code::$code['COLLECT_ERROR'] = ['code' => '400', 'msg' => '收藏失败'];

        if (!$goods) $this->_error('GOODS_NO_FOUND_ERROR');

        $collect = $this->hasCollect($this->userInfo['id'], $goodsId);
        if ($collect && $collect['status'] == GoodsCollectModel::COLLECT_STATUS_YES) return $this->_error('HAS_GOODS_COLLECT');

        try {
            Db::startTrans();

            $goodsCollectModel = new GoodsCollectModel();

            if ($collect) {
                $res = $goodsCollectModel->save([
                    'status' => GoodsCollectModel::COLLECT_STATUS_YES,
                ], [
                    'user_id' => $this->userInfo['id'],
                    'goods_id' => $goodsId,
                ]);
            } else {
                $res = $goodsCollectModel->save([
                    'user_id' => $this->userInfo['id'],
                    'goods_id' => $goodsId,
                    'status' => GoodsCollectModel::COLLECT_STATUS_YES,
                ]);
            }

            //更新商品收藏数
            $goodsExtModel = new GoodsExt();
            $goodsExtModel->updateGoodsCollectCount($goodsId);

            Db::commit();
            return $this->_success();
        } catch (Exception $e) {

            Db::rollback();

            $this->_error('COLLECT_ERROR');
        }
    }

    /**
     * 取消收藏
     * @Author: wuyh
     * @Date: 2020/3/22 12:47
     */
    function unCollect()
    {
        $goodsId = $this->_param('goods_id');
        if (empty($goodsId) || empty($this->userInfo)) $this->_error('PARAM_ERROR');

        Code::$code['GOODS_NO_COLLECT'] = ['code' => 400, 'msg' => '你还没有收藏过该商品'];
        Code::$code['GOODS_UN_COLLECT_ERROR'] = ['code' => 400, 'msg' => '取消收藏失败'];

        $collect = $this->hasCollect($this->userInfo['id'], $goodsId);
        if (!$collect || $collect['status'] == GoodsCollectModel::COLLECT_STATUS_NO) $this->_error('ARTICLE_NO_COLLECT');

        try {

            Db::startTrans();
            $goodsCollectModel = new GoodsCollectModel();

            $res = $goodsCollectModel->save([
                'status' => GoodsCollectModel::COLLECT_STATUS_NO,
            ], [
                'user_id' => $this->userInfo['id'],
                'goods_id' => $goodsId
            ]);

            if ($res === false) throw new Exception('error');

            $res = GoodsExt::where(['goods_id' => $goodsId])->setDec('collect_count');

            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();


        } catch (Exception $e) {
            Db::rollback();
            $this->_error('GOODS_UN_COLLECT_ERROR');
        }
    }


    /**
     * 是否已经收藏过
     * @param $userId
     * @param $goodsId
     * @return int|string
     * @Author: wuyh
     * @Date: 2020/3/19 13:33
     */
    public function hasCollect($userId, $goodsId)
    {
        $info = GoodsCollectModel::where([
            "user_id" => $userId,
            'goods_id' => $goodsId,
//            'status' => GoodsCollectModel::COLLECT_STATUS_YES
        ])->find();

        return $info;
    }
}