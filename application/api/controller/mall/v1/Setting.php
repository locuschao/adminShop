<?php
// +----------------------------------------------------------------------
// | 配置信息
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/22 17:02
// +----------------------------------------------------------------------
namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;

class Setting extends Base
{
    /**
     * 配置
     * @return false|string
     * @Author: wuyh
     * @Date: 2020/3/22 17:10
     */
    public function getSetting()
    {
        $data = [
            'order_status_desc' => config('enum.order_status_desc'),
            'order_page_limit' => config('cfg.ORDER_PAGE_LIMIT')
        ];

        return json_encode(['code' => 200, 'msg' => 'SUCCESS', 'data' => $data]);
    }

}