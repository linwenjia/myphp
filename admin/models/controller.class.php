<?php
	class Controller{
		static $str = '';
		private $dir = PROJECT_PATH.'home'.DS.'controls';//控制器文件夹-绝对路径
		/**
		 * [根据id获取控制器详情]
		 * @param  [int] $controller_id [控制器表主键id]
		 * @return [array]                [控制器详情]
		 */
		function getControllerInfo($controller_id){
			if(empty($controller_id))return;
			$info = $this->where($controller_id)->find();
			return $info;
		}

		/**
		 * [根据id获取控制器扩展表信息]
		 * @param  [int] $controller_id [控制器表主键id]
		 * @return [array]                [控制器扩展表信息]
		 */
		function getControllerExtendInfo($controller_id){
			if(empty($controller_id))return;
			$list = $this->setTable('controller_extend')->where(array('controller_id'=>$controller_id))->select();
			return $list;
		}

		/**
		 * [获取控制器列表]
		 * @param  [array] $condition [查询条件]
		 * @return [array]            [控制器列表信息]
		 */
		function getControllerList($condition,$order='controller_id desc',$limit=10){
			$total = $this->where($condition)->total();//总数
        	$mpage = new Page($total,$limit);
			$list = $this->where($condition)->order($order)->limit($mpage->limit)->select();
			return array('list'=>$list,'fpage'=>$mpage->fpage(),'url'=>B_URL.'/mod?controller_id=');
		}

		/**
		 * [添加控制器]
		 * @param [array] $info        [控制器表数据]
		 * @param [array] $post [控制器扩展表数据]
		 * @param [string] $dir [控制器所在的文件夹]
		 * @return [array] 
		 */
		function addController($info,$post,$dir){
			/* 使用事物添加 start */
			//控制器扩展表模型
	        $db_controller_extend = D('controller_extend');
	        try{
	            $this->beginTransaction();
	            /* 数据插入controller表 start*/
	            $controller_id = $this->insert($info);
	            if(!$controller_id)
	                throw new Exception('控制器创建失败');
	            /* 数据插入controller表 end*/

	            /* 数据写入controller_extend表 start*/
	            $i=1;
	            foreach ($post as $key => $val) {
	                $insert = array();
	                $insert['controller_id'] = $controller_id;
	                $insert['model_id'] = $val['model_id'];
	                $insert['model_pic'] = $val['model_pic'];
	                $insert['sort'] = $i;
	                $insert['comment'] = $val['comment'];
	                $insert['cls_id'] = $val['cls_id'];
	                $insert['type'] = $val['type'];
	                $insert['output'] = $val['output'];
	                if(!empty($val['position']))
	                	$insert['position'] = $val['position'];
	                $insert['level'] = $val['level'];
	                $insert['output_type'] = $val['output_type'];
	                $insert['news_id'] = $val['news_id'];
	                $insert['textnum'] = $val['textnum'];
	                $insert['count_data'] = $val['count_data'];
	                $insert['add_time'] = time();
	                if(!$db_controller_extend->insert($insert))
	                    throw new Exception('控制器拓展表插入失败');
	                $i++;
	            }
	            /* 数据写入controller_extend表 start*/

	            /*文件写入 start*/
	            // 使用最大权限0777创建文件
	            if (!is_dir($this->dir))
	                mkdir($this->dir, 0775); 
	            //处理数据
	            $this->Build($info['controller_name'],$info['controller_desc'],$post);
	            //将数据写入文件
	            $result = file_put_contents($this->dir.DS.$info['controller_path'], self::$str);
	            if(!$result)
	                throw new Exception('文件写入失败');
	            /*文件写入 end*/

	            $this->commit();
	            // ajaxReturn(array('control'=>'controller','code'=>200,'msg'=>'创建成功','url'=>B_URL.'/controller_list'),"JSON");
	            return array('code'=>200,'msg'=>'创建成功');
	        } catch (Exception $e) {
	            $this->rollback();
	            return array('code'=>0,'msg'=>$e->getMessage());
	        }
			/* 使用事物添加 start */
		}

		/**
		 * [修改控制器扩展表信息]
		 * @param  [array] $info    [控制器详情]
		 * @param  [array] $update  [控制器扩展表信息]
		 * @param  [array] $del_arr [删除的模块]
		 * @return [array]          
		 */
		function updateController($info,$postDate,$del_arr){
			$db_controller_extend = D('controller_extend');
    
	        $db_controller_extend->beginTransaction();
	        $i = 1;
	        try{
	        	/* controller_extend表数据删除 start*/
	            if(!empty($del_arr) && is_array($del_arr)){
	                $condition = array();
	                $condition['id'] = $del_arr;
	                $result_del = $db_controller_extend->where($condition)->delete();
	                if(!$result_del)
	                    throw new Exception('控制器拓展表删除失败');
	            }
	            /* controller_extend表数据删除 start*/

	            /* 数据写入controller_extend表 start*/
	            foreach ($postDate as $key => $val) {
	                $update = array();
	                $update['sort'] = $i;
	                $update['comment'] = $val['comment'];
	                $update['cls_id'] = $val['cls_id'];
	                $update['type'] = $val['type'];
	                $update['output'] = $val['output'];
	                $update['position'] = $val['position'];
	                $update['level'] = $val['level'];
	                $update['output_type'] = $val['output_type'];
	                $update['news_id'] = $val['news_id'];
	                $update['textnum'] = $val['textnum'];
	                $update['count_data'] = $val['count_data'];
	                $id = intval($val['id']);
	                if(empty($id)){
	                    $update['controller_id'] = $info['controller_id'];
	                    $update['model_id'] = $val['model_id'];
	                    $update['model_pic'] = $val['model_pic'];
	                    $update['add_time'] = time();
	                    $result_ex = $db_controller_extend->insert($update);
	                }else{
	                    $update['update_time'] = time();
	                    $result_ex = $db_controller_extend->where($id)->update($update);
	                }
	                if(!$result_ex)
	                    throw new Exception('控制器拓展表写入失败');
	                $i++;
	            }
	            /* 数据写入controller_extend表 start*/

	            /* 文件写入 start*/
	            // 处理数据
	            $this->Build($info['controller_name'],$info['controller_desc'],$postDate);
	            // 将数据写入文件
	            $result = file_put_contents($this->dir.DS.$info['controller_path'], self::$str);
	            if(!$result)
	                throw new Exception('文件写入失败');
	            /* 文件写入 end*/

	            $db_controller_extend->commit();
	            return array('code'=>200,'msg'=>'修改成功');
	        } catch (Exception $e) {
	            $db_controller_extend->rollback();
	            return array('code'=>0,'msg'=>$e->getMessage());
	        }
		}

		/**
		 * [删除控制器及其附属信息]
		 * @param  [int] $controller_id [控制器id]
		 * @return [array]                
		 */
		function delController($controller_id){
			if(empty($controller_id))
				return array('code'=>0,'msg'=>'参数错误');
			$info = $this->getControllerInfo($controller_id);
			if(empty($info))
				return array('code'=>0,'msg'=>'控制器不存在');
			$db_controller_extend = D('controller_extend');
			$this->beginTransaction();
			try{
				//删除控制器
				$result_del = $this->where($controller_id)->delete();
				if(!$result_del)
					throw new Exception('控制器删除失败');
				//删除控制器扩展表
				$result_ex = $db_controller_extend->where(array('controller_id'=>$controller_id))->delete();
				if(!$result_ex)
					throw new Exception('控制器扩展表删除失败');
				//删除文件
				if(!empty($info['controller_path']) && is_file($info['controller_path'])){
	                $bool = unlink($this->dir.DS.$info['controller_path']);
	                if(!$bool)
						throw new Exception('文件删除失败');
	            }
				$this->commit();
				return array('code'=>200,'msg'=>'删除成功');
			} catch (Exception $e) {
	            $this->rollback();
	            return array('code'=>0,'msg'=>$e->getMessage());
	        }
		}

		/**
		 * [生成写入控制器的内容]
		 * @param [type] $name    [控制器的名字]
		 * @param [type] $explain [控制器描述]
		 * @param [type] $post    [要写入的数据]
		 */
		function Build($name,$explain,$post) {
	        $str = "<?php\r\n";
	        $str .= "\t/**\r\n";
	        $str .= "\t*".$explain."\r\n";
	        $str .= "\t*/\r\n";
	        $str .= "\tclass ".$name." {\r\n";
	        $str .= "\t\tfunction index() {\r\n";
	        // items
	        // p($post);exit();
	        foreach ($post as $key => $val) {
	            $function = $this->get_function($val);
	            // echo $function;exit();
	            if(empty($function)){
	                ajaxReturn(array('control'=>'controller','code'=>0,'msg'=>'使用的方法不存在','data'=>array()),"JSON");
	                exit();
	            }
	            $str .= "\t\t\t/*".$val['comment']." start*/\r\n";
	            $str .= "\t\t\t\$".$val['output']." = $function;\r\n";
	            $str .= "\t\t\t\$json_data['".$val['output']."'] = \$".$val['output'].";\r\n";
	            $str .= "\t\t\t/*".$val['comment']." end*/\r\n\n";
	        }
	        // $str .= "\t\t\tp(\$json_data);\r\n";
	        $str .= "\t\t\t\$this->assign('json_data',\$json_data);\r\n";
	        $str .= "\t\t\t\$this->display();\r\n";
	        // close
	        $str .= "\t\t}\r\n";
	        $str .= "\t}\r\n";
	        self::$str = $str;
	        // return $str;为什么这里return不能把str传过去？
	    }

	    /**
	     * [获取对应的函数方法，写入控制器文件时使用]
	     * @param  [array] $param [参数]
	     * @return [string]       [函数方法]
	     */
	    function get_function($param){
	        // $functions = array(
	        //     'banner'    =>  'get_banner';
	        //     'advert'    =>  'get_banner';
	        //     'cls_data'  =>  'get_cls_data';
	        //     'news_data' =>  'get_news_data';
	        // );
	        // return $functions[$key];
	        $str = '';
	        switch ($param['type']) {
	            case 'banner':
	                $str = 'get_banner('.$param["position"].','.$param["cls_id"].','.$param["textnum"].')';
	                break;
	            case 'advert':
	                $str = 'get_img_by_id('.$param["cls_id"].')';
	                break;
	            case 'cls_data':
	                $str = 'get_cls_data('.$param["output_type"].','.$param["level"].','.$param['cls_id'].','.$param['textnum'].')';
	                break;
	            case 'news_data':
	                if(!empty($param['news_id']))
	                    $str = 'get_news_data('.$param["output_type"].','.$param["cls_id"].',"'.$param["news_id"].'",'.$param['textnum'].')';
	                else
	                    $str = 'get_news_data('.$param["output_type"].','.$param["cls_id"].','.$param['textnum'].')';

	                break;
	            case 'count_data':
	            	$str = 'get_count("'.$param["count_data"].'")';
	            	break;
	            default:
	                $str = 'get_banner('.$param["position"].')';
	                break;
	        }

	        return $str;
	    }
	}