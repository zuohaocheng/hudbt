<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
if(!permissionAuth("viewkeepers",$CURUSER['usergroups'],$CURUSER['class'])){
	permissiondenied();	
}
$year=0+$_GET['year'];
if (!$year || $year < 2000)
$year=date('Y');
$month=0+$_GET['month'];
if (!$month || $month<=0 || $month>12)
$month=date('m');
/*$order=$_GET['order'];
if (!in_array($order, array('username', 'num_total', 'torrent_count', 'storing_total_time')))
	$order='username';
if ($order=='username')
	$order .=' ASC';
else $order .= ' DESC';*/
stdhead($lang_keepers['head_keepers']);

$year2 = substr($datefounded, 0, 4);
$yearfounded = ($year2 ? $year2 : 2007);
$yearnow=date("Y");

$timestart=strtotime($year."-".$month."-01 00:00:00");
$sqlstarttime=date("Y-m-d H:i:s", $timestart);
$timeend=strtotime("+1 month", $timestart);
$sqlendtime=date("Y-m-d H:i:s", $timeend);

print("<h1 align=\"center\">".$lang_keepers['text_keepers']." - ".date("Y-m",$timestart)."</h1>");

$yearselection="<select name=\"year\">";
for($i=$yearfounded; $i<=$yearnow; $i++)
	$yearselection .= "<option value=\"".$i."\"".($i==$year ? " selected=\"selected\"" : "").">".$i."</option>";
$yearselection.="</select>";

$monthselection="<select name=\"month\">";
for($i=1; $i<=12; $i++)
	$monthselection .= "<option value=\"".$i."\"".($i==$month ? " selected=\"selected\"" : "").">".$i."</option>";
$monthselection.="</select>";

?>
<div>
<form method="get" action="?">
<span>
<?php echo $lang_keepers['text_select_month']?><?php echo $yearselection?>&nbsp;&nbsp;<?php echo $monthselection?>&nbsp;&nbsp;<input type="submit" value="<?php echo $lang_keepers['submit_go']?>" />
</span>
</form>
</div>

<?php
$keepers_res = sql_query("SELECT user_id FROM users_usergroups WHERE usergroup_id = 1 AND role = 'member' OR role = 'boss' AND removed_by IS NULL AND removed_date IS NULL") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($keepers_res)==0) {
	print("<p align=\"center\">".$lang_keepers['text_no_keepers_yet']."</p>");
}
else {
	print("<table cellpadding=\"5\" class=\"no-vertical-line\" id=\"uploaders-works\"><thead><tr>");
	print("<th>".$lang_keepers['col_username']."</th>");
	print("<th>".$lang_keepers['col_storing_size_total']."</th>");
	print("<th>".$lang_keepers['col_storing_time_total']."</th>");
	print("<th>".$lang_keepers['col_storing_num_total']."</th>");
	print("<th>".$lang_keepers['col_estimate_bonus']."</th>");
	print("<th>".$lang_keepers['col_cur_storing']."</th>");
	print("</tr></thead><tbody>");

	while($keeperid = mysql_fetch_assoc($keepers_res)){
		$storingInfoRes = sql_query("SELECT SUM(DISTINCT size) as size_total, SUM(out_seedtime-in_seedtime) as seedtime_total, COUNT(DISTINCT torrent_id) as num_total FROM storing_records 
		JOIN torrents ON storing_records.torrent_id = torrents.id WHERE keeper_id = $keeperid[user_id] AND storing_records.in_date > ".sqlesc($sqlstarttime)." AND storing_records.in_date < ".sqlesc($sqlendtime)) or sqlerr(__FILE__,__LINE__);	
		$storingInfo = mysql_fetch_assoc($storingInfoRes);
		print("<tr>");
		$userid = $keeperid['user_id'];
		$sizeTotal = $storingInfo['size_total'];
		$totalTime = $storingInfo['seedtime_total'];
		print("<td class=\"colfollow\">".get_username($userid, false, true, true, false, false, true)."</td>");
		print("<td class=\"colfollow\">".($sizeTotal>=0 ? mksize($sizeTotal) : "0")."</td>");
		print("<td class=\"colfollow\">".(($totalTime > 0 ?mkprettytime($totalTime) : 0)."</td>"));
		print("<td class=\"colfollow\">".$storingInfo['num_total']."</td>");
		print("<td class=\"colfollow\">".ceil($sizeTotal*$ka_bonus + $totalTime*$kb_bonus/1e6)."</td>");
		print("<td class=\"colfollow\">"."<a href=\"userdetails.php?id=".$userid."#pica5\">".$lang_keepers['txt_click_to_view']."</a>"."</td>");
		print("</tr>");
	}
	
	print("</tbody></table>");
}

stdfoot();

