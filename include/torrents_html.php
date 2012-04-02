<?php
if (isset($searchstr))
	stdhead($lang_torrents['head_search_results_for'].$searchstr_ori);
elseif ($sectiontype == $browsecatmode)
	stdhead($lang_torrents['head_torrents']);
else stdhead($lang_torrents['head_music']);

if ($allsec != 1 || $enablespecial != 'yes'){ //do not print searchbox if showing bookmarked torrents from all sections;
?>
<form method="get" name="searchbox" id="form-searchbox" action="?">
  <div id="searchbox" class="table td">
    <div id="searchbox-header"><a href="#"><img class="plus" src="pic/trans.gif" id="picsearchboxmain" alt="Show/Hide" /><?php echo $lang_torrents['text_search_box'] ?></a></div>
    <div id="ksearchbox-simple" class="minor-list"><ul>
      <?php
  foreach($mainCatsName as $catNo => $name) {
    ?>
    <li><input type="checkbox" value="1" id="cat<?php echo $catNo; ?>" name="cat<?php echo $catNo; ?>" <?php echo ($selectedMainCat[$catNo] ? ' checked="checked"': ''); ?>/><a href="?cat=<?php echo $catNo; ?>"><?php echo $name; ?></a></li>
<?php
  }
?>
    </ul></div>
    <div id="ksearchboxmain" style="display: none;" >
      <div id="searchbox-cats">
      <table>
	<?php
	  function printcat($name, $listarray, $cbname, $wherelistina, $btname, $showimg = false) {
	    global $catpadding,$catsperrow,$lang_torrents,$CURUSER,$CURLANGDIR,$catimgurl;

	  print("<tr><td class=\"embedded\" colspan=\"".$catsperrow."\" align=\"left\"><b>".$name."</b></td></tr><tr>");
	  $i = 0;
	  foreach($listarray as $list){
	    if ($i && $i % $catsperrow == 0){
	      print("</tr><tr>");
	    }
	    print("<td align=\"left\" class=\"bottom\" style=\"padding-bottom: 4px; padding-left: ".$catpadding."px;\"><input type=\"checkbox\" id=\"".$cbname.$list['id']."\" name=\"".$cbname.$list['id']."\"" . (in_array($list['id'],$wherelistina) ? " checked=\"checked\"" : "") . " value=\"1\" />".($showimg ? return_category_image($list['id'], "?") : "<a title=\"" .$list['name'] . "\" href=\"?".$cbname."=".$list['id']."\">".$list['name']."</a>")."</td>\n");
	    $i++;
	  }
	  $checker = "<input id=\"".$btname."\" value='" .  $lang_torrents['input_check_all'] . "' class=\"btn medium\" type=\"button\" onclick=\"javascript:SetChecked('".$cbname."','".$btname."','". $lang_torrents['input_check_all'] ."','" . $lang_torrents['input_uncheck_all'] . "',-1,10)\" />";
	  print("<td colspan=\"2\" class=\"bottom\" align=\"left\" style=\"padding-left: 15px\">".$checker."</td>\n");
	  print("</tr>");
	}
    printcat($lang_torrents['text_category'],$cats,"cat",$wherecatina,"cat_check",true);

    if ($showsubcat){
      if ($showsource)
	printcat($lang_torrents['text_source'], $sources, "source", $wheresourceina, "source_check");
      if ($showmedium)
	printcat($lang_torrents['text_medium'], $media, "medium", $wheremediumina, "medium_check");
      if ($showcodec)
	printcat($lang_torrents['text_codec'], $codecs, "codec", $wherecodecina, "codec_check");
      if ($showaudiocodec)
	printcat($lang_torrents['text_audio_codec'], $audiocodecs, "audiocodec", $whereaudiocodecina, "audiocodec_check");
      if ($showstandard)
	printcat($lang_torrents['text_standard'], $standards, "standard", $wherestandardina, "standard_check");
      if ($showprocessing)
	printcat($lang_torrents['text_processing'], $processings, "processing", $whereprocessingina, "processing_check");
      if ($showteam)
	printcat($lang_torrents['text_team'], $teams, "team", $whereteamina, "team_check");
    }
	?>
      </table>
      </div>
      <?php hotmenu() ?>
		</div>
		<div class="minor-list"><ul>
		    <li>
		    <select class="med" name="inclbookmarked">
		      <option value="0"><?php echo $lang_torrents['text_all_bookmarked'] ?></option>
		      <option value="1"<?php print($inclbookmarked == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked'] ?></option>
		      <option value="2"<?php print($inclbookmarked == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked_exclude'] ?></option>
		    </select>
		  </li>
		  <li>
		  <select name="search_area">
		    <option value="0"><?php echo $lang_torrents['select_title'] ?></option>
		    <option value="1"<?php print($_GET["search_area"] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_description'] ?></option>
		    <option value="3"<?php print($_GET["search_area"] == 3 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_uploader'] ?></option>
		    <option value="4"<?php print($_GET["search_area"] == 4 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_imdb_url'] ?></option>
		  </select>
		</li>
		<li>
		  <select name="search_mode">
		    <option value="0"><?php echo $lang_torrents['select_and'] ?></option>
		    <option value="1"<?php echo $_GET["search_mode"] == 1 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_or'] ?></option>
		    <option value="2"<?php echo $_GET["search_mode"] == 2 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_exact'] ?></option>
		    <option value="3"<?php echo $_GET["search_mode"] == 3 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_complete'] ?></option>
		  </select>
		  <?php echo $lang_torrents['text_mode'] ?>
		</li>
		  <li>
		    <span class="medium">
		    <select class="med" name="incldead">
		      <option value="0"><?php echo $lang_torrents['select_including_dead'] ?></option>
		      <option value="1"<?php print($include_dead == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_active'] ?> </option>
		      <option value="2"<?php print($include_dead == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_dead'] ?></option>
		      <?php if (checkPrivilege(['Torrent', 'startseed'])): ?>
		      <option value="3"<?php print($include_dead == 3 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_no_startseed'] ?></option>
		      <?php endif; ?>
		    </select>
		    </li>
<li>
<select name="indate">
<?php
  $selections = array('0' => '不限时间', '3' => '3天内', '7' => '一周内', '30' => '一个月内', '90' => '三个月内');
  foreach ($selections as $days => $text) {
    echo '<option value="' . $days . '"';
    if ($indate == $days) {
      echo ' selected="selected"';
    }
    echo '>' . $text . '</option>';
  }
  ?>
</select></li>
		   <li>
		    <input id="searchinput" placeholder="<?php echo $lang_torrents['text_search'] ?>" name="search" type="search" value="<?php echo  $searchstr_ori ?>" autocomplete="off" style="width: 200px"/ >
		  </li>
		<li>
		  <input type="submit" class="btn" value="<?php echo $lang_torrents['submit_go'] ?>" />
		</li>
		  <li>
		    <input type="checkbox" name="hot" value="1" <?php echo ($wherehot? 'checked="checked"':'') ?>/><a href="?hot=1"><?php echo $lang_torrents['text_hot'] ?></a>
		    <input type="checkbox" name="storing" value="1" <?php echo ($wherestoring? 'checked="checked"':'') ?>/><a href="?storing=1"><?php echo $lang_torrents['text_storing'] ?></a>
		  </li>
</ul></div>

<?php
$Cache->new_page('hot_search', 3670, true);
if (!$Cache->get_page()){
	$secs = 3*24*60*60;
	$dt = sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs)));
	$dt2 = sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs*2)));
	sql_query("DELETE FROM suggest WHERE adddate <" . $dt2) or sqlerr();
	$searchres = sql_query("SELECT keywords, COUNT(DISTINCT userid) as count FROM suggest WHERE adddate >" . $dt . " GROUP BY keywords ORDER BY count DESC LIMIT 15") or sqlerr();
	$hotcount = 0;
	$hotsearch = "";
	while ($searchrow = mysql_fetch_assoc($searchres))
	{
		$hotsearch .= "<li><a href=\"".htmlspecialchars("?search=" . rawurlencode($searchrow["keywords"]) . "&notnewword=1")."\">" . $searchrow["keywords"] . '</a></li>';
		$hotcount += mb_strlen($searchrow["keywords"],"UTF-8");
		if ($hotcount > 60)
			break;
	}
	$Cache->add_whole_row();
	if ($hotsearch) {
	  print('<div id="hot-search" class="minor-list"><ul>'.$hotsearch.'</ul></div>');
	}
	$Cache->end_whole_row();
	$Cache->cache_page();
}
echo $Cache->next_row();
?>
</div></form>
<script type="text/javascript" src="load.php?name=torrents_searchbox.js"></script>
<?php
}

if ($Advertisement->enable_ad()){
  $belowsearchboxad = $Advertisement->get_ad('belowsearchbox');
  echo "<div align=\"center\" style=\"margin-top: 10px\" id=\"ad_belowsearchbox\">".$belowsearchboxad[0]."</div>";
}

if($inclbookmarked == 1) {
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_bookmarked_torrent'] . "</h1>");
}
elseif($inclbookmarked == 2) {
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_not_bookmarked_torrent'] . "</h1>");
}
?>
<a id="content-marker"></a>
<?php
if ($count) {
  if ($next_page_href != '') {
    print($pagertop);
  }

  $swap_headings = $_GET["swaph"];

  if ($sectiontype == $browsecatmode)
    torrenttable($rows, "torrents", $swap_headings);
//  elseif ($sectiontype == $specialcatmode) die('Error, Contact SYSOP');
//    torrenttable($res, "music", $swap_headings);
//  else die('') die('Error, Contact SYSOP');
//    torrenttable($res, "bookmarks", $swap_headings);

  if ($next_page_href != '') {
    print($pagerbottom);
  }
}
else {
  if (isset($searchstr)) {
    print("<br />");
    stdmsg($lang_torrents['std_search_results_for'] . $searchstr_ori . "\"",$lang_torrents['std_try_again']);
  }
  else {
    stdmsg($lang_torrents['std_nothing_found'],$lang_torrents['std_no_active_torrents']);
  }

  torrenttable('', '', '',true);
}
?>
<div id="loader" style="display: none; "></div>
<?php

if ($CURUSER){
	if ($sectiontype == $browsecatmode)
		$USERUPDATESET[] = "last_browse = ".TIMENOW;
	else	$USERUPDATESET[] = "last_music = ".TIMENOW;
}
echo '<script type="text/javascript">hb.nextpage = "'. $next_page_href .'";';
echo 'hb.config.torrents_query = (' . php_json_encode($_SERVER['QUERY_STRING']) . ');';
echo 'hb.constant.maincats = ' . php_json_encode($mainCats) . ';</script>';


stdfoot();

function hotmenu(){
  global $lang_functions, $lang_torrents, $promotion_text;
  global $wherehot, $indate, $special_state;
?>
  <div id="hotbox" class="table minor-list"><ul>


<li class="minor-list-vertical"><div class="title" style="margin-bottom:0;">促销:</div><ul>
<li><input type="radio" name="spstate" value="0" /><?php echo $lang_torrents['select_all'] ?></li>
  <?php foreach ($promotion_text as $idx => $pr){
  $val = $idx + 1;
  echo '<li><input type="radio" name="spstate" value="' . $val . '"';
  if ($special_state == $val) {
    echo ' checked="checked"';
  }
  echo '><a href="?spstate=' . $val .'">';
  if ($val == 1) {
    echo $lang_functions[$pr['lang']];
  }
  else {
    echo '<img class="' . $pr['name'] . '" alt="' . $lang_functions[$pr['lang']] . '" title="' . $lang_functions[$pr['lang']] . '" src="pic/trans.gif"/ >';
  }
  echo '</a></li>';
}?>
</ul></li>
<li><input type="checkbox" id="swaph" name="swaph" value="1" <?php if ($_GET['swaph']){echo 'checked="checked"';}?> /><a href="?swaph=1">中文主标题</a></li>
</ul></div>
<?php
}
?>




