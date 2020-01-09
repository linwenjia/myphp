<?php
	/**
	*默认注释
	*/
	class index_test {
		function index() {
			/*轮播图 start*/
			$banner = get_banner(1,0,3);
			$json_data['banner'] = $banner;
			/*轮播图 end*/

			/*产品 start*/
			$product = get_news_data(3,2,"2",2);
			$json_data['product'] = $product;
			/*产品 end*/

			/*关于我们 start*/
			$about = get_cls_data(1,1,1,1);
			$json_data['about'] = $about;
			/*关于我们 end*/

			/*学员风采 start*/
			$success = get_news_data(3,4,"2",4);
			$json_data['success'] = $success;
			/*学员风采 end*/

			/*新闻 start*/
			$news = get_cls_data(1,1,6,5);
			$json_data['news'] = $news;
			/*新闻 end*/

			/*合作伙伴 start*/
			$pannert = get_news_data(3,26,"2",10);
			$json_data['pannert'] = $pannert;
			/*合作伙伴 end*/

			$this->assign('json_data',$json_data);
			$this->display();
		}
	}
