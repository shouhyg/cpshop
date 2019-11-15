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
class Admin extends Validate
{
    /**
     * 验证规则
     * @var array
     *   * [username] => admin1
    [nickname] => 管理员1
    [password] => 123123
    [password2] => 123123
    [sex] => on
    [phone] => 15713889722
    [email] => 826183140@qq.com
     */
    protected $rule = [
        'username'=>'require|alphaDash|length:4,16',
        'nickname'=>'require|alphaDash|length:4,16',
        'password'=>'require|length:4,16',
        'password2'=>'require|length:4,16|confirm:password',
        'sex'=>'requier|in:1,2,3',
        'phone'=>'require|mobile',
        'email'=>'email'

    ];

    /**
     * 提示信息
     * @var array
     */
    protected $message = [
       // 'username.length'=>'登录名长度必须在4-16位之间',
       // 'password2.confirm'=>'两次密码不一致'

    ];

    /**
     * 字段描述 当时用默认提示信息时 该描述会添加到相应提示信息的前面
     * @var array
     */
    protected $field = [
        'username'=>'登录名',
        'nickname'=>'昵称',

    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'add'=>['username','nickname'],

    ];


}