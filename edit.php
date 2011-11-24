<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path("edit.php", false, 'chs'));
loggedinorreturn();

$id = 0 + $_GET['id'];
if (!$id)
	die();

$res = sql_query("SELECT torrents.*, categories.mode as cat_mode FROM torrents LEFT JOIN categories ON category = categories.id WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row) die();

if ($enablespecial == 'yes' && get_user_class() >= $movetorrent_class)
	$allowmove = true; //enable moving torrent to other section
else $allowmove = false;

$sectionmode = $row['cat_mode'];
if ($sectionmode == $browsecatmode)
{
	$othermode = $specialcatmode;
	$movenote = $lang_edit['text_move_to_special'];
}
else
{
	$othermode = $browsecatmode;
	$movenote = $lang_edit['text_move_to_browse'];
}

$showsource = (get_searchbox_value($sectionmode, 'showsource') || ($allowmove && get_searchbox_value($othermode, 'showsource'))); //whether show sources or not
$showmedium = (get_searchbox_value($sectionmode, 'showmedium') || ($allowmove && get_searchbox_value($othermode, 'showmedium'))); //whether show media or not
$showcodec = (get_searchbox_value($sectionmode, 'showcodec') || ($allowmove && get_searchbox_value($othermode, 'showcodec'))); //whether show codecs or not
$showstandard = (get_searchbox_value($sectionmode, 'showstandard') || ($allowmove && get_searchbox_value($othermode, 'showstandard'))); //whether show standards or not
$showprocessing = (get_searchbox_value($sectionmode, 'showprocessing') || ($allowmove && get_searchbox_value($othermode, 'showprocessing'))); //whether show processings or not
$showteam = (get_searchbox_value($sectionmode, 'showteam') || ($allowmove && get_searchbox_value($othermode, 'showteam'))); //whether show teams or not
$showaudiocodec = (get_searchbox_value($sectionmode, 'showaudiocodec') || ($allowmove && get_searchbox_value($othermode, 'showaudiocodec'))); //whether show audio codecs or not

stdhead($lang_edit['head_edit_torrent'] . "\"". $row["name"] . "\"");

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && get_user_class() < $torrentmanage_class)) {
	print("<h1 align=\"center\">".$lang_edit['text_cannot_edit_torrent']."</h1>");
	print("<p>".$lang_edit['text_cannot_edit_torrent_note']."</p>");
}
else {
	print("<form method=\"post\" id=\"compose\" name=\"edittorrent\" action=\"takeedit.php\" enctype=\"multipart/form-data\">");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"940\">\n");
	print("<tr><td class='colhead' colspan='2' align='center'>".htmlspecialchars($row["name"])."</td></tr>");
	tr($lang_edit['row_torrent_name']."<font color=\"red\">*</font>", "<input type=\"text\" style=\"width: 650px;\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" />", 1);
	if ($smalldescription_main == 'yes')
		tr($lang_edit['row_small_description'], "<input type=\"text\" style=\"width: 650px;\" name=\"small_descr\" value=\"" . htmlspecialchars($row["small_descr"]) . "\" />", 1);

	get_external_tr($row["url"]);

	if ($enablenfo_main=='yes')
		tr($lang_edit['row_nfo_file'], "<font class=\"medium\"><input type=\"radio\" name=\"nfoaction\" value=\"keep\" checked=\"checked\" />".$lang_edit['radio_keep_current'].
	"<input type=\"radio\" name=\"nfoaction\" value=\"remove\" />".$lang_edit['radio_remove'].
	"<input id=\"nfoupdate\" type=\"radio\" name=\"nfoaction\" value=\"update\" />".$lang_edit['radio_update']."</font><br /><input type=\"file\" name=\"nfo\" onchange=\"document.getElementById('nfoupdate').checked=true\" />", 1);
	print("<tr><td class=\"rowhead\">".$lang_edit['row_description']."<font color=\"red\">*</font></td><td class=\"rowfollow\">");
	textbbcode("edittorrent","descr",($row["descr"]), false);
	print("</td></tr>");
	$s = "<select name=\"type\" id=\"oricat\">";

	$cats = genrelist($sectionmode);
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
		$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	if ($allowmove){
		$s2 = "<select name=\"type\" id=newcat disabled>\n";
		$cats2 = genrelist($othermode);
		foreach ($cats2 as $subrow) {
			$s2 .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s2 .= " selected=\"selected\"";
			$s2 .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		$s2 .= "</select>\n";
		$movecheckbox = "<input type=\"checkbox\" id=movecheck name=\"movecheck\" value=\"1\" onclick=\"disableother2('oricat','newcat')\" />";
	}
	tr($lang_edit['row_type']."<font color=\"red\">*</font>", $s.($allowmove ? "&nbsp;&nbsp;".$movecheckbox.$movenote.$s2 : ""), 1);
	if ($showsource || $showmedium || $showcodec || $showaudiocodec || $showstandard || $showprocessing){
		if ($showsource){
			$source_select = torrent_selection($lang_edit['text_source'],"source_sel","sources",$row["source"]);
		}
		else $source_select = "";

		if ($showmedium){
			$medium_select = torrent_selection($lang_edit['text_medium'],"medium_sel","media",$row["medium"]);
		}
		else $medium_select = "";

		if ($showcodec){
			$codec_select = torrent_selection($lang_edit['text_codec'],"codec_sel","codecs",$row["codec"]);
		}
		else $codec_select = "";

		if ($showaudiocodec){
			$audiocodec_select = torrent_selection($lang_edit['text_audio_codec'],"audiocodec_sel","audiocodecs",$row["audiocodec"]);
		}
		else $audiocodec_select = "";

		if ($showstandard){
			$standard_select = torrent_selection($lang_edit['text_standard'],"standard_sel","standards",$row["standard"]);
		}
		else $standard_select = "";

		if ($showprocessing){
			$processing_select = torrent_selection($lang_edit['text_processing'],"processing_sel","processings",$row["processing"]);
		}
		else $processing_select = "";

		tr($lang_edit['row_quality'], $source_select . $medium_select . $codec_select . $audiocodec_select. $standard_select . $processing_select, 1);
	}

	if ($showteam){
		if ($showteam){
			$team_select = torrent_selection($lang_edit['text_team'],"team_sel","teams",$row["team"]);
		}
		else $showteam = "";

		tr($lang_edit['row_content'],$team_select,1);
	}
	tr($lang_edit['row_check'], "<input type=\"checkbox\" name=\"visible\"" . ($row["visible"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" /> ".$lang_edit['checkbox_visible']."&nbsp;&nbsp;&nbsp;".(get_user_class() >= $beanonymous_class || get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"anonymous\"" . ($row["anonymous"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" />".$lang_edit['checkbox_anonymous_note']."&nbsp;&nbsp;&nbsp;" : "").(get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"yes\" /> ".$lang_edit['checkbox_banned'] : ""), 1);
	if (get_user_class()>= $torrentsticky_class || (get_user_class() >= $torrentmanage_class && $CURUSER["picker"] == 'yes')){
		$pickcontent = "";
	
		if(get_user_class()>=$torrentsticky_class)
		{
			$pickcontent .= "<b>".$lang_edit['row_special_torrent'].":&nbsp;</b>"."<select name=\"sel_spstate\" style=\"width: 100px;\">" .promotion_selection($row["sp_state"], 0). "</select>&nbsp;&nbsp;&nbsp;";
			
			// Added by BruceWolf. Code Supplied by LM@gdbt
			$pickcontent .= ""."<select name=\"promotion_time_type\" style=\"width: 100px;\">" .
			                "<option" . (($row["promotion_time_type"] == "0") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_use_global_setting']."</option>" .
			                "<option" . (($row["promotion_time_type"] == "1") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_forever']."</option>" .
			                "<option" . (($row["promotion_time_type"] == "2") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_until']."</option>" ."</select>&nbsp;&nbsp;&nbsp;";
			$pickcontent .= "<b>截止日期:&nbsp;</b><input type=\"text\" name=\"promotionuntil\" id=\"promotionuntil\" style=\"width: 120px;\" value=\"". (($row["promotion_time_type"]!=2)? date("Y-m-d H:i:s") :$row["promotion_until"]) . "\" />".$lang_edit['text_promotion_until_note']. "<br />";
			$pickcontent .=<<<EOT
<b>延长促销时间</b><select id="time_select_day">
	<option value="0">0</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="5">5</option>
	<option value="7">7</option>
	<option value="10">10</option>
	<option value="15">15</option>
	<option value="20">20</option>
	<option value="30">30</option>
	<option value="40">40</option>
	<option value="50">50</option>
	<option value="60">60</option>
	<option value="90">90</option>
	<option value="180">180</option>
	<option value="365">365</option>
</select>天<select id="time_select_hour">
	<option value="0">0</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="8">8</option>
	<option value="10">10</option>
	<option value="12">12</option>
	<option value="15">15</option>
	<option value="18">18</option>
	<option value="20">20</option>
</select>小时<select id="time_select_minute">
	<option value="0">0</option>
	<option value="5">5</option>
	<option value="10">10</option>
	<option value="15">15</option>
	<option value="20">20</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="45">45</option>
</select>分钟
<input type="button" value="延长" id="delay_promotion_time" />
<script type="text/javascript">
function str2date(string) {

	var default_date = new Date();
	
	date_part = string.split(' ')[0].split('-');
	time_part = string.split(' ')[1].split(':');
	
	default_date.setFullYear(date_part[0]);
	default_date.setMonth(date_part[1] - 1);
	default_date.setDate(date_part[2]);
	default_date.setHours(time_part[0]);
	default_date.setMinutes(time_part[1]);
	default_date.setSeconds(time_part[2]);
	
	return default_date;
}

var timeBox = document.getElementById('promotionuntil');
var default_unixtime = str2date(timeBox.value);

document.getElementById('delay_promotion_time').onclick = function() {
	var new_date = new Date();

	var day = document.getElementById('time_select_day').value;
	var hour = document.getElementById('time_select_hour').value;
	var minute = document.getElementById('time_select_minute').value;

	// 换算成毫秒
	var time_period = (day * 24 * 3600 + hour * 3600 + minute * 60) * 1000;
	
	new_date.setTime(default_unixtime.valueOf() + time_period);

	timeBox.value = new_date.getFullYear() 
	                + '-' + (new_date.getMonth() + 1) 
	                + '-' + new_date.getDate() 
	                + ' ' + new_date.getHours() 
	                + ':' + new_date.getMinutes() 
	                + ':' + new_date.getSeconds();

	return false;
};

</script>
<br />
EOT;
			// End
			
			$pickcontent .= "<b>".$lang_edit['row_torrent_position'].":&nbsp;</b>"."<select name=\"sel_posstate\" style=\"width: 100px;\">" .
			"<option" . (($row["pos_state"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["pos_state"] == "sticky") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_sticky']."</option>" .
			"</select>&nbsp;&nbsp;&nbsp;";
			//Added by bluemonster 20111025
			if (get_user_class() >= $UC_UPLOADER)
			{
			if($row['oday']=='yes')	
				$odaycheckbox = "<input type=\"checkbox\" id=sel_oday name=\"sel_oday\" checked=\"yes\" value=\"oday\"/>".$lang_edit['oday'];
			elseif($row['oday']=='no')
				$odaycheckbox = "<input type=\"checkbox\" id=sel_oday name=\"sel_oday\" value=\"oday\"/>".$lang_edit['oday'];
			$pickcontent.=$odaycheckbox;
			/*
			$pickcontent .= "<b>".$lang_edit['oday'].":&nbsp;</b>"."<select name=\"sel_oday\" style=\"width: 100px;\">" .
			"<option" . " selected=\"selected\"". " value=\"0\">".$lang_edit['select_not_oday']."</option>" .
			"<option" . " value=\"1\">".$lang_edit['select_oday']."</option>" .
			"</select>&nbsp;&nbsp;&nbsp;";
			*/
			}
		}
		if(get_user_class()>=$torrentmanage_class && $CURUSER["picker"] == 'yes')
		{
			$pickcontent .= "<b>".$lang_edit['row_recommended_movie'].":&nbsp;</b>"."<select name=\"sel_recmovie\" style=\"width: 100px;\">" .
			"<option" . (($row["picktype"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["picktype"] == "hot") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_hot']."</option>" .
			"<option" . (($row["picktype"] == "classic") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_classic']."</option>" .
			"<option" . (($row["picktype"] == "recommended") ? " selected=\"selected\"" : "" ) . " value=\"3\">".$lang_edit['select_recommended']."</option>" .
			"</select>";
		}
		tr($lang_edit['row_pick'], $pickcontent, 1);
	}

	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input id=\"qr\" type=\"submit\" value=\"".$lang_edit['submit_edit_it']."\" /> <input type=\"reset\" value=\"".$lang_edit['submit_revert_changes']."\" /></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	print("<br /><br />");
	print("<form method=\"post\" action=\"delete.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />\n");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" align=\"left\" style='padding-bottom: 3px' colspan=\"2\">".$lang_edit['text_delete_torrent']."</td></tr>");
	tr("<input name=\"reasontype\" type=\"radio\" value=\"1\" />&nbsp;".$lang_edit['radio_dead'], $lang_edit['text_dead_note'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"2\" />&nbsp;".$lang_edit['radio_dupe'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"3\" />&nbsp;".$lang_edit['radio_nuked'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"4\" />&nbsp;".$lang_edit['radio_rules'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"5\" checked=\"checked\" />&nbsp;".$lang_edit['radio_other'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input type=\"submit\" style='height: 25px' value=\"".$lang_edit['submit_delete_it']."\" /></td></tr>\n");
	print("</table>");
	print("</form>\n");
}
stdfoot();