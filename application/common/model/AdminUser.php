<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/7 0007
 * Time: 15:14
 */
namespace app\common\model;
use think\Model;
class AdminUser extends Model
{
    //开启自动写入增加和更新时间戳
    protected $autoWriteTimestamp = true;
}