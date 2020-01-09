<?php
	class Index {
		function index(){
            $this->display();
		}
        function show_main(){
        	$db_news = D('news');

        	/*统计文章总数 start*/
        	$news_count = $db_news->total();
        	/*统计文章总数 end*/

        	/*统计所有文章的浏览量 start*/
        	$view_count = 0;
        	$news_data = $db_news->field('open_times')->select();
        	foreach ($news_data as $val) {
        		$view_count += $val['open_times'];
        	}
        	/*统计所有文章的浏览量 end*/

        	/*PHP版本，服务器版本，mysql版本 start*/
        	/*PHP版本，服务器版本，mysql版本 end*/

        	/*统计最近半年的文章发布情况 start*/
        	$time = array();
        	$time['year'] = date("Y",time());
        	$time['month'] = date("n",time());
        	$data = array();
        	array_unshift($data, $time);
        	for($i = 1;$i<7;$i++){
        		$year = $time['year'];
        		$month = $time['month']-$i;
        		if($month <= 0){
        			$year = $time['year']-1;
        			$month = 12-($i-$time['month']);
        		}
        		array_unshift($data, array('year'=>$year,'month'=>$month));
        	}
        	foreach ($data as $key => &$val) {
        		$min_time = strtotime($val['year'].'-'.$val['month']);
        		$max_time = strtotime("next month",$min_time);
        		$count = $db_news->where(array('add_times >'=>$min_time,'add_times <'=>$max_time))->total();
        		$val['count'] = $count;
        		$val['month'] = $this->getMonth($val['month']);
        	}
        	// p(json_encode(array_column($data, 'count')));
        	/*统计最近半年的文章发布情况 end*/

        	/*最近一个月文章发布数量 start*/
        	$where = array();
        	$min_time = strtotime(date("Y-m",time()));
        	$max_time = strtotime('next month',$min_time);
        	$count = $db_news->where(array('add_times >'=>$min_time,'add_times <'=>$max_time))->total();
        	// p(date("Y-m-d H:i:s",$max_time));
        	/*最近一个月文章发布数量 end*/

        	/*mysql版本 start*/
        	$con = mysqli_connect('127.0.0.1','root','root');
		    $mysql_version = mysqli_get_server_info($con).'<br/>';
        	/*mysql版本 end*/

        	$this->assign('news_count', number_format($news_count));
        	$this->assign('view_count', number_format($view_count));
        	$this->assign('count', number_format($count));
        	$this->assign('data',json_encode(array_column($data, 'count')));
        	$this->assign('months',json_encode(array_column($data, 'month')));
        	$this->assign('mysql_version',$mysql_version);
        	$this->assign('siteid',C('siteid'));
            $this->display();
        }

        function getMonth($month){
        	$months = array(
        		1=>'一月',
        		2=>'二月',
        		3=>'三月',
        		4=>'四月',
        		5=>'五月',
        		6=>'六月',
        		7=>'七月',
        		8=>'八月',
        		9=>'九月',
        		10=>'十月',
        		11=>'十一月',
        		12=>'十二月',
        	);
        	return $months[$month];
        }

        /**
         * [sitemap 网站地图]
         * @return [type] [description]
         */
        function sitemap(){
            $files = array('sitemap.xml','sitemap.txt');
            $list = array();
            foreach ($files as $key => $val) {
                $list[$key]['name'] = $val;
                $list[$key]['size'] = tosize(filesize($val));
                $list[$key]['time'] = filemtime(PROJECT_PATH.$val);
            }
            // p($list);
            $this->assign('list',$list);
            $this->display();
        }

        /**
		 * [生成网站地图]
		 */
		function createSitemap(){
		    $db_news_cls = D('news_cls');
		    $db_news = D('news');
		    $cls_data = $db_news_cls->field('news_cls_id,news_cls_name')->select();
		    $news_data = $db_news->field('news_id,news_name')->select();

		    /*生成sitemap.xml satrt*/
		    $sitemap = new sitemap();
		    $sitemap->AddItem(SHOP_SITE_URL,1);
		    foreach ($cls_data as $key => $val) {
		        $sitemap->AddItem(SHOP_SITE_URL.'/whlist?cid='.$val['news_cls_id'],1);
		    }
		    foreach ($news_data as $key => $val) {
		        $sitemap->AddItem(SHOP_SITE_URL.'/whshow?id='.$val['news_id'],1);
		    }
		    $sitemap->SaveToFile('sitemap.xml');
		    /*生成sitemap.xml end*/

		    /*生成sitemap.txt start*/
		    $sitemap->Build_txt();
		    $sitemap->SaveToFile('sitemap.txt');
		    /*生成sitemap.txt end*/
		    // $this->success('成功',2,'index/index');
            ajaxReturn(array('control'=>'createSitemap','code'=>200,'msg'=>'网站地图更新成功'),"JSON");
		}

        function look(){
            $name = $_POST['name'];
            if(!file_exists(PROJECT_PATH.$name)){
                ajaxReturn(array('control'=>'look','code'=>0,'msg'=>'文件不存在'),"JSON");
            }
            $info = file_get_contents(PROJECT_PATH.$name);
            ajaxReturn(array('control'=>'look','code'=>200,'msg'=>'成功','info'=>$info),"JSON");
        }

        //对news表里news_body字段里的图片路径替换 start
        public function imglinkzhuanhuan(){
            $oldimglink = $_GET['oldimglink']; //原来的
            $newsimglink = $_GET['newsimglink']; // 替换的
            if (empty($oldimglink) || empty($newsimglink)) {
                echo '请传递oldimglink和newsimglink参数';die;
            }
            $db_news = D('news');
            $data_all = $db_news->field('news_id,news_body')->order('news_id asc')->select();
            $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
            foreach ($data_all as $k=>$v) {
                $newsbody = htmlspecialchars_decode($v['news_body']);
                preg_match_all($pattern,$newsbody,$match);
                if(isset($match[1]) && !empty($match[1])){ //存在图片则进行替换图片路径
                    $update['news_body'] = str_replace($oldimglink, $newsimglink, $newsbody);
                    p($v['news_id']);
                    $res = $db_news->where(['news_id'=>$v['news_id']])->update($update);
                    echo '成功转换';
                }
            }
        }
        //对news表里news_body字段里的图片路径替换 end
	}