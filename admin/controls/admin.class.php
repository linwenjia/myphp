<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/6/6
 * Time: 9:08
 * QQ:  997823131 
 */
class admin {
    // 添加管理员
    function admin_add(){
        if(empty($_POST['user_name']) || empty($_POST['user_passwd']))
            $this->error('参数错误');
        $db_admin_user = D('admin_user');
        $info = $db_admin_user->where(array('user_name'=>$_POST['user_name']))->find();
        if(!empty($info))
            $this->error('用户名已存在');
        $insert = array();
        $insert['user_name'] = trim($_POST['user_name']);
        $insert['user_passwd'] = md5(trim($_POST['user_passwd']));
        $insert['last_login_time'] = time();
        $insert['add_time'] = time();
        if($db_admin_user->insert($insert))
            $this->success('添加成功');
        else
            $this->error('添加失败');
    }
    // 管理员列表
    function admin_list(){
        $db_admin_user = D('admin_user');
        $user_list = $db_admin_user->select();
        $this->assign('user_list',$user_list);
        $this->display();
    }
    //修改管理员
    function admin_eidt(){
        $user_id = intval($_POST['user_id']);
        if(empty($user_id) || empty($_POST['user_passwd_new']))
            $this->error('参数错误');
        $db_admin_user = D('admin_user');
        $info = $db_admin_user->where($user_id)->find();
        if(empty($info))
            $this->error('管理员不存在');
        //只允许修改自己的密码
        if($info['user_name'] != $_SESSION['admin_user']['user_name'])
            $this->error('不能修改其他人的密码');
        if($info['user_passwd'] != md5($_POST['user_passwd']))
            $this->error('密码不正确');
        $update = array();
        $update['user_passwd'] = md5($_POST['user_passwd_new']);
        if($db_admin_user->where($user_id)->update($update))
            $this->success('修改成功');
        else
            $this->error('无任何修改');
    }
    //删除管理员
    function del_admin(){
        if($_SESSION['admin_user']['user_name'] != 'admin')
            ajaxReturn(array('control'=>'del_admin','code'=>0,'msg'=>'超级管理员才能删除'));
        $user_id = intval($_POST['user_id']);
        if(empty($user_id))
            ajaxReturn(array('control'=>'del_admin','code'=>0,'msg'=>'参数错误'));
        $db_admin_user = D('admin_user');
        $info = $db_admin_user->where($user_id)->find();
        if(empty($info))
            ajaxReturn(array('control'=>'del_admin','code'=>0,'msg'=>'管理员不存在'));
        if($info['user_name'] == 'admin')
            ajaxReturn(array('control'=>'del_admin','code'=>0,'msg'=>'超级管理员不能删除'));
        if($db_admin_user->where($user_id)->delete())
            ajaxReturn(array('control'=>'del_admin','code'=>200,'msg'=>'删除成功'));
        else
            ajaxReturn(array('control'=>'del_admin','code'=>0,'msg'=>'删除失败'));
    }
}