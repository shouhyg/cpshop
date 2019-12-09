<?php
/**admin 模块 公用函数文件
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/21 0021
 * Time: 9:59
 */
use fast\Form;
if(!function_exists('build_select')){

    /**
     * 生成用户组树
     * @param $name  下拉列表的 名称
     * @param $options  下拉列表的可选项
     * @param array $selected  已选中的项
     * @param array $attr  属性
     */
    function build_select($name, $options, $selected=[],$attr=[])
    {
        $options = is_array($options) ? $options : explode(',',$options);
        $selected = is_array($selected) ? $selected : explode(',',$selected);
        //return Form::select($name, $options, $selected, $attr);
        $data =  Form::select($name, $options, $selected, $attr);
        echo $data;
    }
}