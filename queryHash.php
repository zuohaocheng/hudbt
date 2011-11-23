<html>
<head>
<title>HUDBT 存档种子直通车</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

<?php

$hash = isset($_GET['hash']) ? trim($_GET['hash']) : '';

$message = '';
if(!isset($hash[40]) && isset($hash[39])) {
	$message .= '当前查询的 HASH 为：'.$hash.'<br /><br /><span style="color: blue">';
	
	$cgbtInfo = get_cgbt_torrent($hash);
	if(!empty($cgbtInfo)) {
		if($cgbtInfo['newbt'] == 'yes') {
			$message .= '种子需要迁移，进入 <a href="/old/details.php?tid='.$cgbtInfo['id'].'" target="_blank" style="color: green">种子页面</a> 迁移种子或者将连接提交到论坛主题 <a href="/forums.php?action=viewtopic&forumid=3&topicid=318" style="color: green">新手种子迁移需求汇总</a> 请求迁移。';
		} else if($cgbtInfo['newbt'] == 'no' && $cgbtInfo['transfered'] == 0) {
			$message .= '种子已重新发布，进入 <a href="/details.php?id='.$cgbtInfo['nexusId'].'&hit=1" target="_blank" style="color: green">种子页面</a> 重新下载种子，并添加到 utorrent 继续任务。';
		} else {
			$message .= '种子已经被迁移过，打开 utorrent 开始任务吧！在 Track 选项卡里更新 track 或者关闭、重启任务。';
		}
	} else {
		$message .= '存档平台中未找到你所查询的种子。';
	}
	
	$message .= '<span>';
} else {
	$message .= '<span style="color: blue">';
	if(empty($_GET['hash'])) {
		$message .= '<span style="color: blue">输入 HASH 码以查询存档平台种子状态。<span>';
	} else {
		$message .= '有效的 HASH 码由 40 位 0~F 组成，刚才输入的为错误的 HASH 码长度为：'.strlen($hash);
	}
	$message .= '<span>';
}




function get_cgbt_torrent($info_hash) {

	$host     = 'localhost'; 			// MySQL hostname or IP address
	$username = 'root'; 				// MySQL user
	$password = 'p42918635'; 			// MySQL password
	$dbname   = 'pbt';		 			// MySQL db-name

	$sql = "SELECT id, newbt, h24 as transfered FROM torrents WHERE info_hash=0x".$info_hash;

	$link = mysqli_connect($host, $username, $password, $dbname);
	$res  = mysqli_query($link, $sql);
	$row  = mysqli_fetch_array($res);
	unset($res);
	
	if($row['newbt'] == 'no' && $row['transfered'] == 0) {
		$sql  = "SELECT id FROM tmpT WHERE cgbt_hash=0x".$info_hash;
		$res  = mysqli_query($link, $sql);
		$row2 = mysqli_fetch_array($res);
		$row['nexusId'] = $row2[0];
	}
	
	return $row;
}
?>

<h1>本页面仅用于查询存档种子的信息</h1>
<form action="/queryHash.php" method="get">
<label for="hash">输入 HASH 码：</label><input type="text" name="hash" /><input type="submit" value="查询" />
</form>
<h2>说明</h2>
<p>如果发现 tracker 提示需要 <span style="color: red">迁移种子</span> 或者需要 <span style="color: red">重新下载种子</span>，请在这里查找种子信息。</p>
<h2>结果</h2>
<?php echo '<p>'.$message.'</p>'; ?>
<br />
<h2>utorrent 中获取 HASH 码方法</h2>
<p>1、点开 <span style="color: red">Track</span> 选项卡查看错误提示：</p>
<img src="./pic/step_1.png" title="查看错误提示" style="border: 1px solid blue; " />
<p>2、点开 <span style="color: red">常规</span>，即第一个选项卡找到 <span style="color: red">hash</span>，右键弹出提示“复制”，点击提示即可复制</p>
<img src="./pic/step_2.png" title="获取 HASH 值" style="border: 1px solid blue; " />
<p></p>
<p></p>
</body>
</html>