<?php
	class Action extends MyTpl{
		/**
		 * 该方法用来运行框架中的操制器，在brophp.php入口文件中调用
		 */
		function run(){
			if($this->left_delimiter!="<{")
				parent::__construct();
			//如果有子类Common，调用这个类的init()方法 做权限控制
			if(method_exists($this, "init")){
				call_user_func(array($this, "init"));		
			}

			//根据动作去找对应的方法
			$method=$_GET["a"];
			if(method_exists($this, $method)){
				call_user_func(array($this, $method));
			}else{
                /* 判断 404.html 是否存在 不存在则调转 首页 start */
                if(is_file($_SERVER['DOCUMENT_ROOT'].B_ROOT.DS.'404.html')){
		            $url_404 = SHOP_SITE_URL.B_ROOT.DS."404.html";
		        }else{
		            $url_404 = SHOP_SITE_URL.B_ROOT."/index";
		        }
		        header('Location: '.$url_404);
                /* 判断 404.html 是否存在 不存在则调转 首页 end */
                Debug::addmsg("<font color='red'>没有{$_GET["a"]}这个操作！</font>");
			}	
		}

		private function c2p($c) {

				//获取主入口后的内容
				$parr = explode(basename(B_APP), $c);
				$pi = array_pop($parr);
				
      			 	//获取 pathinfo
				$pathinfo = explode('/', trim($pi, "/"));
			
       				// 获取 control
       				$g['m'] = (!empty($pathinfo[0]) ? strtolower($pathinfo[0]) : 'index');

       				array_shift($pathinfo); //将数组开头的单元移出数组 
      				
			       	// 获取 action
       				$g['a'] = (!empty($pathinfo[0]) ? strtolower($pathinfo[0]) : 'index');
				array_shift($pathinfo); //再将将数组开头的单元移出数组 

				for($i=0; $i<count($pathinfo); $i+=2){
					$g[$pathinfo[$i]]=$pathinfo[$i+1];
				}
				$p = http_build_query($g);
				return $p;
		
		}
	
		/** 
		 * 用于在控制器中进行位置重定向
		 * @param	string	$path	用于设置重定向的位置
		 * @param	string	$args 	用于重定向到新位置后传递参数
		 * 
		 * $this->redirect("index")  /当前模块/index
		 * $this->redirect("user/index") /user/index
		 * $this->redirect("user/index", 'page/5') /user/index/page/5
		 */
		function redirect($path, $args=""){
			debug(0);
			$path=trim($path, "/");
			if($args!="")
				$args="/".trim($args, "/");
			if(strstr($path, "/")){
				$url=$path.$args;
			}else{
				$url=$_GET["m"]."/".$path.$args;
			}

			if (defined("URLMOD") && URLMOD == 1 ){
				$uri=B_APP.'/'.$url;
			}else{
		
				$uri=B_APP."?".$this->c2p($url);
			}
	
			echo '<script>';
			echo 'location="'.$uri.'"';
			echo '</script>';
		}


		/**
		 * 成功的消息提示框
		 * @param	string	$mess		用示输出提示消息
		 * @param	int	$timeout	设置跳转的时间，单位：秒
		 * @param	string	$location	设置跳转的新位置
		 * @param 	string  $type		默认值为空， 可以设置top值，回到最顶层窗口显示消息框
		 */
		function success($mess="操作成功", $timeout=1, $location="", $type=""){
			$this->pub($mess, $timeout, $location, $type, 'success');
		
			$this->assign("mark", true);  //如果成功 $mark=true
			$this->display("public/success");	

			exit;
		}
		/**
		 * 失败的消息提示框
		 * @param	string	$mess		用示输出提示消息
		 * @param	int	$timeout	设置跳转的时间，单位：秒
		 * @param	string	$location	设置跳转的新位置
		 */
		function error($mess="操作失败", $timeout=3, $location="", $type=""){
			$this->pub($mess, $timeout, $location, $type, "error");
		
			$this->assign("mark", false); //如果失败 $mark=false
			$this->display("public/success");
			
			exit;
		}

		//跳转函数
		private function pub($mess, $timeout, $location, $type, $msg_result){	
			$this->caching=0;     //设置缓存关闭



			if($location==""){
				$location= $_SERVER['HTTP_REFERER'];
			
			}else if(stristr($location, 'http://')){
				
				$location=urldecode($location);
			
			}else{
				$path=trim($location, "/");
			
				if(strstr($path, "/")){
					$url = $path;
				}else{
					$url = $_GET["m"]."/".$path;
				}
			
				if (defined("URLMOD") && URLMOD == 1 ){
					$location = B_APP.'/'.$url;
				}else{
					$location = B_APP."?".$this->c2p($url);
				}	

			
			}

			//如果是嵌套窗口
			if($type=='top') {
				$location = $location ? "top.location = '{$location}'" : "top.location.reload()";	
			}elseif($type=='self'){
                $location = $location ? "self.location = '{$location}'" : "self.location.reload()";
            }else{
				$location = "window.location='{$location}'";
			}

			//有的消息是多个放在数组中， 合成字符串
			if(is_array($mess))
				$mess = implode('<br>', $mess);

			//如果是弹框模式
			if(defined("MESSMOD") && MESSMOD == 1 ){
				$this->assign("mess", $mess);
				$this->assign("timeout", $timeout);
				$this->assign("location", $location);
				debug(0);
			}else{
				$_SESSION['bro_result']['msg_result'] = $msg_result;
				$_SESSION['bro_result']['msg_show'] = $mess;
				$_SESSION['bro_result']['timeout'] = $timeout;
				echo "<script type='text/javascript'>{$location}</script>";
				exit;
			}

		}

	}
