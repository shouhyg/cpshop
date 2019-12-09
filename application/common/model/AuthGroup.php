<?php
/**
 * 角色组模型文件
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/22 0022
 * Time: 14:03
 */
namespace app\common\model;
use think\Model;
class AuthGroup extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    //设置自动写入时间的字段名称 默认为create_time 和update_time
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}