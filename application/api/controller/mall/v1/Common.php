<?php
//通用接口
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\service\CommonService;

class Common extends Base{

    //省市区
    public function region(){
        $commonService = new CommonService();
        $data = $commonService->getAllRegionCache();
        $list = getChildRegion($data , $id=0);
        $this->_response['data']['list'] = $list;
        $this->_success('v1.common:region');
    }

    //快递
    public function express(){
        $commonService = new CommonService();
        $list = $commonService->getAllexpressCache();
        $list = array(array('shipping_id'=>0,'shipping_name'=>'普通快递'));
        $this->_response['data']['list'] = $list;
        $this->_success('v1.common:express');
    }
}