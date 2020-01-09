<?php
	class Common extends Action {
        public $model_type = array('10'=>'顶部','20'=>'栏目','30'=>'底部','40'=>'自定义','50'=>'广告位');

		function init(){
            /* 判断是否登录 成功 start */
            if(isset($_SESSION['is_login']) && $_SESSION['is_login'] && !empty($_SESSION['is_login'])){
                /*logo 公司名字 简介 start*/
                $db_site = D('site_setting');
                $data_site = $db_site->select();
                for($i=0;$i<count($data_site);$i++){
                    $data[$data_site[$i]['site_key']] = $data_site[$i]['site_value'];
                }
                /*logo 公司名字 简介 end*/
                
                $this->assign('url_model',SHOP_SITE_URL.B_ROOT."/public/uploads/model_view/");//上传网站图片链接
                $this->assign("data_site",$data);
                $this->assign('model_type',$this->model_type);
                return true;
            }else{
                $this->redirect("login/login");
            }
            /* 判断是否登录 成功 end */
		}

        //获取网站秘钥和appid
        function getToken(){
            $db_site_setting = D('site_setting');
            $secret_info = $db_site_setting->where(array('site_key'=>'secret'))->find();
            $appid_info = $db_site_setting->where(array('site_key'=>'appid'))->find();
            $token['secret'] = $secret_info['site_value'];
            $token['appid'] = $appid_info['site_value'];
            return $token;
        }
        //接口调用
        function getJson($url,$data=array(),$method='GET'){
            //检测链接是否可以打开
            if(@!fopen($url, "r")){
                return array('code'=>100001,'msg'=>'请求地址错误','url'=>$url);
            }
            $ch = curl_init();//1.初始化  
            curl_setopt($ch, CURLOPT_URL, $url);//2.请求地址  
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式  
            //4.参数如下  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
            if($method=="POST"){//5.post方式的时候添加数据  
                $data = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
            }  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);//6.执行 
            if (curl_errno($ch)) {//7.如果出错  
                return curl_error($ch);  
            }   
            curl_close($ch);//8.关闭 
            return json_decode($output, true);
        }
	}