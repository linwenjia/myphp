<?php
	class Prourl {
		/**
		 * URL路由,转为PATHINFO的格式
		 */ 
		static function parseUrl(){
		
			if (defined("URLMOD") && URLMOD == 1 && isset($_SERVER['PATH_INFO'])){
      			 	//获取 pathinfo
				$pathinfo = explode('/', trim($_SERVER['PATH_INFO'], "/"));
			
       				// 获取 control
       				$_GET['m'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');

       				array_shift($pathinfo); //将数组开头的单元移出数组 
      				
			       	// 获取 action
       				$_GET['a'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');
				array_shift($pathinfo); //再将将数组开头的单元移出数组 

				for($i=0; $i<count($pathinfo); $i+=2){
					$_GET[$pathinfo[$i]]=$pathinfo[$i+1];
				}

			
			
			}else{	



				$_GET["m"]= (!empty($_GET['m']) ? $_GET['m']: 'index');    //默认是index模块
				$_GET["a"]= (!empty($_GET['a']) ? $_GET['a'] : 'index');   //默认是index动作
	
				if(defined("URLMOD") && URLMOD == 1 && $_SERVER["QUERY_STRING"]){
					$m=$_GET["m"];
					unset($_GET["m"]);  //去除数组中的m
					$a=$_GET["a"];
					unset($_GET["a"]);  //去除数组中的a
					$query=http_build_query($_GET);   //形成0=foo&1=bar&2=baz&3=boom&cow=milk格式
					//组成新的URL
					$url=$_SERVER["SCRIPT_NAME"]."/{$m}/{$a}/".str_replace(array("&","="), "/", $query);
					header("Location:".$url);
				} else {
					if(!strstr($_SERVER['REQUEST_URI'], "?")) {
						$_GET = self::c2p($_SERVER['REQUEST_URI']);
					}
				} 
				
		 
			}
		}

		
		static function c2p($c) {

				//获取主入口后的内容
				$parr = explode(".php", $c);
				$pi = array_pop($parr);
				
      			 	//获取 pathinfo
				$pathinfo = explode('/', trim($pi, "/"));
			
       				// 获取 control
       				$g['m'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');

       				array_shift($pathinfo); //将数组开头的单元移出数组 
      				
			       	// 获取 action
       				$g['a'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');
				array_shift($pathinfo); //再将将数组开头的单元移出数组 

				for($i=0; $i<count($pathinfo); $i+=2){
					$g[$pathinfo[$i]]=$pathinfo[$i+1];
				}
		
		
			//	$p = $parr[0].".php?".http_build_query($g);

				return $g;
					
			//	return array("url"=>$p, "get"=>$g);
		
		} 
	
	}
