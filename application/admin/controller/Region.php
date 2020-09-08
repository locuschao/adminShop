<?php
namespace app\admin\controller;
use app\common\model\Region as RegionModel;
class Region extends Base{
    public function ajaxGetCategory(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $id      = (int)$this->request->post('id', 0);
            $regionModel = new RegionModel();
            $list = $regionModel->where(array('parent_id'=>$id))->select();
            if(empty($list)){
                echo  json_encode($result);die;
            }
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list->toArray();
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;

    }
}
