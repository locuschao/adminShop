<?php
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\service\AdService;

class Ad extends Base{
    protected $pos_id = 1;//首页轮播图

    //首页轮播图（废除）
    public function HomeBanner(){
        $adService = new AdService();
        $adList = $adService->getHomeBannerListCache($this->pos_id);
       if(!empty($adList)){
           foreach ($adList as &$value){
               $value['url'] = $value['link_url'];
           }
       }
        $this->_response['data']['list'] = $adList;
        $this->_success('v1.AdPos:homeBanner');
    }
}