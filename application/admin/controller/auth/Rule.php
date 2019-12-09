<?php
namespace app\admin\controller\auth;
use app\common\controller\Backend;
use app\common\model\AuthRule;
use fast\Tree;
class Rule extends Backend
{
    //模型对象
    protected $model = null;

    //初始化
    public function initialize()
    {
        parent::initialize();
        $this->model = new AuthRule();
        $rulelist = $this->model->order('weigh', 'desc')->order('id', 'asc')->select();

        $rulelist = $rulelist->toArray(); // print_r($rulelist);
        //通过引用赋值的方式 给指定的选项加载语言包
        foreach ($rulelist as $k => &$v) {
            $v['title'] = lang($v['title']);
            $v['remark'] = lang($v['remark']);
        }

        unset($v);
        Tree::instance()->init($rulelist);
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');
        $ruledata = [0 => lang('None')];
        foreach ($this->rulelist as $k => $v) {
            if (!$v['ismenu']) {
                continue;
            }
            $ruledata[$v['id']] = $v['title'];
        }
        $this->view->assign('ruledata', $ruledata);


    }

    /**
     * 添加菜单规则
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = input('row/a', [], 'strip_tags');

            //如果是非菜单规则 则必须存在父类
            if (!$params['ismenu'] && !$params['pid']) {
                $this->error(lang('The non-menu rule must have parent'));
            }
            /*
             * 通过控制器的validate()方法传入要使用的验证器类和验证场景来实现验证
             * 该方法验证成功时会返回一个true 验证失败时 返回错误提示信息
             */
            $params["__token__"] = input('__token__', '');
            $result = $this->validate($params, 'app\admin\validate\Rule.add');
            if ($result !== true) {
                $this->error($result);
            }
            $res = $this->model->save($params);
            if (!$res) {
                $this->error('添加失败');
            }
            $this->success('添加成功');
        }

        return $this->fetch();
    }

    /**
     * 编辑规则
     */
    public function edit()
    {
        $id = input('id/d','','intval');
        if(!$id){
            $this->error('缺失编辑条件，无法编辑');
        }
        $dataObject = $this->model->get($id);
        if($this->request->isPost()){
            $params = input('row/a', [], 'strip_tags');
            print_r($params);die;

        }
        return $this->fetch('',[
            'data'=>$dataObject,
        ]);
    }
}