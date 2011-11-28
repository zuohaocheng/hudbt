<?php

function get_user_prop($id) {
  $user = get_user_row($id);
  $out = '';
  if ($user) {
    $out .= '<user id="' . $user['id'] . '">';
    $out .= '<username>' . $user['username'] . '</username>';
    $out .= '<class>' . $user['class'] . '</class>';
    $out .= '<canonicalClass>' . get_user_class_name($user['class'],false) . '</canonicalClass>';
    if ($user['donor'] == 'yes') {
      $out .= '<donor>true</donor>';
    }
    $out .= '</user>';
  }
  return $out;
}

function torrenttable_api($res, $variant = "torrent", $swap_headings = false) {
  global $Cache;
  global $lang_functions;
  global $CURUSER, $waitsystem;
  global $showextinfo;
  global $torrentmanage_class, $smalldescription_main, $enabletooltip_tweak;
  global $CURLANGDIR;
  // Added Br BruceWolf. 2011-04-24
  // Filter banned torrents
  global $seebanned_class;
  
  if ($variant == "torrent"){
    $last_browse = $CURUSER['last_browse'];
    $sectiontype = $browsecatmode;
  }
  elseif($variant == "music"){
    $last_browse = $CURUSER['last_music'];
    $sectiontype = $specialcatmode;
  }
  else{
    $last_browse = $CURUSER['last_browse'];
    $sectiontype = "";
  }

  $time_now = TIMENOW;
  if ($last_browse > $time_now) {
    $last_browse=$time_now;
  }

  if (get_user_class() < UC_VIP && $waitsystem == "yes") {
    $ratio = get_ratio($CURUSER["id"], false);
    $gigs = $CURUSER["uploaded"] / (1024*1024*1024);
    if($gigs > 10)
      {
	if ($ratio < 0.4) $wait = 24;
	elseif ($ratio < 0.5) $wait = 12;
	elseif ($ratio < 0.6) $wait = 6;
	elseif ($ratio < 0.8) $wait = 3;
	else $wait = 0;
      }
    else $wait = 0;
  }

  $caticonrow = get_category_icon_row($CURUSER['caticon']);
  if ($caticonrow['secondicon'] == 'yes')
    $has_secondicon = true;
  else $has_secondicon = false;
  $counter = 0;
  if ($smalldescription_main == 'no' || $CURUSER['showsmalldescr'] == 'no')
    $displaysmalldescr = false;
  else $displaysmalldescr = true;
  while ($row = mysql_fetch_assoc($res))  {
      if($row['banned'] == 'no' 
	 || ($row['banned'] == 'yes' 
	     && (get_user_class() >= $seebanned_class 
		 || $CURUSER['id'] == $row['owner']))) {
	$id = $row["id"];
	print('<torrent id="' . $id . '">');

	/* $sphighlight = get_torrent_bg_color($row['sp_state']); */
	/* print("<tr" . $sphighlight . ">\n"); */


	if (isset($row["category"])) {
	print('<catid>' . $row['category'] . '</catid>');
	  /* print(return_category_image($row["category"], "?")); */
	  /* if ($has_secondicon){ */
	  /*   print(get_second_icon($row, "pic/".$catimgurl."additional/")); */
	  /* } */
	}



	//torrent name
	$dispname = trim($row["name"]);
	$short_torrent_name_alt = "";
	$mouseovertorrent = "";
	$tooltipblock = "";
	$has_tooltip = false;
	if ($enabletooltip_tweak == 'yes')
	  $tooltiptype = $CURUSER['tooltip'];
	else
	  $tooltiptype = 'off';
	switch ($tooltiptype){
	case 'minorimdb' : {
	  if ($showextinfo['imdb'] == 'yes' && $row["url"])
	    {
	      $url = $row['url'];
	      $cache = $row['cache_stamp'];
	      $type = 'minor';
	      $has_tooltip = true;
	    }
	  break;
	}
	case 'medianimdb' :
	  {
	    if ($showextinfo['imdb'] == 'yes' && $row["url"])
	      {
		$url = $row['url'];
		$cache = $row['cache_stamp'];
		$type = 'median';
		$has_tooltip = true;
	      }
	    break;
	  }
	case 'off' :  break;
	}
	if (!$has_tooltip)
	  $short_torrent_name_alt = "title=\"".htmlspecialchars($dispname)."\"";
	else{
	  $torrent_tooltip[$counter]['id'] = "torrent_" . $counter;
	  $torrent_tooltip[$counter]['content'] = "";
	  $mouseovertorrent = "onmouseover=\"get_ext_info_ajax('".$torrent_tooltip[$counter]['id']."','".$url."','".$cache."','".$type."'); domTT_activate(this, event, 'content', document.getElementById('" . $torrent_tooltip[$counter]['id'] . "'), 'trail', false, 'delay',600,'lifetime',6000,'fade','both','styleClass','niceTitle', 'fadeMax',87, 'maxWidth', 500);\"";
	}
	$count_dispname=mb_strlen($dispname,"UTF-8");
	if (!$displaysmalldescr || $row["small_descr"] == "")// maximum length of torrent name
	  $max_length_of_torrent_name = 120;
	elseif ($CURUSER['fontsize'] == 'large')
	  $max_length_of_torrent_name = 60;
	elseif ($CURUSER['fontsize'] == 'small')
	  $max_length_of_torrent_name = 80;
	else $max_length_of_torrent_name = 70;

	if($count_dispname > $max_length_of_torrent_name)
	  $dispname=mb_substr($dispname, 0, $max_length_of_torrent_name-2,"UTF-8") . "..";

	print('<name>' . htmlspecialchars($dispname) . '</name>');

	if ($row['pos_state'] == 'sticky' && $CURUSER['appendsticky'] == 'yes')
	  $stickyicon = "<img class=\"sticky\" src=\"pic/trans.gif\" alt=\"Sticky\" title=\"".$lang_functions['title_sticky']."\" />&nbsp;";
	else $stickyicon = "";
	
	if ($displaysmalldescr) {
	  $dissmall_descr = trim($row["small_descr"]);
	  $count_dissmall_descr=mb_strlen($dissmall_descr,"UTF-8");
	  $max_lenght_of_small_descr=$max_length_of_torrent_name; // maximum length
	  if($count_dissmall_descr > $max_lenght_of_small_descr)
	    {
	      $dissmall_descr=mb_substr($dissmall_descr, 0, $max_lenght_of_small_descr-2,"UTF-8") . "..";
	    }
	}
	print('<desc>' . htmlspecialchars($dissmall_descr) . '</desc>');
	if ($row['pos_state'] == 'sticky') {
	  print('<sticky>true</sticky>');
	}


	if ($CURUSER['appendpicked'] != 'no' && $row['picktype'] != 'normal'){
	  print('<picktype>' . $row['picktype'] . '</picktype>');
	}

	if($row['oday']=="yes") {
	    print('<oday>true</oday>');
	}

	if ($CURUSER['appendnew'] != 'no' && strtotime($row["added"]) >= $last_browse) {
	  print('<new>true</new>');
	}

	if ($row["banned"] == 'yes') {
	  print('<banned>true</banned>');
	}

	if ($row['sp_state'] != 1) {
	  print('<pr state="' . $row['sp_state'] . '">');

	  if ( $row['promotion_time_type'] != 1) {
	    global $expirefree_torrent;
	    if ( $row['promotion_time_type'] == 2) {
	      $futuretime = strtotime( $row['promotion_until']);
	    } else {
	      $futuretime = strtotime($row["added"]) + $expirefree_torrent * 86400;
	    }

	    $expire = date("Y-m-d H:i:s", $futuretime);
	    $cexpire = gettime($expire, false, false, true, false, true);
	    print('<expire><raw>' . $expire . '</raw><canonical>' .htmlspecialchars($cexpire) .'</canonical></expire>');
	  }
	  print('</pr>');

	}

	  



	/* if ($wait) */
	/*   { */
	/*     $elapsed = floor((TIMENOW - strtotime($row["added"])) / 3600); */
	/*     if ($elapsed < $wait) */
	/*       { */
	/* 	$color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536); */
	/* 	print("<td class=\"rowfollow nowrap\"><a href=\"faq.php#id46\"><font color=\"".$color."\">" . number_format($wait - $elapsed) . $lang_functions['text_h']."</font></a></td>\n"); */
	/*       } */
	/*     else */
	/*       print("<td class=\"rowfollow nowrap\">".$lang_functions['text_none']."</td>\n"); */
	/*   } */
	
	if ($CURUSER['showcomnum'] != 'no')
	  {
	    $nl = "";

	    //comments
	    print('<comments>' . $row["comments"]);

	    if ($row["comments"]) {
	      if ($enabletooltip_tweak == 'yes' && $CURUSER['showlastcom'] != 'no') {
		if (!$lastcom = $Cache->get_value('torrent_'.$id.'_last_comment_content')){
		  $res2 = sql_query("SELECT user, added, text FROM comments WHERE torrent = $id ORDER BY id DESC LIMIT 1");
		  $lastcom = mysql_fetch_array($res2);
		  $Cache->cache_value('torrent_'.$id.'_last_comment_content', $lastcom, 1855);
		}
		$timestamp = strtotime($lastcom["added"]);
		$hasnewcom = ($lastcom['user'] != $CURUSER['id'] && $timestamp >= $last_browse);
		if ($lastcom) {
		  if ($CURUSER['timetype'] != 'timealive') 
		    $lastcomtime = $lang_functions['text_at_time'].$lastcom['added'];
		  else
		    $lastcomtime = $lang_functions['text_blank'].gettime($lastcom["added"],true,false,true);
		}

		if ($hasnewcom) {
		  print('<new><author>'. $lastcom['user'] .'</author><time>' . $timestamp . '</time><content>' . $lastcom['text'] . '</content></new>');
		}
	      } 
	    }

	    print('</comments>');
	  }
       

	$time = $row["added"];
	print('<added>' . $time . '</added>');
#	$time = gettime($time,false,true);


	//size
	print('<size><raw>' . $row['size'] . '</raw><canonical>' . htmlspecialchars(mksize_compact($row["size"])) . '</canonical></size>');


	print('<seeders>' . $row['seeders'] . '</seeders>');
	print('<leechers>' . $row['leechers'] . '</leechers>');
	print('<times_completed>' . $row['times_completed'] . '</times_completed>');

	print('<owner');
	if ($row["anonymous"] == "yes") {
	  print(' anonymous="true">');
	  if (get_user_class() >= $torrentmanage_class) {
	    print(get_user_prop($row["owner"]));
	  }
	}
	else {
	  print('>');

	  print(get_user_prop($row["owner"]));
	}
	print('</owner>');

	if (get_is_torrent_bookmarked($CURUSER['id'], $id)) {
	  print('<bookmarked>true</bookmarked>');
	}

	if (get_user_class() >= $torrentmanage_class) {
	  }
	print("</torrent>\n");
	$counter++;
      }
  }

  if($enabletooltip_tweak == 'yes' && (!isset($CURUSER) || $CURUSER['showlastcom'] == 'yes'))
    create_tooltip_container($lastcom_tooltip, 400);
  create_tooltip_container($torrent_tooltip, 500);
}
?>
<?php

header('Content-type: text/xml');
print('<?xml version="1.0"?>');

?>
<api><query><torrents>
<?php

torrenttable_api($res, "torrents");

if ($next_page_href != '') {
  print('<continue>' . htmlspecialchars($next_page_href) . '</continue>');
  }
print("</torrents></query></api>");


?>