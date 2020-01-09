<?php
	/**
	*详情页
	*/
	class whshow {
		function index() {
			/*分类 start*/
			$cls = get_cls_data(0,1,2,4);
			$json_data['cls'] = $cls;
			/*分类 end*/

			/*文章 start*/
			$obj = get_news_data(1,1,"2",1);
			$json_data['obj'] = $obj;
			/*文章 end*/

			/*推荐 start*/
			$decomm = get_news_data(0,2,"2",8);
			$json_data['decomm'] = $decomm;
			/*推荐 end*/

			$this->assign('json_data',$json_data);
			$this->display();
		}
	}
