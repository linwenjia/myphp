<?php
header("Content-type: text/html; charset=utf-8");

$servername = "localhostlocalhost";
$username = "rootroot";
$password = "123456123456";
$dbname = "www.xiaoshuo.comwww.xiaoshuo.com";
// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8");
// 检测连接
if ($conn->connect_error) {
    die("fail: " . $conn->connect_error);
	return;
} 

//小说章节/集数 重新排序
if ($_GET['getsorts'] == 'xiaoshuosort') {
	$bidstring = file_get_contents('./jinosort.txt');
	$bidstring = trim($bidstring,',');
	$bidarray = array_unique(explode(',', $bidstring));
	foreach ($bidarray as $value) {
		$ji_no = 1;
		$before = $ji_no - 1;
		$next = $ji_no + 1;
		$cstype = "SELECT id,bid,ji_no,huoche_id FROM `vv_book_episodes` WHERE bid='$value' ORDER BY huoche_id ASC";
		$cresult = $conn->query($cstype);
		if($cresult->num_rows > 0){
	 		while ($rows = mysqli_fetch_assoc($cresult)){
	 			$id = $rows['id'];
	 			$updatsql="UPDATE `vv_book_episodes` SET `ji_no`=$ji_no,`before`=$before,`next`=$next WHERE id=$id;"; //关键字冲突用`括起来 `before`
	 			// print_r($updatsql);
				$conn->query($updatsql);
				$ji_no++;
				$before++;
				$next++;
	 		}
	 		//统计总章节
	 		$updatsql="UPDATE `vv_book` SET episodes=$cresult->num_rows WHERE id=$value;";
			$conn->query($updatsql);
	 	}
	 	
	}
	file_put_contents('./jinosort.txt', file_get_contents('./bookbid.txt').','); //小说id
	echo '排序成功';
	exit;
}


$sl=addslashes($_POST['sl']);
$sl1=addslashes($_GET['sl1']);
$bookname=addslashes($_POST['bookname']);//书名
$author=addslashes($_POST['author']);//作者
$des=addslashes($_POST['des']);//简介
$tstype=addslashes($_POST['tstype']);//漫画首页分类
$sstype=addslashes($_POST['sstype']);//所属分类
$zishu=addslashes($_POST['zishu']);//字数
$litpic=addslashes($_POST['litpic']);//封面图
$time=addslashes($_POST['time']);//发布时间
$sharetitle=addslashes($_POST['sharetitle']);//分享标题
$mhtitle=addslashes($_POST['title']);//漫画标题
$jino=addslashes($_POST['jino']);//漫画编号
$mhbody=addslashes($_POST['content']);//漫画内容
$jine=empty($_POST['jine']) ? 0 : addslashes($_POST['jine']);//阅读金额
$sex=addslashes($_POST['sex']);//阅读金额
$send=addslashes($_POST['send']);//打赏金额
$status=isset($_POST['status']) ? addslashes($_POST['status']) : 1;//'状态:1连载 2完结' 
$huoche_url=isset($_POST['huoche_url']) ? addslashes($_POST['huoche_url']) : 0;
$huoche_id=isset($_POST['huoche_id']) ? intval($_POST['huoche_id']) : 0;//火车头本身自带ID 
$reads_mh=mt_rand(30000, 7000000);// 漫画阅读数（3万-700万之间）
$dz_mh=mt_rand(10000, 20000);// 漫画点赞数（1万-2万之间）
$dzzj_mh=mt_rand(10000, 20000);// 章节点赞数（1万-2万之间）
$sc_mh=mt_rand(5000, 9000);// 收藏数（5000-9000之间）
$ds_mh=mt_rand(1000, 5000);// 打赏数（1000-5000之间）
$reads_book=mt_rand(10000, 100000);// 小说阅读数（1万-10万之间）
$dz_book=mt_rand(3000, 10000);// 小说点赞数（3000-1万之间）
$dzzj_book=$dz_book;// 章节点赞数（3000-1万之间）
$sc_book=mt_rand(3000, 5000);// 收藏数（3000-5000之间）
$ds_book=mt_rand(1000, 3000);// 打赏数（1000-3000之间）


if ($sl1==3) {
	for($i=114;$i<417;$i++) { 
		$upsql="UPDATE vv_mh_list SET summary=share_desc where id=$i";
	    $conn->query($upsql);
		echo ' 更新成功';
	}
	return;
}

//添加小说-------------------------------------------
if ($sl == 2) {
	if ($huoche_url == '') exit;
	$duibibid = file_get_contents('./bookbid.txt'); //bid
	$stype = "SELECT * FROM `vv_book_episodes` WHERE huoche_url='$huoche_url' LIMIT 0,1"; //查询huoche_url是否存在
    $result = $conn->query($stype);
    if($result->num_rows > 0){ //huoche_url存在
		//修改该条数据
		if (!empty($mhbody)) {
 			$updatsql="UPDATE vv_book_episodes SET title='$mhtitle',info='$mhbody' WHERE huoche_url='$huoche_url';";
			$conn->query($updatsql);
			echo '添加小说成功';
		} else {
			echo 'fail:添加小说失败'; 
		}
		exit;
    } else { //huoche_url不存在
    	//检查该条记录是否存在 存在不执行插入操作
    	if($bookname != "" && $bookname != 'true'){
			$cstype="SELECT id,title,episodes FROM `vv_book` WHERE title='$bookname' LIMIT 0,1";
			$cresult=$conn->query($cstype);
			if($cresult->num_rows > 0){ //存在
				$rowbook = mysqli_fetch_assoc($cresult);
				file_put_contents('./bookbid.txt', $rowbook['id']); //bid
				file_put_contents('./bookiepisodesd.txt', intval($rowbook['episodes'])+1); //集数/章节
			} else {
	    		//插入小说
		    	$booksql = "INSERT INTO `vv_book` (`title`, `cateids`, `bookcate`, `send`, `author`, `summary`, `cover_pic`, `detail_pic`, `sort`, `status`, `free_type`, `episodes`, `pay_num`, `reader`, `likes`, `collect`, `is_new`, `is_recomm`, `create_time`, `update_time`, `readnum`, `chargenum`, `chargemoney`, `share_title`, `share_pic`, `share_desc`) VALUES ('$bookname', '$sstype', '$tstype',$send, '$author', '$des', '$litpic', '$litpic', 1, $status, 2, 1,10, $reads_book, $dz_book, $sc_book, 1, 1, $time, $time, 0, 0, 0, '$bookname', '', '$litpic')";
		   		$conn->query($booksql);
		   		// $_SESSION['xiaoshuo']['bid'] = mysqli_insert_id($conn); //bid
		   		file_put_contents('./bookbid.txt', mysqli_insert_id($conn)); //bid
				file_put_contents('./bookiepisodesd.txt', 1); //集数/章节
			}
    	}
		
    }
    $mhid = file_get_contents('./bookbid.txt'); //bid
	$jino = file_get_contents('./bookiepisodesd.txt'); // 集数/章节
	$before = $jino - 1;
	$next = $jino + 1;

	//新增小说（集数/章节）数据
	$zjsql="INSERT INTO `vv_book_episodes` (`bid`, `title`, `ji_no`, `info`, `readnums`, `likes`, `before`, `next`, `money`, `create_time`, `update_time`, `huoche_url`, `huoche_id`) VALUES ($mhid, '$mhtitle', $jino, '$mhbody', 0, 50, $before, $next, '$jine', $time, $time, '$huoche_url', '$huoche_id');";
 	$result = $conn->query($zjsql);
 	$lastid = mysqli_insert_id($conn);
 	if ($lastid > 0) {
 		//bid是否改变
		if ($duibibid != $mhid) {
			file_put_contents('./jinosort.txt', $mhid.',', FILE_APPEND); //小说id

		}
		$upsql="UPDATE vv_book SET episodes=$jino WHERE id=$mhid;";
		$conn->query($upsql);
		file_put_contents('./bookiepisodesd.txt', $next); //集数/章节
		echo '添加小说成功';
 	} else {
 		echo 'fail:添加小说失败'; 
 	}
	exit;
}


//添加听书
if($sl==3){
	if($bookname!=""){
		$stype="SELECT * FROM `vv_yook` where title='$bookname'";
    	$result=$conn->query($stype);
  	 	if($result->num_rows>0){
	 		while ($row = mysqli_fetch_assoc($result)){
	 			$mhid=$row['id'];
     			$cid=$row['cid'];
     			$jino=$row['episodes']+1;
	 			$before=$jino-1;
	   			$next=$jino+1;
				$zjsql="INSERT INTO `vv_yook_episodes` (`yid`, `title`, `ji_no`, `info`, `likes`, `before`, `next`, `money`, `create_time`, `update_time`) VALUES
				($mhid, '$mhtitle', $jino, '$mhbody', 50, $before, $next, $jine, $time, $time);";
     			$result=$conn->query($zjsql);
       			$lastid=mysqli_insert_id($conn);
	   			if($lastid>1){ 
	  				$upsql="UPDATE vv_yook SET episodes=$jino WHERE id=$mhid;";
         			$conn->query($upsql);
		   			echo '添加听书成功';
		   			return;
	   			}else{
		 			echo 'fail:添加听书失败'; 
     				return;		 
	   				}
			}
			return;		
		 }else{
 			$booksql="INSERT INTO `vv_yook` (`title`, `cateids`, `bookcate`, `send`, `author`, `summary`, `cover_pic`, `detail_pic`, `sort`, `status`, `free_type`, `episodes`, `pay_num`, `reader`, `likes`, `collect`, `is_new`, `is_recomm`, `create_time`, `update_time`, `readnum`, `chargenum`, `chargemoney`) VALUES
			('$bookname', '$tstype', '$sstype',$send, '$author', '$des', '$litpic', '$litpic', 1, 2, 2, 1,10, $reads_book, $dz_book, $sc_book, 1, 1, $time, $time, 0, 0, 0)";
       		$result=$conn->query($booksql);
       		$lasmhid=mysqli_insert_id($conn);
	  		$before=$jino-1;
	  	 	$next=$jino+1;
			$zjsql="INSERT INTO `vv_yook_episodes` (`yid`, `title`, `ji_no`, `info`, `likes`, `before`, `next`, `money`, `create_time`, `update_time`) VALUES
			($lasmhid, '$mhtitle', 1, '$mhbody', 0, $dzzj_book, $before, $next, $jine, $time, $time);";
			echo $booksql;
       		$result=$conn->query($zjsql);
       		$lastid=mysqli_insert_id($conn);
	   		if($lasmhid>1){
		   		echo '添加小说成功';
	   		}else{
		 		echo 'fail:添加小说失败';  
	   		}
   		}
	}else{
		echo 'fail:书籍名不能为空';
	}
}

//添加动漫
if($sl==4){
	if($bookname!=""){
		$stype="SELECT * FROM `vv_video` where title='$bookname'";
    	$result=$conn->query($stype);
  	 	if($result->num_rows>0){
	 		while ($row = mysqli_fetch_assoc($result)){
	 			$mhid=$row['id'];
     			$cid=$row['cid'];
     			$jino=$row['episodes']+1;
	 			$before=$jino-1;
	   			$next=$jino+1;
				$zjsql="INSERT INTO `vv_video_episodes` (`vid`, `title`, `ji_no`, `info`, `likes`, `before`, `next`, `money`, `create_time`, `update_time`) VALUES
				($mhid, '$mhtitle', $jino, '$mhbody', 50, $before, $next, $jine, $time, $time);";
     			$result=$conn->query($zjsql);
       			$lastid=mysqli_insert_id($conn);
	   			if($lastid>1){ 
	  				$upsql="UPDATE vv_video SET episodes=$jino WHERE id=$mhid;";
         			$conn->query($upsql);
		   			echo '添加动漫成功';
		   			return;
	   			}else{
		 			echo 'fail:添加动漫失败'; 
     				return;		 
	   				}
			}
			return;		
		 }else{
 			$booksql="INSERT INTO `vv_video` (`title`, `cateids`, `bookcate`, `send`, `author`, `summary`, `cover_pic`, `detail_pic`, `sort`, `status`, `free_type`, `episodes`, `pay_num`, `reader`, `likes`, `collect`, `is_new`, `is_recomm`, `create_time`, `update_time`, `readnum`, `chargenum`, `chargemoney`) VALUES
			('$bookname', '$tstype', '$sstype',$send, '$author', '$des', '$litpic', '$litpic', 1, 2, 2, 1,10, $reads_book, $dz_book, $sc_book, 1, 1, $time, $time, 0, 0, 0)";
       		$result=$conn->query($booksql);
       		$lasmhid=mysqli_insert_id($conn);
	  		$before=$jino-1;
	  	 	$next=$jino+1;
			$zjsql="INSERT INTO `vv_video_episodes` (`vid`, `title`, `ji_no`, `info`, `likes`, `before`, `next`, `money`, `create_time`, `update_time`) VALUES
			($lasmhid, '$mhtitle', 1, '$mhbody', 0, $dzzj_book, $before, $next, $jine, $time, $time);";
			echo $booksql;
       		$result=$conn->query($zjsql);
       		$lastid=mysqli_insert_id($conn);
	   		if($lasmhid>1){
		   		echo '添加小说成功';
	   		}else{
		 		echo 'fail:添加小说失败';  
	   		}
   		}
	}else{
		echo 'fail:书籍名不能为空';
	}
}
$conn->close();