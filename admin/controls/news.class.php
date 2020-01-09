<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/6/2
 * Time: 11:00
 * QQ:  997823131 
 */
class news{

    function index(){

    }

    // 资讯 分类列表
    function cls_list(){
        $db_news_cls = D('news_cls');
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
                //如果有二级 则遍历二级的
                if($data_second_cls){
                    for($j=0;$j<count($data_second_cls);$j++){
                        $data_three_cls = $db_news_cls
                            ->where(array('cls_pid'=>$data_second_cls[$j]['news_cls_id']))
                            ->order('sort asc')
                            ->select();
                        $data_second_cls[$j]['son_data'] = $data_three_cls;
                    }
                }
                $data_news_cls[$i]['son_data'] = $data_second_cls;
            }
        }
//        p($data_news_cls);
        $this->assign("data_news_cls",$data_news_cls);
        $this->display();
    }
    // 删除类别
    function del_cls(){
        $db_cls = D("news_cls");
        $result = $db_cls->where(array('news_cls_id'=>$_GET['cls_id']),array('cls_pid'=>$_GET['cls_id']))->delete();
        if($result){
            ajaxReturn(array('control'=>'del_cls','code'=>200,'msg'=>'删除成功'),"JSON");
        }else{
            ajaxReturn(array('control'=>'del_cls','code'=>0,'msg'=>'删除失败'),"JSON");
        }
    }

    // 添加资讯
    function add(){
        $db_news = D('news');
        
        if(isset($_POST['sub'])){
            if(empty($_POST['goods_name']))
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'标题不能为空'),"JSON");
            if(empty($_POST['goods_pname']))
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'副标题不能为空'),"JSON");
            //判断$_POST['goods_name']标题是否已经存在 start
            $name_isexist = $db_news->where(['news_name'=>$_POST['goods_name']])->field('news_name')->find();
            if ($name_isexist) {
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'资讯标题已存在，换一个吧！'),"JSON");
            }
            //判断$_POST['goods_name']标题是否已经存在 end
        
            /*产品属性 start*/
            //p($_POST);
            $label_list = explode('|',$_POST['label_list']);
            $label_list_id = explode('|',$_POST['label_list_id']);
            $label_desc = $_POST['label_desc'];
            //p($label_list);p($label_list_id);
            $attribute = array();
            foreach ($label_list as $key => $val) {
                $attribute[$key]['id'] = $label_list_id[$key];
                $attribute[$key]['name'] = $val;
                $attribute[$key]['desc'] = $label_desc[$key];
            }
            //p($attribute);p(serialize($attribute));exit();
            $insert['attribute'] = serialize($attribute);
            /*产品属性 end*/

            $db_news_cls = D('news_cls');
            $data_news_cls = $db_news_cls->where($_POST['goods_cls'])->find();
            $data_news_cls['news_cls_id'] = $data_news_cls['news_cls_id'] ? $data_news_cls['news_cls_id'] : "1";
            $data_news_cls['news_cls_name'] = $data_news_cls['news_cls_name'] ? $data_news_cls['news_cls_name'] : "默认分类";
            /* 先插入数据 再上传图片 再修改内容 start */
            $insert['news_name'] = $_POST['goods_name'];
            $insert['news_pname'] = $_POST['goods_pname'];
            $insert['news_cls_id'] = $data_news_cls['news_cls_id'];
            $insert['news_cls_name'] = $data_news_cls['news_cls_name'];
            $insert['news_body'] = $_POST['content'];
            $insert['news_key'] = $_POST['goods_key'];
            $insert['news_imgalt'] = $_POST['goods_imgalt'];
            $insert['label'] = $_POST['label'];
            $insert['sort'] = $_POST['sort'];
            $insert['case_url'] = $_POST['case_url'];

            $insert['add_times'] = time();
            $insert['source'] = $_POST['goods_source'];
            
            $row = $db_news->insert($insert);
            if($row){
                
                /*封面图 start*/
                if($_FILES['file']['name']){
                    $up = new FileUpload();
                    $up->set('path','./public/uploads/news');
                    $result_file = $up->upload('file');
                    if($result_file){
                        $pic_name = $up->getFileName();
                        $update = array();
                        $update['news_pic'] = $pic_name;
                        $result = $db_news->where($row)->update($update);
                    }
                }
                /*封面图 end*/

                /*案例背景图 start*/
                if($_FILES['case_img']['name']){
                     $up = new FileUpload();
                     $up->set('path','./public/uploads/news');
                     $result_file = $up->upload('case_img');
                     if($result_file){
                        $update = array();
                        $update['case_img'] = $up->getFileName();
                        $result = $db_news->where($row)->update($update);
                    }
                }
                /*案例背景图 end*/

                /* 轮播图上传 start */
                $j=6;
                for($i=0;$i<$j;$i++){
                    $up = new FileUpload();
                    $up->set('path','./public/uploads/news');
                    $result_files = $up->upload('banner'.$i);
                    if($result_files){
                        $banner[$i] = $up->getFileName();
                    }
                }
                if($banner){
                    $update = array();
                    $update_new['news_banner'] = serialize($banner);
                    $result = $db_news->where($row)->update($update_new);
                }
                // p($_FILES);
                // p($banner);
                // p($update_new);
                // exit();
                /* 轮播图上传 end */

                //百度主动推送
                if(C('is_push') == 'on'){
                    $result = tuisong_baidu(array(SHOP_SITE_URL.'/whshow?id='.$row));
                    if(!is_dir(PROJECT_PATH.'log'))
                        mkdir(PROJECT_PATH.'log', 0775);
                    @file_put_contents(PROJECT_PATH.'log'.DS.'tuisong_baidu_log.txt',$result.PHP_EOL,FILE_APPEND);
                }
                ajaxReturn(array('control'=>'add','code'=>200,'msg'=>'添加成功','url'=>SHOP_SITE_URL.'/admin.php/news/news_list'),"JSON");
            }else{
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'添加失败'),"JSON");
            }
            /* 先插入数据 再上传图片 再修改内容 end */
        }else{
            //获取产品属性
            $db_attribute = D('attribute');
            $list_attr = $db_attribute->select();
            $this->assign('list_attr',$list_attr);
            //$cls_array = $this->get_cls_list();
            $cls_array = getChildArr();
            $this->assign("cls_array",$cls_array);
            $this->display();
        }
    }
    function mod(){
        $db_news = D('news');
        
        if(isset($_POST['sub'])){
            if(empty($_POST['goods_name']))
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'标题不能为空'),"JSON");
            if(empty($_POST['goods_pname']))
                ajaxReturn(array('control'=>'add','code'=>0,'msg'=>'副标题不能为空'),"JSON");
            
            /*产品属性 start*/
            //p($_POST);
            $label_list = explode('|',$_POST['label_list']);
            $label_list_id = explode('|',$_POST['label_list_id']);
            $label_desc = $_POST['label_desc'];
            //p($label_list);p($label_list_id);
            $attribute = array();
            foreach ($label_list as $key => $val) {
                $attribute[$key]['id'] = $label_list_id[$key];
                $attribute[$key]['name'] = $val;
                $attribute[$key]['desc'] = $label_desc[$key];
            }
            //p($attribute);p(serialize($attribute));exit();
            $update['attribute'] = serialize($attribute);

            /*产品属性 end*/
            $info = $db_news->where($_POST['goods_id'])->find();
            // P($_POST);
            
            /* 封面图 如果有新的图片上传成功 则删除原来的图片 start */
            if($_FILES['file']['name']){
                $up = new FileUpload();
                $up->set('path','./public/uploads/news');
                $result_file = $up->upload('file');
                if($result_file){
                    if($info['news_pic'] != 'logo.png')
                        unlink('./public/uploads/news/'.$info['news_pic']);
                    $update['news_pic'] = $up->getFileName();
                }
            }
            /* 封面图 如果有新的图片上传成功 则删除原来的图片 end */

            /* 案例背景图 如果有新的图片上传成功 则删除原来的图片 start*/
            if($_FILES['case_img']['name']){
                $up = new FileUpload();
                $up->set('path','./public/uploads/news');
                $result_file = $up->upload('case_img');
                if($result_file){
                    if($info['case_img'] != 'logo.png')
                        unlink('./public/uploads/news/'.$info['case_img']);
                    $update['case_img'] = $up->getFileName();
                }
            }
            /* 案例背景图 如果有新的图片上传成功 则删除原来的图片 end*/            

            /* 产品轮播图 如果有新的轮播图上传成功 则删除原来的图片 start */
            //每次必须要new一个新商场对象，不然会按日期重新生成文件夹
            $j=6;
            $banner = unserialize(htmlspecialchars_decode($info['news_banner']));
            for($i=0;$i<$j;$i++){
                $up = new FileUpload();
                $up->set('path','./public/uploads/news');
                $result_files = $up->upload('banner'.$i);
                if($result_files){
                    unlink('./public/uploads/news/'.$banner[$i]);
                    $banner[$i] = $up->getFileName();
                }
            }
            if($banner){
                $update['news_banner'] = serialize($banner);
            }
            // p($banner);
            // p($update);
            // p($_FILES);
            // exit();
            /* 产品轮播图 如果有新的轮播图上传成功 则删除原来的图片 end */
            
            /* 整理需要更新的数据 start */
            $db_news_cls = D('news_cls');
            $data_news_cls = $db_news_cls->where($_POST['goods_cls'])->find();
            $data_news_cls['news_cls_id'] = $data_news_cls['news_cls_id'] ? $data_news_cls['news_cls_id'] : "1";
            $data_news_cls['news_cls_name'] = $data_news_cls['news_cls_name'] ? $data_news_cls['news_cls_name'] : "默认分类";

                $update['news_name'] = $_POST['goods_name'];
                $update['news_pname'] = $_POST['goods_pname'];
                $update['news_cls_id'] = $data_news_cls['news_cls_id'];
                $update['news_cls_name'] = $data_news_cls['news_cls_name'];
                $update['news_body'] = $_POST['content'];
                $update['news_key'] = $_POST['goods_key'];
                $update['news_imgalt'] = $_POST['goods_imgalt'];
                $update['label'] = $_POST['label'];
                $update['sort'] = $_POST['sort'];
                $update['case_url'] = $_POST['case_url'];
                // $update['source'] = $_POST['goods_source'];

            /* 整理需要更新的数据 end */
            $result = $db_news->where($_POST['goods_id'])->update($update);

            if($result){
                // echo '修改成功';
                //百度主动推送
                if(C('is_push') == 'on'){
                    $result = tuisong_baidu(array(SHOP_SITE_URL.'/whshow?id='.$_POST['goods_id']));
                    if(!is_dir(PROJECT_PATH.'log'))
                        mkdir(PROJECT_PATH.'log', 0775);
                    @file_put_contents(PROJECT_PATH.'log'.DS.'tuisong_baidu_log.txt',$result.PHP_EOL,FILE_APPEND);
                }
                ajaxReturn(array('control'=>'add','code'=>200,'msg'=>'修改成功','url'=>SHOP_SITE_URL.'/admin.php/news/news_list'),"JSON");
            }else{
                ajaxReturn(array('control'=>'add','code'=>200,'msg'=>'无任何修改','url'=>SHOP_SITE_URL.'/admin.php/news/news_list'),"JSON");
            }

        }else{
            $data_news = $db_news->where($_GET['news_id'])->find();
            if($data_news){
                //取所有分类
                //$cls_array = $this->get_cls_list();
                $cls_array = getChildArr();
                $this->assign("cls_array",$cls_array);
                $this->assign("data_news",$data_news);

                //获取产品所有属性
                $db_attribute = D('attribute');
                $list_attr = $db_attribute->select();
                $this->assign('list_attr',$list_attr);

                //轮播图
                $banner = unserialize(htmlspecialchars_decode($data_news['news_banner']));
                //p($banner);
                $this->assign("banner",$banner);
                //产品属性
                if(!empty($data_news['attribute']))
                    $attribute = unserialize(htmlspecialchars_decode($data_news['attribute']));
                else
                    $attribute = array();
                if(!empty($attribute)){
                    $label_list = implode('|', array_column($attribute,'name'));
                    $label_list_id = implode('|', array_column($attribute,'id'));
                    $this->assign('label_list',$label_list);
                    $this->assign('label_list_id',$label_list_id);
                }
                $this->assign('attribute',$attribute);
                $this->display();

            }else{
//                echo '没有此资讯';
                $this->error("没有此资讯");
            }
        }



    }

    // 资讯列表
    function news_list(){
        $cls_array = getChildArr();
        $this->assign("cls_array",$cls_array);
        $this->display();
    }
    // 添加分类
    function cls_add(){
        $db_news_cls = D('news_cls');

        if($_POST['sub']){
            /*if($_POST['level'] == 1){
                $this->error("目前不能增加顶级分类",2);
                die();
            }*/
            $upload = new FileUpload();
            //小程序栏目类别封面图(存在图片才添加) start
            if ($_FILES['apiimg_show']['error'] == '0') {
                $upload->set('path','./uploads/news');
                $result_apiupload = $upload->upload('apiimg_show');

                if($result_apiupload){
                    $insert['news_cls_icon'] = $upload->getFileName();
                }
            }
            //小程序栏目类别封面图(存在图片才添加) end
            
            if(empty($_POST['news_cls_name'])){
                $this->error("栏目为空或重复",2);
            }
            
            //封面图片 start
            $upload->set('path','./uploads/news');
            $result_upload = $upload->upload('img_show');

            if($result_upload){
                $insert['news_cls_pic'] = $upload->getFileName();
            }
            //封面图片 end
            
            $insert['news_cls_name'] = $_POST['news_cls_name'];
            $insert['news_cls_desc'] = $_POST['news_cls_desc'];
            $insert['cls_pid'] = $_POST['cls_pid'];
            $insert['level'] = $_POST['level'] ? $_POST['level']:2;
            $insert['url'] = $_POST['cls_url'];
            $insert['english_name'] = $_POST['cls_englishname']; //栏目英文名
            $result = $db_news_cls->insert($insert);

            if($result){
                $this->success('新增成功',2,"news/cls_list",'self');
            }else{
                $this->error("数据不完善",2);
            }

        }else{
            if(isset($_GET['pid'])){
                $this->assign('pid',$_GET['pid']);
                $where['news_cls_id'] = $_GET['pid'];
            }else{
                $where['level'] = 1;
            }
            $data_news_cls = $db_news_cls->where($where)->select();
            $this->assign('data_news_cls',$data_news_cls);
            $this->display();
        }

    }
    // 修改分类
    function edit_cls(){
        $db_news_cls = D('news_cls');

        /* 如果有新的图片上传成功 则删除原来的图片 start */
        $info = $db_news_cls->where(['news_cls_id'=>$_POST['cls_id']])->find();
        $up = new FileUpload();
        $up->set('path','./uploads/news');
        $result_file = $up->upload('news_cls_pic');
//         P($result_file);p($_FILES);die;
        if($result_file){
            if($info['news_cls_pic'] != 'logo.png')
                unlink('./uploads/news/'.$info['news_cls_pic']);
            $update['news_cls_pic'] = $up->getFileName();
        }

        //小程序图片 有则删除 start
        if ($_FILES['news_cls_icon']['error'] == '0') {
            $up->set('path','./uploads/news');
            $result_apifile = $up->upload('news_cls_icon');
            if($result_apifile){
                if($info['news_cls_icon'] != 'logo.png')
                    unlink('./uploads/news/'.$info['news_cls_icon']);
                $update['news_cls_icon'] = $up->getFileName();
            }
        }
        //小程序图片 有则删除 end
        
        /* 如果有新的图片上传成功 则删除原来的图片 end */

        /* 如果是顶级则不能被修改 start */
        // if($info['level'] != 1){
            //$update['news_cls_name'] = $_POST['cls_name'];
        // }
        /* 如果是顶级则不能被修改 end */
        $update['news_cls_name'] = $_POST['cls_name'];
        $update['news_cls_desc'] = $_POST['cls_desc'];
        $update['url'] = $_POST['cls_url'];
        $update['english_name'] = $_POST['cls_englishname']; //栏目英文名
        $result = $db_news_cls->where($_POST['cls_id'])->update($update);
        if($result){
            $this->success('修改成功',2,"news/cls_list");
            /*ajaxReturn(array('control'=>'edit_cls','code'=>200,'msg'=>'成功'),"JSON");*/
        }else{
            $this->error('无任何修改',2,"news/cls_list");
            /*ajaxReturn(array('control'=>'edit_cls','code'=>0,'msg'=>'失败','data'=>$_GET),"JSON");*/
        }
    }
    // 删除 咨询
    function del(){
        $db_news = D('news');
        $info = $db_news->where($_GET['news_id'])->find();
        $result = $db_news->where($_GET['news_id'])->delete();
        if($result){
            if($info['news_pic'] != 'logo.png') { //如果是默认图片则不删除
                unlink('./public/uploads/news/'.$info['news_pic']);
            }
            $get_img_src = getImgs(htmlspecialchars_decode($info['news_body']),$order='ALL');//查找出$info['news_body']里的所有图片
            if (!empty($get_img_src)) { //不为空时循环删除$info['news_body']里的所有图片
                for ($i=0; $i < count($get_img_src); $i++) { 
                    unlink("./".strstr($get_img_src[$i],'ueditor/php'));//截取ueditor/php之后的全部
                }
            }
            $this->success("已删除",2,"news/news_list");
        }else{
            $this->error("未删除",2,"news/news_list");
        }
    }

    //批量删除数据 start 
    function del_piliang_news(){
        if ($_POST['isdel'] == "del") {
            $db_news = D('news');
            $id_arr = $_POST['id_arr'];
            $del_num = 0;
            foreach ($id_arr as $key => $value) {
                $info = $db_news->where(['news_id'=>$value])->field('news_id,news_pic,news_body')->find();
                $row = $db_news->where(['news_id'=>$value])->delete();
                if($row > 0){ //删除这一条数据成功
                    $del_success_num += 1; 
                    //查找出$info['news_body']里的所有图片 start
                    $get_img_src = getImgs(htmlspecialchars_decode($info['news_body']),$order='ALL'); //该方法getImgs()在公共函数
                    //查找出$info['news_body']里的所有图片 end
                    if (!empty($get_img_src)) { //不为空时循环删除$info['news_body']里的所有图片
                        for ($i=0; $i < count($get_img_src); $i++) { 
                            unlink("./".strstr($get_img_src[$i],'ueditor/php'));//截取ueditor/php之后的全部
                        }
                    }
                    
                    if($info['news_pic'] != 'logo.png') { //如果是默认图片则不删除
                        unlink('./public/uploads/news/'.$info['news_pic']);
                    }
                    
                }else{ //删除这一条数据失败
                    $fail_news_id .= ','.$value;
                    $fail_news_id = ltrim($fail_news_id);//去除左边逗号（,）
                    
                }
            }
            if (count($id_arr) == $del_success_num) {
                ajaxReturn(array('control'=>'del_piliang_news','code'=>200,'msg'=>'批量删除成功'),"JSON");
            } else {
                ajaxReturn(array('control'=>'del_piliang_news','code'=>0,'msg'=>'批量删除失败!文章ID='.$fail_news_id.'未被删除'),"JSON");
            }
        }
    }
    //批量删除数据 end
    
    // 排序
    function sort(){
        $db_news_cls = D('news_cls');
        $update['sort'] = $_GET['sort_id'];
        $result = $db_news_cls->where($_GET['cls_id'])->update($update);
        if($result){
            ajaxReturn(array('control'=>'sort','code'=>200,'msg'=>'成功'),"JSON");
        }else{
            ajaxReturn(array('control'=>'sort','code'=>0,'msg'=>'失败','data'=>$_GET),"JSON");
        }
    }
    // ajax 请求获取资讯列表
    function get_news_list(){
        $db_news = D('news');
        /* 查询条件 start */
        $where = array("news_name like"=>"%{$_GET['search']['value']}%");
        /* 查询条件 end */
        /* 排序条件 start */
        // $column = array("news_id","member_name","city","add_time","member_phone","last_login_ip","is_teacher","integral");
        $column = array_column($_GET['columns'], 'data');
        $search = array_column($_GET['columns'], 'search');
        $column_key = intval($_GET['order'][0]['column']);
        $choose_column = $column[$column_key];
        $choose_sort = $_GET['order'][0]['dir'];
        $order = $choose_column." ".$choose_sort;
//        $order = "";
//        p($order);
        /* 排序条件 end */

        $db_draw = intval($_GET['draw']);
        $db_start = $_GET['start'] ? $_GET['start'] : 0;
        $db_length = $_GET['length'] ? $_GET['length'] : 10;
        $db_limit = $db_start.",".$db_length;

        /* 每一列对应关键字的搜索 start */
        // foreach ($search as $key => $val) {
        //     if(!empty($val['value']))
        //         $where[$column[$key]] = array('eq',$val['value']);
        // }
        /* 每一列对应关键字的搜索 end */

        //分类id
        if(!empty($search[3]['value'])){
            $cls_data = getChildArr($search[3]['value'],$search[3]['value']);
            $where['news_cls_id'] = array_column($cls_data,'news_cls_id');
        }

        $db_total = $db_news->where($where)->total();
        $data_news = $db_news->where($where)->limit($db_limit)->order($order)->select();

        ajaxReturn(array('draw'=>$db_draw,'recordsTotal'=>$db_total,'recordsFiltered'=>$db_total,'data'=>$data_news),"json");

        ajaxReturn($data_news,"JSON");
    }
    // 获取 分类列表ajax 从小到大 排序
    private function get_cls_list(){
        $db_cls = D('news_cls');
        $data_first = $db_cls
            ->where(array('level'=>1))
            ->order('sort asc')
            ->select();
        $data = array();
        for($i=0;$i<count($data_first);$i++){
            array_push($data,$data_first[$i]);
            $data_second = $db_cls->where(array('level'=>2,'cls_pid'=>$data_first[$i]['news_cls_id']))->order('sort asc')->select();
            if($data_second){
                for($j=0;$j<count($data_second);$j++){
                    array_push($data,$data_second[$j]);
                }
            }
        }
//        P($data);
        return $data;
    }
    //添加属性
    function attr_add(){
        $db_attribute = D('attribute');
        if(empty($_POST['name']))
            ajaxReturn(array('code'=>0,'msg'=>'属性名不能为空'),"JSON");
        $insert['name'] = $_POST['name'];
        $result = $db_attribute->insert($insert);
        if($result)
            ajaxReturn(array('code'=>200,'msg'=>'添加成功','result'=>$result),"JSON");
        else
            ajaxReturn(array('code'=>0,'msg'=>'添加失败'),"JSON");
    }
    /* 获取标签  标签位置 public/tag.txt start */
    function get_tag(){
        $tag_path = $GLOBALS['public'].'tag.txt';
        $tag_path = $_SERVER['DOCUMENT_ROOT'].$tag_path;
        if(is_file($tag_path)){
            $tag_str = file_get_contents($tag_path);
            $tag_array = explode("\r\n", $tag_str);
            ajaxReturn(array('control'=>'get_tag','code'=>200,'msg'=>'成功','data'=>$tag_array),"JSON");
        }else{
            ajaxReturn(array('control'=>'get_tag','code'=>0,'msg'=>'无任何标签'),"JSON");
        }

    }
    /* 获取标签  标签位置 public/tag.txt end */

    /* 获取分类列表 start */
    function ajax_cls_list(){
        $cls_list = get_cls_html();
        ajaxReturn(array('control'=>'ajax_cls_list','code'=>200,'msg'=>'成功','data'=>$cls_list),"JSON");
    }
    /* 获取分类列表 end */

    function ajax_mod_news(){
        if(isset($_POST['id_arr']) && !empty($_POST['id_arr'])){
            $db = D('news');
            $num = count($_POST['id_arr']);
            if($num <= 0){
                ajaxReturn(array('control'=>'ajax_mod_news','code'=>0,'msg'=>'非法请求'),"JSON");
                die();
            }
            $tag_str = implode("|",$_POST['choose_tag']);
            $where['news_id'] = $_POST['id_arr'];
            $db_cls = D('news_cls');
            $data_cls = $db_cls->where(array('news_cls_id'=>$_POST['cls_id']))->find();
            if(!$data_cls){
                ajaxReturn(array('control'=>'ajax_mod_news','code'=>0,'msg'=>'非法请求'),"JSON");
                die();
            }
            $update['news_cls_id'] = $data_cls['news_cls_id'];
            $update['news_cls_name'] = $data_cls['news_cls_name'];
            $update['tag'] = $tag_str;
            $result = $db->where($where)->update($update);
            if($result){
                ajaxReturn(array('control'=>'ajax_mod_news','code'=>200,'msg'=>'修改成功'),"JSON");
            }else{
                ajaxReturn(array('control'=>'ajax_mod_news','code'=>0,'msg'=>'修改失败'),"JSON");
            }
        }else{
            ajaxReturn(array('control'=>'ajax_mod_news','code'=>0,'msg'=>'非法请求'),"JSON");
        }

    }

    //查找当前分类的父级
    function findPid(){
        $cid = $_POST['cid'];
        if(empty($cid))
            ajaxReturn(array('control'=>'findPid','code'=>0,'msg'=>'参数错误'),"JSON");
        $db_news_cls = D('news_cls');
        $info = $db_news_cls->where($cid)->find();
        if($info['level'] != 1){
            $info = $db_news_cls->where($info['cls_pid'])->find();
        }
        if(!empty($info))
            ajaxReturn(array('control'=>'findPid','code'=>200,'msg'=>'父级分类','info'=>$info),"JSON");
        else
            ajaxReturn(array('control'=>'findPid','code'=>0,'msg'=>'分类不存在'),"JSON");

    }

    function test(){
        $cls_list = get_cls_html(1,2);
        print_r($cls_list);
        die();
//        p($cls_list);
        $json_data = array_merge($_POST,$_FILES);
        ajaxReturn($json_data,"JSON");
    }

    //删除产品轮播图
    function del_product_img(){
        $img = $_POST['img'];
        $news_id = $_POST['goods_id'];
        if(empty($img) || empty($news_id))
            ajaxReturn(array('code'=>0,'msg'=>'参数错误'));

        $db_news = D('news');
        $info = $db_news->where(array('news_id'=>$news_id))->find();
        if(empty($info))
            ajaxReturn(array('code'=>0,'msg'=>'资讯不存在'));
        $news_banner = unserialize(htmlspecialchars_decode($info['news_banner']));

        unlink('./public/uploads/news/'.$img);
        $update = array();
        $update['news_banner'] = serialize(array_merge(array_diff($news_banner, array($img))));
        $result = $db_news->where(array('news_id'=>$news_id))->update($update);
        if($result)
            ajaxReturn(array('code'=>200,'msg'=>'删除成功'));
        else
            ajaxReturn(array('code'=>0,'msg'=>'删除失败'));
    }

}