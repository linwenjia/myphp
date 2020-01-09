<?php

class FileCache{
 
    //提示信息
    public $tip_message;
     
    //缓存目录
    protected $cache_dir;
    //缓存文件名
    private $cache_file_name;
    //缓存文件后缀
    private $cache_file_suffix;

    /**
     *@param $dir	String  设置缓存目录， 如果为空使用常量 BRO_CACHE_DIR, 如果都没有设置创建一个缺省的缓存文件 
     *@param $cache_file_suffix 缓存文件的后缀 默认使用.php, 建议使用这个后缀， 不能被访问。
     */  
    public function __construct($dir="", $cache_file_suffix = '.php'){
	
	if($dir!="") {
		$this->cache_dir = $dir;
	}else if(defined('BRO_CACHE_DIR')) {
		$this->cache_dir = BRO_CACHE_DIR;
	}else {
	     //$this->cache_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'default_cache_data';
	     $this->cache_dir = './runtime/default_cache_data';
	}

 
       	$this->cache_file_suffix = $cache_file_suffix;
         
      	if(!$this->dir_isvalid($this->cache_dir)){
		die($this->tip_message);//创建目录失败
     	}
             
    }
    /**
     * 添加一个值， 如果已经存在，则返回false
     *
     * @param $cache_key mixed	键值
     * @param $cache_value mixed 保存的缓存数据
     * @param $life_time int	保存时间，如果设置为0则永远不过期， 默认值为0
     * @return boolean 	设置成功为true, 失败为false
     *
     */

    public function add($cache_key, $cache_value, $life_time=0){
         if(file_exists($this->get_cache_file_name($cache_key))){
            $this->tip_message = '缓存数据已存在.';
            return false;
         }
         $cache_data['data'] = $cache_value;
         $cache_data['life_time'] = $life_time;
         //以JSON格式写入文件
         if(file_put_contents($this->get_cache_file_name($cache_key), json_encode($cache_data))){
            return true;
         }else{
            $this->tip_message = '写入缓存失败.';
            return false;
         }
         
    }
    /**
     * 修改一个值， 如果已经存在，则改写， 如果不存在则添加
     *
     * @param $cache_key mixed	键值
     * @param $cache_value mixed 保存的缓存数据
     * @param $life_time int	保存时间，如果设置为0则永远不过期， 默认值为0
     * @return boolean 	设置成功为true, 失败为false
     *
     */
    public function set($cache_key, $cache_value, $life_time=0){
         
        $cache_data['data'] = $cache_value;
        $cache_data['life_time'] = $life_time;
        if(file_put_contents($this->get_cache_file_name($cache_key), json_encode($cache_data))){
            return true;
         }else{
            $this->tip_message = '写入缓存失败.';
            return false;
         }
    }

   /**
     * 通过指定的键来从缓存文件中获取值
     *
     * @param $cache_key mixed	键值
     *
     * @return mixed 获取成功返回获取的值，失败返回false
     *
     */
    public function get($cache_key){
	$cache_file_name = $this->get_cache_file_name($cache_key);    
        if(!file_exists($cache_file_name)){
            return false;
        }
        $data = $this->object_to_array(json_decode(file_get_contents($cache_file_name)));
         
        if($this->check_isvalid($data['life_time'])){
            unset($data['life_time']);
            return $data['data'];
        }else{
            unlink($cache_file_name);
            $this->tip_message = '数据已过期.';
            return false;
        }  
    }
   /**
     * 通过指定的键删除指定的缓存
     *
     * @param $cache_key mixed	键值
     *
     * @return boolean，成功返回true, 失败返回false
     *
     */
    public function delete($cache_key){
	$cache_file_name = $this->get_cache_file_name($cache_key);    
        if(file_exists($cache_file_name)){
            if(unlink($cache_file_name))
                return true;
            else
                return false;
        }else{
            $this->tip_message = '文件不存在.';
            return true;
        }
    }
    /**
     *清除所有缓存文件
     */
    public function flush(){
        $this->delete_file($this->cache_dir);
    }


    /**
     *自动清除过期文件
     */
 
    public function auto_delete_expired_file(){
        $this->delete_file($this->cache_dir,false);
    }
    
    // |检查目录是否存在,不存在则创建
    private function dir_isvalid($dir){
         
        if (is_dir($dir))
            return true;
        try {
           mkdir($dir,0777);
        }catch (Exception $e) {
             $this->tip_message = '所设定缓存目录不存在并且创建失败!请检查目录权限!';
             return false;           
       }
       return true;
    }

    // |检查有效时间
    private function check_isvalid($expired_time = 0) {
	    if($expired_time == 0)
		    return true;

	    if (!(@$mtime = filemtime($this->cache_file_name))) 
		    return false;
	    if (time() -$mtime > $expired_time) 
		    return false;
	    
	    return true;
    }


    // |获得缓存文件名
    public function get_cache_file_name($key){
        $this->cache_file_name = $this->cache_dir.DIRECTORY_SEPARATOR.md5("bro_".$key).$this->cache_file_suffix;
        return $this->cache_file_name;
    }

    // |object对象转换为数组
    protected function object_to_array($obj){
          
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    // |删除目录下的所有文件
    // |$mode true删除所有 false删除过期
    protected function delete_file($dir,$mode=true) { 
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    if($mode){
                        unlink($fullpath);
                    }else{
                        $this->cache_file_name = $fullpath;
			if(!$this->get_isvalid_by_path($fullpath))
			       	unlink($fullpath);
                    }
                     
                } else {
                    delete_file($fullpath,$mode);
                }
            }
        }
        closedir($dh);
    }
    //判断文件是否过期
    private function get_isvalid_by_path($path){
        $data = $this->object_to_array(json_decode(file_get_contents($path)));
        return $this->check_isvalid($data['life_time']);
    }
}


