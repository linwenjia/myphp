<?php
    //全局可以使用的通用函数声明在这个文件中.
function is_mobile_request()
{
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
    $mobile_browser = '0';
    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser++;
    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser++;
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda','xda-'
    );
    if(in_array($mobile_ua, $mobile_agents))
        $mobile_browser++;
    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser++;
    // Pre-final check to reset everything if the user is on Windows
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser=0;
    // But WP7 is also Windows, with a slightly different characteristic
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser++;
    if($mobile_browser>0)
        return true;
    else
        return false;
}

/*  通过cls_id  来查询自己的下级cls_id  列表 默认是产品ID 21 start */
function getChildArr($pid=0,$init_cid=false,$table='news_cls',$data_cls=array(),$level=0){
    $db_cls = D($table);
    $rows = $db_cls
        ->field('news_cls_id,news_cls_name,cls_pid,level,news_cls_pic,type')
        ->where(array("cls_pid"=>$pid))
        ->select(); //21 产品
    //返回结果集
    if($init_cid){
        $data_init = $db_cls->where(array('news_cls_id'=>$init_cid))->find();
        array_unshift($data_cls,$data_init);
    }
    //p($rows);return false;exit();
    //判断程序执行的条件
    if(!empty($rows) && $level<20){
        //递归调用，得到下一级的节点集
        foreach($rows as $key=>$value){
            $data_cls[]=$value;
            $pid=$value['news_cls_id'];
            $next_level=$level+1;
            $data_cls=getChildArr($pid,$init_cid=false,$table,$data_cls,$next_level);
        }
    }
    return $data_cls;
}
/*  通过cls_id  来查询自己的下级cls_id  列表 默认是产品ID 21 start */

/* 百度主动推送 start*/
function tuisong_baidu($urls = array()){
    // $urls = array(
    //     'http://www.example.com/1.html',
    //     'http://www.example.com/2.html',
    // );
    // $api = 'http://data.zz.baidu.com/urls?site=https://www.shikexu.com&token=oLeKZk0QV85zoxXF';
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => 'http://data.zz.baidu.com/urls?site='.$_SERVER['HTTP_HOST'].'&token='.C('baidu_token'),
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    // p($options);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    return $result;
}
/* 百度主动推送 end*/

/* 根据内容ID获取上一篇 和下一篇 start */
function get_pre_next($id=1,$class='wh_pre_next'){
    $str = '';
    $str .= '<div class="'.$class.'">';
    $db = D('news');
    $info = $db->where($id)->find();
    $cls_info = D('news_cls')->where($info['news_cls_id'])->find();
    $cls_data = getChild($cls_info['cls_pid']);
    $data_pre = $db->where(array('news_id <'=>$id,'news_cls_id'=>$cls_data))->order("news_id desc")->find();
    $data_next = $db->where(array('news_id >'=>$id,'news_cls_id'=>$cls_data))->order("news_id asc")->find();

    if($data_pre){
        $pre_id = $data_pre['news_id'];
        $pre_desc = $data_pre['news_name'];
        $str .= '<a href="'.SHOP_SITE_URL.'/whshow?id='.$pre_id.'"><span>上一篇:</span>'.$pre_desc.'</a>';
    }else{
        $str .='<a href="javascript:;"><span>上一篇:</span>没有上一篇</a>';
    }

    if($data_next){
        $next_id = $data_next['news_id'];
        $next_desc = $data_next['news_name'];
        $str .= '<a href="'.SHOP_SITE_URL.'/whshow?id='.$next_id.'"><span>下一篇:</span>'.$next_desc.'</a>';
    }else{
        $str .='<a href="javascript:;"><span>下一篇:</span>没有下一篇</a>';
    }

    $str .='</div>';
    return $str;
}
/* 根据内容ID获取上一篇 和下一篇 end */

/* 根据内容ID获取上一篇 和下一篇 数据start */
function get_pre_next_data($id=1,$class='wh_pre_next'){
    $db_news = D('news');
    $info = $db_news->where($id)->find();
    $cls_info = D('news_cls')->where($info['news_cls_id'])->find();
    $cls_data = getChild($cls_info['cls_pid']);
    $data_pre = $db_news->where(array('news_id <'=>$id,'news_cls_id'=>$cls_data))->order("news_id desc")->find();
    $data_next = $db_news->where(array('news_id >'=>$id,'news_cls_id'=>$cls_data))->order("news_id asc")->find();
    return array('pre'=>$data_pre,'next'=>$data_next);
}
/* 根据内容ID获取上一篇 和下一篇 数据 end */

/* 获取详情页的定位 面包屑 start */
function whshow_position($id=1,$class='whshow_position'){
    $db_news = D('news');
    $data_news = $db_news->where(array('news_id'=>$id))->find();
    if(!$data_news){
     $data_news = $db_news->find();
    }
    $str = "";
    $str .="<div class='{$class}'>";
    $str .="<a href='".SHOP_SITE_URL."'>首页</a>";
    $str .="<a href='".SHOP_SITE_URL."/whlist?cid={$data_news['news_cls_id']}'>".$data_news['news_cls_name']."</a>";
    $str .="<a href='".SHOP_SITE_URL."/whshow?cid={$data_news['news_id']}'>".$data_news['news_name']."</a>";
    $str .="</div>";

    return $str;
}
/* 获取详情页的定位 面包屑 end */

/* 无限极面包屑 start */
            /*参数$id         可以是列表项的cid值和详情表的id值
             *参数$iscid      当传进的$id值是列表项的cid时 该参数设为true   例如 show_crumbs_new(21,true,false')
             *参数$news_names  该参数设为true时 最后一级也会展示
             *参数$class      为前端控制样式用
             *
             * 默认（参数不填）是使用详情页的面包屑即传详情表的id值
            */
function show_crumbs_new($id=1,$iscid=false,$news_names=false,$class='whshow_position'){
    if (is_numeric($id)) {
        $db_news = D('news');
        $db_news_cls = D('news_cls');
        $db_column = D('column');
        if ($iscid) {
            $data_crumbs_news = $db_news_cls->where(['news_cls_id'=>$id])
                                            ->field('news_cls_id,news_cls_name,cls_pid')
                                            ->find();
            $news_cls_names = 'yes'; //下面判断用
        } else {
            $data_crumbs_news = $db_news->where(['news_id'=>$id])
                                        ->field('news_id,news_name,news_cls_id,news_cls_name,news_pic,add_times')
                                        ->find();
            if(!$data_crumbs_news){
                $data_crumbs_news = $db_news->find();
            }
            $news_cls_names = 'no'; //下面判断用
        }
        
        if (empty($data_crumbs_news)) {
            return false;
            echo '<script>alert("该参数没有相应的面包屑！");</script>';
            exit;
        }

        $str = "";
        $str .="<div class='{$class}'>";
        $str .="<a href='".SHOP_SITE_URL."'>首页</a> > ";

        $i = 1;
        $data_crumbs_cls[$i] = $db_news_cls->where(['news_cls_id'=>$data_crumbs_news['news_cls_id']])
                                       ->field('news_cls_id,news_cls_name,cls_pid')
                                       ->find();
      
        end:
        if ($data_crumbs_cls[$i]['cls_pid'] !== '0') {
            $ii = $i+1;
            $data_crumbs_cls[$ii] = $db_news_cls->where(['news_cls_id'=>$data_crumbs_cls[$i]['cls_pid']])
                                       ->field('news_cls_id,news_cls_name,cls_pid')
                                       ->find();

            $i = $ii;
            goto end;
            
        }

        for ($j=$i; $j > 1; $j--) { 
            $column_url_trmp = $db_column->where(['cls_id'=>$data_crumbs_cls[$j]['news_cls_id']])
                                         ->field('id,cls_id,url')
                                         ->find();
            if (empty($column_url_trmp)) {
                $url = SHOP_SITE_URL."/whlist?cid={$data_crumbs_cls[$j]['news_cls_id']}";
            } else {
                $url = $column_url_trmp['url'];
            }
            $str .="<a href='".$url."'>".$data_crumbs_cls[$j]['news_cls_name']."</a> > ";
        }

        //倒数第二级 start
        $column_url_trmp1 = $db_column->where(['cls_id'=>$data_crumbs_cls[1]['news_cls_id']])
                                     ->field('id,cls_id,url')
                                     ->find();
        if (empty($column_url_trmp1)) {
            $url1 = SHOP_SITE_URL."/whlist?cid={$data_crumbs_cls[1]['news_cls_id']}";
        } else {
            $url1 = $column_url_trmp1['url'];
        }
        $str .="<a href='".$url1."'>".$data_crumbs_cls[1]['news_cls_name']."</a> ";
        //倒数第二级 end

        if ($news_names) {
            if ($news_cls_names === 'yes') {
                $column_url_trmp2 = $db_column->where(['cls_id'=>$data_crumbs_news['news_cls_id']])
                                             ->field('id,cls_id,url')
                                             ->find();
                if (empty($column_url_trmp2)) {
                    $url2 = SHOP_SITE_URL."/whlist?cid={$data_crumbs_cls['news_cls_id']}";
                } else {
                    $url2 = $column_url_trmp2['url'];
                }
                $str .=" > <a href='".$url2."'>".$data_crumbs_news['news_cls_name']."</a> ";
            } else {
                $str .=" > <a href='".SHOP_SITE_URL."/whshow?id={$data_crumbs_news['news_id']}'>".$data_crumbs_news['news_name']."</a>";
            }
        }
        
        $str .="</div>";

        return $str;
    }
    
}
/* 无限极面包屑 end */

// 根据经纬度获取 之间的距离 start
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}
// 根据经纬度获取 之间的距离 end


/* 获取无限级分类方法 start */
function get_cls_html($cls_id=0,$type=1,$level=1,&$data=array()){
    $db = D('news_cls');
    // type 1 是 HTML 数据  2 是原始数据
    if($level == 1){
        //原始初级
        $data = $db->where(array('level'=>$level))->order("sort asc")->select();
        //html 数据
        $data_html = '<select name="test" class="form-control">';

        if($data){
            foreach($data as $k=>$v){
                if($v['news_cls_id'] == $cls_id){
//                    $data_html .= "<option selected value='".$v['news_cls_id']."'>{$v['level']}级".str_repeat(' ',$v['level']).$v['news_cls_name']."</option>";
                    $data_html .= "<option selected value='".$v['news_cls_id']."'>".str_repeat(' ',$v['level']).$v['news_cls_name']."</option>";
                }else{
                    $data_html .= "<option value='".$v['news_cls_id']."'>".str_repeat(' ',$v['level']).$v['news_cls_name']."</option>";
                }
                $temp_data = $db->where(array('cls_pid'=>$v['news_cls_id']))->order("sort asc")->select();
                if($temp_data){
                    $son_level = $temp_data[0]['level'];
                    if($type == 1){
                        $data_html .= get_cls_html($cls_id,$type,$son_level,$temp_data);
                    }else{
                        $data[$k]['son'] = get_cls_html($cls_id,$type,$son_level,$temp_data);

                    }
                }
            }
            $data_html .= '</select>';
            if($type == 1){
                return $data_html;

            }else{
                return $data;
            }

        }
    }else{
        $data = $data;
        $data_html = '';
        foreach($data as $k=>$v){
            if($v['news_cls_id'] == $cls_id){
                $data_html .= "<option selected value='".$v['news_cls_id']."'>".str_repeat(' |-',$v['level']).$v['news_cls_name']."</option>";
            }else{
                $data_html .= "<option value='".$v['news_cls_id']."'>".str_repeat(' |-',$v['level']).$v['news_cls_name']."</option>";
            }
            $temp_data = $db->where(array('cls_pid'=>$v['news_cls_id']))->order("sort asc")->select();
            if($temp_data){
                $son_level = $temp_data[0]['level'];
                if($type == 1){
                    $data_html .= get_cls_html($cls_id,$type,$son_level,$temp_data);
                }else{
                    $data[$k]['son'] = get_cls_html($cls_id,$type,$son_level,$temp_data);

                }
            }
        }
        if($type == 1){
            return $data_html;

        }else{
            return $data;
        }
    }

}
/* 获取无限级分类方法 end */

/*遍历一个目录下的所有文件 start*/
function my_scandir($dir) {  
    if(!is_dir($dir)) {  
        return false;  
    }  
      
    $files = array();  
    $handle = opendir($dir);  
    while(false !== ($filename = readdir($handle))) {  
        if($filename == '.' || $filename == '..') {  
            continue;  
        }  
        $file = $dir . '/' . $filename;  
        if(is_dir($file)) {  
            $files = array_merge($files, my_scandir($file));  
        } else {  
            $files[] = $filename;  
        }  
    }  
    closedir($handle);  
    return $files;  
}  
/*遍历一个目录下的所有文件 end*/

/*获取内容中的所有图片 start*/
function getImgs($content,$order='ALL'){
    $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
    preg_match_all($pattern,$content,$match);
    if(isset($match[1])&&!empty($match[1])){
        if($order==='ALL'){
            return $match[1];
        }
        if(is_numeric($order)&&isset($match[1][$order])){
            return $match[1][$order];
        }
    }
    return '';
}
/*获取内容中的所有图片 end*/

/**
 * 获取导航栏
 * @return array $data_column 
 */
function get_default_column(){
    $db_column = D('column');
    $data_column = $db_column
        ->where(array('level'=>1))
        ->order("sort asc")
        ->select();
    for($j=0;$j<count($data_column);$j++){
        $data_second_column = $db_column
            ->where(array('cls_pid'=>$data_column[$j]['cls_id'],'type'=>$data_column[$j]['type'],'level'=>2))
            ->order('sort asc')
            ->select();
        $data_column[$j]['son_data'] = $data_second_column;
    }
    $index_page['id'] = '1';
    $index_page['cls_name'] = '首页';
    $index_page['cls_id'] = '0';
    $index_page['type'] = '1';
    $index_page['sort'] = '0';
    $index_page['level'] = '1';
    $index_page['url'] = SHOP_SITE_URL.B_ROOT;
    $index_page['cls_pid'] = 0;
    $index_page['english_name'] = 'index';
    $index_page['son_data'] = array();
    array_unshift($data_column,$index_page);
   // P($data_column);
    return $data_column;
}

/**
 * 确保多个进程同时写入同一个文件成功
 * @param  [string] $filepath   [文件路径]
 * @param  [mixed]   $data       [写入的内容]
 * @return [bool]               [true成功 false失败]
 */
function writeData($filepath,$data) 
{ 
    $fp = fopen($filepath,'a');  
    do{ 
        usleep(100); 
    }while (!flock($fp, LOCK_EX));  //LOCK_EX 取得独占锁定（写入的程序）进行排它型锁定 获取锁　有锁就写入，没锁就得
    $res = fwrite($fp, $data."\n"); 
    flock($fp, LOCK_UN);    //LOCK_UN 释放锁定（无论共享或独占）。
    fclose($fp);  
    return $res; 
} 

/**
 * [获取图片，根据广告位和栏目页面获取轮播图]
 * @param  integer  $position   [显示位置 1.pc 2.手机]
 * @param  integer  $cls_id     [显示页面 对应栏目的分类id 0首页]
 * @param  integer  $num        [图片数量]
 * @param  integer  $default    [默认图片]
 * @return [array]              [轮播图片]
 */
function get_banner($position=1,$cls_id=0,$num=3,$default='pcB_background1.jpg'){
    $db_home_adv = D('home_adv');
    //手机
    if(is_mobile_request()){
        $position = 2;
        //小程序
        if(!empty($_GET['get_smarty_json']))
            $position = 3;
    }
    /*num=1，查对应栏目的广告图 start*/
    if($num == 1){
        /*如果是详情页 start*/
        if(!empty($_GET['id'])){
            $info = D('news')->where($_GET['id'])->find();
            $cls_id = $info['news_cls_id'];
        }
        /*如果是详情页 end*/

        /*如果是列表页 start*/
        if(!empty($_GET['cid']))
            $cls_id = $_GET['cid'];
        /*如果是列表页 end*/
        $column_info = D('column')->where(array('cls_id'=>$cls_id))->find();
        $cls_id = $column_info['level']==1?$column_info['cls_id']:$column_info['cls_pid'];
        
        /*如果是搜索页 start*/
        if(isset($_GET['keyword']))
            $cls_id = 9999;
        /*如果是搜索页 end*/

        $list = get_adv($position,$num,false,$cls_id);
        if(empty($list))
            $list = $GLOBALS['res']."img/".$default;
    }else{
        $list = get_adv($position,$num,false,$cls_id);
    }
    /*num=1，查对应栏目的广告图 end*/

    // p($list);
    return $list;
}

/**
 * [get_adv 根据广告位和栏目页面获取广告图]
 * @param  integer $position [广告位id]
 * @param  integer $num      [数量]
 * @param  boolean $rand     [是否随机]
 * @param  integer $cls_id   [栏目id]
 * @return [type]            [单张图片  随机图片   多张图片]
 */
function get_adv($position=1,$num=1,$rand=false,$cls_id=''){
    $db_home_adv = D('home_adv');
    $where = array();
    $where['position'] = $position;
    if($cls_id !== '')
        $where['cls_id'] = $cls_id;
    $order = '';
    if($rand)
        $order = 'rand()';
    $list = $db_home_adv->where($where)->order($order)->limit($num)->select();
    foreach ($list as $key => &$val) {
        if($val['type'] == 1){
            $val['pic_url'] = $val['img_url'];
        }else{
            $val['pic_url'] = $GLOBALS["public"]."uploads/home_adv"."/".$val['img_path'];
        }
    }
    if($num == 1){
        return $list[0];
    }else{
        return $list;
    }
}

/**
 * [get_adv_byid 根据广告id获取广告图]
 * @param  [type] $adv_id [广告图id]
 * @return [type]         [广告图信息]
 */
function get_img_by_id($adv_id){
    $db_home_adv = D('home_adv');
    $info = $db_home_adv->where($adv_id)->find();
    return $info;
}

//传入要获取的相应的个数 默认是10个
function get_recommend_info($num=10,$cls_id=21){
    $db_site = D("site_setting");
    $data_site = $db_site->where(array("site_key"=>"home_10_recommend"))->find();
    $list_array = explode("|",$data_site['site_value']);
    $db_news = D("news");
    $data_news = $db_news->where(array("news_id"=>$list_array))->select();
    $data_cls = getChild($cls_id);
    // p($data_cls);p($list_array);
    end:
    if($data_news){
        for($i=0;$i<$num;$i++){
            // if(!$data_news[$i+1]){
            //     $data_news[$i+1] = $data_news[$i];
            // }
            if(!$data_news[$i]){
                $data_news[$i] = $data_news[$i-1];
            }
        }
        return $data_news;
    }else{
        $data_news = $db_news
                    ->where(array('news_cls_id'=>$data_cls))
                    ->limit($num)
                    ->order('news_id desc')
                    ->select();
        if($data_news){
            goto end;
        }else{
            return array();
        }
    }
}
/* 获取相应个数的内容推荐 end */

/**
 * [获取相应层级的分类数据]
 * @param  [array] $type   [输出数据形式 1.类别 2.类别+文章]
 * @param  [integer] $level  [类别层级]
 * @param  [integer] $cls_id [分类id]
 * @param  [integer] $limit [限制条数]
 * @return [array]         [1.类别 2.类别+文章]
 */
function get_cls_data($type=0,$level=0,$cls_id=21,$limit=6){
    $type = $type+1;
    $level = $level+1;
    // p($type);p($level);p($cls_id);
    if(!empty($_GET['id']))
        $news_id = $_GET['id'];
    $db_news_cls = D('news_cls');
    if($type == 1){
        if(!empty($_GET['cid']))
            $cls_id = $_GET['cid'];
        /*如果在详情页面调用，则要使用文章的cls_id start*/
        if(!empty($_GET['id'])){
            $db_news = D('news');
            $news_info = $db_news->where($_GET['id'])->find();
            
            $cls_id = $news_info['news_cls_id'];
        }
        /*如果在详情页面调用，则要使用文章的cls_id end*/

        /*info里面存的始终要是父级栏目的信息 start*/
        $info = $db_news_cls->where($cls_id)->find();
        if($info['level'] != 1)
            $info = $db_news_cls->where($info['cls_pid'])->find();
        /*info里面存的始终要是父级栏目的信息 end*/

        if(isset($_GET['keyword']))
            $info = array();
        $news_cls_data['cls_id'] = $info['news_cls_id'];
        $news_cls_data['cls_name'] = $info['news_cls_name']?$info['news_cls_name']:'搜索结果';
        $news_cls_data['english_name'] = $info['english_name']?$info['english_name']:'search';
        $condition = array();
        $condition['cls_pid'] = $info['news_cls_id']?$info['news_cls_id']:0;
        if(!empty($level) && !isset($_GET['keyword']))
            $condition['level'] = $level;
        $news_cls_data['data'] = $db_news_cls->where($condition)->order('sort')->select();
        return $news_cls_data;
    }else{
        $db_news = D('news');
        $condition = array();
        $condition['cls_pid'] = $cls_id;
        if(!empty($level))
            $condition['level'] = $level;
        $news_cls_data = $db_news_cls->where($condition)->order('sort')->select();
        foreach ($news_cls_data as $key => $val) {
            $news_data[$key]['cls_id'] = $val['news_cls_id'];
            $news_data[$key]['cls_name'] = $val['news_cls_name'];
            $data = $db_news->where(array('news_cls_id'=>$val['news_cls_id']))->order('sort,news_id desc')->limit($limit)->select();
            foreach ($data as $k => &$v) {
                // $v['news_body'] = str_replace('&nbsp;', '', strip_tags(htmlspecialchars_decode($v['news_body'])));
                $v['news_body'] = htmlspecialchars_decode($v['news_body']);
                $v['time'] = date('Y-m-d',$v['add_times']);
                $v['year'] = date('Y',$v['add_times']);
                $v['month'] = date('m',$v['add_times']);
                $v['day'] = date('d',$v['add_times']);
            }
            $news_data[$key]['data'] = $data;
        }
        return $news_data;
    }
    
}


/**
 * [获取文章数据]
 * @param  integer $type    [文章类型1.推荐文章 2.具体的某一篇文章 3.具体的某几篇文章 4.根据传递的cls_id获取的文章]
 * @param  integer $cls_id     [文章类别]
 * @param  integer $news_id [文章id]
 * @param  integer $limit   [限制条数]
 * @return [array]           [文章数据]
 */
function get_news_data($type=0,$cls_id=21,$news_id=1,$limit=10){
    $type = $type+1;
    $db_news = D('news');
    $news_data = array();
    switch ($type) {
        case 1:
            /* 详情页的推荐新闻 start */
            if(!empty($_GET['id'])){
                $info = $db_news->where($_GET['id'])->find();
                $condition = array();
                $condition['news_cls_id'] = getChild($info['news_cls_id']);
                $condition['news_id !='] = $info['news_id'];
                $news_data = $db_news->where($condition)->order('add_times desc')->limit($limit)->select();
            }else{
                $condition = array();
                $condition['news_cls_id'] = getChild($cls_id);
                $news_data = $db_news->where($condition)->order('add_times desc')->limit($limit)->select();
                // $news_data = get_recommend_info($limit,$cls_id);
            }
            foreach ($news_data as $key => &$val) {
                $val['day'] = date("d",$val['add_times']);
                $val['month'] = date("m",$val['add_times']);
                $val['year'] = date("Y",$val['add_times']);
            }
            /* 详情页的推荐新闻 end */
            break;
        case 2:
            if(!empty($_GET['id']))
                $news_id = $_GET['id'];
            $news_data = $db_news->where($news_id)->find();

            //判断详情页还是首页
            if(!empty($_GET['id'])){
                $news_data['news_body'] = htmlspecialchars_decode($news_data['news_body']);
                $news_data['add_times'] = date('Y-m-d',$news_data['add_times']);
                //获取文章父级信息
                getParentColum($news_data['news_cls_id']);
                $_GET['name'] = $news_data['news_name'];
                //上一页下一页
                $news_data['page'] = get_pre_next_data($news_id);
                //面包屑
                $news_data['show_crumbs_new'] = show_crumbs_new($news_id);
                //浏览量增加
                // $db_news->where($_GET['id'])->update(array('open_times'=>array('exp','open_times+1')));
                $db_news->where($_GET['id'])->update(array('open_times'=>$news_data['open_times']+1));
            }else{
                $news_data['news_body'] = str_replace('&nbsp;', '', strip_tags(htmlspecialchars_decode($news_data['news_body'])));
            }
            $news_data['attribute'] = unserialize(htmlspecialchars_decode($news_data['attribute']));
            $news_data['news_banner'] = unserialize(htmlspecialchars_decode($news_data['news_banner']));
            break;
        case 3:
            $news_ids = explode('|',$news_id);
            if(is_array($news_ids))
                $news_data = $db_news->where(array('news_id'=>$news_ids))->select();
            break;
        case 4:
            if(!empty($_GET['cid']))
                $cls_id = $_GET['cid'];
            $condition = array();
            if(!empty($cls_id))
                $condition['news_cls_id'] = getChild($cls_id);
            //传 cid  或者  keyword
            if(!empty($_GET['cid']) || isset($_GET['keyword'])){
                if(isset($_GET['keyword']))
                    $condition = array('news_name like'=>"%{$_GET['keyword']}%");
                $total = $db_news->where($condition)->total();//总数
                $mpage = new Page($total,$limit);
                $news = $db_news
                    ->where($condition)
                    ->limit($mpage->limit)
                    ->order('sort,add_times desc')
                    ->select();
                foreach ($news as &$val) {
                    $val['time'] = date('Y-m-d',$val['add_times']);
                    $val['time_month_day'] = date('Y-m',$val['add_times']);
                    $val['time_month'] = date('m',$val['add_times']);
                    $val['time_day'] = date('d',$val['add_times']);
                    $val['label'] = explode('|', $val['label']);
                } 
                // $news_data = array('data'=>$news,'fpage'=>$mpage->fpage(),'show_crumbs_new'=>show_crumbs_new($cls_id,true,false));
                $news_data = array('data'=>$news,'allpage'=>$mpage->pageNum,'next_page'=>$mpage->page,'fpage'=>$mpage->fpage_old(0,3,4,5,6,7,8),'show_crumbs_new'=>show_crumbs_new($cls_id,true,false));
                getParentColum($_GET['cid']);
            }else{
                $news_data = $db_news->where($condition)->order('sort,add_times desc')->limit($limit)->select();
                foreach ($news_data as &$val) {
                    $val['add_times'] = date("Y-m-d",$val['add_times']);
                    $val['label'] = explode('|', $val['label']);
                }   
            }

            break;
        default:
            $news_data = array();
            break;
    }
    return $news_data;
}

/**
 * [传入一个信息分类ID 获取这个分类的所有子类id和自己]
 * @param  integer $pid      [父id]
 * @param  array   $data_cls [结果集]
 * @param  integer $level    [递归层级]
 * @return [type]            [结果集]
 */
function getChild($pid=0,$data_cls=array(),$level=0){
    $db_cls = D('news_cls');
    $rows = $db_cls
            ->field('news_cls_id,news_cls_name,cls_pid')
            ->where(array("cls_pid"=>$pid))
            ->select(); //21 产品
    $data_cls[] = $pid;
    //p($rows);return false;exit();
    //判断程序执行的条件
    if(!empty($rows) && $level<20){
        //递归调用，得到下一级的节点集
        foreach($rows as $key=>$value){
            //$data_cls[]=$value;
            $pid=$value['news_cls_id'];
            $next_level=$level+1;
            $data_cls=getChild($pid,$data_cls,$next_level);
        }
    }
    //返回结果集
    return $data_cls;
}

/**
 * [手机端数据分页加载]
 * @param  integer $cid [分类id]
 * @return [array]       [文章列表和分页]
 */
function getListAjax($cid=0){
    $cid = !empty($_POST['cid'])?$_POST['cid']:$cid;
    $cls_arr = getChild($cid);
    $where['news_cls_id'] = $cls_arr;
    
    if(!empty($_POST['keyword']))
        $where['news_name like'] = "%{$_POST['keyword']}%";
    $pageSize = $_POST['pageSize']?$_POST['pageSize']:10;
    $page = $_GET['page']?$_GET['page']:1;
    $order = 'sort,add_times desc';

    $db_news = D('news');
    $total = $db_news->where($where)->total();//总数
    $mpage = new Page($total,$pageSize);
    $product = $db_news
        ->where($where)
        ->limit($mpage->limit)
        ->order($order)
        ->select();
    foreach ($product as $key => &$val) {
        $val['day'] = date("d",$val['add_times']);
        $val['new_month'] = date("m",$val['add_times']);
        $val['month'] = date("Y-m",$val['add_times']);
        $val['add_times'] = date("Y-m-d",$val['add_times']);
        $val['attribute'] = unserialize(htmlspecialchars_decode($val['attribute']));
    }
    // p($product);
    if(!empty($product))
        echo json_encode(array('code'=>200,'msg'=>'加载数据','list'=>$product,'url_pre'=>SHOP_SITE_URL.'/public/uploads/news/','url'=>SHOP_SITE_URL.'/whshow?id='));
    else
        echo json_encode(array('code'=>0,'msg'=>'暂无数据'));
}

/**
 * [获取当前导航栏目名和父级栏目id]
 * @param  integer $cid [分类id]
 * @return [array]       [导航栏目名和父级栏目id]
 */
function getParentColum($cid=0){
    $cid = $cid?$cid:0;
    $db_column = D('column');
    $info = $db_column->where(array('cls_id'=>$cid))->find();
    if(isset($_GET['keyword']))
        $info = array();
    $name = $info['cls_name']?$info['cls_name']:'搜索结果';
    if($info['level'] == 2){
        $info = $db_column->where(array('cls_id'=>$info['cls_pid']))->find();
    }
    $pid = $info['cls_id']?$info['cls_id']:0;
    $_GET['cloum_pid'] = $pid;
    $_GET['name'] = $name;

    /*获取分类的父级id start*/
    getParentCls($cid);
    /*获取分类的父级id end*/
    return array('name'=>$name,'pid'=>$pid);
}

/**
 * [获取当前分类的父级id]
 * @param  integer $cid [分类id]
 * @return [array]       [分类的父级id]
 */
function getParentCls($cid=0){
    $cid = $cid?$cid:0;
    $db_news_cls = D('news_cls');
    $info = $db_news_cls->where($cid)->find();
    if(isset($_GET['keyword']))
        $info = array();
    $name = $info['cls_name']?$info['cls_name']:'搜索结果';
    if($info['level'] == 2){
        $info = $db_news_cls->where($info['cls_pid'])->find();
    }
    $pid = $info['news_cls_id']?$info['news_cls_id']:0;
    $_GET['pid'] = $pid;
    return $pid;
}

/**
 * 取得随机数
 *
 * @param int $length 生成随机数的长度
 * @param int $numeric 是否只产生数字随机数 1是0否
 * @return string
 */
function random($length, $numeric = 0) {
    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

/**
 * [get_count 获取统计数据]
 * @param  [string] $count_data [统计参数]
 * @return [array]             [统计数据]
 */
function get_count($count_data){
    $count_arr = array();
    $arr1 = explode('|', $count_data);
    foreach ($arr1 as $val) {
        $arr2 = explode(',', $val);
        $count_arr[] = $arr2;
    }
    return $count_arr;
}

/*
 * 取配置里边的参数
 * @param varchar $key key
 * @return varchar
 */
function C($key){
    if(empty($key)){
        return false;
    }
    $db = D("site_setting");
    $data = $db->where(array('site_key'=>$key))->find();
    if($data){
        return $data['site_value'];
    }else{
        return false;
    }
}

//接口调用
function getJson($url,$data=array(),$method='GET'){
    //检测链接是否可以打开
    if(@!fopen($url, "r")){
        return array('code'=>100001,'msg'=>'请求地址错误','url'=>$url);
    }
    $ch = curl_init();//1.初始化  
    curl_setopt($ch, CURLOPT_URL, $url);//2.请求地址  
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式  
    //4.参数如下  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    if($method=="POST"){//5.post方式的时候添加数据  
        $data = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
    }  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);//6.执行 
    if (curl_errno($ch)) {//7.如果出错  
        return curl_error($ch);  
    }   
    curl_close($ch);//8.关闭 
    return json_decode($output, true);
}








