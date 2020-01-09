<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/11/16
 * Time: 11:24
 * QQ:  997823131 
 */
class demand{
    function index(){
        $this->display();
    }
    function get_jsonData(){
        $db_demand = D('demand');
        $data_demand = $db_demand->where()->order("time desc")->select();
        ajaxReturn($data_demand,"JSON");
    }
}