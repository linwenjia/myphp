<?php
	/**
	*首页控制器
	*/
	class index {
		function index() {
			/*轮播图 start*/
			$banner = get_banner(1);
			$json_data['banner'] = $banner;
			/*轮播图 end*/

			/*产品 start*/
			$product = get_cls_data(0,1,3,4);
			$json_data['product'] = $product;
			/*产品 end*/

			/*公司介绍 start*/
			$about = get_news_data(1,1,"9",1);
			$json_data['about'] = $about;
			/*公司介绍 end*/

			/*万鸿团队 start*/
			$team = get_news_data(3,20,"1",3);
			$json_data['team'] = $team;
			/*万鸿团队 end*/

			/*案例展示 start*/
			$case = get_news_data(3,6,"1",4);
			$json_data['case'] = $case;
			/*案例展示 end*/

			/*新闻资讯 start*/
			$news = get_cls_data(1,1,12,4);
			$json_data['news'] = $news;
			/*新闻资讯 end*/

			/*新闻速递 start*/
			$news_sudi = get_news_data(3,12,"1",12);
			$json_data['news_sudi'] = $news_sudi;
			/*新闻速递 end*/

			/*案例分类 start*/
			$case_cls = get_cls_data(0,1,6,1);
			$json_data['case_cls'] = $case_cls;
			/*案例分类 end*/

			$this->assign('json_data',$json_data);
			$this->display();
		}
	}
