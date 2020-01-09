<?php

	/**

	*列表页

	*/

	class whlist {

		function index() {

			/*分类 start*/

			$cls = get_cls_data(0,1,2,2);

			$json_data['cls'] = $cls;

			/*分类 end*/


			/*列表数据 start*/

			$list = get_news_data(3,2,"2",10);

			$json_data['list'] = $list;

			/*列表数据 end*/


			$this->assign('json_data',$json_data);

			$this->display();

		}
		function listPage(){
			getListAjax($_GET['cid']);
		}

	}

