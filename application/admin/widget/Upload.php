<?php
// +----------------------------------------------------------------------
// | 上传小部件
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-05
// +----------------------------------------------------------------------

namespace app\admin\widget;

class Upload extends BaseWidget
{
    const uploadImgExt = 'jpg|png|gif|bmp|jpeg';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 单图上传组件
     * @param string $name 组件对应的数据库名称
     * @param string $img_url 组件图片地址
     * @param string $size 组件图片区域尺寸
     * @param string $title 组件按钮标题
     * @param string $size_tips 建议上传尺寸提示
     * @param string $crop_size 图片裁剪尺寸
     * @param string $crop_rate 图片旋转率
     * @return mixed
     */
    public function uploadSingleImage($name, $img_url, $size = '90x90', $title = null, $size_tips = null, $crop_size = null, $crop_rate = null)
    {
        // 图片默认显示尺寸：90x90
        $size = $size ? $size : '90x90';
        $title = $title ? $title : "图片";
        $is_crop = isset($crop_size) ? 1 : 2;
        $crop_size = isset($crop_size) ? $crop_size : '300x300';
        $crop_rate = isset($crop_rate) ? $crop_rate : 1 / 1;

        //长宽
        $size_arr = explode('x', $size);
        //裁剪尺寸
        $crop_arr = explode('x', $crop_size);

        //图片地址的域名需要修改
        $this->assign('name', $name);
        $this->assign('img_url', $img_url);
        // $this->assign('img_hidden', str_replace(IMG_URL, '', $img_url));
        $this->assign('img_hidden', $img_url);
        $this->assign('img_width', $size_arr[0]);
        $this->assign('img_height', $size_arr[1]);
        $this->assign('title', $title);
        $this->assign('size_tips', $size_tips);
        $this->assign('crop_width', $crop_arr[0]);
        $this->assign('crop_height', $crop_arr[1]);
        $this->assign('crop_rate', $crop_rate);
        $this->assign('is_crop', $is_crop);
        $this->assign('uploadImgExt', self::uploadImgExt);
        $this->assign('uploadImgSize', 1024 * 10);
        return $this->fetch("widget/upload/upload_single_image");
    }
}
