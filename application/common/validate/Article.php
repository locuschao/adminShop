<?php
// +----------------------------------------------------------------------
// | 商品表验证
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\validate;
use think\Validate;
class Article extends Validate{

    protected $rule = [
        'title' => 'require',
        'thum' => 'require',
        'content' => 'require',
        'nickname' => 'require',
        'user_id' => 'require',
        'recommend_img' => 'require',
    ];

    protected $message = [
        'title.require' => '请填写文章标题',
        'thum.require' => '请上传标题图片',
        'content.require' => '请填写文章内容',
        'nickname.require' => '请选用用户',
        'user_id.require' => '请选用用户',
        'recommend_img.require' => '请上传推荐位图片',
    ];

    protected $scene = [
        'save' => ['title','thum','content', 'nickname', 'user_id'],
        'save_recommend' => ['title','thum','content', 'nickname', 'user_id','recommend_img'],
    ];
}