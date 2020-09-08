<?php
// +----------------------------------------------------------------------
// | 商品规格
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\SpecGoodsPrice;
use app\common\model\Goods as GoodsModel;
use app\common\model\SpecGoodsPrice as SpecGoodsPriceModel;
use think\Db;
class GoodsSpec extends Base{

    //添加商品规格
    public function add(){
        $goods_id = $this->request->param('goods_id');
        $type_id = $this->request->param('type_id');
        $specGoodsPriceModel = new SpecGoodsPriceModel();
        if($this->request->isPost()){
            // 接收参数
            $sku = $this->request->post('sku/a');
            $goodsModel = new GoodsModel();
            $sku_data = array();
            Db::startTrans();
            //添加规格
            $store_count = 0;
            if (!empty($sku)){
                foreach ($sku as $value){
                    if(empty($value['price']) || empty($value['store_count'])){
                        $this->error("库存或者金额不能为空");
                    }
                    $t = array();
                    $t['goods_id']=$goods_id;
                    $t['key']=$value['key'];
                    $t['key_name']=$value['key_name'];
                    $t['price']=$value['price'];
                    $t['store_count']=$value['store_count'];
                    $sku_data[] = $t;
                    $store_count +=$value['store_count'];
                }
            }
            $add = $specGoodsPriceModel->insertAll($sku_data);
            if(!$add){
                Db::rollback();
                $this->error("添加失败");
            }

            //更新商品库存
            $update = $goodsModel->update(array('store_count'=>$store_count),array("goods_id"=>$goods_id));
            if(false === $update){
                Db::rollback();
                $this->error("添加失败");
            }
            Db::commit();
            $this->success("添加成功");
        }

        $goodsModel = new \app\common\model\Goods();
        $goods = $goodsModel->getDetail(array("goods_id"=>$goods_id),"goods_sn,goods_name");
        $this->assign('goods',$goods);
        $this->assign('type_id', $type_id);
        $this->assign('goods_id', $goods_id);
        $goods_list = $specGoodsPriceModel->getSpecGoodsPriceInfoByGoodsId($goods_id);
        if(!empty($goods_list)){
            return $this->view->fetch('goods_spec/edit');
        }
        return $this->view->fetch('goods_spec/add');
    }


    //保存规格值
    public function save(){
        $goods_id = $this->request->param('goods_id');
        if($this->request->isPost()){
            // 接收参数
            $sku = $this->request->post('sku/a');
            $specGoodsPriceModel = new SpecGoodsPriceModel();
            $goodsModel = new GoodsModel();
            Db::startTrans();
            //添加规格
            $store_count = 0;
            if (!empty($sku)){
                foreach ($sku as $value){
                    $add = $specGoodsPriceModel->update(array("price"=>$value['price'],"store_count"=>$value['store_count']),array('item_id'=>$value['id']));
                    if(false === $add){
                        Db::rollback();
                        $this->error("更新失败");
                    }
                    $store_count +=$value['store_count'];
                }
            }

            //更新商品库存
            $update = $goodsModel->update(array('store_count'=>$store_count),array("goods_id"=>$goods_id));
            if(false === $update){
                Db::rollback();
                $this->error("更新失败");
            }
            Db::commit();
            $this->success("更新成功");
        }
    }

    //获取sku
    public function get_Goods_Spec(){
        $result = array(
            'code'=> 1,
            'msg'=> "无数据",
            'data'=> array(),
        );
        $goods_id = $this->request->param('goods_id');
        if(!empty($goods_id)){
            $goods_id = explode(',',$goods_id);
            $goods_ids = array_filter($goods_id,'intval');
            $specGoodsPriceModel = new SpecGoodsPriceModel();
            $where['a.goods_id'] = array('in',$goods_ids);
            $field = "a.item_id,a.goods_id,b.goods_name,b.goods_sn,b.goods_code,a.key,a.key_name,a.price,a.store_count";
            $goods_list = $specGoodsPriceModel->fetchList($where,$field);
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data'] = $goods_list;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //批量
    public function  batch_save(){
    if($this->request->isPost()){
        // 接收参数
        $sku = $this->request->post('data/a');
        $specGoodsPriceModel = new SpecGoodsPriceModel();
        $goodsModel = new GoodsModel();
        Db::startTrans();
        //添加规格
        $store = array();
        if (!empty($sku)){
            foreach ($sku as $value){
                if(!is_numeric($value['price']) || !is_numeric($value['store_count'])){
                    Db::rollback();
                    $this->error("填写正确价格和库存");
                }
                $store[$value['goods_id']]['goods_id'] = $value['goods_id'];
                $add = $specGoodsPriceModel->update(array("price"=>$value['price'],"store_count"=>$value['store_count']),array('key'=>$value['key'],'goods_id'=>$value['goods_id']));
                if(false === $add){
                    Db::rollback();
                    $this->error("更新失败");
                }
                //更新商品库存
                $update = $goodsModel->where(array("goods_id"=>$value['goods_id']))->inc("store_count",$value['store_count']);
                if(false === $update){
                    Db::rollback();
                    $this->error("更新失败");
                }
            }
        }

        Db::commit();
        $this->success("更新成功");
    }
   }

    //下载规格
    public function upload_spec(){
       $ids = $this->request->param('id');
       $ids = array_unique(explode(',',$ids));
       $goods_ids = array_filter($ids,'intval');
       if(empty($goods_ids)){
           $this->error('请先编辑规格sku');
       }
        $specGoodsPriceModel = new SpecGoodsPriceModel();
        $where['a.goods_id'] = array('in',$goods_ids);
        $field = "a.item_id,a.goods_id,b.goods_name,b.goods_sn,b.goods_code,a.key,a.key_name,a.price,a.store_count";
        $data = $specGoodsPriceModel->fetchList($where,$field);
        @ini_set('memory_limit','10240M');
        set_time_limit(0);
        header('Content-type: text/html; charset=utf-8');
        vendor('PHPExcel.Classes.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //定义配置
        $title = "商品规格sku";
        $topNumber = 1;//表头有几行占用
        $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件名称
        $fileName = $title.date('_YmdHis');//文件名称
        $cellKey = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
            'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
        );
        //表头
        $sheet_title = array('商品id','商品名称','货号','组合key','组合key名称','价格','库存');
        //首先是赋值表头
         for ($k=0;$k<count($sheet_title);$k++) {
             $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k].$topNumber,$sheet_title[$k]);
             $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k].$topNumber)->getFont()->setSize(10)->setBold(true);
             //设置单元格内容水平居中
             $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k].$topNumber)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
             //设置每一列的宽度
             $objPHPExcel->getActiveSheet()->getColumnDimension($cellKey[$k])->setWidth(18);
             $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
         }

        //处理数据
        foreach ($data as $k=>$v)
        {
            $objPHPExcel->getActiveSheet()->setCellValue("A".($k+2), $v['goods_id']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".($k+2), $v['goods_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".($k+2), $v['goods_sn']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".($k+2), $v['key']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".($k+2), $v['key_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".($k+2), $v['price']);
            $objPHPExcel->getActiveSheet()->setCellValue("G".($k+2), $v['store_count']);
        }

        //导出execl
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
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
            $data[$i]['goods_id'] = $excel->getActiveSheet()->getCell("A" . $j)->getValue();
            $data[$i]['goods_name'] = $excel->getActiveSheet()->getCell("B" . $j)->getValue();
            $data[$i]['goods_sn'] = $excel->getActiveSheet()->getCell("C" . $j)->getValue();
            $data[$i]['key'] = $excel->getActiveSheet()->getCell("D" . $j)->getValue();
            $data[$i]['key_name'] = $excel->getActiveSheet()->getCell("E" . $j)->getValue();
            $data[$i]['price'] = $excel->getActiveSheet()->getCell("F" . $j)->getValue();
            $data[$i]['store_count'] = $excel->getActiveSheet()->getCell("G" . $j)->getValue();
            $i++;
        }
        return json(['code' => 1, 'msg' => '导入成功', 'data' => $data]);
    }
}