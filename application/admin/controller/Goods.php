<?php
namespace app\admin\controller;
use app\common\model\Brand;
use app\common\model\Goods as GoodModel;
use app\common\model\GoodsCategory as GoodsCategoryModel;
use app\common\model\GoodsAttrType;
use app\common\model\GoodsNavList;
use app\common\model\GoodsAttr;
use app\common\model\SpecGoodsPrice;
use library\Response;
use library\XunSearch;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Db;
use think\Exception;

class Goods extends Base{

    //商品列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $xunSearch = new XunSearch();
            $keywords   = $this->request->post('keywords');
            $check      =  $this->request->post('check');
            $is_on_sale =  $this->request->post('is_on_sale');
            $goods_ids = $xunSearch ->searchGoods($keywords);
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];
            if(!empty($keywords)){
                $condition['goods_id'] = array('in',$goods_ids);
            }
            if(isset($check) && $check>=0){
                $condition['check'] = $check;
            }
            if( isset($is_on_sale) && $is_on_sale>=0){
                $condition['is_on_sale'] = $is_on_sale;
            }
            $goodsModel = new GoodModel();
            $count = $goodsModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加商品
    public function add(){
        if($this->request->isPost()){
            $params = $this->request->param();
            $attr = $this->request->post('attr/a');
            $cat_id_1 = (int) $this->request->post('cat_id_1');
            $cat_id_2 = (int) $this->request->post('cat_id_2');
            $cat_id_3 = (int) $this->request->post('cat_id_3');
            if(!empty($cat_id_1)){
                $params['cat_id'] = $cat_id_1;
            }
            if (!empty($cat_id_2)){
                $params['cat_id'] = $cat_id_2;
            }
            if (!empty($cat_id_3)){
                $params['cat_id'] = $cat_id_3;
            }
           
            $validate = new \app\common\validate\Goods();
            $result = $validate->scene('save')->check($params);
            if(!$result){
                $this->error($validate->getError());
            }

            if(mb_strlen($params['goods_name'],'utf-8')>30){
                $this->error("商品名称不可超过30个字符");
            }

            if(mb_strlen($params['goods_remark'],'utf-8')>60){
                $this->error("商品简介不可超过60个字符");
            }

            $time = time();
            $params['create_time'] = $time;
            $goodsModel = new \app\common\model\Goods();
            unset($params['cat_id_1']);
            unset($params['cat_id_2']);
            unset($params['cat_id_3']);
            unset($params['attr']);
            $params['goods_content'] = preg_replace_callback('#(<img.*?style=".*?)width:\d+px;([^"]*?.*?>)#i', function($matches){return $matches[1].'width:100%;'.$matches[2];}, $params['goods_content']);
            $insert_id = $goodsModel -> insertGetId($params);

            if ($insert_id<=0) {
                $this->error('添加失败');
            }

            $attr_data = array();
            if(!empty($attr)){
                foreach ($attr as $key=>$value){

                    if(!empty($value['attr_value'])){
                        $t = array();
                        $t['attr_value'] = $value['attr_value'];
                        $t['attr_id'] = $key;
                        $t['goods_id'] = $insert_id;
                        $attr_data[] = $t;
                    }
                }

                if(!empty($attr_data)){
                    $goodsAttrModel = new GoodsAttr();
                    $insertAll = $goodsAttrModel->insertAll($attr_data);
                    if(!$insertAll){
                        $this->error("添加失败");
                    }
                }
            }


            try{
                $xunSearch = new XunSearch();
                $xunSearch->addGoodsIndex(
                    array(
                        'goods_id'=>$params['goods_id'],
                        'goods_name'=>$params['goods_name'],
                        'goods_remark'=>$params['goods_remark'],
                        'keywords'=>$params['keywords'],
                        'goods_content'=>$params['goods_content']
                    )
                );
            }catch (Exception $e){

            }

            $this->success("添加成功");
        }

        return $this->view->fetch();
    }

    //编辑商品
    public function edit(){
        $id = $this->request->get('id');
        $goodsModel = new GoodModel();
        $specGoodsPrice = new SpecGoodsPrice();
        $goodsAttrModel = new GoodsAttr();
        $goodsAttrTypeModel = new GoodsAttrType();
        $goodsCategoryModel = new GoodsCategoryModel();

        $info = $goodsModel->getDetail(array("goods_id"=>$id));
        $sku = $specGoodsPrice -> getSpecGoodsPriceInfoByGoodsId($id);
        $is_sku = 1;
        if(!empty($sku)){
            $is_sku = 0;
        }
        $category = $goodsCategoryModel->getDetail(array("id"=>$info['cat_id']));
        if(empty($category)){
            $info['cat_id_1'] = $info['cat_id_2'] = $info['cat_id_3'] = 0;
        }else{
            $pid_id_path = $category['pid_id_path'];
            $pids = explode('_',$pid_id_path);
            $info['cat_id_1'] = !empty($pids[1])?$pids[1]:0;
            $info['cat_id_2'] = !empty($pids[2])?$pids[2]:0;
            $info['cat_id_3'] = !empty($pids[3])?$pids[3]:0;
        }
        $this->assign('info', $info);

        $goodsAttrType = $goodsAttrTypeModel
            ->alias('a')
            ->join('cc_goods_attribute b','a.id=b.type_id','left')
            ->where(array('a.id'=>$info['goods_type']))
            ->select()->toArray();
        //查询属性值
        $attrList = $goodsAttrModel
            ->where(array('goods_id'=>$info['goods_id']))
            ->select()
            ->toArray();
        $attrList = arrayByField($attrList,"attr_id");
        foreach ($goodsAttrType as &$value){
            $value['attr_value'] = empty($attrList[$value['attr_id']]['attr_value'])?'':$attrList[$value['attr_id']]['attr_value'];
        }
        $this->assign('goodsAttrType', $goodsAttrType);
        $this->assign('is_sku', $is_sku);
        $this->assign('goods_id', $id);
        return $this->view->fetch();
    }

    //保存商品
    public function save(){
        if($this->request->isPost()){
            $params = $this->request->param();
            $goods_id = (int) $this->request->post('goods_id');
            $cat_id_1 = (int) $this->request->post('cat_id_1');
            $cat_id_2 = (int) $this->request->post('cat_id_2');
            $cat_id_3 = (int) $this->request->post('cat_id_3');
            if(!empty($cat_id_1)){
                $params['cat_id'] = $cat_id_1;
            }
            if (!empty($cat_id_2)){
                $params['cat_id'] = $cat_id_2;
            }
            if (!empty($cat_id_3)){
                $params['cat_id'] = $cat_id_3;
            }
            if(mb_strlen($params['goods_name'],'utf-8')>30){
                $this->error("商品名称不可超过30个字符");
            }

            if(mb_strlen($params['goods_remark'],'utf-8')>60){
                $this->error("商品简介不可超过60个字符");
            }
            $validate = new \app\common\validate\Goods();
            $result = $validate->scene('save')->check($params);
            if(!$result){
                $this->error($validate->getError());
            }

            Db::startTrans();
            $goodsAttrModel = new GoodsAttr();
            $delete = $goodsAttrModel->where(array('goods_id'=>$goods_id))->delete();
            if(false === $delete){
                Db::rollback();
                $this->error("更新失败");
            }
            $attr = empty($params['attr'])?array():$params['attr'];
            $attr_data = array();
            if(!empty($attr)){
                foreach ($attr as $key=>$value){
                    if(!empty($value['attr_value'])){
                        $t = array();
                        $t['attr_value'] = $value['attr_value'];
                        $t['attr_id'] = $key;
                        $t['goods_id'] = $goods_id;
                        $attr_data[] = $t;
                    }
                }
                if(!empty($attr_data)){
                    $insertAll = $goodsAttrModel->insertAll($attr_data);
                    if(!$insertAll){
                        Db::rollback();
                        $this->error("更新失败");
                    }
                }
            }
            $time = time();
            $params['goods_content'] = preg_replace_callback('#(<img.*?style=".*?)width:\d+px;([^"]*?.*?>)#i', function($matches){return $matches[1].'width:100%;'.$matches[2];}, $params['goods_content']);
            $params['create_time'] = $time;
            $goodsModel = new \app\common\model\Goods();
            $res = $goodsModel -> toAdd($params);
            if($res['code'] == 0){
                Db::rollback();
                return $res;
            }
            //更新
            try{
                $xunSearch = new XunSearch();
                $xunSearch->addGoodsIndex(
                    array(
                        'goods_id'=>$params['goods_id'],
                        'goods_name'=>$params['goods_name'],
                        'goods_remark'=>$params['goods_remark'],
                        'keywords'=>$params['keywords'],
                        'goods_content'=>$params['goods_content']
                    )
                );
            }catch (Exception $e){

            }

            Db::commit();
            $this->success("更新成功");
        }
    }

    //批量审核
    public function batch_check(){
        return $this->view->fetch();
    }


    //批量发布
    public function batch_sale(){
        return $this->view->fetch();
    }

    //批量添加商品
    public function batch_add_goods(){

       if($this->request->isAjax()){
           $param = $this->request->param('data/a');
           if(empty($param)){
               $this->error("无数据");
           }
           $data = array();
           foreach ($param as $value){
               $t=array();
               $t['goods_name'] = $value['goods_name'];
               $t['goods_sn'] = $value['goods_sn'];
               $t['goods_code'] = $value['goods_code'];
               $t['keywords'] = $value['keywords'];
               $t['market_price'] = $value['market_price'];
               $t['shop_price'] = $value['shop_price'];
               $t['cost_price'] = $value['cost_price'];
               $t['create_time'] = time();
               $data[] = $t;
           }
           $goodsModel = new \app\common\model\Goods();
           $res = $goodsModel->insertAll($data);
           if(!$res){
               $this->error("批量添加失败");
           }
           $this->success("批量添加成功");
       }
        return $this->view->fetch();
    }

    //批量添加规格
    public function batch_add_spec(){
        $goods_id = $this->request->param('goods_id');
        $this->assign('goods_id',$goods_id);
        return $this->view->fetch();
    }


    /**
     * 上传excel
     */
    public  function uploadExcel(){
        @ini_set('memory_limit','10240M');
        set_time_limit(60);

        $file = request()->file("file");
        $info = $file->move(BATCH_ORDER_EXCEL);
        if(!$info) return json(['code' => 0, 'msg' => '错误,未获取到文件信息']);
        $fileName = $info->getSaveName();

        $fileName = BATCH_ORDER_EXCEL. $fileName;
        $fileName = iconv('GB2312','UTF-8', $fileName);

        if(is_file($fileName) == false) return json(['code' => 0, 'msg' => '错误,未发现文件']);

        $extension = strtolower( pathinfo($fileName, PATHINFO_EXTENSION) );
        if ($extension != 'xls') return json(['code' => 0, 'msg' => '错误,文件格式不支持']);

        header('Content-type: text/html; charset=utf-8');
        vendor('PHPExcel.Classes.PHPExcel');
        $Excel = new \PHPExcel();

        if($extension == "xls"){
            vendor("PHPExcel.Classes.PHPExcel.Reader.Excel5");
            $PHPReader = new \PHPExcel_Reader_Excel5();
        }
        if($extension == "xlsx"){
            vendor("PHPExcel.Classes.PHPExcel.Reader.Excel2007");
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }

        $excel = $PHPReader->load($fileName);
        $sheet = $excel->getSheet(0);
        //获取总列数
        $allColumn = $sheet -> getHighestColumn();
        //获取总行数
        $allRow = $sheet -> getHighestRow();
        $data = [];

        $i=0;
        for ($j = 2; $j <= $allRow; $j++) {
            $data[$i]['id'] = $i+1;
            $data[$i]['goods_name'] = $excel->getActiveSheet()->getCell("A" . $j)->getValue();
            $data[$i]['goods_sn'] = $excel->getActiveSheet()->getCell("B" . $j)->getValue();
            $data[$i]['goods_code'] = $excel->getActiveSheet()->getCell("C" . $j)->getValue();
            $brand_name = $excel->getActiveSheet()->getCell("D" . $j)->getValue();
            $brandModel = new Brand();
            $brand = $brandModel->getDetail(array('name'=>$brand_name));
            if(empty($brand)){
                $brand_id = $brandModel->insertGetId(array('name'=>$brand_name));
            }else{
                $brand_id = (int)$brand['id'];
            }
            $data[$i]['brand_id'] = $brand_id ;
            $data[$i]['keywords'] = $excel->getActiveSheet()->getCell("E" . $j)->getValue();
            $data[$i]['market_price'] = $excel->getActiveSheet()->getCell("F" . $j)->getValue();
            $data[$i]['shop_price'] = $excel->getActiveSheet()->getCell("G" . $j)->getValue();
            $data[$i]['cost_price'] = $excel->getActiveSheet()->getCell("H" . $j)->getValue();
            $i++;
        }
        return json(['code' => 1, 'msg' => '导入成功', 'data' => $data]);
    }

    //复制商品信息
    public function copy_goods(){
        $id = (int)$this->request->param('id');
        $goodsModel = new GoodModel();
        $specGoodsPrice = new SpecGoodsPrice();
        $goodsAttrModel = new GoodsAttr();
        $goodsAttrTypeModel = new GoodsAttrType();
        $goodsCategoryModel = new GoodsCategoryModel();

        $info = $goodsModel->getDetail(array("goods_id"=>$id));
        $sku = $specGoodsPrice -> getSpecGoodsPriceInfoByGoodsId($id);
        $is_sku = 1;
        if(!empty($sku)){
            $is_sku = 0;
        }
        $category = $goodsCategoryModel->getDetail(array("id"=>$info['cat_id']));
        if(empty($category)){
            $info['cat_id_1'] = $info['cat_id_2'] = $info['cat_id_3'] = 0;
        }else{
            $pid_id_path = $category['pid_id_path'];
            $pids = explode('_',$pid_id_path);
            $info['cat_id_1'] = !empty($pids[1])?$pids[1]:0;
            $info['cat_id_2'] = !empty($pids[2])?$pids[2]:0;
            $info['cat_id_3'] = !empty($pids[3])?$pids[3]:0;
        }
        $this->assign('info', $info);

        $goodsAttrType = $goodsAttrTypeModel
            ->alias('a')
            ->join('cc_goods_attribute b','a.id=b.type_id','left')
            ->where(array('a.id'=>$info['goods_type']))
            ->select()->toArray();
        //查询属性值
        $attrList = $goodsAttrModel
            ->where(array('goods_id'=>$info['goods_id']))
            ->select()
            ->toArray();
        $attrList = arrayByField($attrList,"attr_id");
        foreach ($goodsAttrType as &$value){
            $value['attr_value'] = empty($attrList[$value['attr_id']]['attr_value'])?'':$attrList[$value['attr_id']]['attr_value'];
        }

        $this->assign('goodsAttrType', $goodsAttrType);
        $this->assign('is_sku', $is_sku);
        $this->assign('goods_id', $id);
        return $this->view->fetch();
    }


    //添加导航栏商品
    public function addNavGoods(){

        if($this->request->isPost()){
            // 接收参数
            $ids = $this->request->post('ids/a');
            $cate_id = (int) $this->request->post('cate_id');
            $goodsNavListModel = new GoodsNavList();
            $navGoodsList = array();
            foreach ($ids as $value){
                $t= array();
                $t['cate_id'] = $cate_id;
                $t['goods_id'] = $value;
                $navGoodsList[] = $t;
            }

            Db::startTrans();
            //删除
            $goodsNavListModel->where(array('cate_id'=>$cate_id))->delete();
            $add = $goodsNavListModel->insertAll($navGoodsList);
            if(!$add){
                Db::rollback();
                $this->error("添加失败");
            }

            Db::commit();
            $this->success("添加成功");
        }

        $cate_id = $this->request->get('id');
        $goodsModel =  new GoodModel();
        $goodsNavListModel = new GoodsNavList();
        $goodsList = $goodsModel ->where(array('is_on_sale'=>1))->select();
        $goodsData = array();
        $ids = array();
        if(!empty($goodsList)){
            $goodsList = $goodsList->toArray();
            foreach ($goodsList as $key => $value){
                $goodsData[$key]['value'] = $value['goods_id'];
                $goodsData[$key]['title'] = $value['goods_name'];
            }
        }
        $goodsNav = $goodsNavListModel->where(array('cate_id'=>$cate_id))->select();
        if(!empty($goodsNav)){
            $goodsNav = $goodsNav->toArray();
            foreach ($goodsNav as $value){
                $ids[] = $value['goods_id'];
            }
        }
        $this->assign('cate_id', $cate_id);
        $this->assign('goodsList', json_encode($goodsData,true));
        $this->assign('ids', json_encode($ids,true));
        return $this->view->fetch();
    }



}
