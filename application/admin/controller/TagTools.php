<?php
// +----------------------------------------------------------------------
// | 公用组件
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;

use library\Response;
use app\common\model\Article as ArticleModel;

class TagTools extends Base
{
    /**
     * 供tag标签选择商品的时候使用
     */
    public function selectGoods()
    {
        if ($this->request->isPost()) {
            $params = input('param.');
            $params['is_on_sale'] = 1;
            $params['check'] = 1;
            $data = model('common/Goods')->tableData($params);
            return json($data);
        }
        return $this->view->fetch('', ['goods_max' => input('goods_max/d', 0),'no_ids' => input('no_ids')]);
    }

    /**
     * 供tag标签选择员工的时候使用
     */
    public function selectAdmin()
    {
        $res = api('/admin/v1.admin/getList', ['a' => 2]);
        $apiRes = json_decode($res, true);
        $result = ['code' => 0, 'msg' => 'SUCCESS', 'data' => $apiRes['data']];
        return json($result);
    }

    /**
     * 选择文章
     */
    public function selectArticle(){
        return $this->view->fetch('',['ids'=>input('ids','')]);
    }
}
