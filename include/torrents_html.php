<?php
if (isset($searchstr))
	stdhead($lang_torrents['head_search_results_for'].$searchstr_ori);
elseif ($sectiontype == $browsecatmode)
	stdhead($lang_torrents['head_torrents']);
else stdhead($lang_torrents['head_music']);

if ($allsec != 1 || $enablespecial != 'yes'){ //do not print searchbox if showing bookmarked torrents from all sections;
?>
<form method="get" name="searchbox" action="?">
  <div id="searchbox" class="table td">
    <div id="searchbox-header"><a href="javascript: klappe_news('searchboxmain');klappe_news('searchbox-simple', true)"><img class="plus" src="pic/trans.gif" id="picsearchboxmain" alt="Show/Hide" /><?php echo $lang_torrents['text_search_box'] ?></a></div>
    <div id="ksearchbox-simple" class="minor-list"><ul>
      <?php
  foreach($mainCatsName as $catNo => $name) {
    ?>
      <li><input type="checkbox" value="1" name="cat<?php echo $catNo; ?>"/><a href="?cat=<?php echo $catNo; ?>"><?php echo $name; ?></a></li>
<?
  }
?>
    </ul></div>
    <div id="ksearchboxmain" style="display: none;" >
      <div id="searchbox-cats">
      <table>
					<?php
						function printcat($name, $listarray, $cbname, $wherelistina, $btname, $showimg = false)
						{
							global $catpadding,$catsperrow,$lang_torrents,$CURUSER,$CURLANGDIR,$catimgurl;

							print("<tr><td class=\"embedded\" colspan=\"".$catsperrow."\" align=\"left\"><b>".$name."</b></td></tr><tr>");
							$i = 0;
							foreach($listarray as $list){
								if ($i && $i % $catsperrow == 0){
									print("</tr><tr>");
								}
								print("<td align=\"left\" class=\"bottom\" style=\"padding-bottom: 4px; padding-left: ".$catpadding."px;\"><input type=\"checkbox\" id=\"".$cbname.$list[id]."\" name=\"".$cbname.$list[id]."\"" . (in_array($list[id],$wherelistina) ? " checked=\"checked\"" : "") . " value=\"1\" />".($showimg ? return_category_image($list[id], "?") : "<a title=\"" .$list[name] . "\" href=\"?".$cbname."=".$list[id]."\">".$list[name]."</a>")."</td>\n");
								$i++;
							}
							$checker = "<input name=\"".$btname."\" value='" .  $lang_torrents['input_check_all'] . "' class=\"btn medium\" type=\"button\" onclick=\"javascript:SetChecked('".$cbname."','".$btname."','". $lang_torrents['input_check_all'] ."','" . $lang_torrents['input_uncheck_all'] . "',-1,10)\" />";
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
			<div class="rowfollow minor-list" id="searchbox-opts">
			  <ul><li>
			    <span class="medium"><?php echo $lang_torrents['text_show_dead_active'] ?></span>

			    <select class="med" name="incldead" style="width: 100px;">
			      <option value="0"><?php echo $lang_torrents['select_including_dead'] ?></option>
			      <option value="1"<?php print($include_dead == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_active'] ?> </option>
			      <option value="2"<?php print($include_dead == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_dead'] ?></option>
			    </select>
			    </li><li>
			    <span class="medium"><?php echo $lang_torrents['text_show_bookmarked'] ?></span>
			    <select class="med" name="inclbookmarked" style="width: 100px;">
			      <option value="0"><?php echo $lang_torrents['select_all'] ?></option>
			      <option value="1"<?php print($inclbookmarked == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked'] ?></option>
			      <option value="2"<?php print($inclbookmarked == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked_exclude'] ?></option>
			    </select>
			    </li><li>
					<input type="checkbox" id="swaph" name="swaph" value="1" <?php if ($_GET['swaph']){echo 'checked="checked"';}?> />
			    <a href="?swaph=1">以中文为主标题</a>
			  </li>
			</ul>
			</div>
		</div>
		<div class="minor-list"><ul>
		   <li><?php echo $lang_torrents['text_search'] ?>
		    <input id="searchinput" name="search" type="text" value="<?php echo  $searchstr_ori ?>" autocomplete="off" style="width: 200px"/ >
		  <script src="suggest.js" type="text/javascript"></script>
		  <div id="suggcontainer" class="minor-div-vertical" style="display: none;">
		    <ul id="suggestions"></ul>
		  </div></li>
		  <li>
		  <?php echo $lang_torrents['text_in'] ?>
		  <select name="search_area">
		    <option value="0"><?php echo $lang_torrents['select_title'] ?></option>
		    <option value="1"<?php print($_GET["search_area"] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_description'] ?></option>
		    <option value="3"<?php print($_GET["search_area"] == 3 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_uploader'] ?></option>
		    <option value="4"<?php print($_GET["search_area"] == 4 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_imdb_url'] ?></option>
		  </select>
		</li>
		<li>
		  <?php echo $lang_torrents['text_with'] ?>
		  <select name="search_mode" style="width: 60px;">
		    <option value="0"><?php echo $lang_torrents['select_and'] ?></option>
		    <option value="1"<?php echo $_GET["search_mode"] == 1 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_or'] ?></option>
		    <option value="2"<?php echo $_GET["search_mode"] == 2 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_exact'] ?></option>
		  </select>
		  <?php echo $lang_torrents['text_mode'] ?>
		</li>
		<li>
		  <input type="submit" class="btn" value="<?php echo $lang_torrents['submit_go'] ?>" />
		</li></ul></div>

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
	  print('<div class="minor-list"><ul>'.$hotsearch.'</ul></div>');
	}
	$Cache->end_whole_row();
	$Cache->cache_page();
}
echo $Cache->next_row();
?>
</div></form>

<?php
}


	if ($Advertisement->enable_ad()){
			$belowsearchboxad = $Advertisement->get_ad('belowsearchbox');
			echo "<div align=\"center\" style=\"margin-top: 10px\" id=\"ad_belowsearchbox\">".$belowsearchboxad[0]."</div>";
	}
if($inclbookmarked == 1)
{
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_bookmarked_torrent'] . "</h1>");
}
elseif($inclbookmarked == 2)
{
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_not_bookmarked_torrent'] . "</h1>");
}









if ($count) {
	print($pagertop);

	$swap_headings = $_GET["swaph"];

	if ($sectiontype == $browsecatmode)
	  torrenttable($res, "torrents", $swap_headings);
	elseif ($sectiontype == $specialcatmode) 
	  torrenttable($res, "music", $swap_headings);
	else 
	  torrenttable($res, "bookmarks", $swap_headings);

	if ($swap_headings) {
	  ?><script type="text/javascript">
	  hb.config.swaph = true;
	  </script>
	  <?php
	}
	
	print($pagerbottom);
}
else {
	if (isset($searchstr)) {
		print("<br />");
		stdmsg($lang_torrents['std_search_results_for'] . $searchstr_ori . "\"",$lang_torrents['std_try_again']);
	}
	else {
		stdmsg($lang_torrents['std_nothing_found'],$lang_torrents['std_no_active_torrents']);
	}
}
if ($CURUSER){
	if ($sectiontype == $browsecatmode)
		$USERUPDATESET[] = "last_browse = ".TIMENOW;
	else	$USERUPDATESET[] = "last_music = ".TIMENOW;
}
print('<script type="text/javascript">hb.nextpage = "'. $next_page_href .'"</script>');
?>
<a href="#" id="back-to-top" title="回到页首" style="display:none;"></a>
<script src="js/torrents.js" type="text/javascript"></script>

<?php
stdfoot();

function hotmenu(){
  global $lang_functions, $lang_torrents; 
?>
  <div id="hotbox" class="table">
  <div class="minor-list"><span class="title"><span class="align">热门资源</span>:</span><ul><li><a class="index" href=hot.php?cat=>最多做种</a></li><li><a class="index" href=hot.php?cat=1>电影</a></li><li><a class="index" href=hot.php?cat=2>剧集</a></li><li><a class="index" href=hot.php?cat=3>动漫</a></li><li><a class="index" href=hot.php?cat=4>游戏</a></li><li><a class="index" href=hot.php?cat=5>综艺</a></li>
  <li><a class="index" href=hot.php?cat=6>资料</a></li><li><a class="index" href=hot.php?cat=7>体育</a></li><li><a class="index" href=hot.php?cat=8>音乐</a></li><li><a class="index" href=hot.php?cat=9>纪录片</a></li><li><a class="index" href=hot.php?cat=10>软件</a></li><li><a class="index" href=hot.php?cat=11>MTV</a>
</ul></div>
<div class="minor-list"><span class="title">最近热门:</span><ul><li><a class="index" href=hot.php?time=1>3天</a></li><li><a class="index" href=hot.php?time=2>1个月</a></li><li><a class="index" href=hot.php?time=3>3个月</a></li></ul></div>

<div class="minor-list-vertical"><span class="title"><span class="align">促&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;销</span>:</span><ul>
<li><input type="radio" name="spstate" value="0" /><?php echo $lang_torrents['select_all'] ?></li>
<li><input type="radio" name="spstate" value="1" /><a href="?spstate=1"><?php echo $lang_functions['text_normal']?></a></li>
<li><input type="radio" name="spstate" value="2" /><a href="?spstate=2"><span class="free"><?php echo $lang_functions['text_free'] ?></span></a></li>
<li><input type="radio" name="spstate" value="3" /><a href="?sptate=3"><span class="twoup"><?php echo $lang_functions['text_two_times_up'] ?></span></a></li>
<li><input type="radio" name="spstate" value="4" /><a href="?sptate=4"><span class="twoupfree"><?php echo $lang_functions['text_free_two_times_up'] ?></span></a></li>
<li><input type="radio" name="spstate" value="5" /><a href="?sptate=5"><span class="halfdown"><?php echo $lang_functions['text_half_down'] ?></span></a></li>
<li><input type="radio" name="spstate" value="6" /><a href="?sptate=6"><span class="twouphalfdown"><?php echo $lang_functions['text_half_down_two_up'] ?></span></a></li>
<li><input type="radio" name="spstate" value="7" /><a href="?sptate=7"><span class="thirtypercent"><?php echo $lang_functions['text_thirty_percent_down'] ?></span></a></li>
</ul></div>
</div>
<?php
}
?>