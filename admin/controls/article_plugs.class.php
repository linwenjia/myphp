<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2018/10/7
 * Time: 13:58
 * QQ:  997823131 
 */
// 自动发送文章
class article_plugs extends Action{
    protected $ip="47.106.155.35";
    protected $key='455a35b0261a0671e7b0a5921b45ab7c';
    function add(){
        $db = D('news');
        /*if($_SERVER['REMOTE_ADDR'] != $this->ip){
            ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'非法','data'=>$_SERVER['REMOTE_ADDR'],'ip'=>$this->ip),"JSON");
        }*/

        if($_POST['app_key'] != $this->key){
            ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'非法'),"JSON");
        }

        if(empty($_POST['news_body'])){
            ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'无内容'),"JSON");
        }
        /* 查出行业资讯分类 start */
        $db_cls = D('news_cls');
        $data_cls = $db_cls->where(array("news_cls_name"=>'%行业%'))->find();
        if(!$data_cls){
            $data_cls = $db_cls->find();
        }
        /* 查出行业资讯分类 end */
        $_POST['news_cls_id'] = $data_cls['news_cls_id'];
        $result = $db->insert($_POST);
        if($result){
            ajaxReturn(array('control'=>'add','code'=>200,'msg'=>'成功','data'=>$result),"JSON");
        }else{
            $str = var_export($_POST,true);
            file_put_contents("log.txt",$str.PHP_EOL);
            ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'失败','data'=>$_POST),"JSON");
        }
    }
}