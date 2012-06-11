<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
if (isset($_GET['del']))
{
	if (is_valid_id($_GET['del']))
	{
		if((get_user_class() >= $sbmanage_class))
		{
			sql_query("DELETE FROM shoutbox WHERE id=".mysql_real_escape_string($_GET['del']));
		}
	}
}
$where=$_GET["type"];
$refresh = 120;
?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Refresh" content="<?php echo $refresh?>; url=<?php echo get_protocol_prefix() . $BASEURL?>/shoutbox.php?type=<?php echo $where?>">
<?php
  echo get_load_uri('css');
  echo get_load_uri('js');
$startcountdown = "startcountdown(".$refresh.")";
?>
</head>
<body class='inframe' <?php if ($_GET["type"] != "helpbox"){?> onload="<?php echo $startcountdown?>" <?php } else {?> onload="hbquota()" <?php } ?>>
<?php
if($_GET["sent"]=="yes"){
if(!$_GET["shbox_text"])
{
	$userid=0+$CURUSER["id"];
}
else
{
	if($_GET["type"]=="helpbox")
	{
		if ($showhelpbox_main != 'yes'){
			write_log("Someone is hacking shoutbox. - IP : ".getip(),'mod');
			die($lang_shoutbox['text_helpbox_disabled']);
		}
		$userid=0;
		$type='hb';
	}
	elseif ($_GET["type"] == 'shoutbox')
	{
		$userid=0+$CURUSER["id"];
		if (!$userid){
			write_log("Someone is hacking shoutbox. - IP : ".getip(),'mod');
			die($lang_shoutbox['text_no_permission_to_shoutbox']);
		}
		if ($_GET["toguest"])
			$type ='hb';
		else $type = 'sb';
	}
	$date=sqlesc(time());
	$text=trim($_GET["shbox_text"]);

	sql_query("INSERT INTO shoutbox (userid, date, text, type) VALUES (" . sqlesc($userid) . ", $date, " . sqlesc($text) . ", ".sqlesc($type).")") or sqlerr(__FILE__, __LINE__);
	print "<script type=\"text/javascript\">parent.document.forms['shbox'].shbox_text.value='';</script>";
}
}

$limit = 70; 
if ($where == "helpbox")
{
$sql = "SELECT * FROM shoutbox WHERE type='hb' ORDER BY date DESC LIMIT ".$limit;
}
elseif ($CURUSER['hidehb'] == 'yes' || $showhelpbox_main != 'yes'){
$sql = "SELECT * FROM shoutbox WHERE type='sb' ORDER BY date DESC LIMIT ".$limit;
}
elseif ($CURUSER){
$sql = "SELECT * FROM shoutbox ORDER BY date DESC LIMIT ".$limit;
}
else {
die("<h1>".$lang_shoutbox['std_access_denied']."</h1>"."<p>".$lang_shoutbox['std_access_denied_note']."</p></body></html>");
}
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
print("\n");
else
{
	print("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='left'>\n");

	while ($arr = mysql_fetch_assoc($res))
	{
		if (get_user_class() >= $sbmanage_class) {
			$del="[<a href=\"shoutbox.php?del=".$arr[id]."\">".$lang_shoutbox['text_del']."</a>]";
		}
		if ($arr["userid"]) {
			$username = get_username($arr["userid"],false,true,true,true,false,false,"",true);
			if ($_GET["type"] != 'helpbox' && $arr["type"] == 'hb')
				$username .= $lang_shoutbox['text_to_guest'];
			}
		else $username = $lang_shoutbox['text_guest'];
			$time = strftime("%m.%d %H:%M",$arr["date"]);
		print("<tr><td class=\"shoutrow\"><span class='date'>[".$time."]</span> ".
$del ." ". $username." " . format_comment($arr["text"],true,false,true,true,600,true,false)."
</td></tr>\n");
	}
	print("</table>");
}
?>
</body>
</html>
