<?php
namespace app\admin\controller;
use app\common\model\Config;
use app\common\model\FreightConfig;
use app\common\model\FreightRegion;
use app\common\model\FreightTemplate;
use app\common\model\Nav;
use app\common\model\Region;
use app\common\model\Shipping;
use think\Validate;
use think\Db;

class Setting extends Base{

    public function shoping(){
        if($this->request->isPost()){
            // 接收参数
            $id = $this->request->post('id');
            $content = $this->request->post('content');
            $data = [
                'content' => $content,
            ];

            $configModel = new Config();
            $res = $configModel->update($data,array('id'=>$id));
            if (false === $res) {
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
        $configModel = new Config();
        $info = $configModel->getConfigById($id=1);
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //快递配置
    public function logistics(){
        $result = array(
            'code'=> 0,
            'msg'=> "",
            'count'=> 0,
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $shippingModel = new Shipping();
            $count = $shippingModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $shippingModel->fetchList($condition,$offset,$limit);
            }
            $result['count'] = $count;
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        return $this->view->fetch();
    }

    public function addLogistics(){
        if($this->request->isPost()){
            // 接收参数
            $shipping_name = $this->request->post('shipping_name');
            $shipping_code = $this->request->post('shipping_code');
            $shipping_desc = $this->request->post('shipping_desc');
            $shipping_logo = $this->request->post('shipping_logo');
            $is_open = $this->request->post('is_open');

            $rule = [
                ['shipping_name', 'require', '快递名称不能为空'],
            ];
            $data = [
                'shipping_name' => $shipping_name,
                'shipping_code' => $shipping_code,
                'shipping_desc' => $shipping_desc,
                'shipping_logo' => $shipping_logo,
                'is_open' => $is_open,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            $shippingModel = new Shipping();
            $id = $shippingModel->insertGetId($data);
            if ($id < 0) {
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
        return $this->view->fetch();
    }

    public function editLogistics(){
        if($this->request->isPost()){
            // 接收参数
            $shipping_id = $this->request->post('shipping_id');
            $shipping_name = $this->request->post('shipping_name');
            $shipping_code = $this->request->post('shipping_code');
            $shipping_desc = $this->request->post('shipping_desc');
            $shipping_logo = $this->request->post('shipping_logo');
            $is_open = $this->request->post('is_open');

            $rule = [
                ['shipping_name', 'require', '快递名称不能为空'],
            ];
            $data = [
                'shipping_name' => $shipping_name,
                'shipping_code' => $shipping_code,
                'shipping_desc' => $shipping_desc,
                'shipping_logo' => $shipping_logo,
                'is_open' => $is_open,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            $shippingModel = new Shipping();
            $res = $shippingModel->update($data,array('shipping_id'=>$shipping_id));
            if (false === $res) {
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
        $shipping_id = $this->request->get('shipping_id');
        $shippingModel = new Shipping();
        $info = $shippingModel->getDetail(['shipping_id'=>$shipping_id]);
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //运费模板列表
    public function freight(){
        $result = array(
            'code'=> 0,
            'msg'=> "",
            'count'=> 0,
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $freightTemplateModel = new FreightTemplate();
            $count = $freightTemplateModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $freightTemplateModel->fetchList($condition,$offset,$limit);
            }
            $result['count'] = $count;
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        return $this->view->fetch();
    }

    public function addFreight(){
        if($this->request->isPost()){
            // 接收参数
            $freight = $this->request->post('freight/a');
            $template_name = $this->request->post('template_name');
            $type = $this->request->post('type');
            $is_enable_default = $this->request->post('is_enable_default');

            $rule = [
                ['template_name', 'require', '模板名称不能为空'],
            ];
            $data = [
                'template_name' => $template_name,
                'type' => $type,
                'is_enable_default' => $is_enable_default,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            Db::startTrans();
            $freightTemplateModel = new FreightTemplate();
            $id = $freightTemplateModel->insertGetId($data);
            if ($id < 0) {
                Db::rollback();
                $this->error("添加失败");
            }

            if(!empty($freight)){
                $freightConfigModel = new FreightConfig();
                $freightRegionModel = new FreightRegion();
                foreach ($freight as $value){
                    $config_id = $freightConfigModel->insertGetId(array('first_unit'=>$value['first_unit'],'first_money'=>$value['first_money'],'continue_unit'=>$value['continue_unit'],'continue_money'=>$value['continue_money'],'template_id'=>$id));
                    if ($config_id < 0) {
                        Db::rollback();
                        $this->error("添加失败");
                    }
                    if(!empty($value['region_id'])){
                        $region_id = explode(',',$value['region_id']);
                        foreach ($region_id as $value){
                            $freightRegionId = $freightRegionModel -> insertGetId(array('template_id'=>$id,'config_id'=>$config_id,'region_id'=>$value));
                            if ($freightRegionId < 0) {
                                Db::rollback();
                                $this->error("添加失败");
                            }
                        }
                    }
                }
            }
            Db::commit();
            $this->success("添加成功");
        }
        $regionModel = new Region();
        $province = $regionModel->getAllProvinces();
        $this->assign('province',$province);
        return $this->view->fetch();
    }

    public function editFreight(){
        if($this->request->isPost()){
            // 接收参数
            $freight = $this->request->post('freight/a');
            $template_name = $this->request->post('template_name');
            $template_id = $this->request->post('template_id');
            $type = $this->request->post('type');
            $is_enable_default = $this->request->post('is_enable_default');

            $rule = [
                ['template_name', 'require', '模板名称不能为空'],
            ];
            $data = [
                'template_name' => $template_name,
                'type' => $type,
                'is_enable_default' => $is_enable_default,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            Db::startTrans();
            $freightTemplateModel = new FreightTemplate();
            $freightConfigModel = new FreightConfig();
            $freightRegionModel = new FreightRegion();
            $res= $freightTemplateModel->update($data,array('template_id'=>$template_id));
            if (false === $res) {
                Db::rollback();
                $this->error("更新失败");
            }

            //删除
            $freightConfigModel->where(array('template_id'=>$template_id))->delete();
            $freightRegionModel->where(array('template_id'=>$template_id))->delete();
            if(!empty($freight)){
                foreach ($freight as $key=>$value){
                    $config_id = $freightConfigModel->insertGetId(array('first_unit'=>$value['first_unit'],'first_money'=>$value['first_money'],'continue_unit'=>$value['continue_unit'],'continue_money'=>$value['continue_money'],'template_id'=>$template_id));
                    if ($config_id < 0) {
                        Db::rollback();
                        $this->error("更新失败");
                    }
                    if(!empty($value['region_id'])){
                        $region_id = explode(',',$value['region_id']);
                        $freightRegionData  = array();
                        foreach ($region_id as $val){
                            $t = array();
                            $t['template_id'] = $template_id;
                            $t['config_id'] = $config_id;
                            $t['region_id'] = $val;
                            $freightRegionData[]=$t;
                        }
                        $freightRegionModel -> insertAll($freightRegionData);
                    }
                }
            }
            Db::commit();
            $this->success("更新成功");
        }

        $template_id = $this->request->get('template_id');
        $regionModel = new Region();
        $freightTemplateModel = new FreightTemplate();
        $freightConfig = new FreightConfig();
        $freightRegionModel = new FreightRegion();
        $freightRegion = $freightRegionModel->getRegionByTemplateId($template_id);
        $regions = array();
        if(!empty($freightRegion)){
            foreach ($freightRegion as $value){
                $regions[$value['config_id']]['region_name'][]=$value['name'];
                $regions[$value['config_id']]['region_id'][]=$value['region_id'];
            }

            foreach ($regions as &$val){
                $val['region_name'] = implode(',',$val['region_name']);
                $val['region_id'] = implode(',',$val['region_id']);
            }
        }

        $freightConfigList = $freightConfig->getFreightTemplateConfig($template_id);
        if(!empty($freightConfigList)){
            foreach ($freightConfigList as &$value){
                $value['region_name'] = !empty($regions[$value['config_id']])?$regions[$value['config_id']]['region_name']:'';
                $value['region_id'] = !empty($regions[$value['config_id']])?$regions[$value['config_id']]['region_id']:'';
            }
        }

        $max_id = $freightConfig ->getMaxId();
        $info = $freightTemplateModel ->getFreightTemplateInfoByTemplateId($template_id);
        $province = $regionModel->getAllProvinces();
        $this->assign('max_id',$max_id);
        $this->assign('freightConfigList',$freightConfigList);
        $this->assign('info',$info);
        $this->assign('province',$province);
        return $this->view->fetch();
    }

    //首页分类配置
    public function nav(){
        $result = array(
            'code'=> 0,
            'msg'=> "",
            'count'=> 0,
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $navModel = new Nav();
            $count = $navModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $navModel->fetchList($condition,$offset,$limit);
            }
            $result['count'] = $count;
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        return $this->view->fetch();
    }

    public function addNav(){
        if($this->request->isPost()){
            // 接收参数
            $name = $this->request->post('name');
            $order_by = $this->request->post('order_by');
            $is_show = $this->request->post('is_show');

            $rule = [
                ['name', 'require', '首页分类名称不能为空'],
            ];
            $data = [
                'name' => $name,
                'order_by' => $order_by,
                'is_show' => $is_show,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            $navModel = new Nav();
            $id = $navModel->insertGetId($data);
            if ($id < 0) {
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
        return $this->view->fetch();
    }

    public function editNav(){
        if($this->request->isPost()){
            // 接收参数
            $id = $this->request->post('id');
            $name = $this->request->post('name');
            $order_by = $this->request->post('order_by');
            $is_show = $this->request->post('is_show');

            $rule = [
                ['name', 'require', '首页分类名称不能为空'],
            ];
            $data = [
                'name' => $name,
                'order_by' => $order_by,
                'is_show' => $is_show,
            ];
            // 校验参数
            $validate = new Validate($rule);
            if ( ! $validate->check($data) ) {
                $this->error($validate->getError());
            }
            $navModel = new Nav();
            $res = $navModel->update($data,array('id'=>$id));
            if (false === $res) {
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
        $id = $this->request->get('id');
        $navModel = new Nav();
        $info = $navModel->getDetail(array('id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }
}
