<?php
// +----------------------------------------------------------------------
// | 订单商品管理
// +----------------------------------------------------------------------
// | modify: Wuyh 2020-03-05
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\GoodsOrder;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class OrderGoods extends Base
{
    protected $beforeActionList = [
        'mkUploadDir' =>  ['only'=>'uploadExcel'],
    ];

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->model = new GoodsOrder();
    }

    /**
     * 创建目录
     */
    public function mkUploadDir()
    {
        if (!is_dir(storage_path(BATCH_ORDER_EXCEL))) {
            mkdir(storage_path(BATCH_ORDER_EXCEL), 0777);
        }
    }

    /**
     * 列表
     * @return string
     * @throws \think\Exception
     * @author
     */
    public function index()
    {
        if ($this->request->isAjax()) return $this->model->tableData(input('param.'));
        return $this->view->fetch();
    }

    /**
     * 查看详情
     * @param $id
     * @return string
     * @throws \think\Exception
     */
    public function view($id)
    {
        if (empty($id)) $this->error('参数错误');
        $info = $this->model->with('orders')->find(['id' => $id]);

        if (empty($info)) $this->error('没有找到商品信息');
        return $this->view->fetch('', ['info' => $info]);
    }

    /**
     * 批量发货
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function batchShipping()
    {
        if ($this->request->isGet()) return $this->view->fetch('', []);
        if ($this->request->isPost()) {
            $params = input('post.');
            $ret = $this->model->batchShipping($params);
            if ($ret['code'] == 1) return json(['code' => 1, 'msg' => '修改成功']);
            return json(['code' => 0, 'msg' => $ret['msg']]);
        };
    }


    /**
     * 批量发货excel表上传
     * @return \think\response\Json
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @author wuyh
     */
    public function uploadExcel()
    {
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


        $objReader = IOFactory::createReader('Xls');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($fileName);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $exits = [];
        $data = [];
        for ($j = 2; $j <= $highestRow; $j++) {
            $data[$j - 2] = [
                'id' => $objPHPExcel->getActiveSheet()->getCell("A" . $j)->getValue(),
                'order_id' => $objPHPExcel->getActiveSheet()->getCell("B" . $j)->getValue(),
                'shipping_name' => $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue(),
                'delivery_id' => $objPHPExcel->getActiveSheet()->getCell("D" . $j)->getValue()
            ];

            //判断订单是否存在
//            if ($exits) {
//                array_push($exits, $data[$j - 2]['order_id']);
//            }
        }

        return json(['code' => 1, 'msg' => '上传成功', 'data' => $data]);
    }

}