<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Request;
use think\facade\Config;
use think\Validate;
use fast\Random;
class Index extends Backend
{
    //不需要登录 鉴权的操作
    protected $noNeedLogin = ['login'];
    public function index()
    {
        return $this->fetch('index');
    }

    public function welcome()
    {
        $rand = Random::nozero();
        echo $rand;
       // return $this->fetch();
    }

    /**
     * 后台登录
     */
    public function login()
    {
        $url = $this->request->get('url', 'index/index');

        //如果已登录则直接返回上一页
        if ($this->auth->isLogin()) {
            $this->success(lang('You\'ve logged in, do not login again'),$url);
        }

        //post提交
        if ($this->request->isPost()) {
            $username = input('post.username','');
            $password = input('post.password','');
            $keeplogin = input('post.keeplogin','');
            $token = input('post.__token__');//表单令牌

            //使用validate 验证器验证数据
            $rule = [
                'username'=>'require|length:3,30',
                'password'=>'require|length:3,30',
                '__token__'=>'require|token',
            ];
            $message = [
                'username.require'=>'必须输入用户名',
                'username.length'=>'用户名长度为3-30个字符',
                'password.require'=>'必须输入密码',
                'password.length'=>'密码长度为3-30个字符',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
                '__token__' => $token,
            ];
            if (Config::get('login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = input('post.captcha');
            }
            //validate 第一个参数为规则 第二个参数为规则提示信息 第三个参数为字段描述信息 在第二参数没有时 提示信息 以 第三个参数+默认规则的形式提示
            $validate = new Validate($rule,$message,['username' => lang('Username'), 'password' => lang('Password'), 'captcha' => lang('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(),$url);
            }

            //验证登录信息4
            $result = $this->auth->login($username, $password,$keeplogin ? 86400 : 0);
            if($result == true) {
                $this->success(lang('Login successful'),$url,['id'=>$this->auth->id,'username'=>$username]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : lang('Username or password is incorrect');
                $this->error($msg,$url,['token'=>$this->auth->token]);
            }
        }

        //根据客户端cookie 判断是否可以自动登录
        if ($this->auth->autoLogin()) {
            $this->redirect($url);
        }


        return $this->fetch();
    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        $this->auth->loginout();
        $this->success(lang('Logout successful'),'index/login');
    }
}
