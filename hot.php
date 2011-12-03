<?php
require_once("include/bittorrent.php");
dbconn(true);
require_once(get_langfile_path("torrents.php"));
loggedinorreturn();
parked();

//check searchbox

if ($_GET['cat']){
	$where="WHERE torrents.picktype='hot' ";
   	switch($_GET['cat']) {
		case '1': $where=$where . " AND ( torrents.category=401 OR torrents.category=413 OR torrents.category=414 OR torrents.category=415 )" ; break;
		case '2': $where=$where . " AND ( torrents.category=402 OR torrents.category=416 OR torrents.category=417 OR torrents.category=418 )" ; break;
		case '3': $where=$where . " AND ( torrents.category=405 OR torrents.category=427 OR torrents.category=428 OR torrents.category=429 )" ; break;
		case '4': $where=$where . " AND ( torrents.category=410  )" ; break;
		case '5': $where=$where . " AND ( torrents.category=403 OR torrents.category=419 OR torrents.category=420 OR torrents.category=421  )" ; break;
		case '6': $where=$where . " AND ( torrents.category=412 OR torrents.category=409 )" ; break;
		case '7': $where=$where . " AND ( torrents.category=407  )" ; break;
		case '8': $where=$where . " AND ( torrents.category=408 OR torrents.category=422 OR torrents.category=423 OR torrents.category=424 OR torrents.category=425 )" ; break;
		case '9': $where=$where . " AND ( torrents.category=404  )" ; break;
		case '10': $where=$where . " AND ( torrents.category=411  )" ; break;
		case '11': $where=$where . " AND ( torrents.category=406  )" ; break;
		
		default:  $where=$where." AND torrents.category=".$_GET['cat'] ; break;
	}

} 


if ($_GET['sp']){
	$where="WHERE torrents.sp_state=";
   	switch($_GET['sp']) {
		case '1': $where=$where . "1" ; break;
		case '2': $where=$where . "2" ; break;
		case '3': $where=$where . "3" ; break;
		case '4': $where=$where . "4" ; break;
		case '5': $where=$where . "5" ; break;
		case '6': $where=$where . "6" ; break;
		case '7': $where=$where . "7" ; break;
		
		default:  $where=$where.$_GET['sp'] ; break;
	}

} 
if ($_GET['time']){
	$where="WHERE torrents.picktype='hot' ";
	switch($_GET['time']) {
		case '1': $where=$where . " AND DATEDIFF('".date("Y-m-d H:i:s")."',torrents.added)<=3"; break;
		case '2': $where=$where . " AND DATEDIFF('".date("Y-m-d H:i:s")."',torrents.added)<=30"; break;
		case '3': $where=$where . " AND DATEDIFF('".date("Y-m-d H:i:s")."',torrents.added)<=90"; break;
		default:  $where=""; break;
	}
} 


$sql = "SELECT COUNT(*) FROM torrents ".$where;
$res = sql_query($sql) or die(mysql_error());
$count = 0;
while($row = mysql_fetch_array($res))
	$count += $row[0];

$torrentsperpage = 50;
$addparam = $pagerlink;
if ($_GET['cat']){
	$addparam="cat=".$_GET['cat']."&";
}
if ($_GET['sp']){
	$addparam="sp=".$_GET['sp']."&";
}
if ($_GET['time']){
	$addparam="time=".$_GET['time']."&";
}
list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "?" . $addparam);
if ($_GET['cat'] || $_GET['sp'] || $_GET['time']){
	$orderby="ORDER BY torrents.seeders  DESC";
}
if (!$_GET['cat'] && !$_GET['sp'] && !$_GET['time']){
	$orderby="ORDER BY torrents.seeders  DESC";
}

$query = "SELECT torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.cache_stamp FROM torrents LEFT JOIN categories ON torrents.category=categories.id $where $orderby $limit";

//echo $query;
$res = sql_query($query) or die(mysql_error());
stdhead($lang_torrents['head_torrents']);
print("<table  border='0'> <tr><td width='175' valign='top'>");
print("<table width=\"175\" class=\"torrents\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" al><tr height='27'><td class='colhead'>热门推荐</td></tr><tr ><td class=\"text\" align=\"left\">");
print(" <div>热门资源: </div><B><div><a href=?cat=>&nbsp;&nbsp;<U> 最多做种</U></a></div>&nbsp;&nbsp;<a href=?cat=1><U> 电 影 </U></a>&nbsp;&nbsp;<a href=?cat=2><U> 剧 集 </U></a>&nbsp;&nbsp;<a href=?cat=3><U> 动 漫 </U></a>&nbsp;&nbsp;<a href=?cat=4>&nbsp;<U>游 戏 </U></a>&nbsp;&nbsp;<a href=?cat=5><U> 综 艺 </U></a></B>");
print("&nbsp;&nbsp;<B><a href=?cat=6><U> 资 料 </U></a>&nbsp;&nbsp;<a href=?cat=7>&nbsp;<U>体 育 </U></a>&nbsp;&nbsp;<a href=?cat=8><U> 音 乐  </U></a>&nbsp;&nbsp;<a href=?cat=9><U> 纪录片 </U></a>&nbsp;&nbsp;<a href=?cat=10>&nbsp;<U>软 件 </U></a>&nbsp;&nbsp;<a href=?cat=11><U> MTV </U></a></B>");
print("</br></br>");
print("<div>最近热门: </div><B><a href=?time=1>&nbsp;&nbsp;<U>3天</U></a>&nbsp;&nbsp;<a href=?time=2><U>1个月</U></a>&nbsp;&nbsp;<a href=?time=3><U>3个月</U></a>&nbsp;&nbsp;</B>");
print("</br></br>");
print("<div>促&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;销: </div><B><a href=?sp=2><font class='free'>&nbsp;&nbsp;<U> 免 费 </U></font></a>&nbsp;&nbsp;<a href=?sp=3><font class='twoup'><U>2x上传</U></font></a><div>&nbsp;&nbsp;<a href=?sp=4><font class='twoupfree'><U>免费&2x上传</U> </font></a></div>&nbsp;&nbsp;<a href=?sp=5><font class='halfdown'><U>50%下载</U></font></a>&nbsp;&nbsp;&nbsp;&nbsp;<div><a href=?sp=6><font class='twouphalfdown'>&nbsp;&nbsp;<U>50%下载&2x上传</U></font></a></div>&nbsp;&nbsp;<a href=?sp=7><font class='thirtypercent'><U>30%下载</U></font></a></B>");
print("</td></tr>");
print("</table></td><td width='940' valign='top'>");

torrenttable($res, "torrents");

print("</td></tr></table>");
print($pagerbottom);
stdfoot();
?>

