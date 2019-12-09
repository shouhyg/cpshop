<?php
/**
 * 管理后台基类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/6 0006
 * Time: 9:10
 */
namespace app\common\controller;
use app\admin\library\Auth;
use think\Controller;
use think\facade\Lang;
use think\Loader;
use think\Session;

class Backend extends Controller
{
    //权限控制类
    protected $auth = null;

    //请求路径
    public $requestUri;

    //无需登录的操作 也无需鉴权
    protected $noNeedLogin = [];

    //需要登录但无需鉴权
    protected $noNeedRight = [];

    /**
     * 初始化函数
     */
    protected function initialize(){
        $modulename = $this->request->module();//模块名称
        //paraseName 函数用来处理控制器名的大小写及含下划线的问题 返回小写的名称
        $controllername = Loader::parseName($this->request->controller());//控制器名称
        $actionname = strtolower($this->request->action());//操作名称

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;//请求路径

        //定义是否是AJAX请求
        !defined('AJAX') && define('AJAX',$this->request->isAjax());

        //加载当前控制器语言包 zh-cn.php(语言类型命名)的文件为默认加载
        $this->loadLang();

        //初始化权限控制类
        $this->auth = Auth::instance();

        //设置请求路径
        $this->auth->setRequestUri($path);

        //判断当前用户是否需要登录
        if(!$this->auth->match($this->noNeedLogin))
        {
            //检测登录
            if(!$this->auth->isLogin()){//未登录
                //设置来源路径
                $url = Session('referer');
                $url = $url ? $url : $this->request->url();
                $this->error(lang('Please login first'),url('index/login',[ 'url'=>'index/login' ]));

            }
        }




    }

    /**
     * 加载当前控制器语言包
     */
    public function loadLang()
    {
        $controller = $this->request->controller();
        //解决当使用多级控制器和路由的时 因为$this->request->controller() 返回类似auth.rule 的值 而导致无法正常加载路由文件
        if(strpos($controller,'.')  !== false ){
            $controller  =   str_replace('.','/',$controller);
        }
        Lang::load(APP_PATH.'/'.$this->request->module().'/lang/'.$this->request->langset().'/'.$controller.'.php');
    }

    /**
     * 渲染配置信息
     * @param $name 键名或者数组
     * @param $value 值
     */
    protected function assignconfig($name, $value)
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [] , is_array($name) ? $name : [$name => $value]);
    }
}