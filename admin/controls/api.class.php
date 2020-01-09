<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2017/6/6
 * Time: 9:08
 * QQ:  997823131 
 */
class api extends Action{
    /**
     * [get_file 接受服务端发送过来的文件]
     * @return [type] [description]
     */
    function get_file(){
        // p($_FILES);p($_POST);
        $appid = $_POST['appid'];
        $secret = $_POST['secret'];
        $version = $_POST['version'];
        if(empty($appid) || empty($secret) || empty($version)){
            echo json_encode(array('code'=>0,'msg'=>'参数错误','post'=>$_POST));
            exit();
        }
        if(C('appid') != $appid || C('secret') != $secret){
            echo json_encode(array('code'=>0,'msg'=>'非法请求'));
            exit();
        }

        if(C('version') >= $version){
            echo json_encode(array('code'=>0,'msg'=>'已经是最新版本'));
            exit();
        }
        
        if($_FILES){
            $filename = $_FILES['file']['name'];
            $tmpname = $_FILES['file']['tmp_name'];
            // 使用最大权限0775创建文件
            if (!is_dir(PROJECT_PATH.'version/'))
                mkdir(PROJECT_PATH.'version/', 0775);
            if(@move_uploaded_file($tmpname, PROJECT_PATH.'version/'.$filename)){
                //测试执行 
                // $this->unzip_file(PROJECT_PATH.'version/'.$filename,PROJECT_PATH.'version',$version);
                $this->unzip_file(PROJECT_PATH.'version/'.$filename,substr(PROJECT_PATH, 0,-1),$version);
            }else{
                echo json_encode(array('code'=>0,'msg'=>'文件上传失败','data'=>$_FILES));
            }
        }else{
            echo json_encode(array('code'=>0,'msg'=>'没有文件上传'));
        }
    }

    /**
     * [unzip_file zip文件解压]
     * @param  [type] $file        [需要解压的文件的路径]
     * @param  [type] $destination [解压之后存放的路径]
     * @param  [type] $version     [版本号]
     * @需要使用 ZZIPlib library    请确认该扩展已经开启
     * @return [type]              [description]
     */
    function unzip_file($file, $destination, $version){ 
        // 实例化对象 
        $zip = new ZipArchive(); 
        // $code = mb_detect_encoding($file, array('ASCII','GB2312','GBK','UTF-8')); 
        // p($code);exit();
        //打开zip文档，如果打开失败返回提示信息 
        if ($zip->open($file) !== TRUE) { 
            echo json_encode(array('code'=>0,'msg'=>'文件打开失败','data'=>$file));
            exit();
        } 
        //将压缩文件解压到指定的目录下 
        $result = $zip->extractTo($destination); //如果线上的文件里面，有不属于apache的文件，那么这个文件在解压的时候会失败，解压文件的默认所属者是apache
        if(!$result){
            echo json_encode(array('code'=>0,'msg'=>'解压失败','result'=>$result));
            exit();
        }
        //关闭zip文档 
        $zip->close(); 
        //修改版本号
        $db_site_setting = D('site_setting');
        $result = $db_site_setting->where(array('site_key'=>'version'))->update(array('site_value'=>$version));
        //删除文件
        if($result){
            // chmod($file,0775);
            unlink(realpath($file));
        }
        echo json_encode(array('code'=>200,'msg'=>'升级成功'));
    } 
}