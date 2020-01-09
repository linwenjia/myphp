<?php
	/**
	*首页
	*/
	class index {
		function index() {
			/*轮播图 start*/
			$banner = get_banner(1,0,3);
			$json_data['banner'] = $banner;
			/*轮播图 end*/

			/*产品 start*/
			$product = get_news_data(3,2,"2",2);
			$json_data['product'] = $product;
			/*产品 end*/

			/*about start*/
			$about = get_news_data(2,1,"1|2|3",1);
			$json_data['about'] = $about;
			/*about end*/

			/*学员风采 start*/
			$success = get_news_data(3,4,"2",4);
			$json_data['success'] = $success;
			/*学员风采 end*/

			/*新闻 start*/
			$news = get_cls_data(1,1,6,10);
			$json_data['news'] = $news;
			/*新闻 end*/

			/*合作伙伴 start*/
			$parnert = get_news_data(3,26,"2",10);
			$json_data['parnert'] = $parnert;
			/*合作伙伴 end*/

			$this->assign('json_data',$json_data);
			$this->display();
		}
	}
