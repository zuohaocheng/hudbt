<?php

$ip=getenv('REMOTE_ADDR');
//echo $ip;

require "include/bittorrent.php";
dbconn(true);
require_once(get_langfile_path());

// �ܶ��û���ʾ��½������ת�������¼��Ҳû���˺ſɵ�½����ʱ����4�ˡ��㲻�� Q������BruceWolf
//����ϸ����� û�д��� ������һ��ʼ�İ汾������ �Һ�4�ĺ���
//�������������� ���ں��� 
//echo $ip."ok";exit("ok");
if($ip=="2001:da8:3000:ffff::a168"){
  if(!$_SERVER["HTTP_SHIB_IDENTITY_PROVIDER"]){
    header("Location: https://sp-pt.hust.edu.cn:444/Shibboleth.sso/DS?target=http://sp-pt.hust.edu.cn:81");
    die();
  }
  session_start();
  $_SESSION["carsi_username"]=$_SERVER["HTTP_USERNAME"];
  $_SESSION["carsi_inst"]=$_SERVER["HTTP_INSTITUTION"];
  $_SESSION["carsi_ip"]=$_SERVER["HTTP_X_FORWARDED_FOR"];
  loggedinorreturn2(true);
}
else{
  loggedinorreturn(true);
}

if ($showextinfo['imdb'] == 'yes')
  require_once ("imdb/imdb.class.php");
if ($_SERVER["REQUEST_METHOD"] == "POST")
  {
    if ($showpolls_main == "yes")
      {
	$choice = $_POST["choice"];
	if ($CURUSER && $choice != "" && $choice < 256 && $choice == floor($choice))
	  {
	    $res = sql_query("SELECT * FROM polls ORDER BY added DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
	    $arr = mysql_fetch_assoc($res) or die($lang_index['std_no_poll']);
	    $pollid = $arr["id"];

	    $hasvoted = get_row_count("pollanswers","WHERE pollid=".sqlesc($pollid)." && userid=".sqlesc($CURUSER["id"]));
	    if ($hasvoted)
	      stderr($lang_index['std_error'],$lang_index['std_duplicate_votes_denied']);
	    sql_query("INSERT INTO pollanswers VALUES(0, ".sqlesc($pollid).", ".sqlesc($CURUSER["id"]).", ".sqlesc($choice).")") or sqlerr(__FILE__, __LINE__);
	    $Cache->delete_value('current_poll_content');
	    $Cache->delete_value('current_poll_result', true);
	    if (mysql_affected_rows() != 1)
	      stderr($lang_index['std_error'], $lang_index['std_vote_not_counted']);
	    //add karma
	    KPS("+",$pollvote_bonus,$userid);

	    header("Location: " . get_protocol_prefix() . "$BASEURL/");
	    die;
	  }
	else
	  stderr($lang_index['std_error'], $lang_index['std_option_unselected']);
      }
  }

stdhead($lang_index['head_home']);
begin_main_frame();

// ------------- start: recent news ------------------//
print('<h2 class="page-titles">'.$lang_index['text_recent_news'].(get_user_class() >= $newsmanage_class ? " - <font class=\"small\">[<a class=\"altlink\" href=\"news.php\"><b>".$lang_index['text_news_page']."</b></a>]</font>" : "")."</h2>");

$Cache->new_page('recent_news', 86400, true);
if (!$Cache->get_page()){
  $res = sql_query("SELECT * FROM news ORDER BY added DESC LIMIT ".(int)$maxnewsnum_main) or sqlerr(__FILE__, __LINE__);
  if (mysql_num_rows($res) > 0)
    {
      $Cache->add_whole_row();
      print('<div id="news" class="table td main minor-list-vertical"><ul>');
      $Cache->end_whole_row();
      $counter = 0;
      while($array = mysql_fetch_array($res)) {
	  $Cache->add_row();
	  $Cache->add_part();
	    print("<li><a href=\"javascript: klappe_news('a".$array['id']."')\"><img class=\"" . ($counter == 0 ? 'minus' : 'plus') . "\" src=\"pic/trans.gif\" id=\"pica".$array['id']."\" alt=\"Show/Hide\" title=\"".$lang_index['title_show_or_hide']."\" />&nbsp;" . date("Y.m.d",strtotime($array['added'])) . " - " ."<b>". $array['title'] . "</b></a>");
	  $Cache->end_part();
	  $Cache->add_part();
	    print(" [<a class=\"faqlink\" href=\"news.php?action=edit&amp;newsid=" . $array['id'] . "\">".$lang_index['text_e']."</a>] ");
	  print(" [<a class=\"faqlink\" href=\"news.php?action=delete&amp;newsid=" . $array['id'] . "\">".$lang_index['text_d']."</a>]");
	  $Cache->end_part();
	  $Cache->add_part();
	  print("<div id=\"ka".$array['id']."\" style=\"display: " . ($counter == 0 ? 'block' : 'none'). ";\"> ".format_comment($array["body"],0)." </div></li>");
	  $Cache->end_part();

	  $Cache->end_row();
	  ++$counter;
      }
      $Cache->break_loop();
      $Cache->add_whole_row();
      print("</ul></div>\n");
      $Cache->end_whole_row();
    }
  $Cache->cache_page();
}
echo $Cache->next_row();
while($Cache->next_row()){
  echo $Cache->next_part();
  if (get_user_class() >= $newsmanage_class) {
    echo $Cache->next_part();
  }
  else {
    $Cache->next_part();
  }
  echo $Cache->next_part();
}
echo $Cache->next_row();
// ------------- end: recent news ------------------//
// ------------- start: hot and classic movies ------------------//
if ($showextinfo['imdb'] == 'yes' && ($showmovies['hot'] == "yes" || $showmovies['classic'] == "yes"))
  {
    $type = array('hot', 'classic');
    foreach($type as $type_each)
      {
	if($showmovies[$type_each] == 'yes')
	  {
	    $Cache->new_page($type_each.'_resources', 900, true);
	    if (!$Cache->get_page())
	      {
		$Cache->add_whole_row();

		$imdbcfg = new imdb_config();
		$res = sql_query("SELECT * FROM torrents WHERE picktype = " . sqlesc($type_each) . " AND seeders > 0 AND url != '' ORDER BY id DESC LIMIT 30") or sqlerr(__FILE__, __LINE__);
		if (mysql_num_rows($res) > 0)
		  {
		    $movies_list = "";
		    $count = 0;
		    $allImdb = array();
		    while($array = mysql_fetch_array($res))
		      {
			$pro_torrent = get_torrent_promotion_append($array[sp_state],'word');
			if ($imdb_id = parse_imdb_id($array["url"]))
			  {
			    if (array_search($imdb_id, $allImdb) !== false) { //a torrent with the same IMDb url already exists
			      continue;
			    }
			    $allImdb[]=$imdb_id;
			    $photo_url = $imdbcfg->photodir . $imdb_id. $imdbcfg->imageext;

			    if (file_exists($photo_url))
			      $thumbnail = "<img width=\"101\" height=\"140\" src=\"".$photo_url."\" border=\"0\" alt=\"poster\" />";
			    else continue;
			  }
			else continue;
			$thumbnail = "<a href=\"details.php?id=" . $array['id'] . "&amp;hit=1\" onmouseover=\"domTT_activate(this, event, 'content', '" . htmlspecialchars("<font class=\'big\'><b>" . (addslashes($array['name'] . $pro_torrent)) . "</b></font><br /><font class=\'medium\'>".(addslashes($array['small_descr'])) ."</font>"). "', 'trail', true, 'delay', 0,'lifetime',5000,'styleClass','niceTitle','maxWidth', 600);\">" . $thumbnail . "</a>";
			$movies_list .= $thumbnail;
			$count++;
			if ($count >= 9)
			  break;
		      }
?>
<h2 class="page-titles"><?php echo $lang_index['text_' . $type_each . 'movies'] ?></h2>
<table width="100%" border="1" cellspacing="0" cellpadding="5"><tr><td class="text nowrap" align="center">
<?php echo $movies_list ?></td></tr></table>
<?php
		  }
		$Cache->end_whole_row();
		$Cache->cache_page();
	      }
	    echo $Cache->next_row();
	  }
      }
  }
// ------------- end: hot and classic movies ------------------//
// ------------- start: funbox ------------------//
if ($showfunbox_main == "yes"){
  // Get the newest fun stuff
  $funid = $Cache->get_value('current_fun_content_id');
  $neednew = $Cache->get_value('current_fun_content_neednew');
  $owner = $Cache->get_value('current_fun_content_owner');
  if (!$funid){
    $result = sql_query("SELECT fun.id, fun.userid, IF(ADDTIME(added, '1 0:0:0') < NOW(),true,false) AS neednew FROM fun WHERE status != 'banned' AND status != 'dull' ORDER BY added DESC LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = mysql_fetch_array($result);
    $funid = $row['id'];
    $neednew = $row['neednew'];
    $owner = $row['userid'];
    $Cache->cache_value('current_fun_content_id', $funid, 900);
    $Cache->cache_value('current_fun_content_neednew', $neednew, 900);
    $Cache->cache_value('current_fun_content_owner', $owner, 900);
  }

    if (!$funid) { //There is no funbox item
      print('<h2 class="page-titles">'.$lang_index['text_funbox'].(get_user_class() >= $newfunitem_class ? "<font class=\"small\"> - [<a class=\"altlink\" href=\"fun.php?action=new\"><b>".$lang_index['text_new_fun']."</b></a>]</font>" : "")."</h2>");
    }
  else {
      $totalvote = $Cache->get_value('current_fun_vote_count');
      if ($totalvote == ""){
	$totalvote = get_row_count("funvotes", "WHERE funid = ".sqlesc($funid));
	$Cache->cache_value('current_fun_vote_count', $totalvote, 756);
      }
      $funvote = $Cache->get_value('current_fun_vote_funny_count');
      if ($funvote == ""){
	$funvote = get_row_count("funvotes", "WHERE funid = ".sqlesc($funid)." AND vote='fun'");
	$Cache->cache_value('current_fun_vote_funny_count', $funvote, 756);
      }
      //check whether current user has voted
      $funvoted = get_row_count("funvotes", "WHERE funid = ".sqlesc($funid)." AND userid=".sqlesc($CURUSER['id']));
      print ('<h2 class="page-titles">'.$lang_index['text_funbox']);
      if ($CURUSER) {
	  print("<font class=\"small\">".(get_user_class() >= $log_class ? " - [<a class=\"altlink\" href=\"log.php?action=funbox\"><b>".$lang_index['text_more_fun']."</b></a>]": "").($neednew && get_user_class() >= $newfunitem_class ? " - [<a class=\"altlink\" href=\"fun.php?action=new\"><b>".$lang_index['text_new_fun']."</b></a>]" : "" ).( ($CURUSER['id'] == $owner || get_user_class() >= $funmanage_class) ? " - [<a class=\"altlink\" href=\"fun.php?action=edit&amp;id=".$funid."&amp;returnto=index.php\"><b>".$lang_index['text_edit']."</b></a>]" : "").(get_user_class() >= $funmanage_class ? " - [<a class=\"altlink\" href=\"fun.php?action=delete&amp;id=".$funid."&amp;returnto=index.php\"><b>".$lang_index['text_delete']."</b></a>] - [<a class=\"altlink\" href=\"fun.php?action=ban&amp;id=".$funid."&amp;returnto=index.php\"><b>".$lang_index['text_ban']."</b></a>]" : "")."</font>");
	}
      print("</h2>");

      print('<div id="funbox" class="table td text main">');
      require_once(get_langfile_path('fun.php'));
      echo '<div>', get_fun(), '</div>';

      if ($CURUSER) {
	echo '<div id="funvote" class="minor-list compact">';
	print("<b>".$funvote."</b>" . $lang_index['text_out_of'] . $totalvote . $lang_index['text_people_found_it']);

	if (!$funvoted) {
	  echo "<span class=\"striking\">".$lang_index['text_your_opinion']."</span>";

	  $id = $funid;
	  echo '<ul>';
	  echo '<li><form action="fun.php?action=vote" method="post"><input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="yourvote" value="fun" /><input type="submit" class="btn" value="' . $lang_index['submit_fun'] . '" /></form></li>';
	  echo '<li><form action="fun.php?action=vote" method="post"><input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="yourvote" value="dull" /><input type="submit" class="btn" value="' . $lang_index['submit_dull'] . '" /></form></li>';
	  echo '</ul></div>';
	  echo "<div id=\"voteaccept\" style=\"display: none;\">" . $lang_index['text_vote_accepted'];
	}
	echo '</div>';
      }
      print("</div>");
    }
}
// ------------- end: funbox ------------------//
// ------------- start: shoutbox ------------------//
if ($showshoutbox_main == "yes") {
?>
<h2 class="page-titles"><?php echo $lang_index['text_shoutbox'] ?> - <font class="small"><?php echo $lang_index['text_auto_refresh_after']?></font><font class='striking' id="countdown"></font><font class="small"><?php echo $lang_index['text_seconds']?></font></h2>
<?php
  print("<table width=\"100%\"><tr><td class=\"text\">\n");
  print("<iframe src='shoutbox.php?type=shoutbox' width='900' height='180' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe><br /><br />\n");
  print("<form action='shoutbox.php' method='get' target='sbox' name='shbox'>\n");
  print("<label for='shbox_text'>".$lang_index['text_message']."</label><input type='text' name='shbox_text' id='shbox_text' size='100' style='width: 650px; border: 1px solid gray;' />  <input type='submit' id='hbsubmit' class='btn' name='shout' value=\"".$lang_index['sumbit_shout']."\" />");
  if ($CURUSER['hidehb'] != 'yes' && $showhelpbox_main =='yes')
    print("<input type='submit' class='btn' name='toguest' value=\"".$lang_index['sumbit_to_guest']."\" />");
  print("<input type='reset' class='btn' value=\"".$lang_index['submit_clear']."\" /> <input type='hidden' name='sent' value='yes' /><input type='hidden' name='type' value='shoutbox' /><br />\n");
  print(smile_row("shbox","shbox_text"));
  print("</form></td></tr></table>");
}
// ------------- end: shoutbox ------------------//
// ------------- start: latest forum posts ------------------//

if ($showlastxforumposts_main == "yes" && $CURUSER) {
  $res = sql_query("SELECT posts.id AS pid, posts.userid AS userpost, posts.added, topics.id AS tid, topics.subject, topics.forumid, topics.views, forums.name FROM posts, topics, forums WHERE posts.topicid = topics.id AND topics.forumid = forums.id AND minclassread <=" . sqlesc(get_user_class()) . " ORDER BY posts.id DESC LIMIT 5") or sqlerr(__FILE__,__LINE__);
  if(mysql_num_rows($res) != 0) {
    print('<h2 class="page-titles">'.$lang_index['text_last_five_posts']."</h2>");
    print("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td class=\"colhead\" width=\"100%\" align=\"left\">".$lang_index['col_topic_title']."</td><td class=\"colhead\" align=\"center\">".$lang_index['col_view']."</td><td class=\"colhead\" align=\"center\">".$lang_index['col_author']."</td><td class=\"colhead\" align=\"left\">".$lang_index['col_posted_at']."</td></tr>");

    while ($postsx = mysql_fetch_assoc($res)) {
      print("<tr><td><a href=\"forums.php?action=viewtopic&amp;topicid=".$postsx["tid"]."&amp;page=p".$postsx["pid"]."#pid".$postsx["pid"]."\"><b>".htmlspecialchars($postsx["subject"])."</b></a><br />".$lang_index['text_in']."<a href=\"forums.php?action=viewforum&amp;forumid=".$postsx["forumid"]."\">".htmlspecialchars($postsx["name"])."</a></td><td align=\"center\">".$postsx["views"]."</td><td align=\"center\">" . get_username($postsx["userpost"]) ."</td><td>".gettime($postsx["added"])."</td></tr>");
    }
    print("</table>");
  }
}

// ------------- end: latest forum posts ------------------//
// ------------- start: latest torrents ------------------//

if ($showlastxtorrents_main == "yes") {
  $result = sql_query("SELECT * FROM torrents where visible='yes' ORDER BY added DESC LIMIT 5") or sqlerr(__FILE__, __LINE__);
  if(mysql_num_rows($result) != 0 )
    {
      print ('<h2 class="page-titles">'.$lang_index['text_last_five_torrent']."</h2>");
      print ("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td class=\"colhead\" width=\"100%\">".$lang_index['col_name']."</td><td class=\"colhead\" align=\"center\">".$lang_index['col_seeder']."</td><td class=\"colhead\" align=\"center\">".$lang_index['col_leecher']."</td></tr>");

      while( $row = mysql_fetch_assoc($result) )
	{
	  print ("<tr><a href=\"details.php?id=". $row['id'] ."&amp;hit=1\"><td><a href=\"details.php?id=". $row['id'] ."&amp;hit=1\"><b>" . htmlspecialchars($row['name']) . "</b></td></a><td align=\"center\">" . $row['seeders'] . "</td><td align=\"center\">" . $row['leechers'] . "</td></tr>");
	}
      print ("</table>");
    }
}
// ------------- end: latest torrents ------------------//
// ------------- start: polls ------------------//
if ($CURUSER && $showpolls_main == "yes")
  {
    // Get current poll
    if (!$arr = $Cache->get_value('current_poll_content')){
      $res = sql_query("SELECT * FROM polls ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
      
      $arr = mysql_fetch_array($res);
      $Cache->cache_value('current_poll_content', $arr, 7226);
    }
    if (!$arr)
      $pollexists = false;
    else $pollexists = true;

    print('<h2 class="page-titles">'.$lang_index['text_polls']);

    if (get_user_class() >= $pollmanage_class) {
      print("<font class=\"small\"> - [<a class=\"altlink\" href=\"makepoll.php?returnto=main\"><b>".$lang_index['text_new']."</b></a>]\n");
      if ($pollexists) {
	print(" - [<a class=\"altlink\" href=\"makepoll.php?action=edit&amp;pollid=".$arr[id]."&amp;returnto=main\"><b>".$lang_index['text_edit']."</b></a>]\n");
	print(" - [<a class=\"altlink\" href=\"log.php?action=poll&amp;do=delete&amp;pollid=".$arr[id]."&amp;returnto=main\"><b>".$lang_index['text_delete']."</b></a>]");
	print(" - [<a class=\"altlink\" href=\"polloverview.php?id=".$arr[id]."\"><b>".$lang_index['text_detail']."</b></a>]");
      }
      print("</font>");
    }
    print("</h2>");
    if ($pollexists) {
      $pollid = 0+$arr["id"];
      $userid = 0+$CURUSER["id"];
      $question = $arr["question"];

      print('<div class="text table td" id="poll">');
      print('<h3 class="page-titles">'.$question.'</h3>');

      // Check if user has already voted
      $res = sql_query("SELECT selection FROM pollanswers WHERE pollid=".sqlesc($pollid)." AND userid=".sqlesc($CURUSER["id"])) or sqlerr();
      $voted = mysql_fetch_assoc($res);
      if ($voted) { //user has already voted
	$uservote = $voted["selection"];
	$cache_key = 'current_poll_result_' + $uservote;
	$out = $Cache->get_value($cache_key);
	if (!$out) {
	  $out = votes($arr, $uservote);
	  $Cache->cache_value($cache_key, $out, 3652);
	}
	echo $out;
	$i = 0;
      }
      else {//user has not voted yet
	  print('<form method="post" action="index.php"><div class="minor-list-vertical"><ul>');
	  $i = 0;
	  while ($a = $arr['option' . $i]) {
	    print('<li><input type="radio" name="choice" value="'.$i.'">'.$a.'</li>');
	    ++$i;
	  }
	  print("<li></li>");
	  print("<li><input type=\"radio\" name=\"choice\" value=\"255\">".$lang_index['radio_blank_vote']."</li></ul></div>");
	  print("<div class=\"center\"><input type=\"submit\" class=\"btn\" value=\"".$lang_index['submit_vote']."\" /></div></form>");
	}

      if ($voted && get_user_class() >= $log_class) {
	print('<div class="center"><a href="log.php?action=poll">'.$lang_index['text_previous_polls']."</a></div>\n");
      }
      print("</div>");
    }
  }
// ------------- end: polls ------------------//
// ------------- start: sns ------------------//
$Cache->new_page('sns', 3000, true);
if (!$Cache->get_page()) {
  $Cache->add_whole_row();

  echo '<div id="sns"><h2 class="page-titles">' . $lang_index['text_sns'] . '</h2><div class="minor-list text table td center"><ul>';
  foreach ($sns as $s) {
    echo '<li><a href="' . $s['url'] . '">' . $s['text'] . '</a></li>';
  }
  echo '</ul></div></div>';
  $Cache->end_whole_row();
  $Cache->cache_page();
}
echo $Cache->next_row();

// ------------- end: sns ------------------//
// ------------- start: stats ------------------//
if ($showstats_main == "yes")
  {
?>
<h2 class="page-titles"><?php echo $lang_index['text_tracker_statistics'] ?></h2>
<table width="60%" class="main text" border="1" cellspacing="0" cellpadding="10">
  <?php
    $Cache->new_page('stats_users', 3000, true);
    if (!$Cache->get_page()){
      $Cache->add_whole_row();
      $registered = number_format(get_row_count("users"));
      $unverified = number_format(get_row_count("users", "WHERE status='pending'"));
      $totalonlinetoday = number_format(get_row_count("users","WHERE last_access >= ". sqlesc(date("Y-m-d H:i:s",(TIMENOW - 86400)))));
      $totalonlineweek = number_format(get_row_count("users","WHERE last_access >= ". sqlesc(date("Y-m-d H:i:s",(TIMENOW - 604800)))));
      $VIP = number_format(get_row_count("users", "WHERE class=".UC_VIP));
      $donated = number_format(get_row_count("users", "WHERE donor = 'yes'"));
      $warned = number_format(get_row_count("users", "WHERE warned='yes'"));
      $disabled = number_format(get_row_count("users", "WHERE enabled='no'"));
      $registered_male = number_format(get_row_count("users", "WHERE gender='Male'"));
      $registered_female = number_format(get_row_count("users", "WHERE gender='Female'"));
?>
  <tr>
  <?php
      twotd($lang_index['row_users_active_today'],$totalonlinetoday);
      twotd($lang_index['row_users_active_this_week'],$totalonlineweek);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_registered_users'],$registered." / ".number_format($maxusers));
      twotd($lang_index['row_unconfirmed_users'],$unverified);
  ?>
  </tr>
  <tr>
  <?php
      twotd(get_user_class_name(UC_VIP,false,false,true),$VIP);
      twotd($lang_index['row_donors']." <img class=\"star\" src=\"pic/trans.gif\" alt=\"Donor\" />",$donated);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_warned_users']." <img class=\"warned\" src=\"pic/trans.gif\" alt=\"warned\" />",$warned);
      twotd($lang_index['row_banned_users']." <img class=\"disabled\" src=\"pic/trans.gif\" alt=\"disabled\" />",$disabled);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_male_users'],$registered_male);
      twotd($lang_index['row_female_users'],$registered_female);
  ?>
  </tr>
  <?php
      $Cache->end_whole_row();
      $Cache->cache_page();
    }
    echo $Cache->next_row();
  ?>
  <tr><td colspan="4" class="rowhead">&nbsp;</td></tr>
  <?php
    $Cache->new_page('stats_torrents', 1800, true);
    if (!$Cache->get_page()){
      $Cache->add_whole_row();
      $torrents = number_format(get_row_count("torrents"));
      $dead = number_format(get_row_count("torrents", "WHERE visible='no'"));
      $seeders = get_row_count("peers", "WHERE seeder='yes'");
      $leechers = get_row_count("peers", "WHERE seeder='no'");
      if ($leechers == 0)
	$ratio = 0;
      else
	$ratio = round($seeders / $leechers * 100);
      $activewebusernow = get_row_count("users","WHERE last_access >= ".sqlesc(date("Y-m-d H:i:s",(TIMENOW - 900))));
      $activewebusernow=number_format($activewebusernow);
      $activetrackerusernow = number_format(get_single_value("peers","COUNT(DISTINCT(userid))"));
      $peers = number_format($seeders + $leechers);
      $seeders = number_format($seeders);
      $leechers = number_format($leechers);
      $totaltorrentssize = mksize(get_row_sum("torrents", "size"));
      $totaluploaded = get_row_sum("users","uploaded");
      $totaldownloaded = get_row_sum("users","downloaded");
      $totaldata = $totaldownloaded+$totaluploaded;
  ?>
  <tr>
  <?php
      twotd($lang_index['row_torrents'],$torrents);
      twotd($lang_index['row_dead_torrents'],$dead);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_seeders'],$seeders);
      twotd($lang_index['row_leechers'],$leechers);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_peers'],$peers);
      twotd($lang_index['row_seeder_leecher_ratio'],$ratio."%");
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_active_browsing_users'], $activewebusernow);
      twotd($lang_index['row_tracker_active_users'], $activetrackerusernow);
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_total_size_of_torrents'],$totaltorrentssize);
      twotd($lang_index['row_total_uploaded'],mksize($totaluploaded));
  ?>
  </tr>
  <tr>
  <?php
      twotd($lang_index['row_total_downloaded'],mksize($totaldownloaded));
      twotd($lang_index['row_total_data'],mksize($totaldata));
  ?>
  </tr>
  <?php
      $Cache->end_whole_row();
      $Cache->cache_page();
    }
    echo $Cache->next_row();
  ?>
  <tr><td colspan="4" class="rowhead">&nbsp;</td></tr>
  <?php
    $Cache->new_page('stats_classes', 4535, true);
    if (!$Cache->get_page()){
      $Cache->add_whole_row();
      $peasants =  number_format(get_row_count("users", "WHERE class=".UC_PEASANT));
      $users = number_format(get_row_count("users", "WHERE class=".UC_USER));
      $powerusers = number_format(get_row_count("users", "WHERE class=".UC_POWER_USER));
      $eliteusers = number_format(get_row_count("users", "WHERE class=".UC_ELITE_USER));
      $crazyusers = number_format(get_row_count("users", "WHERE class=".UC_CRAZY_USER));
      $insaneusers = number_format(get_row_count("users", "WHERE class=".UC_INSANE_USER));
      $veteranusers = number_format(get_row_count("users", "WHERE class=".UC_VETERAN_USER));
      $extremeusers = number_format(get_row_count("users", "WHERE class=".UC_EXTREME_USER));
      $ultimateusers = number_format(get_row_count("users", "WHERE class=".UC_ULTIMATE_USER));
      $nexusmasters = number_format(get_row_count("users", "WHERE class=".UC_NEXUS_MASTER));
  ?>
  <tr>
  <?php
      twotd(get_user_class_name(UC_PEASANT,false,false,true)." <img class=\"leechwarned\" src=\"pic/trans.gif\" alt=\"leechwarned\" />",$peasants);
      twotd(get_user_class_name(UC_USER,false,false,true),$users);
  ?>
  </tr>
  <tr>
  <?php
      twotd(get_user_class_name(UC_POWER_USER,false,false,true),$powerusers);
      twotd(get_user_class_name(UC_ELITE_USER,false,false,true),$eliteusers);
  ?>
  </tr>
  <tr>
  <?php
      twotd(get_user_class_name(UC_CRAZY_USER,false,false,true),$crazyusers);
      twotd(get_user_class_name(UC_INSANE_USER,false,false,true),$insaneusers);
  ?>
  </tr>
  <tr>
  <?php
      twotd(get_user_class_name(UC_VETERAN_USER,false,false,true),$veteranusers);
      twotd(get_user_class_name(UC_EXTREME_USER,false,false,true),$extremeusers);
  ?>
  </tr>
  <tr>
  <?php
      twotd(get_user_class_name(UC_ULTIMATE_USER,false,false,true),$ultimateusers);
      twotd(get_user_class_name(UC_NEXUS_MASTER,false,false,true),$nexusmasters);
  ?>
  </tr>
  <?php
      $Cache->end_whole_row();
      $Cache->cache_page();
    }
    echo $Cache->next_row();
  ?>
</table>
<?php
  }
// ------------- end: stats ------------------//
// ------------- start: tracker load ------------------//
if ($showtrackerload == "yes") {
  $uptimeresult=exec('uptime');
  if ($uptimeresult){
?>
<h2 class="page-titles"><?php echo $lang_index['text_tracker_load'] ?></h2>
<div class="text table td center">
  <?php
    //uptime, work in *nix system
    print (trim($uptimeresult));
    print("</div>");
  }
  }
  // ------------- end: tracker load ------------------//

  // ------------- start: disclaimer ------------------//
?>
  <h2 class="page-titles"><?php echo $lang_index['text_disclaimer'] ?></h2>
  <div class="text table td center">
  <?php echo $lang_index['text_disclaimer_content'] ?></div>
  <?php
    // ------------- end: disclaimer ------------------//
  // ------------- start: links ------------------//
  print('<h2 class="page-titles">'.$lang_index['text_links']);
  if (get_user_class() >= $applylink_class)
    print("<span class=\"small\"> - [<a class=\"altlink\" href=\"linksmanage.php?action=apply\"><b>".$lang_index['text_apply_for_link']."</b></a>]</span>");
    if (get_user_class() >= $linkmanage_class)
      {
	print("<span class=\"small\">");
	print(" - [<a class=\"altlink\" href=\"linksmanage.php\"><b>".$lang_index['text_manage_links']."</b></a>]\n");
	print("</span>");
      }
      print("</h2>");
      $Cache->new_page('links', 86400, false);
      if (!$Cache->get_page()){
	$Cache->add_whole_row();
	$res = sql_query("SELECT * FROM links ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) > 0)
	  {
	    $links = "";
	    while($array = mysql_fetch_array($res))
	      {
		$links .= "<li><a href=\"" . $array['url'] . "\" title=\"" . $array['title'] . "\" target=\"_blank\">" . $array['name'] . "</a></li>";
	      }
	    print('<div class="table td center text minor-list"><ul>'.trim($links)."</ul></div>");
	  }
	$Cache->end_whole_row();
	$Cache->cache_page();
      }
      echo $Cache->next_row();
      // ------------- end: links ------------------//
      // ------------- start: browser, client and code note ------------------//
  ?>
  <div class="main center medium"><?php echo $lang_index['text_browser_note'] ?></div>
  <?php
	// ------------- end: browser, client and code note ------------------//
	if ($CURUSER) {
	  $USERUPDATESET[] = "last_home = ".sqlesc(date("Y-m-d H:i:s"));
	}
	$Cache->delete_value('user_'.$CURUSER["id"].'_unread_news_count');
	end_main_frame();
	stdfoot();

