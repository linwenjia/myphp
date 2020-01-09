<?php
/**
 * 留言管理
 * Created by 杨奇林
 * Date: 2018/5/29
 * Time: 16:08
 * QQ:  928944169 
 */
class message{
    function index(){

    }
    // 留言列表
    function message_list(){
        $db_message = D('message');
        $data = $db_message->total();

        $page = new Page($db_message->total(),8);
        $data_message = $db_message->select();
        $this->assign('data_message',$data_message);
        $this->assign('fpage',$page->fpage());
        $this->display();
    }
    //删除留言
    function del_message(){
        if(empty($_POST['message_id']))
            ajaxReturn(array('code'=>0,'msg'=>'参数错误'),"JSON");
        $db_message = D('message');
        $result = $db_message->where($_POST['message_id'])->delete();
        if($result)
            ajaxReturn(array('code'=>200,'msg'=>'删除成功'),"JSON");
        else
            ajaxReturn(array('code'=>0,'msg'=>'删除失败'),"JSON");
    }
}