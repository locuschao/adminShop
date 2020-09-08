<?php
// +----------------------------------------------------------------------
// | 公用用的组件
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-09
// +----------------------------------------------------------------------
namespace app\admin\widget;


class Common extends BaseWidget
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 下拉单选框
     * @param string $param 组件参数
     * @param array $list 数据源
     * @param int $selectedId 已选择ID
     * @return mixed
     * @author wuyh
     */
    public function singleSelect($param, $list, $selectedId)
    {
        $arr = explode('|', $param);
        $name = $arr[0];
        $required = $arr[1];
        $tips = $arr[2];
        $showName = $arr[3];
        $value = $arr[4];

        $this->assign('name', $name);
        $this->assign('is_require', $required);
        $this->assign('show_tips', $tips);
        $this->assign('show_name', $showName);
        $this->assign('show_value', $value);
        $this->assign('data_list', $list);
        $this->assign("selected_id", $selectedId);
        return $this->fetch("widget/common/single_select");
    }

    /**
     * 复选框
     * @param $param
     * @param array $list
     * @param array $checkedList
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/24 15:04
     */
    public function checkbox($param, $list = [], $checkedList = [])
    {
        $arr = explode('|', $param);

        // 参数
        $name = trim($arr[0]);
        $showName = trim($arr[1]);
        $showValue = trim($arr[2]);
        foreach ($list as &$val) {
            $val['show_value'] = $val[$showValue];
            $val['show_name'] = $val[$showName];
            $val['checked'] = in_array($val['id'], $checkedList);
        }

        $this->assign('name', $name);
        $this->assign("checkbox_list", $list);
        return $this->fetch("widget/common/checkbox");
    }

    /**
     * 单选按钮
     * @param array $list 数据源
     * @param array $name input框name属性
     * @param int $selected_Id 已选择ID
     * @return mixed
     */
    public function radio($name,$lay_filter,$list, $selected_Id)
    {

        $this->assign('name', $name);
        $this->assign('lay_filter', $lay_filter);
        $this->assign('list', $list);
        $this->assign("selected_id", $selected_Id);
        return $this->fetch("widget/common/radio");
    }

    /**
     * @param $name[字段名]
     * @param $value[值]
     * @return mixed
     */
    public function editor($name,$value){
        $this->assign('name', $name);
        $this->assign('value', $value);
        return $this->fetch("widget/common/editor");
    }

    /**
     * @param $name
     * @param $data
     * @param $select_id
     * @return mixed
     * [下拉选择]
     */
    public function select($name,$data,$select_id){
        $this->assign('name', $name);
        $this->assign('data', $data);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/common/select");
    }


    /**
     * 单选按钮
     * @param array $list 数据源
     * @param array $name input框name属性
     * @param int $selected_Id 已选择ID
     * @return mixed
     */
    public function radio2($name,$lay_filter,$list, $selected_Id)
    {

        $this->assign('name', $name);
        $this->assign('lay_filter', $lay_filter);
        $this->assign('list', $list);
        $this->assign("selected_id", $selected_Id);
        return $this->fetch("widget/common/radio2");
    }
}