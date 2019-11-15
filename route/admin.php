<?php
/**
 * 管理后台路由文件
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/12 0012
 * Time: 16:27
 */
use think\facade\Route;
Route::rule('admin/admin/index','admin/auth.admin/index'); //管理员列表 多级控制器
Route::rule('admin/admin/add','admin/auth.admin/add'); //添加管理员 多级控制器
