<?php
// +----------------------------------------------------------------------
// | 小部件基类
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-05
// +----------------------------------------------------------------------
namespace app\admin\widget;

use think\Controller;

class BaseWidget extends Controller
{
    /**
     * 模型
     * @var
     */
    protected $model;

    public function _initialize()
    {
        parent::_initialize();

        $this->view->engine->layout(false);
    }
}
