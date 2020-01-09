<?php

	/**
	 * 输出各种类型的数据，调试程序时打印数据使用。
	 * @param	mixed	参数：可以是一个或多个任意变量或值
	 */
	function p(){
		$args=func_get_args();  //获取多个参数
		if(count($args)<1){
			Debug::addmsg("<font color='red'>必须为p()函数提供参数!");
			return;
		}	

		echo '<div style="width:100%;text-align:left"><pre>';
		//多个参数循环输出
		foreach($args as $arg){
			if(is_array($arg)){  
				print_r($arg);
				echo '<br>';
			}else if(is_string($arg)){
				echo $arg.'<br>';
			}else{
				var_dump($arg);
				echo '<br>';
			}
		}
		echo '</pre></div>';	
	}
	/**
	 * 创建Models中的数据库操作对象
	 *  @param	string	$className	类名或表名
	 *  @param	string	$app	 应用名,访问其他应用的Model
	 *  @return	object	数据库连接对象
	 */
	function D($className=null,$app=""){
		$db=null;	
		//如果没有传表名或类名，则直接创建DB对象，但不能对表进行操作
		if(is_null($className)){
			$class="D".DRIVER;

			$db=new $class;
		}else{
			$className=strtolower($className);
			$model=Structure::model($className, $app);	
			$model=new $model();
			
			//如果表结构不存在，则获取表结构
			$model->setTable($className);		
		

			$db=$model;
		}
		if($app=="")
			$db->path=APP_PATH;
		else
			$db->path=PROJECT_PATH.strtolower($app).'/';
		return $db;
	}
	/**
	 * 文件尺寸转换，将大小将字节转为各种单位大小
	 * @param	int	$bytes	字节大小
	 * @return	string	转换后带单位的大小
	 */
	function tosize($bytes) {       	 	     //自定义一个文件大小单位转换函数
		if ($bytes >= pow(2,40)) {      		     //如果提供的字节数大于等于2的40次方，则条件成立
			$return = round($bytes / pow(1024,4), 2);    //将字节大小转换为同等的T大小
			$suffix = "TB";                        	     //单位为TB
		} elseif ($bytes >= pow(2,30)) {  		     //如果提供的字节数大于等于2的30次方，则条件成立
			$return = round($bytes / pow(1024,3), 2);    //将字节大小转换为同等的G大小
			$suffix = "GB";                              //单位为GB
		} elseif ($bytes >= pow(2,20)) {  		     //如果提供的字节数大于等于2的20次方，则条件成立
			$return = round($bytes / pow(1024,2), 2);    //将字节大小转换为同等的M大小
			$suffix = "MB";                              //单位为MB
		} elseif ($bytes >= pow(2,10)) {  		     //如果提供的字节数大于等于2的10次方，则条件成立
			$return = round($bytes / pow(1024,1), 2);    //将字节大小转换为同等的K大小
			$suffix = "KB";                              //单位为KB
		} else {                     			     //否则提供的字节数小于2的10次方，则条件成立
			$return = $bytes;                            //字节大小单位不变
			$suffix = "Byte";                            //单位为Byte
		}
		return $return ." " . $suffix;                       //返回合适的文件大小和单位
	}
	/**
	 * 关闭调试模式
	 * @param	bool	$falg	调式模式的关闭开关
	 */
	function debug($falg=0){
		$GLOBALS["debug"]=$falg;
	}

	/**
	 * 显示成功或失败的状态
	 */
	function bro_result() {
		
		if (isset($_SESSION['bro_result']['msg_show']) && $_SESSION['bro_result']['msg_show']) {
			$mess = $_SESSION['bro_result']['msg_show'];
			$msg_result = $_SESSION['bro_result']['msg_result'];
			$timeout = $_SESSION['bro_result']['timeout'];

		
			unset($_SESSION['bro_result']);
		
			$timeout = 3;
		
			if ($msg_result == 'success') {
				$color = "green";
				$bj = "OK！";
			

			}else {
				$color = "red";
				$bj = "ERROR！";
			
			}

print<<<html
			<style type="text/css">
				#bro_notice { width: 300px;padding:3px;border: 2px solid rgba(0,0,0,0.2);border-radius:6px; background: #EEE;position: absolute; left: 50%; top: 50%; margin:-150px 0 0 -150px;z-index:9999;
					  -webkit-box-shadow:0 5px 10px rgba(0,0,0,0.2); -moz-box-shadow:0 5px 10px rgba(0,0,0,0.2); box-shadow:0 5px 10px rgba(0,0,0,0.2); }
				#bro_notice div {padding:10px;background: #FFF; font-size: 1.1em; text-align:left;}
				#bro_notice div:first-line {font-size:1.2em;font-weight:bold;color:{$color}}
				#bro_notice p { background: #FFF;line-height:50px;margin: 0;font-weight:bold;}
			
			</style>　
			

			<script type="text/javascript">
				function setOpacity(elm, value) {
 					  if(document.all){ 
     						   elm.style.filter='alpha(opacity='+value+')';
  					  }else{            
       						 elm.style.opacity=value/100;
   					 }
				}



				function fadeOut(duration) {
				
					var ele=document.createElement("div");
					ele.id = "bro_notice";
					ele.innerHTML='<div>{$mess}</div><p style="text-align:center;font-size:20px;color:{$color}">{$bj}</p>';

					document.body.appendChild(ele);
	
				
				
					setTimeout(function(){
						var opavalue = 100;
  						  var interval_fadeOut = setInterval(function() {
        						if(opavalue > 0) {
								setOpacity(ele, opavalue);
								opavalue--;	
							
       							 } else {
								 clearInterval(interval_fadeOut);
								 ele.style.display="none";
							
								
       					 		}
  					 	 }, duration / 100);
					
					},parseInt({$timeout})*1000);

				
				}
		
				fadeOut(2000);
			
			</script>
html;
		}
	}





	/* 创建多级目录 */
	function createFolder($path) {
 		if (!file_exists($path)) {
			createFolder(dirname($path));
			@mkdir($path, 0755);
		}
	}


	//遍历目录
	function bdirs($dir) {
		$dir = rtrim($dir, "/");
		$d = opendir($dir);
		$dirs = array();
		while($filename= readdir($d)) {
			if($filename !="." && $filename!="..") {
				if(is_dir($dir."/".$filename)) {
					$dirs[]	= $filename;
				}	
			}
		}

		return $dirs;

	}

	//获取ip
	function bro_ip() {
   		if (isset($_SERVER)){
        		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
           			 $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
       			 } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
           			 $realip = $_SERVER["HTTP_CLIENT_IP"];
      			 } else {
            			$realip = $_SERVER["REMOTE_ADDR"];
      			}
  	       } else {
       			 if (getenv("HTTP_X_FORWARDED_FOR")){
           			 $realip = getenv("HTTP_X_FORWARDED_FOR");
     	  	         } else if (getenv("HTTP_CLIENT_IP")) {
          			 $realip = getenv("HTTP_CLIENT_IP");
       	       		 } else {
            			$realip = getenv("REMOTE_ADDR");
       	       		 }
    	       }
   		 return $realip;
	}


	//获取当前网址为下个地址的fromto
	function bro_fromto() {
		$host = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		stripos($host, 'fromto') !== false && $host = substr($host, 0, stripos($host, 'fromto')-1);
		return 'fromto='.urlencode($host);
	}



	//多久以前
	function bro_dayago($dmtime) {
		if (!$dmtime) return '≠';
		if ((time()-$dmtime) > 86400) {
			return intval((time()-$dmtime)/86400).'天前';
		} elseif ((time()-$dmtime) > 3600) {
			return intval((time()-$dmtime)/3600).'小时前';
		} elseif ((time()-$dmtime) > 60) {
			return intval((time()-$dmtime)/60).'分钟前';
		} elseif ((time()-$dmtime) > 0) {
			return (time()-$dmtime).'秒前';
		}	
	}

	//文件夹大小
	function bro_dirsize($dir_path)
	{
		$size = 0;
		if (is_file($dir_path)) {
			$size = filesize($dir_path);
		} else {
			$dir_arr = glob(trim($dir_path).'/*');
			if (is_array($dir_arr)) {
				foreach ($dir_arr as $k => $v) {
					$size += bro_dirsize($v);
				}
			}
		}
		return $size;
	}
	//删除文件夹
	function bro_dirdel($dir_path)
	{
		if (is_file($dir_path)) {
			unlink($dir_path);
		} else {
		
			$dir_arr = glob(trim($dir_path).'/*');
	
			if (is_array($dir_arr)) {
				foreach ($dir_arr as $k => $v) {
					bro_dirdel($v, $type);
				}	
			}
			@rmdir($dir_path);
		}
	}
// ajax 请求
function ajaxReturn($data,$type='') {
    if(empty($type)) $type = 'JSON';
    switch (strtoupper($type)){
        case 'JSON' :
// 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        case 'XML' :
// 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
// 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler = isset($_GET['VAR_JSONP_HANDLER']) ? $_GET['VAR_JSONP_HANDLER'] : 'jsonpReturn';
            exit($handler.'('.json_encode($data).');');
        case 'EVAL' :
// 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}

/* array_column 方法兼容 php5.4版本 start */
if( ! function_exists('array_column'))
{
    function array_column($input, $columnKey, $indexKey = NULL)
    {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();

        foreach ((array)$input AS $key => $row)
        {
            if ($columnKeyIsNumber)
            {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            }
            else
            {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if ( ! $indexKeyIsNull)
            {
                if ($indexKeyIsNumber)
                {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                }
                else
                {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }

            $result[$key] = $tmp;
        }

        return $result;
    }
}
/* array_column 方法兼容 php5.4版本 end */

