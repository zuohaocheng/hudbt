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

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] &&  get_user_class() < $torrentmanage_class )) {
	print("<h1 align=\"center\">".$lang_edit['text_cannot_edit_torrent']."</h1>");
	print("<p>".$lang_edit['text_cannot_edit_torrent_note']."</p>");
}
else {
	print("<form method=\"post\" id=\"compose\" name=\"edittorrent\" action=\"takeedit.php\" enctype=\"multipart/form-data\">");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />");
	print('<h1 id="page-title">'.htmlspecialchars($row["name"])."</h1>");
	echo '<dl class="table">';
	dl_item('<span class="required">' . $lang_edit['row_torrent_name']."</span>", "<input type=\"text\" style=\"width: 650px;\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" />", 1);
	if ($smalldescription_main == 'yes') {
	  dl_item($lang_edit['row_small_description'], "<input type=\"text\" style=\"width: 650px;\" name=\"small_descr\" value=\"" . htmlspecialchars($row["small_descr"]) . "\" />", 1);
	}

	get_external_tr($row["url"], true);

	if ($enablenfo_main=='yes') {
	  dl_item($lang_edit['row_nfo_file'], "<font class=\"medium\"><input type=\"radio\" name=\"nfoaction\" value=\"keep\" checked=\"checked\" />".$lang_edit['radio_keep_current'].
	"<input type=\"radio\" name=\"nfoaction\" value=\"remove\" />".$lang_edit['radio_remove'].
	"<input id=\"nfoupdate\" type=\"radio\" name=\"nfoaction\" value=\"update\" />".$lang_edit['radio_update']."</font><br /><input type=\"file\" name=\"nfo\" onchange=\"document.getElementById('nfoupdate').checked=true\" />", 1);
	}
	print('<dt><span class="required">'.$lang_edit['row_description']."</span></dt><dd>");
	textbbcode("edittorrent","descr",($row["descr"]), false);
	print("</dd>");
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
	dl_item('<span class="required">' . $lang_edit['row_type']."</span>", $s.($allowmove ? "&nbsp;&nbsp;".$movecheckbox.$movenote.$s2 : ""), 1);
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

		dl_item($lang_edit['row_quality'], $source_select . $medium_select . $codec_select . $audiocodec_select. $standard_select . $processing_select, 1);
	}

	if ($showteam){
		if ($showteam){
			$team_select = torrent_selection($lang_edit['text_team'],"team_sel","teams",$row["team"]);
		}
		else $showteam = "";

		dl_item($lang_edit['row_content'],$team_select,1);
	}
	dl_item($lang_edit['row_check'], "<input type=\"checkbox\" name=\"visible\"" . ($row["visible"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" /> ".$lang_edit['checkbox_visible']."&nbsp;&nbsp;&nbsp;".(get_user_class() >= $beanonymous_class || get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"anonymous\"" . ($row["anonymous"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" />".$lang_edit['checkbox_anonymous_note']."&nbsp;&nbsp;&nbsp;" : "").(get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"yes\" /> ".$lang_edit['checkbox_banned'] : ""), 1);
	if (get_user_class()>= $torrentsticky_class || (get_user_class() >= $torrentmanage_class && $CURUSER["picker"] == 'yes')) {
		$pickcontent = "";
	
		if (checkPrivilege(['Torrent', 'pr'])) {
			$pickcontent .= "<b>".$lang_edit['row_special_torrent'].':&nbsp;</b><select id="sel_spstate" name="sel_spstate" style="width: 100px;">' .promotion_selection($row["sp_state"], 0). "</select>&nbsp;&nbsp;&nbsp;";
			
			// Added by BruceWolf. Code Supplied by LM@gdbt
			$pickcontent .= ""."<select id='promotion_time_type' name=\"promotion_time_type\" style=\"width: 100px;\">" .
			                "<option" . (($row["promotion_time_type"] == "0") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_use_global_setting']."</option>" .
			                "<option" . (($row["promotion_time_type"] == "1") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_forever']."</option>" .
			                "<option" . (($row["promotion_time_type"] == "2") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_until']."</option>" ."</select>&nbsp;&nbsp;&nbsp;";
			$pickcontent .= "<label id='pr-expire'><b>截止日期:&nbsp;</b><input type=\"text\" name=\"promotionuntil\" id=\"promotionuntil\" style=\"width: 120px;\" value=\"". (($row["promotion_time_type"]!=2)? date("Y-m-d H:i:s") :$row["promotion_until"]) . "\" />".$lang_edit['text_promotion_until_note']. "</label><br />";
			$pickcontent .='<div id="expand-pr"></div>';
			// End
		}
		
		if (checkPrivilege(['Torrent', 'sticky'])) {
			$pickcontent .= "<b>".$lang_edit['row_torrent_position'].":&nbsp;</b>"."<select id=\"sel_posstate\" name=\"sel_posstate\" style=\"width: 100px;\">" .
			"<option" . (($row["pos_state"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"normal\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["pos_state"] == "sticky") ? " selected=\"selected\"" : "" ) . " value=\"sticky\">".$lang_edit['select_sticky']."</option>" .
			"<option" . (($row["pos_state"] == "random") ? " selected=\"selected\"" : "" ) . " value=\"random\">".$lang_edit['select_random']."</option>" .
			"</select>&nbsp;&nbsp;&nbsp;";
				$pickcontent .= "<label id='pos-expire'><b>截止日期:&nbsp;</b><input type=\"text\" name=\"posstateuntil\" id=\"posstateuntil\" style=\"width: 120px;\" value=\"". (($row["pos_state"]!="sticky")? date("Y-m-d H:i:s") :$row["pos_state_until"]) . "\" />". "</label><br />";
				$pickcontent .='<div id="expand-pos"></div>';
		}
		
		//Added by bluemonster 20111025
		if (checkPrivilege(['Torrent', 'oday'])) {
		  if($row['oday']=='yes')	
		    $odaycheckbox = "<input type=\"checkbox\" id=sel_oday name=\"sel_oday\" checked=\"yes\" value=\"oday\"/>".$lang_edit['oday'];
		  elseif($row['oday']=='no')
		    $odaycheckbox = "<input type=\"checkbox\" id=sel_oday name=\"sel_oday\" value=\"oday\"/>".$lang_edit['oday'];
		  $pickcontent.=$odaycheckbox;
		}
		//Added by Eggsorer 20120328
		if (permissionAuth("setstoring",$CURUSER['usergroups'],$CURUSER['class'])) {
		  if($row['storing']=='1')	
		    $storingcheckbox = "<input type=\"checkbox\" id=sel_storing name=\"sel_storing\" checked=\"yes\" value=\"storing\"/>".$lang_edit['text_storing'];
		  elseif($row['storing']=='0')
		    $storingcheckbox = "<input type=\"checkbox\" id=sel_storing name=\"sel_storing\" value=\"storing\"/>".$lang_edit['text_storing'];
		  $pickcontent.=$storingcheckbox;
		}
		if (get_user_class()>=$torrentmanage_class && $CURUSER["picker"] == 'yes') {
			$pickcontent .= "<b>".$lang_edit['row_recommended_movie'].":&nbsp;</b>"."<select name=\"sel_recmovie\" style=\"width: 100px;\">" .
			"<option" . (($row["picktype"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["picktype"] == "hot") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_hot']."</option>" .
			"<option" . (($row["picktype"] == "classic") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_classic']."</option>" .
			"<option" . (($row["picktype"] == "recommended") ? " selected=\"selected\"" : "" ) . " value=\"3\">".$lang_edit['select_recommended']."</option>" .
			"</select>";
		}
		dl_item($lang_edit['row_pick'], $pickcontent, true, '', 'pr');
	}

	print("<dd class=\"toolbox\"><input id=\"qr\" type=\"submit\" value=\"".$lang_edit['submit_edit_it']."\" /> <input type=\"reset\" value=\"".$lang_edit['submit_revert_changes']."\" /></dd>\n");
	print("</dl>\n");
	print("</form>\n");

	if (checkPrivilege(['Torrent', 'delete'], $id)) {
	  print("<br /><br />");
	  print("<form id=\"delete\" method=\"post\" action=\"//" . $CAKEURL . "/torrents/delete/$id\"><div class=\"table td\" style=\"width: 50%;text-align:center;padding:5px;\">\n");
	  if (isset($_GET["returnto"])) {
	    print("<input type=\"hidden\" name=\"data[returnto]\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	  }
	  $reasons = [$lang_edit['radio_dead'], $lang_edit['radio_dupe'], $lang_edit['radio_nuked'], $lang_edit['radio_rules'], $lang_edit['radio_other']];
	  echo '<input type="hidden" name="_method" value="DELETE" /><select id="reason-type" name="data[reasonType]">';
	  foreach ($reasons as $k => $reason) {
	    echo '<option value="', $k, '">', $reason, '</option>';
	  }
	  echo '</select><input type="text" id="reason-detail" name="data[reasonDetail]" placeholder="详细理由" />';
	  echo "<input type=\"submit\" style='height: 25px' value=\"".$lang_edit['submit_delete_it']."\" />";
	  print("</div></form>\n");
	}
}
stdfoot();

