<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/11/16
 * Time: 11:25
 * QQ:  997823131 
 */
class investment{
    function index(){
        $this->display();
    }
    function get_jsonData(){
        $db_investment = D('investment');
        $data_investment = $db_investment->order("time desc")->select();
        ajaxReturn($data_investment,"JSON");
    }
}