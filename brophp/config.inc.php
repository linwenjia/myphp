<?php
	define("OUTPUT_CHARSET", "utf-8"); 	              //设置发送到客户端数据的字符集，默认为utf-8
	define("DEFAULT_TIMEZONE", "PRC");		      //设置时区
	define("URLMOD", 1);				      //设置URL的访问模式 1 为pathinfo模式，  0为普通模式
	define("MESSMOD", 0);				      //设置消息弹出模式 1 为弹出模式，  0为Ajax模式
	define("TPLPREFIX", "html");                           //模板文件的后缀名
	define("LEFT_DELIMITER", "<{");                       //模板文件中使用的“左”分隔符号
	define("RIGHT_DELIMITER", "}>");                       //模板文件中使用的“右”分隔符号

/* 定义mongodb 数据库 start */
define("MONGODB_SERVER", "127.0.0.1");                       //数据地址
define("MONGODB_USER", "");                       //数据地址
define("MONGODB_PASS", "");                       //数据地址
define("MONGODB_PORT", "");                       //数据地址
define("MONGODB_NAME", "yunzhuanma");                       //数据地址

/* 定义mongodb 数据库 end */

