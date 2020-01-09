<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/6/6
 * Time: 9:00
 * QQ:  997823131 
 */
class pro{
    function index(){

    }
    // 商品添加
    function pro_add(){
        $this->display();
    }
    // 商品列表
    function pro_list(){


    }
    // 商品分类
    function pro_cls(){
        $db_news_cls = D('pro_cls');
        // 一级分类
        $data_news_cls = $db_news_cls
            ->where(array('level'=>1))
            ->order("sort asc")
            ->select();
        // 根据一级 获取 二级分类

        if($data_news_cls){
            for($i=0;$i<count($data_news_cls);$i++){
                $data_second_cls = $db_news_cls
                    ->where(array('cls_pid'=>$data_news_cls[$i]['news_cls_id'],'level'=>2))
                    ->order('sort asc')
                    ->select();
                $data_news_cls[$i]['son_data'] = $data_second_cls;
            }
        }
        $this->assign("data_news_cls",$data_news_cls);
        $this->display();
    }

    // 添加分类
    function cls_add(){
        $db_news_cls = D('pro_cls');

        if($_POST['sub']){
            $upload = new FileUpload();
            $result_upload = $upload->upload('img_show');
            if($result_upload){
                $insert['news_cls_pic'] = $upload->getFileName();
                $insert['news_cls_name'] = $_POST['news_cls_name'];
                $insert['cls_pid'] = $_POST['cls_pid'];
                $insert['level'] = $_POST['cls_pid'] ? 2:1;
                $result = $db_news_cls->insert($insert);
                if($result){
                    $this->success('新增成功',2,"news/cls_list",'self');
                }else{
                    $this->error('新增不成功',2,"news/cls_list",'self');
                }
            }else{
                $this->error($upload->getErrorMsg(),2,"news/cls_list",'self');
            }
        }else{
            if(isset($_GET['pid'])){
                $this->assign('pid',$_GET['pid']);
            }
            $data_news_cls = $db_news_cls->where(array('level'=>1))->select();
            $this->assign('data_news_cls',$data_news_cls);
            $this->display();
        }

    }
    // 修改分类
    function edit_cls(){
        $db_news_cls = D('pro_cls');
        $update['news_cls_name'] = $_GET['cls_name'];
        $result = $db_news_cls->where($_GET['cls_id'])->update($update);
        if($result){
            ajaxReturn(array('control'=>'edit_cls','code'=>200,'msg'=>'成功'),"JSON");
        }else{
            ajaxReturn(array('control'=>'edit_cls','code'=>0,'msg'=>'失败','data'=>$_GET),"JSON");
        }
    }
    // 排序
    function sort(){
        $db_news_cls = D('pro_cls');
        $update['sort'] = $_GET['sort_id'];
        $result = $db_news_cls->where($_GET['cls_id'])->update($update);
        if($result){
            ajaxReturn(array('control'=>'sort','code'=>200,'msg'=>'成功'),"JSON");
        }else{
            ajaxReturn(array('control'=>'sort','code'=>0,'msg'=>'失败','data'=>$_GET),"JSON");
        }
    }
}