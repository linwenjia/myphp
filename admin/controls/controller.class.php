<?php
/**
 * 数据模型控制器
 * Created by 杨奇林
 * Date: 2018/7/19
 * Time: 10:29
 * QQ:  928944169 
 */
class controller extends Common{
    function index(){

        //查公司的secret和appid
        $token = $this->getToken();
        $token['regist_ip'] = bro_ip();
        
        /* 查询模型视图 start*/
        $model_view = $this->getJson(MODEL_API.'api/get_model_view_data',$token,'POST');
        if($model_view['code'] != '000000'){
            $this->error($model_view['msg']);
        }
        $this->assign('model_view_list',$model_view['list']);
        /* 查询模型视图 end*/

        /* 查询前端视图 start*/
        $view_data = $this->getJson(MODEL_API.'api/get_view_list',$token,'POST');
        if($view_data['code'] != '000000'){
            $this->error($view_data['msg']);
        }
        $this->assign('view_data',$view_data['list']);
        /* 查询前端视图 end*/

        //选择使用方法
        $func_data = array(
            'banner' => '轮播图',
            'advert' => '广告图',
            'cls_data' => '分类数据',
            'news_data' => '文章数据',
            'count_data' => '统计数据'
        );
        $this->assign('func_data',$func_data);
        $this->display();
    } 

    //控制器列表
    function controller_list(){
        $db_controller = D('controller');
        $controller = $db_controller->getControllerList();
        $this->assign('controller',$controller);
        $this->display();
    }
    
    //设置控制器的名字
    function setController(){
        if($_POST['name']){
            //查询控制器是否已经存在
            $db_controller = D('controller');
            $info = $db_controller->where(array('controller_name'=>$_POST['name']))->find();
            if(!empty($info)){
                ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'控制器已存在，请重新填写一个'),"JSON");
            }
            $_SESSION[bro_ip().'_controller'] = $_POST['name']?$_POST['name']:'index_test';
            $_SESSION[bro_ip().'_controller_explain'] = $_POST['explain']?$_POST['explain']:'测试控制器';
            ajaxReturn(array('control'=>'controller','code'=>200,'msg'=>$_SESSION[bro_ip().'_controller'],'url'=>B_URL.'/index'),"JSON");
        }
    }

    //创建控制器
    function create(){
        $control_name = $_SESSION[bro_ip().'_controller']?$_SESSION[bro_ip().'_controller']:'index_test';//控制器文件名
        $fileName = strtolower($control_name).'.class.php';// 获取需要创建的文件名称
        $db_controller = D('controller');

        /*获取对应的方法 start*/
        $function = $db_controller->get_function($_POST['name']);
        if(empty($function))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'调用的方法不存在'),"JSON");
        /*获取对应的方法 end*/

        /* 整理controller表数据 start*/
        $insert = array();
        $insert['controller_name'] = $control_name;
        $insert['controller_desc'] = $_SESSION[bro_ip().'_controller_explain']?$_SESSION[bro_ip().'_controller_explain']:'默认注释';
        $insert['controller_path'] = $fileName;
        $insert['add_time'] = time();
        /* 整理controller表数据 end*/

        $result = $db_controller->addController($insert,$_POST);
        ajaxReturn(array('control'=>'controller','code'=>$result['code'],'msg'=>$result['msg'],'url'=>B_ROOT.'/'.$control_name.'?get_smarty_json=1'),"JSON");
    }

    //修改控制器
    function mod(){
        //控制器模型
        $db_controller = D('controller');
        $controller_id = intval($_GET['controller_id']);
        $info = $db_controller->where($controller_id)->find();
        if(empty($info))
            $this->error('控制器不存在',2);
        $this->assign('info',$info);

        //查公司的secret和appid
        $token = $this->getToken();
        $token['regist_ip'] = bro_ip();
        
        /* 查询模型视图 start*/
        $model_view = $this->getJson(MODEL_API.'api/get_model_view_data',$token,'POST');
        if($model_view['code'] != '000000'){
            $this->error($model_view['msg']);
        }
        $this->assign('model_view_list',$model_view['list']);
        /* 查询模型视图 end*/

        //选择使用方法
        $func_data = array(
            'banner' => '轮播图',
            'advert' => '广告图',
            'cls_data' => '分类数据',
            'news_data' => '文章数据',
            'count_data' => '统计数据'
        );
        $this->assign('func_data',$func_data);
        
        $this->display();
    }

    //修改操作
    function ajaxMod(){
        $db_controller = D('controller');
        $controller_id = intval($_POST['controller_id']);
        if(empty($controller_id) || empty($_POST['postDate']))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'参数错误'),"JSON");
        $info = $db_controller->where($controller_id)->find();
        if(empty($info))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'控制器不存在'),"JSON");
        
        //调用方法，修改controller_extend表
        $result = $db_controller->updateController($info,$_POST['postDate'],$_POST['dele_arr']);
        ajaxReturn(array('control'=>'controller','code'=>$result['code'],'msg'=>$result['msg'],'url'=>B_ROOT.'/'.$info['controller_name'].'?get_smarty_json=1'),"JSON");
    }

    //ajax获取控制器信息
    function getControllerInfo(){
        $controller_id = intval($_POST['controller_id']);
        if(empty($controller_id))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'参数错误'.$controller_id),"JSON");
        $db_controller_extend = D('controller_extend');
        $post = $db_controller_extend->where(array('controller_id'=>$controller_id))->select();
        if(!empty($post)){
            ajaxReturn(array('control'=>'controller','code'=>200,'msg'=>'控制器信息','post'=>$post),"JSON");
        }else{
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'控制器不存在'),"JSON");
        }
    }

    //删除控制器
    function del(){
        $controller_id = intval($_POST['controller_id']);
        if(empty($controller_id))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'参数错误'),"JSON");
        $db_controller = D('controller');
        $result = $db_controller->delController($controller_id);
        ajaxReturn(array('control'=>'controller','code'=>$result['code'],'msg'=>$result['msg']),"JSON");
    }

    //获取调用方法，预览时使用
    private function get_function($param){
        $str = '';
        switch ($param['type']) {
            case 'banner':
                $str = get_banner($param['position'],$param['cls_id'],$param['textnum']);
                break;
            case 'advert':
                $str = get_img_by_id($param['cls_id']);
                break;
            case 'cls_data':
                $str = get_cls_data($param['output_type'],$param['level'],$param['cls_id'],$param['textnum']);
                break;
            case 'news_data':
                $str = get_news_data($param['output_type'],$param['cls_id'],$param['news_id'],$param['textnum']);
                break;
            case 'count_data':
                $str = get_count($param['count_data']);
                break;
            default:
                $str = '';
                break;
        }
        return $str;
        // return $functions_arr[$key];
    }

    //获取预览数据
    function get_preview_data(){
        if(empty($_POST))
            ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'参数错误','data'=>array()),"JSON");
        $json_data = array();
        foreach ($_POST as $key => $val) {
            $json_data[$val['output']] = $this->get_function($val);
            // p($json_data);
        }
        // p($json_data);exit();
        ajaxReturn(array('control'=>'controller','code'=>200,'msg'=>'成功','data'=>$json_data),"JSON");
    }

    //测试
    function test(){
       $db_controller = D('controller');
       $db_controller->getControllerInfo(10);
    }
}