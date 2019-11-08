<?php
/**
 * 管理后台权限控制类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/6 0006
 * Time: 17:43
 */
namespace app\admin\library;

use fast\Random;
use think\facade\Session;
use think\Request;
use app\common\model\AdminUser;
use think\facade\Config;
use think\facade\Cookie;

class Auth
{
    protected static $instance = null; //权限验证实例 采用单例模式 此变量用来保存
    public $logined = false; //登录状态 默认未登录
    protected $requestUri;
    protected $_error = ''; //验证类错误信息提示


    /**
     * 初始化权限类
     * @acess public
     * @param array $option
     * @return Auth 实例
     */
    public static function instance($options=[])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);//new static 返回调用者的实例 new self 返回当前文件的实例
        }
        return self::$instance;
    }

    /**
     * 在auth 类里面设置魔术方法 __get 这样在啊获取类里面未定义的字段时调用此方法 如 $this->id 的调用
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return Session('admin.'.$name);
    }

    /**
     * 判断当前账号是否登录
     * @return bool true 为已登录 false 未登录
     */
    public function isLogin()
    {
        /**
         * 系统每初始化一个 logined的值就为false  只有在同一个执行过程 第二次调用auth对象时该值才可能为true;
         * 通过设置成员属性的方法可以避免一个调用进程 重复登陆验证的操作 进程执行完毕给变量就会释放
         * 但对于非同一个请求该变量无实际意义，只能通过session 里面的参数来验证
         */
        if ($this->logined) {
            return true;
        }
        $admin = Session::get('admin');
        if (!$admin) {
            return false;
        }

        //同一时间一个账号只允许一个人登陆
        if (Config::get('app.admin.login_uinque')) {
            $adminuser = AdminUser::get($admin['id']);
            if (!$adminuser || $adminuser['token'] != $admin['token'] ) {
                return false;
            }
        }

        $this->logined = true;
        return true;
    }

    /**
     * 设置当前请求的url
     * @param $uri 当前请求的url
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取当前请求的uri
     * @return string 返回当前的uri
     */
    public function getRequsstUri()
    {
        return $this->requestUri;
    }

    /**
     * 检查当前操作与 传入的数组是否能匹配
     * @param array $arr  字符串或者数组
     * @return bool true 为无需登录或鉴权 false 没匹配到则需要登录或鉴权
     */
    public function match($arr=[])
    {
       $arr = is_array($arr) ? $arr : explode(',',$arr);
       if (!is_array($arr)) {
           return false;
       }
       // 将操作全部转换为小写
       $arr = array_map('strtolower',$arr);
       //判断当前操作是否需要登录或鉴权
       if (in_array(strtolower(request()->action()),$arr) || in_array('*',$arr)) {
           return true;
       }
       //没有匹配到
       return false;
    }

    /**
     * 验证登录
     * @param $username 用户名
     * @param $password 密码
     * @param int $keeptime 登录保持时间
     */
    public function login($username, $password, $keeptime=0)
    {
        $adminuser = AdminUser::get(['username'=>$username]);

        //用户不存在
        if (!$adminuser) {
            $this->setError('Username is incorrect');
            return false;
        }

        //封号
        if ($adminuser['status'] == Config::get('app.adminstatus.hidden')) {
            $this->setError('Admin is forbidden');
            return false;
        }

        //每天登录失败超过10次需要一天后再登录
        if (Config::get('app.admin.login_failure_retry') && $adminuser->loginfailure > 10 && time() - $adminuser->update_time < 86400 ) {
            $this->setError('Please try again after 1 day');
            return false;
        }

        //验证密码
        if ($adminuser->password != md5(md5($password.$adminuser->salt))) {
            $adminuser->loginfailure++;
            $adminuser->save();
            $this->setError('Password is incorrect');
            return false;
        }

        //登录验证通过 更新登录信息
        $adminuser->loginfailure = 0;
        $adminuser->logintime = time();
        $adminuser->loginip = request()->ip();
        $adminuser->token = Random::uuid();
        $adminuser->save();
        Session::set("admin", $adminuser->toArray());
        $this->keepLogin($keeptime);
        return true;
    }

    //设置错误提示信息
    public function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 设置自动信息写入cookie
     * @param int $keeptime 登录信息有效时间
     * @return bool
     */
    public function keepLogin($keeptime=0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;
            $key = md5(md5($this->id).md5($keeptime).md5($expiretime).$this->token);
            $data = [
                'id' => $this->id,
                'keeptime' => $keeptime,
                'expiretime' => $expiretime,
                'key' => $key,
            ];
            //设置cookie 键名 keeplogin 值为data的字符串形式 cookie 过期时间30天
            Cookie::set('keeplogin',implode('|',$data),86400*30);
            return true;
        }
        return false;
    }

    public function autoLogin(){
        $cookiedata = Cookie::get('keeplogin');
        if (!$cookiedata) {
            return false;
        }
        list($id,$keeptime,$expiretime,$key) = explode('|',$cookiedata);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()){
            $adminuser = AdminUser::get(intval($id));
          //  if (!$adminuser || !$adminuser->token) { //token 暂未生成
            if (!$adminuser ) {
                return false;
            }

            if ($key != md5(md5($id).md5($keeptime).md5($expiretime).$adminuser->token)) {
                return false;
            }
            $ip = request()->ip();
            if ($ip != $adminuser->loginip ) {
                return false;
            }

            //验证通过更新登录信息
            Session::set('admin',$adminuser->toarray());
            $this->keeplogin($keeptime);
            return true;
        }
    }

    //退出登录
    public function loginout(){
        $adminuser = AdminUser::get(intval($this->id));
        if ($adminuser) {
            $adminuser->token = '';
            $adminuser->save();
        }
        $this->logined = false;
        Session::delete('admin');
        Cookie::delete('keeplogin');
        return true;
    }

}