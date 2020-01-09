<?php
	//PHP程序所有需要的路径，都使用相对路径
	define("BROPHP_PATH", str_replace("\\", "/", dirname(__FILE__)).'/');     //BroPHP框架的路径
	define("PROJECT_PATH", dirname(BROPHP_PATH).'/');  //项目的根路径，也就是框架所在的目录
	
	if(!defined('APP')) {
		$app_path =PROJECT_PATH;
	}else{
		$app_path = trim(APP,'./');
		
		if($app_path != "") {
			$app_path =PROJECT_PATH.$app_path."/";
		}else{
			$app_path =PROJECT_PATH;
		}
	}



	define("APP_PATH", $app_path);            //用户项目的应用路径
	define("TMPPATH", str_replace(array(".", "/"), "_", ltrim($_SERVER["SCRIPT_NAME"], '/'))."/");


	include BROPHP_PATH."config.inc.php";   //加载框架的配置文件
	header("Content-Type:text/html;charset=".OUTPUT_CHARSET); 	 //设置系统的输出字符为utf-8
	date_default_timezone_set(DEFAULT_TIMEZONE);    		 //设置时区（中国）

	//包含系统配置文件
	$config=PROJECT_PATH."config.inc.php";
	if(file_exists($config)){
		include $config;
	}

	//包含系统的资料配置文件
	$pathconfig=PROJECT_PATH."path.inc.php";
	if(file_exists($pathconfig)){
		include $pathconfig;
	}

	//判断是否设置过runtime的路径
	if(defined("B_RUN_PATH")) {
	
		define('RUNTIME', rtrim(B_RUN_PATH, '/').'/');		
	}else{
		define('RUNTIME',PROJECT_PATH);
	}



	error_reporting(E_ALL ^ E_NOTICE);   //输出除了注意的所有错误报告

	//设置Debug模式
	if(defined("DEBUG") && DEBUG==1){
		$GLOBALS["debug"]=1;                 //初例化开启debug
	
		include BROPHP_PATH."bases/debug.class.php";  //包含debug类
		Debug::start();                               //开启脚本计算时间
		set_error_handler(array("Debug", 'Catcher')); //设置捕获系统异常
	}else{
		ini_set('display_errors', 'Off'); 		//屏蔽错误输出
		ini_set('log_errors', 'On');             	//开启错误日志，将错误报告写入到日志中
		ini_set('error_log', RUNTIME.'runtime/error_log'); //指定错误日志文件

	}
	//包含框架中的函数库文件
	include BROPHP_PATH.'commons/functions.inc.php';
	

	//包含全局的函数库文件，用户可以自己定义函数在这个文件中
	$funfile=PROJECT_PATH."commons/functions.inc.php";
	if(file_exists($funfile))
		include $funfile;

    $GLOBALS['is_mobile'] = is_mobile_request();
    if(defined("MOBILE_TPL")){
        if($GLOBALS['is_mobile']){
            define("TPLSTYLE", MOBILE_TPL);                        //默认模板存放的目录
        }else{
            define("TPLSTYLE", PC_TPL);                        //默认模板存放的目录
        }
    }else{
        define("TPLSTYLE", PC_TPL);                        //默认模板存放的目录
    }

	
	//设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
	$include_path=get_include_path();                         //原基目录
	$include_path.=PATH_SEPARATOR.BROPHP_PATH."bases/";       //框架中基类所在的目录
	$include_path.=PATH_SEPARATOR.BROPHP_PATH."classes/" ;    //框架中扩展类的目录
	$include_path.=PATH_SEPARATOR.BROPHP_PATH."libs/" ;       //模板Smarty所在的目录
	$include_path.=PATH_SEPARATOR.BROPHP_PATH."libs/sysplugins/";
	$include_path.=PATH_SEPARATOR.PROJECT_PATH."classes/";    //项目中用的到的工具类
	$controlerpath= RUNTIME."runtime/controls/".TMPPATH;  //生成控制器所在的路径
	$include_path.=PATH_SEPARATOR.$controlerpath;             //当前应用的控制类所在的目录 

	//设置include包含文件所在的所有目录	
	set_include_path($include_path);

	//自动加载类 	
	function __autoload($className){
		if($className=="memcache"){        //如果是系统的Memcache类则不包含
			return;
		}else if($className=="Smarty"){    //如果类名是Smarty类，则直接包含
			include "Smarty.class.php";
		}elseif(file_exists(BROPHP_PATH."libs/sysplugins/".strtolower($className).".php")){
			//判断是否是Smarty3内部类，若是就导入
			include strtolower($className).".php";	
		}else{                             //如果是其他类，将类名转为小写
			include strtolower($className).".class.php";	
		}
		Debug::addmsg("<b> $className </b>类", 1);  //在debug中显示自动包含的类
	}

	//判断是否开启了页面静态化缓存
	if(CSTART==0){
		Debug::addmsg("<font color='red'>没有开启页面缓存!</font>（但可以使用）"); 
	}else{
		Debug::addmsg("开启页面缓存，实现页面静态化!"); 
	}
	
	//启用memcache缓存
	if(!empty($memServers)){
		//判断memcache扩展是否安装
		if(extension_loaded("memcache")){
			$mem=new MemcacheModel($memServers);
			//判断Memcache服务器是否有异常
			if(!$mem->mem_connect_error()){
				Debug::addmsg("<font color='red'>连接memcache服务器失败,请检查!</font>"); //debug
			}else{
				define("USEMEM",true);
				Debug::addmsg("启用了Memcache");
			}
		}else{
			Debug::addmsg("<font color='red'>PHP没有安装memcache扩展模块,请先安装!</font>"); //debug
		}	
	}else{
		Debug::addmsg("<font color='red'>没有使用Memcache</font>(为程序的运行速度，建议使用Memcache)");  //debug
	}

	//如果Memcach开启，设置将Session信息保存在Memcache服务器中
	if(defined("USEMEM")){
		MemSession::start($mem->getMem());
		Debug::addmsg("开启会话Session (使用Memcache保存会话信息)"); //debug

	}else{
		session_start();
		Debug::addmsg("<font color='red'>开启会话Session </font>(但没有使用Memcache，开启Memcache后自动使用)"); //debug
	}
	Debug::addmsg("会话ID:".session_id());
	
	Structure::create();   //初使化时，创建项目的目录结构
	Prourl::parseUrl();    //解析处理URL 




	//模板文件中所有要的路径，html\css\javascript\image\link等中用到的路径，从WEB服务器的文档根开始	
	$spath=rtrim(substr(dirname(str_replace("\\", '/', dirname(__FILE__))), strlen(rtrim($_SERVER["DOCUMENT_ROOT"],"/\\"))), '/\\');
	$GLOBALS["root"]=$spath.'/'; //Web服务器根到项目的根
	

	

	if (defined("URLMOD") && URLMOD == 1 ){
		$GLOBALS["app"]=$_SERVER["SCRIPT_NAME"].'/';           	//当前应用脚本文件
		$GLOBALS["url"]=$GLOBALS["app"].$_GET["m"].'/';       //访问到当前模块
	}else {
		$GLOBALS["app"]=$_SERVER["SCRIPT_NAME"];           	//当前应用脚本文件
		$GLOBALS["url"]=$GLOBALS["app"]."?m=".$_GET["m"];       //访问到当前模块
	}

	define("B_ROOT", rtrim($GLOBALS["root"], '/'));
	define("B_APP", rtrim($GLOBALS["app"], '/'));
	define("B_URL", rtrim($GLOBALS["url"], '/'));

	if(dirname(BROPHP_PATH) == dirname($_SERVER['SCRIPT_FILENAME'])) {
		Debug::addmsg("<b>建议将网站主程序与WEB目录分离</b>", 3);	
	} else {
		Debug::addmsg("网站主程序已经与WEB目录分离", 3);
	}
	Debug::addmsg("[<b>BROPHP</b>] 框架所在位置：".BROPHP_PATH, 3);
	Debug::addmsg("[<b>main file</b>] 单一入口文件位置：{$_SERVER['SCRIPT_FILENAME']}", 3);



	if(!defined("B_PUBLIC")) {
		$GLOBALS["public"]=$GLOBALS["root"].'public/';        //项目的全局资源目录
		define("B_PUBLIC", rtrim($GLOBALS["public"], '/'));
		Debug::addmsg("[<b>public</b>] 公共资源请求位置（默认）：".B_PUBLIC, 3);
	} else {
		Debug::addmsg("[<b>public</b>] 公共资源请求位置：".B_PUBLIC, 3);
	}

	$b_res = 'b_res_'.trim(APP, './');
	if(!empty($$b_res)) {
		define('B_RES', rtrim($$b_res, '/'));
		Debug::addmsg("[<b>APP</b>] 当前应用( <b>".APP."</b> )的资源请求位置：".B_RES, 3);
	}else {
		$GLOBALS["res"]=rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\").'/'.trim(APP, './')."/views/".TPLSTYLE."/resource/"; //当前应用模板的资源
		define("B_RES", rtrim($GLOBALS["res"], '/'));
		Debug::addmsg("[<b>APP</b>] 当前应用( <b>".APP."</b> )的资源请求位置（默认）：".B_RES, 3);
	}




	//和上传有关的默认选项的设置
	if(!defined("B_UP_PATH")) {
		define('B_UP_PATH', PROJECT_PATH.'public/uploads');                               //文件上传的服务器位置， 相对于服务器目结构

		Debug::addmsg("[<b>upload path</b>] 上传资源位置（默认）：".B_UP_PATH, 3);

		if(!defined("B_UPW_PATH")) {
			define('B_UPW_PATH', B_PUBLIC.'/uploads/');      //运程请求上传的内容， 相对于Web服务器文档根目录
			Debug::addmsg("[<b>upload url</b>] 上传的资源请求位置（默认）：".B_UPW_PATH, 3);
		}else {
			Debug::addmsg("[<b>upload url</b>] 上传的资源请求位置：".B_UPW_PATH, 3);
		}

	}else {
		Debug::addmsg("[<b>upload path</b>] 上传资源位置：".B_UP_PATH, 3);
		if(defined("B_UPW_PATH")) {
			Debug::addmsg("[<b>upload url</b>] 上传的资源请求位置：".B_UPW_PATH, 3);
		}else {
			Debug::addmsg("<font color='red'>必须设置上传的资源请求位置，在path.inc.php配置文件中设置常量B_UPW_PATH的值。</font>", 3);
		}
	}


	if(!defined("B_UPC_PATH")) {
		define('B_UPC_PATH', PROJECT_PATH.'thumb/');                       //上传的图片， 应用时生成的缓存图片存放路径
		Debug::addmsg("[<b>thumb path</b>] 图片缓存的位置(默认)：".B_UPC_PATH, 3);
		if(!defined("B_UPCW_PATH")) {
			define('B_UPCW_PATH', B_ROOT.'/thumb/');             //远程请求上传的缓存内容， 相对于Web服务器文档根目录
			Debug::addmsg("[<b>thumb url</b>] 图片缓存请求的位置(默认)：".B_UPCW_PATH, 3);
		}else{
			Debug::addmsg("[<b>thumb url</b>] 图片缓存请求的位置：".B_UPCW_PATH, 3);
		}
	}else {
		Debug::addmsg("[<b>thumb path</b>] 图片缓存位置：".B_UPC_PATH, 3);
		if(defined("B_UPCW_PATH")) {
			Debug::addmsg("[<b>thumb url</b>] 图片缓存请求的位置：".B_UPCW_PATH, 3);
		}else {
			Debug::addmsg("<font color='red'>必须设置 图片缓存请求的位置, 在path.inc.php配置文件中设置常量B_UPCW_PATH的值。</font>", 3);
		}
	}



	
	//判断是否设置过runtime的路径
	if(defined("B_RUN_PATH")) {
		Debug::addmsg("[<b>runtime</b>] 运行时生成的缓存runtime目录位置：".RUNTIME, 3);
	}else{
		Debug::addmsg("[<b>runtime</b>] 运行时生成的缓存runtime目录位置(默认)：".RUNTIME, 3);
	}



	//控制器类所在的路径
	$srccontrolerfile=APP_PATH."controls/".strtolower($_GET["m"]).".class.php";

	Debug::addmsg("当前访问的控制器类在项目应用目录下的: <b>$srccontrolerfile</b> 文件！");
	//控制器类的创建
	if(file_exists($srccontrolerfile)){
		Structure::commoncontroler(APP_PATH."controls/",$controlerpath);
		Structure::controler($srccontrolerfile, $controlerpath, $_GET["m"]); 

		$className=ucfirst($_GET["m"])."Action";
		
		$controler=new $className();
		$controler->run();

	}else{
        /* 判断 404.html 是否存在 不存在则调转 首页 start */
        if(is_file($_SERVER['DOCUMENT_ROOT'].B_ROOT.DS.'404.html')){
            $url_404 = SHOP_SITE_URL.B_ROOT.DS."404.html";
        }else{
            $url_404 = SHOP_SITE_URL.B_ROOT."/index";
        }
        header('Location: '.$url_404);
        /* 判断 404.html 是否存在 不存在则调转 首页 end */

		Debug::addmsg("<font color='red'>对不起!你访问的模块不存在,应该在".APP_PATH."controls目录下创建文件名为".strtolower($_GET["m"]).".class.php的文件，声明一个类名为".ucfirst($_GET["m"])."的类！</font>");
	}
	//设置输出Debug模式的信息
	if(defined("DEBUG") && DEBUG==1 && $GLOBALS["debug"]==1){
		Debug::stop();
		echo Debug::message();
	}

	if(defined("MESSMOD") && MESSMOD == 0 ){
		bro_result();
	}


