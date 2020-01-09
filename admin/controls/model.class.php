<?php
/**
 * 数据模型控制器
 * Created by 杨奇林
 * Date: 2018/7/19
 * Time: 10:29
 * QQ:  928944169 
 */
class model extends Common{

    private $token = array();

    function init(){
        parent::init();
        //查公司的secret和appid
        $this->token = $this->getToken();
    }

	//视图模型列表
    function index(){
        $result = $this->getJson(MODEL_API.'api/get_model_view_list',$this->token,'POST');
        // p($result);exit();
        if($result['code'] != '000000'){
            $this->error($result['msg'],2,'controller/controller_list');
        }
        $this->assign('list',$result['list']);
        $this->display();
    } 

    //前端视图列表
    function view_list(){
        $result = $this->getJson(MODEL_API.'api/get_view_list',$this->token,'POST');
        if($result['code'] != '000000'){
            $this->error($result['msg'],2,'controller/controller_list');
        }
        $this->assign('list',$result['list']);
        $this->display();
    }

    //导入视图模型
    function load_view(){
        $view_id = $_POST['view_id'];
        if(empty($view_id))
            ajaxReturn(array('code'=>'100001','msg'=>'参数错误'),'JSON');
        $this->token['view_id'] = $view_id;
        $view_data = $this->getJson(MODEL_API.'api/get_view_info',$this->token,'POST');
        if($view_data['code'] != '000000'){
            ajaxReturn(array('code'=>$view_data['code'],'msg'=>$view_data['msg']),'JSON');
        }else{
            ajaxReturn($view_data,'JSON');
        }
        
    }
}