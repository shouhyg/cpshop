<?php
/**
 * admin 控制器验证类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15 0015
 * Time: 17:28
 */
namespace app\admin\validate;
use think\Validate;
use app\common\model\adminUser;
class Admin extends Validate
{
    /**
     * 验证规则
     * @var array
     *   unique:admin_user   表示在admin_user 表里验证当前字段的唯一性
     * rule 数组定义时 键可以加字段提示信息 'username|用户名'=>'' 这样的方式，相当给字段加字段描述
     * 数组的值 以 竖杠 划分 矿建会检测 每个值是否为内置的验证方法，如果不是则查实调用自定义的方法  冒号后面为要传入的参数
     * 验证方法返回为true时表示验证通过 其他情况均会抛出异常
     */
    protected $rule = [
        'username'=>'require|alphaDash|length:4,16|checkUnique:username',
        'nickname'=>'require|length:4,16|unique:admin_user',
        'password'=>'require|length:4,16',
        'password2'=>'require|length:4,16|confirm:password',
        'sex'=>'require|in:1,2,3',
        'phone'=>'require|mobile|unique:admin_user',
        'email'=>'require|email|unique:admin_user',
        '__token__'=>'require|token'

    ];

    /**
     * 提示信息
     * @var array
     */
    protected $message = [
        'username.length'=>'登录名长度必须在4-16位之间',
        'password2.confirm'=>'两次密码不一致',


    ];

    /**
     * 字段描述 当时用默认提示信息时 该描述会添加到相应提示信息的前面
     * @var array
     */
    protected $field = [
        'username'=>'登录名',
        'nickname'=>'昵称',
        '__token__'=>'token',
        'phone'=>'手机号',
        'email'=>'邮箱',
        'password'=>'密码',
        'password2'=>'确认密码',

    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
      'add'=>['username','nickname','__token__','phone','email','password','password2','sex'],
        //'add'=>['username'],

    ];

    /**
     * 检验字段是否唯一
     * $key 为要验证的字段通过 在$rule数组中调用checkUnique 方法时传入
     *当返回true 时表示验证通过
     * 如果未通过验证，可以直接返回要提示的 错误信息
     */
    protected function checkUnique($value, $key)
    {
        $model = new AdminUser();
        $data = AdminUser::get([$key=>$value]);
        if ($data) {
            return (isset($this->field[$key]) ? $this->field[$key] : $key) .'已存在';
        }
        return true;
    }


}