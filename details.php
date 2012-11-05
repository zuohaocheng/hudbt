<?php
ob_start(); //Do not delete this line
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path('details.php'));
if ($showextinfo['imdb'] == 'yes')
  require_once("imdb/imdb.class.php");
loggedinorreturn();

$id = 0 + $_GET["id"];

int_check($id);
$storingKeeperList = storing_keeper_list($id);

if (!isset($id) || !$id)
  die();

$res = sql_query("SELECT torrents.cache_stamp, torrents.storing, torrents.sp_state, torrents.url, torrents.small_descr, torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, nfo, LENGTH(torrents.nfo) AS nfosz, torrents.last_action, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.promotion_time_type, torrents.promotion_until, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, torrents.anonymous, torrents.startseed, categories.name AS cat_name, sources.name AS source_name, media.name AS medium_name, codecs.name AS codec_name, standards.name AS standard_name, processings.name AS processing_name, teams.name AS team_name, audiocodecs.name AS audiocodec_name FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN sources ON torrents.source = sources.id LEFT JOIN media ON torrents.medium = media.id LEFT JOIN codecs ON torrents.codec = codecs.id LEFT JOIN standards ON torrents.standard = standards.id LEFT JOIN processings ON torrents.processing = processings.id LEFT JOIN teams ON torrents.team = teams.id LEFT JOIN audiocodecs ON torrents.audiocodec = audiocodecs.id WHERE torrents.id = $id LIMIT 1")
  or sqlerr();
$row = mysql_fetch_array($res);

if (!$row)
  stderr($lang_details['std_error'], $lang_details['std_no_torrent_id']);
// BruceWolf Added at 2011-04-22
// elseif ($row['banned'] == 'yes' && get_user_class() < $seebanned_class)
elseif ($row['banned'] == 'yes' && get_user_class() < $seebanned_class && $CURUSER['id'] != $row['owner'])
  permissiondenied();
else {
  if ($_GET["hit"]) {
    sql_query("UPDATE LOW_PRIORITY torrents SET views = views + 1 WHERE id = $id");
  }

  if (!isset($_GET["cmtpage"])) {
    stdhead($lang_details['head_details_for_torrent']. "\"" . $row["name"] . "\"");

    if ($_GET["uploaded"])
      {
	print('<h1 class="page-titles">'.$lang_details['text_successfully_uploaded']."</h1>");
	print('<div style="text-align:center">'.$lang_details['text_redownload_torrent_note']."</div>");
	header("refresh: 1; url=download.php?id=$id");
	//header("refresh: 1; url=getimdb.php?id=$id&type=1");
      }
    elseif ($_GET["edited"]) {
      print('<h1 class="page-titles">'.$lang_details['text_successfully_edited']."</h1>");
      if (isset($_GET["returnto"]))
	print("<p><b>".$lang_details['text_go_back'] . "<a href=\"".htmlspecialchars($_GET["returnto"])."\">" . $lang_details['text_whence_you_came']."</a></b></p>");
    }
    $sp_torrent = get_torrent_promotion_append($row['sp_state'],"",true,$row["added"], $row['promotion_time_type'], $row['promotion_until']);

    $s=htmlspecialchars($row["name"]);
    print('<h1 id="page-title">'.$s.'</h1><a id="top"></a>');
    echo '<div class="minor-list" style="text-align:center;font-size:120%;"><ul>';

    if ($sp_torrent) {
      echo '<li>' . $sp_torrent; 
      $sp_torrent_sub = get_torrent_promotion_append_sub($row['sp_state'],"",true,$row["added"], $row['promotion_time_type'], $row['promotion_until']);
      if ($sp_torrent_sub != '') {
	echo $sp_torrent_sub;
      }
      echo '</li>';
    }
    if($row['storing']==1)
    	echo "<li><img alt= \"$lang_details[text_storing]\" title=\"$lang_details[text_storing]\" src=\"//$BASEURL/pic/ico_storing.png\"/></li>";
    if ($row['banned'] == 'yes') {
      echo "<li>(<span class=\"striking\">".$lang_functions['text_banned']."</span>)</li>";
    }
    echo '</ul></div>';
    
    echo '<dl class="table">';

    $url = "edit.php?id=" . $row["id"];
    if (isset($_GET["returnto"])) {
      $url .= "&returnto=" . rawurlencode($_GET["returnto"]);
    }
    $editlink = "a title=\"".$lang_details['title_edit_torrent']."\" href=\"$url\"";
    $prlink = "a href=\"$url#pr\" id=\"set-pr\"";
    $deletelink = "a title=\"".$lang_details['title_delete_torrent']."\" href=\"$url#delete\" id=\"torrent-delete\"";

    // ------------- start upped by block ------------------//
    if($row['anonymous'] == 'yes') {
      if (get_user_class() < $viewanonymous_class)
	$uprow = "<i>".$lang_details['text_anonymous']."</i>";
      else
	$uprow = "<i>".$lang_details['text_anonymous']."</i> (" . get_username($row['owner'], false, true, true, false, false, true) . ")";
    }
    else {
      $uprow = (isset($row['owner']) ? get_username($row['owner'], false, true, true, false, false, true) : "<i>".$lang_details['text_unknown']."</i>");
    }

    if ($CURUSER["id"] == $row["owner"]) {
      $CURUSER["downloadpos"] = "yes";
    }
    
    if ($CURUSER["downloadpos"] != "no") {

	  $uploadtime = $lang_details['text_at'].$row['added'];
	
	$dt= $lang_details['row_download'];
	$dd = ("<a class=\"index\" href=\"download.php?id=$id\">" . htmlspecialchars($torrentnameprefix ."." .$row["save_as"]) . ".torrent</a>&nbsp;&nbsp;<a id=\"bookmark" . $row['id'] . "\" href=\"javascript: bookmark(".$row['id'].",0);\">".get_torrent_bookmark_state($CURUSER['id'], $row['id'], false)."</a><br />".$lang_details['row_upped_by']."&nbsp;".$uprow.$uploadtime);
	dl_item($dt, $dd, true);

      }
    else {
      dl_item($lang_details['row_download'], $lang_details['text_downloading_not_allowed']);
    }

    $small_desc = trim($row["small_descr"]);
    if ($smalldescription_main == 'yes' && $small_desc) {
      dl_item($lang_details['row_small_description'], htmlspecialchars($row['small_descr']),true);
    }

    $size_info =  "<b>".$lang_details['text_size']."</b>" . mksize($row["size"]);
    $type_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['row_type'].":</b>&nbsp;".$row["cat_name"];
    if (isset($row["source_name"]))
      $source_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_source']."&nbsp;</b>".$row['source_name'];
    if (isset($row["medium_name"]))
      $medium_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_medium']."&nbsp;</b>".$row['medium_name'];
    if (isset($row["codec_name"]))
      $codec_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_codec']."&nbsp;</b>".$row['codec_name'];
    if (isset($row["standard_name"]))
      $standard_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_stardard']."&nbsp;</b>".$row['standard_name'];
    if (isset($row["processing_name"]))
      $processing_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_processing']."&nbsp;</b>".$row['processing_name'];
    if (isset($row["team_name"]))
      $team_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_team']."&nbsp;</b>".$row['team_name'];
    if (isset($row["audiocodec_name"]))
      $audiocodec_info = "&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_audio_codec']."&nbsp;</b>".$row['audiocodec_name'];

    dl_item($lang_details['row_basic_info'], $size_info.$type_info.$source_info . $medium_info. $codec_info . $audiocodec_info. $standard_info . $processing_info . $team_info, 1);

    $actions = '<div class="minor-list list-seperator"><ul>';
    if ($CURUSER["downloadpos"] != "no") {
      $actions .= "<li><a title=\"".$lang_details['title_download_torrent']."\" href=\"download.php?id=".$id."\"><img class=\"dt_download\" src=\"pic/trans.gif\" alt=\"download\" />&nbsp;<span class=\"small\">".$lang_details['text_download_torrent']."</span></a></li>";
    }
    if (checkPrivilege(['Torrent', 'edit']) || $CURUSER["id"] == $row["owner"]||permissionAuth("edittorrent",$CURUSER['usergroups'],$CURUSER['class'])) {
      $actions .= "<li><$editlink><img class=\"dt_edit\" src=\"pic/trans.gif\" alt=\"edit\" />&nbsp;<span class=\"small\">".$lang_details['text_edit_torrent'] . "</span></a></li>";
    }
    if (checkPrivilege(['Torrent', 'delete'], $id)) {
      $actions .= '<li><' . $deletelink . '>' . $lang_details['text_delete_torrent'] . '</a></li>';
    }

    if (get_user_class() >= $askreseed_class && $row['seeders'] == 0) {
      $actions .= "<li><a title=\"".$lang_details['title_ask_for_reseed']."\" href=\"takereseed.php?reseedid=$id\"><img class=\"dt_reseed\" src=\"pic/trans.gif\" alt=\"reseed\">&nbsp;<span class=\"small\">".$lang_details['text_ask_for_reseed'] ."</span></a></li>";
    }

    $actions .= "<li><a title=\"".$lang_details['title_report_torrent']."\" href=\"report.php?torrent=$id\"><img class=\"dt_report\" src=\"pic/trans.gif\" alt=\"report\" />&nbsp;<span class=\"small\">".$lang_details['text_report_torrent']."</span></a></li>";

    if (checkPrivilege(['Torrent', 'sticky'])) {
      $actions .= '<li><' . $prlink . '>设定优惠</a></li>';
    }
		if(permissionAuth("storing",$CURUSER['usergroups'],$CURUSER['class'])){
			if(in_array($CURUSER['id'], $storingKeeperList)){
				$actions .= "<li><a href=\"storing.php?action=checkout&torrentid=$id&userid=$CURUSER[id]\">$lang_details[text_check_out]</a></li>";	
			}
			else{
				if($row['storing']==1){
					$actions .= "<li><a href=\"storing.php?action=checkin&torrentid=$id&userid=$CURUSER[id]\">$lang_details[text_check_in]</a></li>";	
				}
			}
		}

    $actions .= '</ul></div>';

    dl_item($lang_details['row_action'], $actions, true);

    // ---------------- start subtitle block -------------------//
    $r = sql_query("SELECT subs.*, language.flagpic, language.lang_name FROM subs LEFT JOIN language ON subs.lang_id=language.id WHERE torrent_id = " . sqlesc($row["id"]). " ORDER BY subs.lang_id ASC") or sqlerr(__FILE__, __LINE__);
    print("<dt>".$lang_details['row_subtitles']);
    if($CURUSER['id']==$row['owner']  ||  get_user_class() >= $uploadsub_class) {
      print('<div style="font-weight:normal">[<a href="subtitles.php?torrent=' . $row['id'] . '">' . $lang_details['submit_upload_subtitles'] .'</a>]</div>');
    }
    echo "</dt>";
    print("<dd>");
    if (mysql_num_rows($r) > 0) {
      print("<table border=\"0\" cellspacing=\"0\">");
	while($a = mysql_fetch_assoc($r)) {
	    $lang = "<tr><td class=\"embedded\"><img border=\"0\" src=\"pic/flag/". $a["flagpic"] . "\" alt=\"" . $a["lang_name"] . "\" title=\"" . $a["lang_name"] . "\" style=\"padding-bottom: 4px\" /></td>";
	    $lang .= "<td class=\"embedded\">&nbsp;&nbsp;<a href=\"downloadsubs.php?torrentid=".$a[torrent_id]."&subid=".$a[id]."\"><u>". $a["title"]. "</u></a>".(get_user_class() >= $submanage_class || (get_user_class() >= $delownsub_class && $a["uppedby"] == $CURUSER["id"]) ? " <font class=\"small\"><a href=\"subtitles.php?delete=".$a[id]."\">[".$lang_details['text_delete']."]</a></font>" : ""). "<a href=\"report.php?subtitle=$a[id]\">[$lang_details[report]]</a>"."</td><td class=\"embedded\">&nbsp;&nbsp;".($a["anonymous"] == 'yes' ? $lang_details['text_anonymous'] . (get_user_class() >= $viewanonymous_class ? get_username($a['uppedby'],false,true,true,false,true) : "") : get_username($a['uppedby'])).'</dd>';
	    print($lang);
	}
	print("</table>");
    }
    else {
      print($lang_details['text_no_subtitles']);
    }
    
    print("<table border=\"0\" cellspacing=\"0\"><tr>");

    $moviename = "";
    $imdb_id = parse_imdb_id($row["url"]);
    if ($imdb_id && $showextinfo['imdb'] == 'yes')
      {
	$thenumbers = $imdb_id;
	if (!$moviename = $Cache->get_value('imdb_id_'.$thenumbers.'_movie_name')){
	  $movie = new imdb ($thenumbers);
	  $target = array('Title');
	  switch ($movie->cachestate($target)){
	  case "1":{
	    $moviename = $movie->title (); break;
	    $Cache->cache_value('imdb_id_'.$thenumbers.'_movie_name', $moviename, 1296000);
	  }
	  default: break;
	  }
	}
      }
    print("<td class=\"embedded\"><form method=\"get\" action=\"http://shooter.cn/sub/\" target=\"_blank\"><input type=\"search\" name=\"searchword\" id=\"keyword\" style=\"width: 250px\" value=\"".$moviename."\" /><input type=\"submit\" value=\"".$lang_details['submit_search_at_shooter']."\" /><input type=\"submit\" formaction=\"http://www.opensubtitles.org/en/search2/\" value=\"" . $lang_details['submit_search_at_opensubtitles'] . "\" /></form></td>\n");
    print("</tr></table>");
    echo '</dd>';

    // ---------------- end subtitle block -------------------//

    if (!empty($row["descr"])){
      $torrentdetailad=$Advertisement->get_ad('torrentdetail');
      if ($Advertisement->enable_ad() && $torrentdetailad) {
	dl_item("", "<div align=\"left\" style=\"margin-bottom: 10px\" id=\"ad_torrentdetail\">".$torrentdetailad[0]."</div>", 1);
      }
      dl_item("<a href=\"javascript: klappe_news('descr')\"><span class=\"nowrap\"><img class=\"minus\" src=\"pic/trans.gif\" alt=\"Show/Hide\" id=\"picdescr\" title=\"".$lang_detail['title_show_or_hide']."\" /> ".$lang_details['row_description']."</span></a>", "<div id='kdescr'>".format_comment($row["descr"])."</div>", 1);
    }

    if (get_user_class() >= $viewnfo_class && $CURUSER['shownfo'] != 'no' && $row["nfosz"] > 0){
      $nfo = $Cache->get_value('nfo_block_torrent_id_'.$id);
      if (!$nfo) {
	$nfo = code($row["nfo"], $view == "magic");
	$Cache->cache_value('nfo_block_torrent_id_'.$id, $nfo, 604800);
      }
      dl_item("<a href=\"javascript: klappe_news('nfo')\"><img class=\"plus\" src=\"pic/trans.gif\" alt=\"Show/Hide\" id=\"picnfo\" title=\"".$lang_detail['title_show_or_hide']."\" /> ".$lang_details['text_nfo']."</a><br /><a href=\"viewnfo.php?id=".$row['id']."\" class=\"sublink\">". $lang_details['text_view_nfo']. "</a>", "<div id='knfo' style=\"display: none;\"><pre style=\"font-size:10pt; font-family: 'Courier New', monospace;\">".$nfo."</pre></div><div style=\"clear:both;\"\n", 1);
    }

    if ($imdb_id && $showextinfo['imdb'] == 'yes')
      {
	$thenumbers = $imdb_id;

	$Cache->new_page('imdb_id_'.$thenumbers.'_large', 1296000, true);
	if (!$Cache->get_page()){
	  $movie = new imdb ($thenumbers);
	  $movieid = $thenumbers;
	  $movie->setid ($movieid);
	  $target = array('Title', 'Credits', 'Plot');
	  switch ($movie->cachestate($target))
	    {
	    case "0" : //cache is not ready, try to
	      {
		if($row['cache_stamp']==0 || ($row['cache_stamp'] != 0 && (time()-$row['cache_stamp']) > $auto_obj->timeout))	//not exist or timed out
		  dl_item($lang_details['text_imdb'] . $lang_details['row_info'] , $lang_details['text_imdb'] . $lang_details['text_not_ready']."<a href=\"retriver.php?id=". $id ."&amp;type=1&amp;siteid=1\">".$lang_details['text_here_to_retrieve'] . $lang_details['text_imdb'],1);
		else
		  dl_item($lang_details['text_imdb'] . $lang_details['row_info'] , "<img src=\"pic/progressbar.gif\" alt=\"\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $lang_details['text_someone_has_requested'] . $lang_details['text_imdb'] . " ".min(max(time()-$row['cache_stamp'],0),$auto_obj->timeout) . $lang_details['text_please_be_patient'],1);
		break;
	      }
	    case "1" :
	      {
		reset_cachetimestamp($row['id']);
		$country = $movie->country ();
		$director = $movie->director();
		$creator = $movie->creator(); // For TV series
		$write = $movie->writing();
		$produce = $movie->producer();
		$cast = $movie->cast();
		$plot = $movie->plot ();
		$plot_outline = $movie->plotoutline();
		$compose = $movie->composer();
		$gen = $movie->genres();
		//$comment = $movie->comment();
		$similiar_movies = $movie->similiar_movies();

		if (($photo_url = $movie->photo_localurl() ) != FALSE)
		  $smallth = "<img src=\"".$photo_url. "\" width=\"105\" onclick=\"Preview(this);\" alt=\"poster\" />";
		else
		  $smallth = "<img src=\"pic/imdb_pic/nophoto.gif\" alt=\"no poster\" />";

		$autodata = '<a href="http://www.imdb.com/title/tt'.$thenumbers.'">http://www.imdb.com/title/tt'.$thenumbers."</a><br /><strong><font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font><br />\n";
		$autodata .= "<font color=\"darkred\" size=\"3\">".$lang_details['text_information']."</font><br />\n";
		$autodata .= "<font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font></strong><br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">". $lang_details['text_title']."</font></strong>" . "".$movie->title ()."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_also_known_as']."</font></strong>";

		$temp = "";
		foreach ($movie->alsoknow() as $ak)
		  {
		    $temp .= $ak["title"].$ak["year"]. ($ak["country"] != "" ? " (".$ak["country"].")" : "") . ($ak["comment"] != "" ? " (" . $ak["comment"] . ")" : "") . ", ";
		  }
		$autodata .= rtrim(trim($temp), ",");
		$runtimes = str_replace(" min",$lang_details['text_mins'], $movie->runtime_all());
		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_year']."</font></strong>" . "".$movie->year ()."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_runtime']."</font></strong>".$runtimes."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_votes']."</font></strong>" . "".$movie->votes ()."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_rating']."</font></strong>" . "".$movie->rating ()."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_language']."</font></strong>" . "".$movie->language ()."<br />\n";
		$autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_country']."</font></strong>";

		$temp = "";
		for ($i = 0; $i < count ($country); $i++) {
		    $temp .="$country[$i], ";
		  }
		$autodata .= rtrim(trim($temp), ",");

		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_all_genres']."</font></strong>";
		$temp = "";
		for ($i = 0; $i < count($gen); $i++) {
		    $temp .= "$gen[$i], ";
		  }
		$autodata .= rtrim(trim($temp), ",");

		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_tagline']."</font></strong>" . "".$movie->tagline ()."<br />\n";
		if ($director){
		  $autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_director']."</font></strong>";
		  $temp = "";
		  for ($i = 0; $i < count ($director); $i++) {
		      $temp .= "<a target=\"_blank\" href=\"http://www.imdb.com/Name?" . "".$director[$i]["imdb"]."" ."\">" . $director[$i]["name"] . "</a>, ";
		    }
		  $autodata .= rtrim(trim($temp), ",");
		}
		elseif ($creator) {
		  $autodata .= "<strong><font color=\"DarkRed\">".$lang_details['text_creator']."</font></strong>".$creator;
		}

		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_written_by']."</font></strong>";
		$temp = "";
		for ($i = 0; $i < count ($write); $i++) {
		    $temp .= "<a target=\"_blank\" href=\"http://www.imdb.com/Name?" . "".$write[$i]["imdb"]."" ."\">" . "".$write[$i]["name"]."" . "</a>, ";
		  }
		$autodata .= rtrim(trim($temp), ",");

		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_produced_by']."</font></strong>";
		$temp = "";
		for ($i = 0; $i < count ($produce); $i++) {
		    $temp .= "<a target=\"_blank\" href=\"http://www.imdb.com/Name?" . "".$produce[$i]["imdb"]."" ." \">" . "".$produce[$i]["name"]."" . "</a>, ";
		  }
		$autodata .= rtrim(trim($temp), ",");

		$autodata .= "<br />\n<strong><font color=\"DarkRed\">".$lang_details['text_music']."</font></strong>";
		$temp = "";
		for ($i = 0; $i < count($compose); $i++) {
		    $temp .= "<a target=\"_blank\" href=\"http://www.imdb.com/Name?" . "".$compose[$i]["imdb"]."" ." \">" . "".$compose[$i]["name"]."" . "</a>, ";
		  }
		$autodata .= rtrim(trim($temp), ",");

		$autodata .= "<br /><br />\n\n<strong><font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font><br />\n";
		$autodata .= "<font color=\"darkred\" size=\"3\">".$lang_details['text_plot_outline']."</font><br />\n";
		$autodata .= "<font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font></strong>";

		if(count($plot) == 0) {
		    $autodata .= "<br />\n".$plot_outline;
		  }
		else {
		    for ($i = 0; $i < count ($plot); $i++)
		      {
			$autodata .= "<br />\n<font color=\"DarkRed\">.</font> ";
			$autodata .= $plot[$i];
		      }
		  }


		$autodata .= "<br /><br />\n\n<strong><font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font><br />\n";
		$autodata .= "<font color=\"darkred\" size=\"3\">".$lang_details['text_cast']."</font><br />\n";
		$autodata .= "<font color=\"navy\">------------------------------------------------------------------------------------------------------------------------------------</font></strong><br />\n";

		for ($i = 0; $i < count ($cast); $i++) {
		  if ($i > 9) {
			break;
		      }
		    $autodata .= "<font color=\"DarkRed\">.</font> " . "<a target=\"_blank\" href=\"http://www.imdb.com/Name?" . "".$cast[$i]["imdb"]."" ."\">" . $cast[$i]["name"] . "</a> " .$lang_details['text_as']."<strong><font color=\"DarkRed\">" . "".$cast[$i]["role"]."" . " </font></strong><br />\n";
		  }

		$cache_time = $movie->getcachetime();

		$Cache->add_whole_row();
		print("<dt><a href=\"javascript: klappe_ext('imdb')\"><span class=\"nowrap\"><img class=\"minus\" src=\"pic/trans.gif\" alt=\"Show/Hide\" id=\"picimdb\" title=\"".$lang_detail['title_show_or_hide']."\" /> ".$lang_details['text_imdb'] . $lang_details['row_info'] ."</span></a><div id=\"posterimdb\">".  $smallth."</div></dt>");
		$Cache->end_whole_row();
		$Cache->add_row();
		$Cache->add_part();
		print("<dd><div id='kimdb'>".$autodata);
		$Cache->end_part();
		$Cache->add_part();
		print($lang_details['text_information_updated_at'] . date("Y-m-d", $cache_time) . $lang_details['text_might_be_outdated']."<a href=\"".htmlspecialchars("retriver.php?id=". $id ."&type=2&siteid=1")."\">".$lang_details['text_here_to_update']);
		$Cache->end_part();
		$Cache->end_row();
		$Cache->add_whole_row();
		print("</div></dd>");
		$Cache->end_whole_row();
		$Cache->cache_page();
		echo $Cache->next_row();
		$Cache->next_row();
		echo $Cache->next_part();
		if (get_user_class() >= $updateextinfo_class)
		  echo $Cache->next_part();
		echo $Cache->next_row();
		break;
	      }
	    case "2" : {
		dl_item($lang_details['text_imdb'] . $lang_details['row_info'] ,$lang_details['text_network_error'],1);
		break;
	      }
	    case "3" : {// not a valid imdb url
		break;
	      }
	    }
	}
	else {
	  echo $Cache->next_row();
	  $Cache->next_row();
	  echo $Cache->next_part();
	  if (get_user_class() >= $updateextinfo_class) {
	    echo $Cache->next_part();
	  }
	  echo $Cache->next_row();
	}
      }

    if ($imdb_id) {
	$where_area = " url = " . sqlesc((int)$imdb_id) ." AND torrents.id != ".sqlesc($id);
	$copies_res = sql_query("SELECT torrents.id, torrents.name, torrents.sp_state, torrents.size, torrents.added, torrents.seeders, torrents.leechers, categories.id AS catid, categories.name AS catname, categories.image AS catimage, sources.name AS source_name, media.name AS medium_name, codecs.name AS codec_name, standards.name AS standard_name, processings.name AS processing_name FROM torrents LEFT JOIN categories ON torrents.category=categories.id LEFT JOIN sources ON torrents.source = sources.id LEFT JOIN media ON torrents.medium = media.id  LEFT JOIN codecs ON torrents.codec = codecs.id LEFT JOIN standards ON torrents.standard = standards.id LEFT JOIN processings ON torrents.processing = processings.id WHERE " . $where_area . " ORDER BY torrents.id DESC") or sqlerr(__FILE__, __LINE__);

	$copies_count = mysql_num_rows($copies_res);
	if($copies_count > 0) {
	    $s = "<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";
	    $s.="<tr><td class=\"colhead\" style=\"padding: 0px; text-align:center;\">".$lang_details['col_type']."</td><td class=\"colhead\" align=\"left\">".$lang_details['col_name']."</td><td class=\"colhead\" align=\"center\">".$lang_details['col_quality']."</td><td class=\"colhead\" align=\"center\"><img class=\"size\" src=\"pic/trans.gif\" alt=\"size\" title=\"".$lang_details['title_size']."\" /></td><td class=\"colhead\" align=\"center\"><img class=\"time\" src=\"pic/trans.gif\" alt=\"time added\" title=\"".$lang_details['title_time_added']."\" /></td><td class=\"colhead\" align=\"center\"><img class=\"seeders\" src=\"pic/trans.gif\" alt=\"seeders\" title=\"".$lang_details['title_seeders']."\" /></td><td class=\"colhead\" align=\"center\"><img class=\"leechers\" src=\"pic/trans.gif\" alt=\"leechers\" title=\"".$lang_details['title_leechers']."\" /></td></tr>\n";
	    while ($copy_row = mysql_fetch_assoc($copies_res)) {
		$dispname = htmlspecialchars(trim($copy_row["name"]));
		$count_dispname=strlen($dispname);
		$max_lenght_of_torrent_name="80"; // maximum lenght
		if($count_dispname > $max_lenght_of_torrent_name) {
		    $dispname=substr($dispname, 0, $max_lenght_of_torrent_name) . "..";
		  }

		if (isset($copy_row["source_name"]))
		  $other_source_info = $copy_row[source_name].", ";
		if (isset($copy_row["medium_name"]))
		  $other_medium_info = $copy_row[medium_name].", ";
		if (isset($copy_row["codec_name"]))
		  $other_codec_info = $copy_row[codec_name].", ";
		if (isset($copy_row["standard_name"]))
		  $other_standard_info = $copy_row[standard_name].", ";
		if (isset($copy_row["processing_name"]))
		  $other_processing_info = $copy_row[processing_name].", ";

		$sphighlight = get_torrent_bg_color($copy_row['sp_state']);
		$sp_info = get_torrent_promotion_append($copy_row['sp_state']);

		$s .= "<tr". $sphighlight."><td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>".return_category_image($copy_row["catid"], "torrents.php?allsec=1&amp;")."</td><td class=\"rowfollow\" align=\"left\"><a href=\"" . htmlspecialchars(get_protocol_prefix() . $BASEURL . "/details.php?id=" . $copy_row["id"]. "&hit=1")."\">" . $dispname ."</a>". $sp_info."</td>" .
		  "<td class=\"rowfollow\" align=\"left\">" . rtrim(trim($other_source_info . $other_medium_info . $other_codec_info . $other_standard_info . $other_processing_info), ","). "</td>" .
		  "<td class=\"rowfollow\" align=\"center\">" . mksize($copy_row["size"]) . "</td>" .
		  "<td class=\"rowfollow nowrap\" align=\"center\">" . str_replace("&nbsp;", "<br />", gettime($copy_row["added"],false)). "</td>" .
		  "<td class=\"rowfollow\" align=\"center\">" . $copy_row["seeders"] . "</td>" .
		  "<td class=\"rowfollow\" align=\"center\">" . $copy_row["leechers"] . "</td>" .
		  "</tr>\n";
	      }
	    $s .= "</table>\n";
	    dl_item("<a href=\"javascript: klappe_news('othercopy')\"><span class=\"nowrap\"><img class=\"".($copies_count > 5 ? "plus" : "minus")."\" src=\"pic/trans.gif\" alt=\"Show/Hide\" id=\"picothercopy\" title=\"".$lang_detail['title_show_or_hide']."\" /> ".$lang_details['row_other_copies']."</span></a>", "<b>".$copies_count.$lang_details['text_other_copies']." </b><br /><div id='kothercopy' style=\"".($copies_count > 5 ? "display: none;" : "display: block;")."\">".$s."</div>",1);
	  }
      }

    if ($row["type"] == "multi") {
      function get_torrent_filelist($id) {
	global $lang_details;
	$s = '<table border="1" cellspacing="0" cellpadding="5"><thead>';
	$subres = sql_query("SELECT * FROM files WHERE torrent = ".sqlesc($id)." ORDER BY id");
	$s.='<tr><th>'.$lang_details['col_path'].'</th><th align="center"><img class="size" src="pic/trans.gif" alt="size" /></td></tr></thead><tbody>';
	while ($subrow = mysql_fetch_array($subres)) {
	  $s .= '<tr><td>' . $subrow['filename'] . '</td><td align="right">' . mksize($subrow['size']) . '</td></tr>';
	}
	$s .= '</tbody></table>';
	return $s;
      }
      
      $files_info = "<li><a href=\"javascript: klappe_news('filelist')\"><img class=\"plus\" src=\"pic/trans.gif\" alt=\"Show/Hide\" id=\"picfilelist\"><b>".$lang_details['text_num_files']."</b>". $row["numfiles"] . $lang_details['text_files'] ;
      $files_info .= '</a></li>';

      $files_detail = '<div id="kfilelist" style="display:none;">' . get_torrent_filelist($id) . '</div>';
    }
    else {
      $files_info = '';
      $files_detail = '';
    }

    function hex_esc($matches) {
      return sprintf("%02x", ord($matches[0]));
    }
    
    if ($enablenfo_main=='yes') {
      dl_item($lang_details['row_torrent_info'], '<div class="minor-list"><ul>' . $files_info . "<li><b>".$lang_details['row_info_hash'].":</b>".preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"]))."</li>". (get_user_class() >= $torrentstructure_class ? "<li><b>" . $lang_details['text_torrent_structure'] . "</b><a href=\"torrent_info.php?id=".$id."\">".$lang_details['text_torrent_info_note']."</a></li>" : "") . "</ul></div>" . $files_detail, 1);
    }
    
    dl_item($lang_details['row_hot_meter'], "<table><tr><td class=\"no_border_wide\"><b>" . $lang_details['text_views']."</b>". $row["views"] . "</td><td class=\"no_border_wide\"><b>" . $lang_details['text_hits']. "</b>" . $row["hits"] . "</td><td class=\"no_border_wide\"><b>" .$lang_details['text_snatched'] . "</b><a href=\"viewsnatches.php?id=".$id."\"><b>" . $row["times_completed"]. $lang_details['text_view_snatches'] . "</td><td class=\"no_border_wide\"><b>" . $lang_details['row_last_seeder']. "</b>" . gettime($row["last_action"]) . "</td></tr></table>",1);
    $bwres = sql_query("SELECT uploadspeed.name AS upname, downloadspeed.name AS downname, isp.name AS ispname FROM users LEFT JOIN uploadspeed ON users.upload = uploadspeed.id LEFT JOIN downloadspeed ON users.download = downloadspeed.id LEFT JOIN isp ON users.isp = isp.id WHERE users.id=".$row['owner']);
    $bwrow = mysql_fetch_array($bwres);
    if ($bwrow['upname'] && $bwrow['downname']) {
     // dl_item($lang_details['row_uploader_bandwidth'], "<img class=\"speed_down\" src=\"pic/trans.gif\" alt=\"Downstream Rate\" /> ".$bwrow['downname']."&nbsp;&nbsp;&nbsp;&nbsp;<img class=\"speed_up\" src=\"pic/trans.gif\" alt=\"Upstream Rate\" /> ".$bwrow['upname']."&nbsp;&nbsp;&nbsp;&nbsp;".$bwrow['ispname'],1);
    }

    /*
    // Health
    $seedersTmp = $row['seeders'];
    $leechersTmp = $row['leechers'];
    if ($leechersTmp >= 1)	// it is possible that there's traffic while have no seeders
    {
    $progressPerTorrent = 0;
    $i = 0;
    $subres = sql_query("SELECT seeder, finishedat, downloadoffset, uploadoffset, ip, port, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) AS st, connectable, agent, peer_id, UNIX_TIMESTAMP(last_action) AS la, userid FROM peers WHERE torrent = $row[id]") or sqlerr();

    while ($subrow = mysql_fetch_array($subres)) {
    $progressPerTorrent += sprintf("%.2f", 100 * (1 - ($subrow["to_go"] / $row["size"])));
    $i++;
    if ($subrow["seeder"] == "yes")
    $seeders[] = $subrow;
    else
    $downloaders[] = $subrow;
    }
    if ($i == 0)
    $i = 1;
    $progressTotal = sprintf("%.2f", $progressPerTorrent / $i);

    $totalspeed = 0;

    if($seedersTmp >=1)
    {
    if ($seeders) {
    foreach($seeders as $e) {
    $totalspeed = $totalspeed + ($e["uploaded"] - $e["uploadoffset"]) / max(1, ($e["la"] - $e["st"]));
    $totalspeed = $totalspeed + ($e["downloaded"] - $e["downloadoffset"]) / max(1, $e["finishedat"] - $e[st]);
    }
    }
    }

    if ($downloaders) {
    foreach($downloaders as $e) {
    $totalspeed = $totalspeed + ($e["uploaded"] - $e["uploadoffset"]) / max(1, ($e["la"] - $e["st"]));
    $totalspeed = $totalspeed + ($e["downloaded"] - $e["downloadoffset"]) / max(1, ($e["la"] - $e["st"]));
    }
    }

    $avgspeed = $lang_details['text_average_speed']."<b>" . mksize($totalspeed/($seedersTmp+$leechersTmp)) . "/s</b>";
    $totalspeed = $lang_details['text_total_speed']."<b>" . mksize($totalspeed) . "/s</b> ".$lang_details['text_health_note'];
    $health = $lang_details['text_avprogress'] . get_percent_completed_image(floor($progressTotal))." (".round($progressTotal)."%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".$lang_details['text_traffic']."</b>" . $avgspeed ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $totalspeed;
    }
    else
    $health = "<b>".$lang_details['text_traffic']. "</b>" . $lang_details['text_no_traffic'];

    if ($row["visible"] == "no")
    $health = "<b>".$lang_details['text_status']."</b>" . $lang_details['text_dead'] ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $health;

    tr($lang_details['row_health'], $health, 1);*/
    $startseed = '';
    if (checkPrivilege(['Torrent', 'startseed'])) {
      if ($row['startseed'] == 'yes') {
	$startseed = '<li>' . $lang_details['text_startseed'] . '</li>';
      }
      else {
	$startseed = '<li>' . $lang_details['text_no_startseed'] . '</li>';
      }
    }

    dl_item($lang_details['row_peers'] . '<br /><a class="sublink" id="showpeer" href="viewpeerlist.php?id='. $id . '">' . $lang_details['text_see_full_list'] . '</a><a class="sublink" id="hidepeer" style="display: none;" href="#">' . $lang_details['text_hide_list'] . '</a>',
       '<div id="peercount" class="minor-list list-seperator"><ul><li><span class="seeders">' . $row['seeders'] . '</span>' . $lang_details['text_seeders'] . add_s($row['seeders']) . '</li><li><span class="leechers">' . $row['leechers'] . '</span>' . $lang_details['text_leechers'] . add_s($row['leechers']) . "</li>" . $startseed . '</ul></div><div id="peerlist" style="display:none;"></div>' , true);
/**************************Keepers*************************/
	if($row['storing']==1){
		echo "<dt>$lang_details[text_storing_keepers]</dt><dd>";
		/*$kid_res = sql_query("SELECT keeper_id FROM storing_records WHERE torrent_id = ".sqlesc($id)." AND checkout = 0") or sqlerr(__FILE__,__LINE__);
		if(mysql_num_rows($kid_res)===0)
			echo $lang_details['text_no_storing'];
		while($kid = mysql_fetch_assoc($kid_res)){
			echo get_username($kid['keeper_id']);
		}*/
		foreach($storingKeeperList as $keeper_id){
			echo get_username($keeper_id),' ';	
		}
		echo "</dd>";
	}
/**************************Keepers*************************/		
    /**
     * Added by BruceWolf.
     * Show the donate bonus box.
     */
    define('DONATE_BONUS', true);
    include('./torrentDonateBonusBox.php');

    echo '<dt id="tcategories-title"><a href="//' . $CAKEURL . '/tcategories/">分类</a></dt>';
    echo '<dd>';
    App::uses('Torrent', 'Model');
    App::uses('Tcategory', 'Model');
    $Torrent = new Torrent;
    $Torrent->id = $id;
    if ($Torrent->exists()) {
      $torrent = $Torrent->read(null, $id);
      $tcategories = $torrent['Tcategory'];
      echo '<form action="//' . $CAKEURL . '/torrents/edit/' . $id . '.json?%2Fcake%2Ftorrents%2Fedit%2F' . $id . '=" method="post" accept-charset="utf-8" id="tcategories">';
      echo '<input type="hidden" name="_method" value="PUT">';
      echo '<input type="hidden" name="data[Torrent][id]" value="' . $id . '" id="TorrentId">';
      echo '<div class="minor-list"><ul>';
      $Tcategory = new Tcategory;

      $showCategories = '';
      $hiddenCategories = '';
      foreach ($tcategories as $tcategory) {
	$tcategory = $Tcategory->read(null, $tcategory['id'])['Tcategory'];
	$o = '<li class="tcategory"><span class="tcategory-show"><a href="//' . $CAKEURL . '/tcategories/view/' . $tcategory['id'] . '">' . $tcategory['showName'] . '</a><a href="#" class="edit-tcategory">±</a></span><span class="tcategory-edit" style="display: none;"><input type="text" placeholder="Tcategory" value="' . $tcategory['showName'] . '" /><input type="hidden" name="data[Tcategory][Tcategory][]" value="'. $tcategory['id'] . '"/ ><a href="#" class="remove-tcategory">-</a></span></li>';
	if ($tcategory['hidden']) {
	  $hiddenCategories .= $o;
	}
	else {
	  $showCategories .= $o;
	}
      }
      echo $showCategories;
      if ($hiddenCategories) {
	echo '<div style="display: none" id="hidden-tcategories">', '隐藏分类: ', $hiddenCategories, '</div>';
      }
      echo '</ul>';
      echo '<a href="#" id="add-tcategory">+</a>';
      echo '<input type="submit" value="Submit" class="btn2" style="display: none;" />';
      echo '</div></form>';
    }
    echo '</dd>';

    // ------------- start thanked-by block--------------//

    $torrentid = $id;
    $thanksby = "";
    $nothanks = "";
    $thanks_said = 0;
    $thanks_sql = sql_query("SELECT userid FROM thanks WHERE torrentid=".sqlesc($torrentid)." ORDER BY id DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
    $thanksCount = get_row_count("thanks", "WHERE torrentid=".sqlesc($torrentid));
    $thanks_all = mysql_num_rows($thanks_sql);
    if ($thanks_all) {
      while($rows_t = mysql_fetch_array($thanks_sql)) {
	$thanks_userid = $rows_t["userid"];
	if ($rows_t["userid"] == $CURUSER['id']) {
	  $thanks_said = 1;
	} else {
	  $thanksby .= get_username($thanks_userid)." ";
	}
      }
    }
    else {
      $nothanks = $lang_details['text_no_thanks_added'];
    }

    if (!$thanks_said) {
      $thanks_said = get_row_count("thanks", "WHERE torrentid=$torrentid AND userid=".sqlesc($CURUSER['id']));
    }
    if ($thanks_said == 0) {
      $buttonvalue = " value=\"".$lang_details['submit_say_thanks']."\"";
    } else {
      $buttonvalue = " value=\"".$lang_details['submit_you_said_thanks']."\" disabled=\"disabled\"";
      $thanksby = get_username($CURUSER['id'])." ".$thanksby;
    }
    $thanksbutton = "<input class=\"btn\" type=\"button\" id=\"saythanks\"  onclick=\"saythanks(".$torrentid.");\" ".$buttonvalue." />";
    dl_item($lang_details['row_thanks_by'],"<span id=\"thanksadded\" style=\"display: none;\"><input class=\"btn\" type=\"button\" value=\"".$lang_details['text_thanks_added']."\" disabled=\"disabled\" /></span><span id=\"curuser\" style=\"display: none;\">".get_username($CURUSER['id'])." </span><span id=\"thanksbutton\">".$thanksbutton."</span>&nbsp;&nbsp;<span id=\"nothanks\">".$nothanks."</span><span id=\"addcuruser\"></span>".$thanksby.($thanks_all < $thanksCount ? $lang_details['text_and_more'].$thanksCount.$lang_details['text_users_in_total'] : ""),1);
    // ------------- end thanked-by block--------------//

    print("</dl>\n");
  }
  else {
    stdhead($lang_details['head_comments_for_torrent']."\"" . $row["name"] . "\"");
    print("<h1 id=\"top\">".$lang_details['text_comments_for']."<a href=\"details.php?id=".$id."\">" . htmlspecialchars($row["name"]) . "</a></h1>\n");
  }

  // -----------------COMMENT SECTION ---------------------//
    $count = get_row_count("comments","WHERE torrent=".sqlesc($id));
    if ($count) {
      $page = '';
      $pager_opts = ['lastpagedefault' => 1];
      if (array_key_exists('page', $_GET)) {
      	$page = $_GET["page"];

	if ($page[0] == 'p') {
	  $findpost = 0 + substr($page, 1);
	  $res = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = $id AND id < $findpost ORDER BY id") or sqlerr(__FILE__, __LINE__);
	  $arr = mysql_fetch_row($res);
	  $i = $arr[0];
	  $page = floor($i / 10);
	}
	$pager_opts['page'] = $page;
      }

	list($pagertop, $pagerbottom, $limit,$next_page ,$offset) = pager(10, $count, "details.php?id=$id&cmtpage=1&", $pager_opts, "page");

	$subres = sql_query("SELECT id, text, user, added, editedby, editdate,editnotseen FROM comments WHERE torrent = $id ORDER BY id $limit") or sqlerr(__FILE__, __LINE__);
	$allrows = array();
	while ($subrow = mysql_fetch_array($subres)) {
	  $allrows[] = $subrow;
	}
	print($pagertop);
	commenttable($allrows,"torrent",$id,false, $offset);
	print($pagerbottom);
      }
  

  print ('<div id="forum-reply-post" class="table td"><h2><a class="index" href="'. htmlspecialchars("comment.php?action=add&pid=".$id."&type=torrent") .'">'.$lang_details['text_quick_comment']."</a></h2>");
  echo $lang_details['comment_warning'];
  echo "<form id=\"compose\" name=\"comment\" method=\"post\" action=\"".htmlspecialchars("comment.php?action=add&type=torrent")."\" onsubmit=\"return postvalid(this);\"><input type=\"hidden\" name=\"pid\" value=\"".$id."\" /><br />";
  quickreply('comment', 'body', $lang_details['submit_add_comment'], $lang_details['hint_placeholder_reply']);
  print("</form></div>");
}

echo '<script type="text/javascript">hb.torrent=' . json_encode(['id' => $id]) . ';</script>';
stdfoot();
