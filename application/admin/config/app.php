<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/7 0007
 * Time: 11:46
 */
return[
    // URL普通方式参数 用于自动生成 类似 ？a=b 形式的参数
    'url_common_param'       => true,
    //取消框架自动添加的htm后缀
    'url_html_suffix'        => '',
    //后台登录是否开启验证码
    'login_captcha'       => false,
    //管理员账号状态对照表
    'adminstatus' => [
        'in_check' => 1, //待审核
        'check_success' => 2,
        'check_false' => 3,
        'hidden' => 4, //封号

    ],
    //自动定位控制器
    'controller_auto_search' => true,
];