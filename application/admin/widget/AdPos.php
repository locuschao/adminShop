<?php
// +----------------------------------------------------------------------
// | 广告位
// +----------------------------------------------------------------------
// | Author: pc 2020-02-05
// +----------------------------------------------------------------------
namespace app\admin\widget;
use app\common\model\AdPosition;
class AdPos extends BaseWidget{

    //广告位下拉框
    public function getList($select_id){
        $adPositionModel = new AdPosition();
        $list = $adPositionModel->select();
        $list = empty($list)?array():$list->toArray();
        $this->assign('list', $list);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/ad_pos/select");
    }
}