<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
checkPrivilegePanel();

$year=0+$_GET['year'];
if (!$year || $year < 2000)
$year=date('Y');
$month=0+$_GET['month'];
if (!$month || $month<=0 || $month>12)
$month=date('m');
stdhead($lang_uploaders['head_uploaders']);

$year2 = substr($datefounded, 0, 4);
$yearfounded = ($year2 ? $year2 : 2007);
$yearnow=date("Y");

$timestart=strtotime($year."-".$month."-01 00:00:00");
$sqlstarttime=date("Y-m-d H:i:s", $timestart);
$timeend=strtotime("+1 month", $timestart);
$sqlendtime=date("Y-m-d H:i:s", $timeend);

print("<h1 align=\"center\">".$lang_uploaders['text_uploaders']." - ".date("Y-m",$timestart)."</h1>");

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
<?php echo $lang_uploaders['text_select_month']?><?php echo $yearselection?>&nbsp;&nbsp;<?php echo $monthselection?>&nbsp;&nbsp;<input type="submit" value="<?php echo $lang_uploaders['submit_go']?>" />
</span>
</form>
</div>

<?php
  $num = get_row_count('users', "WHERE class >= ?", [UC_UPLOADER]);
if (!$num) {
	print("<p align=\"center\">".$lang_uploaders['text_no_uploaders_yet']."</p>");
}
else {
	print("<table cellpadding=\"5\" class=\"no-vertical-line\" id=\"uploaders-works\"><thead><tr>");
	print("<th>".$lang_uploaders['col_username']."</th>");
	print("<th>".$lang_uploaders['col_torrents_size']."</th>");
	print("<th>".$lang_uploaders['col_torrents_num']."</th>");
	print("<th>".$lang_uploaders['col_last_upload_time']."</th>");
	print("<th>".$lang_uploaders['col_last_upload']."</th>");
	print("</tr></thead><tbody>");
	$res = sql_query('SELECT users.id AS userid, COUNT(1) AS torrent_count, SUM(torrents.size) AS torrent_size FROM users LEFT JOIN torrents ON users.id=torrents.owner AND torrents.added >? AND torrents.added <? WHERE users.class>=? GROUP BY users.id ORDER BY torrent_count DESC', [$sqlstarttime, $sqlendtime, UC_UPLOADER]);
	while($row = _mysql_fetch_array($res))
	{
	  if ($row['torrent_size'] === null) {
	    $row['torrent_count'] = 0;
	  }
		$res2 = sql_query("SELECT torrents.id, torrents.name, torrents.added FROM torrents WHERE owner=".$row['userid']." ORDER BY id DESC LIMIT 1");
		$row2 = _mysql_fetch_array($res2);
		print("<tr>");
		print("<td class=\"colfollow\">".get_username($row['userid'])."</td>");
		print("<td class=\"colfollow\">".($row['torrent_size'] ? mksize($row['torrent_size']) : "0")."</td>");
		print("<td class=\"colfollow\">".$row['torrent_count']."</td>");
		print("<td class=\"colfollow\">".($row2['added'] ? gettime($row2['added']) : $lang_uploaders['text_not_available'])."</td>");
		print("<td class=\"colfollow\">".($row2['name'] ? "<a href=\"details.php?id=".$row2['id']."\">".htmlspecialchars($row2['name'])."</a>" : $lang_uploaders['text_not_available'])."</td>");
		print("</tr>");
		unset($row2);
	}
	print("</tbody></table>");
}

print("<h1 align=\"center\">".$lang_uploaders['text_mods']." - ".date("Y-m",$timestart)."</h1>");
$num= get_row_count('users', " WHERE class >= ".UC_MODERATOR);
if (!$num) {
  print("<p align=\"center\">".$lang_uploaders['text_no_uploaders_yet']."</p>");
}
else {
  print("<table cellpadding=\"5\" class=\"no-vertical-line\" id=\"mods-works\"><thead><tr>");
  print("<th>".$lang_uploaders['col_username']."</th>");
  print("<th>".$lang_uploaders['col_log_num']."</th>");
  print("<th>管理组信箱</th>");
  print("<th>举报</th>");
  print("<th>作弊</th>");
  print("</tr></thead><tbody>");
  $res = sql_query("SELECT id, username FROM users WHERE class >= ".UC_MODERATOR) or sqlerr(__FILE__, __LINE__);

  $hasupuserid=array();
  while($row = _mysql_fetch_assoc($res)) {
    $username = $row['username'];
    $r = get_row_count('sitelog', "WHERE added > ".sqlesc($sqlstarttime)." AND added < ".sqlesc($sqlendtime)." AND txt LIKE '%" . $username . "%'");
    $msg = get_row_count('staffmessages', 'WHERE answeredby = ? AND added > ? AND added < ?', [$row['id'], $sqlstarttime, $sqlendtime]);
    $reports = get_row_count('reports', 'WHERE dealtby = ? AND added > ? AND added < ?', [$row['id'], $sqlstarttime, $sqlendtime]);
    $cheaters = get_row_count('cheaters', 'WHERE dealtby = ? AND added > ? AND added < ?', [$row['id'], $sqlstarttime, $sqlendtime]);
	
    echo '<tr><td>', get_username($row['id']), '</td><td><a href="log.php?action=dailylog&amp;query=' . $username . '">' . $r . '</a></td><td>' . $msg . '</td><td>' . $reports . '</td><td>' . $cheaters . '</td></tr>';
  }
  echo '</tbody></table>';
}

stdfoot();

