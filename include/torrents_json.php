<?php



function torrentInfoForRow($row) {
  global $torrentmanage_class;
  global $CURUSER;
  $info = array();
  
  $id = $row["id"];
  $info['id'] = $id;

  if (isset($row["category"])) {
    $info['catid'] = $row['category'];
  }

  //torrent name
  $dispname = trim($row["name"]);
  $info['name'] = $dispname;

  $dissmall_descr = trim($row["small_descr"]);
  $info['desc'] = $dissmall_descr;

  if ($row['pos_state'] == 'sticky') {
  	if ($row['pos_state_until']!=NULL) {
			$pos = array('sticky' => true);
    	$expire = $row['pos_state_until'];
    	$pos['expire'] = $expire;
    	$info['position'] = $pos;
  	}
  }

  if ($row['pos_state'] == 'random') {
  	if ($row['pos_state_until']!=NULL) {
			$pos = array('randomsticky' => true);
    	$expire = $row['pos_state_until'];
    	$pos['expire'] = $expire;
    	if($row['lucky']){
    		$pos['lucky'] = $row['lucky'];
    	}
    	$info['position'] = $pos;
  	}
  }
  if ($row['picktype'] != 'normal'){
    $info['picktype'] = $row['picktype'];
  }

  if($row['oday']=="yes") {
    $info['oday'] = true;
  }
if($row['storing']==1) {
    $info['storing'] = true;
  }
  $last_browse = $CURUSER['last_browse'];
  if (strtotime($row["added"]) >= $last_browse) {
    $info['new'] = true;
  }

  if ($row["banned"] == 'yes') {
    $info['banned'] = true;
  }

  list($pr_state, $futuretime) = get_pr_state($row['sp_state'], $row['added'], $row['promotion_time_type'], $row['promotion_until']);
  if ($pr_state != 1) {
    $pr = array('state' => $pr_state);

    if ($futuretime != NULL) {
      $expire = date("Y-m-d H:i:s", $futuretime);
      $cexpire = gettime($expire, false, false, true, false, true);
      $pr['expire'] = array('raw' => $expire, 'canonical' => $cexpire);
    }
    $info['pr'] = $pr;
  }

  //comments
  $comments = array('count' => $row['comments']);

  if ($row["comments"]) {
    if ($enabletooltip_tweak == 'yes') {
      if (!$lastcom = $Cache->get_value('torrent_'.$id.'_last_comment_content')){
	$res2 = sql_query("SELECT user, added, text FROM comments WHERE torrent = $id ORDER BY id DESC LIMIT 1");
	$lastcom = mysql_fetch_array($res2);
	$Cache->cache_value('torrent_'.$id.'_last_comment_content', $lastcom, 1855);
      }
      $timestamp = strtotime($lastcom["added"]);
      $hasnewcom = ($lastcom['user'] != $CURUSER['id'] && $timestamp >= $last_browse);


      if ($hasnewcom) {
	$comments['new'] = array('author' => $lastcom['user'], 'time' => $timestamp, 'content' => $lastcom['text']);
      }
    } 
  }

  $info['comments'] = $comments;

  $time = $row["added"];
  $info['added'] = array('raw' => $time, 'canonical' => gettime($time,false,true));

  //size
  $info['size'] = array('raw' => $row['size'], 'canonical'=> mksize_compact($row["size"]));

  $info['seeders'] = $row['seeders'];
  $info['leechers'] = $row['leechers'];
  $info['times_completed'] = $row['times_completed'];

  $owner = array();
  if ($row["anonymous"] == "yes") {
    $owner['anonymous'] = true;
    if (get_user_class() >= $torrentmanage_class) {
      $owner['user'] = get_user_prop($row["owner"]);
    }
  }
  else {
    $owner['user'] = get_user_prop($row["owner"]);
  }
  $info['owner'] = $owner;

  if (get_is_torrent_bookmarked($CURUSER['id'], $id)) {
    $info['bookmarked'] = true;
  }
  return $info;
}

function torrenttable_api($rows, $variant = "torrent", $swap_headings = false) {
  global $Cache;
  global $CURUSER, $waitsystem;
  global $showextinfo;
  global $torrentmanage_class, $smalldescription_main, $enabletooltip_tweak;
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
  if ($smalldescription_main == 'no')
    $displaysmalldescr = false;
  else $displaysmalldescr = true;

  $torrents = array();
  if (!empty($rows)) {
    foreach ($rows as $row)  {
    	if($row['id']!=NULL){
      	if($row['banned'] == 'no' || get_user_class() >= $seebanned_class || $CURUSER['id'] == $row['owner']) {
				$torrents[] = torrentInfoForRow($row);
      	}
    	}
    }
  }
  return $torrents;
}
?>
<?php

header('Content-type: application/json');

$out = array('torrents' => torrenttable_api($rows, "torrents"));
if ($next_page_href != '') {
  $out['continue'] = $next_page_href;
}

$out['pager'] = array('top' => $pagertop, 'bottom' => $pagerbottom);

print(php_json_encode($out));

?>