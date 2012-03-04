<?php
/**
 * 清除对同济 IP 出口[59.175.216.190]的锁定
 * 只有部分人可用该工具
 */
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
$whiteList = array(1, 2, 44185, 32996);
// 素寒潮 44185
// care 32996
if(!in_array($CURUSER['id'], $whiteList)) {
	stderr("Error", "Permission denied.");
}
?>
清除对同济出口 IP [59.175.216.190] 的锁定：
<?php
if($_GET['action'] == 'clean') {
	$dir = '/data/www/hudbt/sandbox/brucewolf';
	$file = $dir.'/unlock.log';
	$contents = "\n".date('Y-m-d H:i:s').' '.$CURUSER['username'].' 执行清理缓存';
	$rsl = file_put_contents($file, $contents, FILE_APPEND);
	$isWritable = is_writable($dir);
//	var_dump($isWritable, $rsl, $file, $contents);
	$sqlGetId = 'DELETE FROM loginattempts WHERE banned=\'yes\' AND `ip`=\'59.175.216.190\'';
	sql_query($sqlGetId);
	if (!mysql_affected_rows()) {
		echo 'IP 并未被锁定，不用刷新。';
	} else {
		$contents = $CURUSER['username'].' 成功解除锁定。';
		file_put_contents($file, $contents, FILE_APPEND);
		echo '已清除锁定，关闭本页面吧 '.$CURUSER['username'].' 。';
	}
} else {
	echo '<a href="unlockTongjiIP.php?action=clean">执行</a>';
}