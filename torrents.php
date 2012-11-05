<?php
#$timer_0_start = microtime(true); // debug
require_once("include/bittorrent.php");
dbconn(true);
require_once(get_langfile_path("torrents.php"));
loggedinorreturn();
parked();
if ($showextinfo['imdb'] == 'yes')
  require_once ("imdb/imdb.class.php");



// -------------- 缓存配置 ------------
// 种子总数的缓存开关，true 为使用缓存
// 时效性要求不高，默认为 true
$torrent_count_cache = true;
// 种子总数缓存时间
define('TORRENT_COUNT_CACHE_TIME', 300);

// 种子详细数据的缓存开关，true 为使用缓存
// 负载高时使用 true，平时设为 false
$torrents_list_cache = true;
// 普通种子缓存时间
define('TORRENT_NORMAL_CACHE_TIME', 5);

//置顶种子缓存时间
define('TORRENT_STICKY_CACHE_TIME', 300);

//固定置顶种子总数缓存时间
define('STICKY_TORRENT_NUM_CACHE_TIME', 300);
// -------------- 缓存配置 ------------|| END

//check searchbox
$sectiontype = $browsecatmode;
$showsubcat = get_searchbox_value($sectiontype, 'showsubcat');//whether show subcategory (i.e. sources, codecs) or not
$showsource = get_searchbox_value($sectiontype, 'showsource'); //whether show sources or not
$showmedium = get_searchbox_value($sectiontype, 'showmedium'); //whether show media or not
$showcodec = get_searchbox_value($sectiontype, 'showcodec'); //whether show codecs or not
$showstandard = get_searchbox_value($sectiontype, 'showstandard'); //whether show standards or not
$showprocessing = get_searchbox_value($sectiontype, 'showprocessing'); //whether show processings or not
$showteam = get_searchbox_value($sectiontype, 'showteam'); //whether show teams or not
$showaudiocodec = get_searchbox_value($sectiontype, 'showaudiocodec'); //whether show audio codec or not

$catsperrow = get_searchbox_value($sectiontype, 'catsperrow'); //show how many cats per line in search box
$catpadding = get_searchbox_value($sectiontype, 'catpadding'); //padding space between categories in pixel

$cats = genrelist($sectiontype);

if ($showsubcat){
  if ($showsource) $sources = searchbox_item_list("sources");
  if ($showmedium) $media = searchbox_item_list("media");
  if ($showcodec) $codecs = searchbox_item_list("codecs");
  if ($showstandard) $standards = searchbox_item_list("standards");
  if ($showprocessing) $processings = searchbox_item_list("processings");
  if ($showteam) $teams = searchbox_item_list("teams");
  if ($showaudiocodec) $audiocodecs = searchbox_item_list("audiocodecs");
}

$searchstr_ori = htmlspecialchars(trim($_GET["search"]));
$searchstr = mysql_real_escape_string(trim($_GET["search"]));
if (empty($searchstr))
  unset($searchstr);

// sorting by MarkoStamcar
if ($_GET['sort'] && $_GET['type']) {

  $column = '';
  $ascdesc = '';

  switch($_GET['sort']) {
  case '1': $column = "name"; break;
  case '2': $column = "numfiles"; break;
  case '3': $column = "comments"; break;
  case '4': $column = "added"; break;
  case '5': $column = "size"; break;
  case '6': $column = "times_completed"; break;
  case '7': $column = "seeders"; break;
  case '8': $column = "leechers"; break;
  case '9': $column = "owner"; break;
  default: $column = "id"; break;
  }

  switch($_GET['type']) {
  case 'asc': $ascdesc = "ASC"; $linkascdesc = "asc"; break;
  case 'desc': $ascdesc = "DESC"; $linkascdesc = "desc"; break;
  default: $ascdesc = "DESC"; $linkascdesc = "desc"; break;
  }

  if($column == "owner")
    {
      $orderby = "ORDER BY torrents.anonymous, users.username " . $ascdesc;
    }
  else
    {
      $orderby = "ORDER BY torrents." . $column . " " . $ascdesc;
    }

  $pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";

} else {

  $orderby = "ORDER BY torrents.id DESC";
  $pagerlink = "";

}

$addparam = "";
$wherea = array();
$wherecatina = array();
if ($showsubcat){
  if ($showsource) $wheresourceina = array();
  if ($showmedium) $wheremediumina = array();
  if ($showcodec) $wherecodecina = array();
  if ($showstandard) $wherestandardina = array();
  if ($showprocessing) $whereprocessingina = array();
  if ($showteam) $whereteamina = array();
  if ($showaudiocodec) $whereaudiocodecina = array();
}
//----------------- start whether show torrents from all sections---------------------//
if ($_GET) {
  $allsec = 0 + $_GET["allsec"];
}
else {
  $allsec = 0;
}

if ($allsec == 1) {		//show torrents from all sections
  $addparam .= "allsec=1&";
}
// ----------------- end whether ignoring section ---------------------//
// ----------------- start bookmarked ---------------------//
if ($_GET) {
  $inclbookmarked = 0 + $_GET["inclbookmarked"];
}
elseif ($CURUSER['notifs']){
  if (strpos($CURUSER['notifs'], "[inclbookmarked=0]") !== false)
    $inclbookmarked = 0;
  elseif (strpos($CURUSER['notifs'], "[inclbookmarked=1]") !== false)
    $inclbookmarked = 1;
  elseif (strpos($CURUSER['notifs'], "[inclbookmarked=2]") !== false)
    $inclbookmarked = 2;
}
else $inclbookmarked = 0;

if (!in_array($inclbookmarked,array(0,1,2)))
  {
    $inclbookmarked = 0;
#    write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking inclbookmarked field in" . $_SERVER['SCRIPT_NAME'], 'mod');
  }
if ($inclbookmarked == 0)  //all(bookmarked,not)
  {
    $addparam .= "inclbookmarked=0&";
  }
elseif ($inclbookmarked == 1)		//bookmarked
  {
    $addparam .= "inclbookmarked=1&";
    if(isset($CURUSER))
      $wherea[] = "torrents.id IN (SELECT torrentid FROM bookmarks WHERE userid=" . $CURUSER['id'] . ")";
  }
elseif ($inclbookmarked == 2)		//not bookmarked
  {
    $addparam .= "inclbookmarked=2&";
    if(isset($CURUSER))
      $wherea[] = "torrents.id NOT IN (SELECT torrentid FROM bookmarks WHERE userid=" . $CURUSER['id'] . ")";
  }
// ----------------- end bookmarked ---------------------//

// if (!isset($CURUSER) || get_user_class() < $seebanned_class)
if (!isset($CURUSER))
  $wherea[] = "banned != 'yes'";

if ($_GET["swaph"]) {
  $addparam .= "swaph=1&";
}

// ----------------- start include dead ---------------------//
if (isset($_GET["incldead"]))
  $include_dead = 0 + $_GET["incldead"];
elseif ($CURUSER['notifs']){
  if (strpos($CURUSER['notifs'], "[incldead=0]") !== false)
    $include_dead = 0;
  elseif (strpos($CURUSER['notifs'], "[incldead=1]") !== false)
    $include_dead = 1;
  elseif (strpos($CURUSER['notifs'], "[incldead=2]") !== false)
    $include_dead = 2;
  else $include_dead = 1;
}
else $include_dead = 1;

if (!in_array($include_dead,array(0,1,2,3)))
  {
    $include_dead = 0;
#    write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking incldead field in" . $_SERVER['SCRIPT_NAME'], 'mod');
  }
if ($include_dead == 0) { //all(active,dead)
  $addparam .= "incldead=0&";
}
elseif ($include_dead == 1) {		//active
  $addparam .= "incldead=1&";
  $wherea[] = "visible = 'yes'";
  $wherea[] = "startseed = 'yes'";
}
elseif ($include_dead == 2) {		//dead
  $addparam .= "incldead=2&";
  $wherea[] = "visible = 'no'";
}
elseif ($include_dead == 3) {
  $addparam .= "incldead=3&";
  $wherea[] = "startseed = 'no'";
}
// ----------------- end include dead ---------------------//
if ($_GET)
  $special_state = 0 + $_GET["spstate"];
elseif ($CURUSER['notifs']){
  if (strpos($CURUSER['notifs'], "[spstate=0]") !== false)
    $special_state = 0;
  elseif (strpos($CURUSER['notifs'], "[spstate=1]") !== false)
    $special_state = 1;
  elseif (strpos($CURUSER['notifs'], "[spstate=2]") !== false)
    $special_state = 2;
  elseif (strpos($CURUSER['notifs'], "[spstate=3]") !== false)
    $special_state = 3;
  elseif (strpos($CURUSER['notifs'], "[spstate=4]") !== false)
    $special_state = 4;
  elseif (strpos($CURUSER['notifs'], "[spstate=5]") !== false)
    $special_state = 5;
  elseif (strpos($CURUSER['notifs'], "[spstate=6]") !== false)
    $special_state = 6;
  elseif (strpos($CURUSER['notifs'], "[spstate=6]") !== false)
    $special_state = 7;
}
else $special_state = 0;

if (!in_array($special_state,array(0,1,2,3,4,5,6,7))) {
  $special_state = 0;
#  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking spstate field in " . $_SERVER['SCRIPT_NAME'], 'mod');
}
if($special_state == 0)	//all
  {
    $addparam .= "spstate=0&";
  }
elseif ($special_state == 1)	//normal
  {
    $addparam .= "spstate=1&";

    $wherea[] = "sp_state = 1";

    if(get_global_sp_state() == 1)
      {
	$wherea[] = "sp_state = 1";
      }
  }
elseif ($special_state == 2)	//free
  {
    $addparam .= "spstate=2&";

    if(get_global_sp_state() == 1)
      {
	$wherea[] = "sp_state = 2";
      }
    else if(get_global_sp_state() == 2)
      {
	;
      }
  }
elseif ($special_state == 3)	//2x up
  {
    $addparam .= "spstate=3&";
    if(get_global_sp_state() == 1)	//only sp state
      {
	$wherea[] = "sp_state = 3";
      }
    else if(get_global_sp_state() == 3)	//all
      {
	;
      }
  }
elseif ($special_state == 4)	//2x up and free
  {
    $addparam .= "spstate=4&";

    if(get_global_sp_state() == 1)	//only sp state
      {
	$wherea[] = "sp_state = 4";
      }
    else if(get_global_sp_state() == 4)	//all
      {
	;
      }
  }
elseif ($special_state == 5)	//half down
  {
    $addparam .= "spstate=5&";

    if(get_global_sp_state() == 1)	//only sp state
      {
	$wherea[] = "sp_state = 5";
      }
    else if(get_global_sp_state() == 5)	//all
      {
	;
      }
  }
elseif ($special_state == 6)	//half down
  {
    $addparam .= "spstate=6&";

    if(get_global_sp_state() == 1)	//only sp state
      {
	$wherea[] = "sp_state = 6";
      }
    else if(get_global_sp_state() == 6)	//all
      {
	;
      }
  }
elseif ($special_state == 7)	//30% down
  {
    $addparam .= "spstate=7&";

    if(get_global_sp_state() == 1)	//only sp state
      {
	$wherea[] = "sp_state = 7";
      }
    else if(get_global_sp_state() == 7)	//all
      {
	;
      }
  }

if ($_GET['hot']) {
  $wherehot = true;
  $wherea[] = "picktype='hot'";
  $addparam .= 'hot=1&';
}

if ($_GET['storing']) {
  $wherestroing = true;
  $wherea[] = "storing=1";
  $addparam .= 'storing=1&';
}

if ($_GET["indate"]) {
  $indate = 0 + $_GET["indate"];

  if ($indate) {
    $addparam .= 'indate=' . $indate .'&';
    $wherea[] = " DATEDIFF('".date("Y-m-d H:i:s")."',torrents.added)<=" . mysql_real_escape_string($indate);
  }
}

$category_get = 0 + $_GET["cat"];
if ($showsubcat){
  if ($showsource) $source_get = 0 + $_GET["source"];
  if ($showmedium) $medium_get = 0 + $_GET["medium"];
  if ($showcodec) $codec_get = 0 + $_GET["codec"];
  if ($showstandard) $standard_get = 0 + $_GET["standard"];
  if ($showprocessing) $processing_get = 0 + $_GET["processing"];
  if ($showteam) $team_get = 0 + $_GET["team"];
  if ($showaudiocodec) $audiocodec_get = 0 + $_GET["audiocodec"];
}

$mainCats = array('1' => array('401', '413', '414', '415', '430'),
		  '2' => array('402', '416', '417', '418'),
		  '3' => array('405', '427', '428', '429'),
		  '4' => array('410'),
		  '5' => array('403', '419', '420', '421'),
		  '6' => array('412', '409'),
		  '7' => array('407'),
		  '8' => array('408', '422', '423', '424', '425'),
		  '9' => array('404'),
		  '10' => array('411', '426'),
		  '11' => array('406'),
		  '12' => ['1037']);
$mainCatsName = array('1' => '电影', '2'=> '剧集', '3' => '动漫', '4'=>'游戏', '5'=>'综艺', '6'=>'资料', '7'=>'体育', '8'=>'音乐', '9'=>'纪录片', '10' =>'软件', '11'=>'MV', '12' => '华中科技大学');

$all = 0 + $_GET["all"];

if (!$all) {
  if (!$_GET && $CURUSER['notifs']) {
    $all = true;

    foreach ($cats as $cat) {
      $all &= $cat['id'];
      $mystring = $CURUSER['notifs'];
      $findme  = '[cat'.$cat['id'].']';
      $search = strpos($mystring, $findme);
      if ($search === false)
	$catcheck = false;
      else
	$catcheck = true;

      if ($catcheck) {
	$wherecatina[] = $cat['id'];
	$addparam .= "cat$cat[id]=1&";
      }
    }
    if ($showsubcat){
      if ($showsource)
	foreach ($sources as $source)
	  {
	    $all &= $source['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[sou'.$source['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $sourcecheck = false;
	    else
	      $sourcecheck = true;

	    if ($sourcecheck)
	      {
		$wheresourceina[] = $source['id'];
		$addparam .= "source$source[id]=1&";
	      }
	  }
      if ($showmedium)
	foreach ($media as $medium)
	  {
	    $all &= $medium['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[med'.$medium['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $mediumcheck = false;
	    else
	      $mediumcheck = true;

	    if ($mediumcheck)
	      {
		$wheremediumina[] = $medium['id'];
		$addparam .= "medium$medium[id]=1&";
	      }
	  }
      if ($showcodec)
	foreach ($codecs as $codec)
	  {
	    $all &= $codec['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[cod'.$codec['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $codeccheck = false;
	    else
	      $codeccheck = true;

	    if ($codeccheck)
	      {
		$wherecodecina[] = $codec['id'];
		$addparam .= "codec$codec[id]=1&";
	      }
	  }
      if ($showstandard)
	foreach ($standards as $standard)
	  {
	    $all &= $standard['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[sta'.$standard['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $standardcheck = false;
	    else
	      $standardcheck = true;

	    if ($standardcheck)
	      {
		$wherestandardina[] = $standard['id'];
		$addparam .= "standard$standard[id]=1&";
	      }
	  }
      if ($showprocessing)
	foreach ($processings as $processing)
	  {
	    $all &= $processing['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[pro'.$processing['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $processingcheck = false;
	    else
	      $processingcheck = true;

	    if ($processingcheck)
	      {
		$whereprocessingina[] = $processing['id'];
		$addparam .= "processing$processing[id]=1&";
	      }
	  }
      if ($showteam)
	foreach ($teams as $team)
	  {
	    $all &= $team['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[tea'.$team['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $teamcheck = false;
	    else
	      $teamcheck = true;

	    if ($teamcheck)
	      {
		$whereteamina[] = $team['id'];
		$addparam .= "team$team[id]=1&";
	      }
	  }
      if ($showaudiocodec)
	foreach ($audiocodecs as $audiocodec)
	  {
	    $all &= $audiocodec['id'];
	    $mystring = $CURUSER['notifs'];
	    $findme  = '[aud'.$audiocodec['id'].']';
	    $search = strpos($mystring, $findme);
	    if ($search === false)
	      $audiocodeccheck = false;
	    else
	      $audiocodeccheck = true;

	    if ($audiocodeccheck)
	      {
		$whereaudiocodecina[] = $audiocodec['id'];
		$addparam .= "audiocodec$audiocodec[id]=1&";
	      }
	  }
    }	
  }
  // when one clicked the cat, source, etc. name/image
  elseif ($category_get) {
    int_check($category_get,true,true,true);
    $mainCat = $mainCats[$category_get];
    if ($mainCat) {
      	$selectedMainCat[$category_get] = true;
	foreach($mainCat as $subcat) {
	  $wherecatina[] = $subcat;
	}
    }
    else {
      $wherecatina[] = $category_get;
    }
    $addparam .= "cat=$category_get&";
  }
  elseif ($medium_get) {
    int_check($medium_get,true,true,true);
    $wheremediumina[] = $medium_get;
    $addparam .= "medium=$medium_get&";
  }
  elseif ($source_get) {
    int_check($source_get,true,true,true);
    $wheresourceina[] = $source_get;
    $addparam .= "source=$source_get&";
  }
  elseif ($codec_get) {
    int_check($codec_get,true,true,true);
    $wherecodecina[] = $codec_get;
    $addparam .= "codec=$codec_get&";
  }
  elseif ($standard_get) {
    int_check($standard_get,true,true,true);
    $wherestandardina[] = $standard_get;
    $addparam .= "standard=$standard_get&";
  }
  elseif ($processing_get) {
    int_check($processing_get,true,true,true);
    $whereprocessingina[] = $processing_get;
    $addparam .= "processing=$processing_get&";
  }
  elseif ($team_get) {
    int_check($team_get,true,true,true);
    $whereteamina[] = $team_get;
    $addparam .= "team=$team_get&";
  }
  elseif ($audiocodec_get) {
    int_check($audiocodec_get,true,true,true);
    $whereaudiocodecina[] = $audiocodec_get;
    $addparam .= "audiocodec=$audiocodec_get&";
  }
  else { //select and go
    $all = True;

    foreach ($cats as $cat) {
      $all &= $_GET["cat$cat[id]"];
      if ($_GET["cat$cat[id]"]) {
	$wherecatina[] = $cat['id'];
	$addparam .= "cat$cat[id]=1&";
      }
    }

    foreach ($mainCats as $cat=>$subcats) {
      if ($_GET["cat$cat"]) {
	$selectedMainCat[$cat] = true;
	foreach($subcats as $subcat) {
	  $wherecatina[] = $subcat;
	}
	$addparam .= "cat$cat=1&";
      }
    }
    $wherecatina = array_unique($wherecatina);

    if ($showsubcat) {
      if ($showsource) {
	foreach ($sources as $source) {
	  $all &= $_GET["source$source[id]"];
	  if ($_GET["source$source[id]"])
	    {
	      $wheresourceina[] = $source['id'];
	      $addparam .= "source$source[id]=1&";
	    }
	}
      }

      if ($showmedium) {
	foreach ($media as $medium) {
	  $all &= $_GET["medium$medium[id]"];
	  if ($_GET["medium$medium[id]"])
	    {
	      $wheremediumina[] = $medium['id'];
	      $addparam .= "medium$medium[id]=1&";
	    }
	}
      }

      if ($showcodec) {
	foreach ($codecs as $codec) {
	  $all &= $_GET["codec$codec[id]"];
	  if ($_GET["codec$codec[id]"])
	    {
	      $wherecodecina[] = $codec['id'];
	      $addparam .= "codec$codec[id]=1&";
	    }
	}
      }

      if ($showstandard) {
	foreach ($standards as $standard) {
	  $all &= $_GET["standard$standard[id]"];
	  if ($_GET["standard$standard[id]"])
	    {
	      $wherestandardina[] = $standard['id'];
	      $addparam .= "standard$standard[id]=1&";
	    }
	}
      }

      if ($showprocessing) {
	foreach ($processings as $processing) {
	  $all &= $_GET["processing$processing[id]"];
	  if ($_GET["processing$processing[id]"])
	    {
	      $whereprocessingina[] = $processing['id'];
	      $addparam .= "processing$processing[id]=1&";
	    }
	}
      }

      if ($showteam) {
	foreach ($teams as $team) {
	  $all &= $_GET["team$team[id]"];
	  if ($_GET["team$team[id]"])
	    {
	      $whereteamina[] = $team['id'];
	      $addparam .= "team$team[id]=1&";
	    }
	}
      }

      if ($showaudiocodec) {
	foreach ($audiocodecs as $audiocodec) {
	  $all &= $_GET["audiocodec$audiocodec[id]"];
	  if ($_GET["audiocodec$audiocodec[id]"])
	    {
	      $whereaudiocodecina[] = $audiocodec['id'];
	      $addparam .= "audiocodec$audiocodec[id]=1&";
	    }
	}
      }
    }
  }
}

if ($all)
  {
    //stderr("in if all","");
    $wherecatina = array();
    if ($showsubcat){
      $wheresourceina = array();
      $wheremediumina = array();
      $wherecodecina = array();
      $wherestandardina = array();
      $whereprocessingina = array();
      $whereteamina = array();
      $whereaudiocodecina = array();}
    $addparam .= "";
  }
//stderr("", count($wherecatina)."-". count($wheresourceina));

if (count($wherecatina) > 1)
  $wherecatin = implode(",",$wherecatina);
elseif (count($wherecatina) == 1)
  $wherea[] = "category = $wherecatina[0]";

if ($showsubcat){
  if ($showsource){
    if (count($wheresourceina) > 1)
      $wheresourcein = implode(",",$wheresourceina);
    elseif (count($wheresourceina) == 1)
      $wherea[] = "source = $wheresourceina[0]";}

  if ($showmedium){
    if (count($wheremediumina) > 1)
      $wheremediumin = implode(",",$wheremediumina);
    elseif (count($wheremediumina) == 1)
      $wherea[] = "medium = $wheremediumina[0]";}

  if ($showcodec){
    if (count($wherecodecina) > 1)
      $wherecodecin = implode(",",$wherecodecina);
    elseif (count($wherecodecina) == 1)
      $wherea[] = "codec = $wherecodecina[0]";}

  if ($showstandard){
    if (count($wherestandardina) > 1)
      $wherestandardin = implode(",",$wherestandardina);
    elseif (count($wherestandardina) == 1)
      $wherea[] = "standard = $wherestandardina[0]";}

  if ($showprocessing){
    if (count($whereprocessingina) > 1)
      $whereprocessingin = implode(",",$whereprocessingina);
    elseif (count($whereprocessingina) == 1)
      $wherea[] = "processing = $whereprocessingina[0]";}
}
if ($showteam){
  if (count($whereteamina) > 1)
    $whereteamin = implode(",",$whereteamina);
  elseif (count($whereteamina) == 1)
    $wherea[] = "team = $whereteamina[0]";}

if ($showaudiocodec){
  if (count($whereaudiocodecina) > 1)
    $whereaudiocodecin = implode(",",$whereaudiocodecina);
  elseif (count($whereaudiocodecina) == 1)
    $wherea[] = "audiocodec = $whereaudiocodecina[0]";}

$wherebase = $wherea;

if (isset($searchstr))
  {
    if (!$_GET['notnewword']){
      insert_suggest($searchstr, $CURUSER['id']);
      $notnewword="";
    }
    else{
      $notnewword="notnewword=1&";
    }
    $search_mode = 0 + $_GET["search_mode"];
    if (!in_array($search_mode,array(0,1,2,3)))
      {
	$search_mode = 0;
#	write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_mode field in" . $_SERVER['SCRIPT_NAME'], 'mod');
      }

    $search_area = 0 + $_GET["search_area"];

    if ($search_area == 4) {
      $searchstr = (int)parse_imdb_id($searchstr);
    }

    unset($like_expression_array);
    $matches = [];
    $likes = [];
    $exact = [];
    $ANDOR = ($search_mode == 0 ? " AND " : " OR ");	// only affects mode 0 and mode 1
    $canMatch = function($a) {
      $len = strlen($a);
      $allAlnum = true;
      for ($i = 0; $i < $len; ++$i) {
	$ch = $a[$i];
	if (!ctype_alnum($ch)) {
	  $allAlnum = false;
	  break;
	}
      }
      return $allAlnum;
    };
    
    $addToken = function($token, $exact = false) use ($canMatch) {
      global $matches, $likes, $exact;
      if ($exact) {
	$exatc[] = $token;
      }
      else if ($canMatch($token)) {
	$matches[] = $token;
      }
      else {
	$likes[] = $token;
      }
    };
    
    $generateMatchSql = function($fields, $canUseMatch) use ($matches, $likes, $ANDOR) {
      global $matches, $likes, $exact;
      $out = [];
      if ($canUseMatch) {
	if (count($matches)) {
	  if ($ANDOR == ' AND ') {
	    $matches = array_map(function($o) {return '+' . $o;}, $matches);
	  }
	  $out[] = 'MATCH(' . implode(',', $fields) . ') AGAINST ("' . implode(' ', $matches) . '" IN BOOLEAN MODE)';
	}
      }
      else {
	$likes = array_merge($matches, $likes);
      }
      $likes = array_map(function($o) {
	  return 'LIKE "%' . $o . '%"';
	}, $likes);
      $exact = array_map(function($o) {
	  return '= "' . $o . '"';
	}, $exact);

      $likes_sql = implode($ANDOR, array_map(function($o) use ($fields) {
	    return '(' . implode(' OR ', array_map(function($field) use ($o) {
		  return $field . ' ' . $o;
		}, $fields)) . ')';
	  }, array_merge($likes, $exact)));
      if ($likes_sql) {
	$out[] = $likes_sql;
      }
      return implode($ANDOR, $out);
    };

    
    switch ($search_mode) {
      case 0:	// AND, OR
      case 1	:
	{
	  $searchstr = str_replace(".", " ", $searchstr);
	  $searchstr_exploded = explode(" ", $searchstr);
	  $searchstr_exploded_count= 0;
	  foreach ($searchstr_exploded as $searchstr_element) {
	      $searchstr_element = trim($searchstr_element);	// furthur trim to ensure that multi space seperated words still work
	      $searchstr_exploded_count++;
	      if ($searchstr_exploded_count > 10)	// maximum 10 keywords
		break;
	      $addToken($searchstr_element);
	    }
	  break;
	}
      case 2	: {	// single match
	$addToken($searchstr);
	break;
      }
      case 3 : {	// exact
	$addToken($searchstr, true);
	break;
      }
    }


    switch ($search_area) {
    case 0 : {	// torrent name
      $wherea[] =  $generateMatchSql(['torrents.name', 'torrents.small_descr'], true);
      break;
    }
    case 1 : {	// torrent description
      $wherea[] =  $generateMatchSql(['torrents.descr'], false);
      break;
    }
	/*case 2	:	// torrent small description
	  {
	  foreach ($like_expression_array as &$like_expression_array_element)
	  $like_expression_array_element =  "torrents.small_descr". $like_expression_array_element;
	  $wherea[] =  implode($ANDOR, $like_expression_array);
	  break;
	  }*/
      case 3 : {	// torrent uploader
	$basic =  $generateMatchSql(['users.username'], false);
	//show all for manager
	$w = '(' . $basic;
	if (!checkPrivilege(['Torrent', 'edit'])) {
	  // show not anonymous torrents for all
	  $w .= " AND torrents.anonymous = 'no') ";
	  if (isset($CURUSER) && (strstr($searchstr, $CURUSER['username']) || strstr($CURUSER['username'], $searchstr))) {
	    // show self torrents for registered users
	    $w .=  'OR (users.id=' . $CURUSER['id'] . ') ';
	  }
	}
	else {
	  $w .= ')';
	}
	$wherea[] = $w;
	break;
      }
      case 4  :  //imdb url
	$wherea[] =  $generateMatchSql(['torrents.url'], false);
	break;
    default : {	// unkonwn
      $search_area = 0;
      $wherea[] =  $generateMatchSql(['torrents.name'], true);
      #	  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_area field in" . $_SERVER['SCRIPT_NAME'], 'mod');
      break;
    }
    }
    $addparam .= "search_area=" . $search_area . "&";
    $addparam .= "search=" . rawurlencode($searchstr) . "&".$notnewword;
    $addparam .= "search_mode=".$search_mode."&";
  }


if ($wherecatin)
  $wherea[] = "category IN(" . $wherecatin . ")";
if ($showsubcat){
  if ($wheresourcein)
    $wherea[] = "source IN(" . $wheresourcein . ")";
  if ($wheremediumin)
    $wherea[] = "medium IN(" . $wheremediumin . ")";
  if ($wherecodecin)
    $wherea[] = "codec IN(" . $wherecodecin . ")";
  if ($wherestandardin)
    $wherea[] = "standard IN(" . $wherestandardin . ")";
  if ($whereprocessingin)
    $wherea[] = "processing IN(" . $whereprocessingin . ")";
  if ($whereteamin)
    $wherea[] = "team IN(" . $whereteamin . ")";
  if ($whereaudiocodecin)
    $wherea[] = "audiocodec IN(" . $whereaudiocodecin . ")";
}


$joins = '';
$group = '';

if (!($allsec == 1 || $enablespecial != 'yes')) {
  $wherea[] = "categories.mode = '$sectiontype'";
  $joins .= 'LEFT JOIN categories ON category = categories.id ';
#  $group .= 'GROUP BY categories.mode';
}

if ($search_area == 3 || $column == "owner") {
  $joins .= 'LEFT JOIN users ON torrents.owner = users.id ';
}

function generateWhere($wherea) {
  if ($wherea) {
    return 'WHERE ' . implode(" AND ", $wherea);
  }
  else {
    return '';
  }
}
$where = generateWhere($wherea);

$sql_extra = $joins . $where;
$sql_extra_md5 = MD5($sql_extra);

if($torrent_count_cache) {
#  $timer_1_start = microtime(true);
  $sql_key = 'torrent-count-' . $sql_extra_md5;
  $count = $Cache->get_value($sql_key);
#  var_dump($sql_key, $count);
  if(empty($count)) {
#    $timer_2_start = microtime(true);
    $count = get_row_count('torrents', $sql_extra);
    $Cache->cache_value($sql_key, $count, TORRENT_COUNT_CACHE_TIME);
    
#    $timer_2_end = microtime(true);
  }
#  $timer_1_end = microtime(true);
#  $time_delta1 = $timer_1_end - $timer_1_start;
#  $time_delta2 = $timer_2_end - $timer_2_start;
}
else {
  $count = get_row_count('torrents', $sql_extra);
}

if ($torrentsperpage_main) {
  $torrentsperpage = $torrentsperpage_main;
}
else {
  $torrentsperpage = 50;
}

#timer_3_start = microtime(true); // debug
// var_dump($count); die(); //
if ($count) {
  if ($addparam != "") {
    if ($pagerlink != "") {
      if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
	$addparam = $addparam . "&" . $pagerlink;
      }
      else {
	$addparam = $addparam . $pagerlink;
      }
    }
  }
  else {
    //stderr("in else","");
    $addparam = $pagerlink;
  }
  //stderr("addparam",$addparam);
  //echo $addparam;

  list($pagertop, $pagerbottom, $limit, $next_page_href, $start) = pager($torrentsperpage, $count, "?" . $addparam);

  $sql_extra_order_md5 = md5($sql_extra . $orderby . $start);
  
  $fields = 'torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.cache_stamp,torrents.oday, torrents.storing, torrents.pos_state_until';

    //Modified by bluemonster 20111026 & by Eggsorer 20120517
  $extraByState = function($state, $eq = true) use ($joins, $wherea, $orderby) {
    $wherea_sticky = $wherea;
    if ($eq) {
      $wherea_sticky[] = 'pos_state = "' . $state . '" ';
    }
    else {
      $wherea_sticky[] = 'pos_state != "' . $state . '" ';
    }
    $where_sticky = generateWhere($wherea_sticky);

    return $joins . $where_sticky . $orderby;
  };

  if ($start == 0) {
    $stickyquery = "SELECT $fields FROM torrents " . $extraByState('sticky');
    $randomquery = "SELECT $fields FROM torrents " . $extraByState('random') . " LIMIT 50";
  
    //固定置顶种子总数的缓存
    $stickynum = false;
    if ($torrents_list_cache) {
      $cache_key = 'torrent-sticky-' . $sql_extra_md5;
      $stickynum = $Cache->get_value($cache_key);
    }

    if ($stickynum === false) {
      $wherea_sticky = $wherea;
      $wherea_sticky[] = 'pos_state = "sticky"';
      $where_sticky = generateWhere($wherea_sticky);
      $stickynum = get_row_count('torrents', $joins . $where_sticky);
      if ($torrents_list_cache) {
	$Cache->cache_value($cache_key, $stickynum, STICKY_TORRENT_NUM_CACHE_TIME);
      }
    }


    //置顶种子的缓存
    // 缓存开关放在文件最顶部 BruceWolf 2012.03.21
    //加入随机种子功能 Eggsorer 2012.06.13
    if ($torrents_list_cache) { // 使用缓存
      // 取缓存数据
      $query_key = 'torrent-rows-sticky-' . $sql_extra_md5;
      $ids_key = 'torrent-rows-sticky-id-' . $sql_extra_md5;
      $stickyrows = $Cache->get_value($query_key);
      $lucky_ids = $Cache->get_value($ids_key);
    }
    else {
      $stickyrows = false;
    }

    // 缓存为空时从数据库获取数据
    if($stickyrows === false) {
      //查询固定置顶的种子
      $res = sql_query($stickyquery) or sqlerr(__FILE__, __LINE__);
      $stickyrows = [];
      while ($row = mysql_fetch_assoc($res)) {
	$stickyrows[] = $row;
      }
      //查询随机置顶的种子
      $res = sql_query($randomquery) or sqlerr(__FILE__, __LINE__);
      $randomrows = [];
      while ($row = mysql_fetch_assoc($res)) {
	$row['lucky'] = true;
	$randomrows[] = $row;
      }
      //挑选随机种子置顶
      $stickyremain = $stickylimit - $stickynum;//计算空位
      $lucky_ids = [];

      if ($stickyremain >= 0) {
	shuffle($randomrows);//摇一摇！
	$lucky = array_slice($randomrows, 0, $stickyremain);//分出被宠幸的随机种子

	//将置顶种和选上的随机种合并
	$stickyrows = array_merge($stickyrows, $lucky);

	foreach ($lucky as $row) {
	  $lucky_ids[$row['id']] = true;
	}
      }
    
      if ($torrents_list_cache) {
	$Cache->cache_value($query_key, $stickyrows, TORRENT_STICKY_CACHE_TIME);
	$Cache->cache_value($ids_key, $lucky_ids, TORRENT_STICKY_CACHE_TIME);
      }
    }
  }
  else {
    $stickyrows = [];
  }
	
  //非固定置顶种子的缓存
  $normalrows = false;
  if ($torrents_list_cache) {
    $query_key = 'torrent-rows-normal-' . $sql_extra_order_md5;
    $normalrows = $Cache->get_value($query_key);
  }

  if($normalrows === false) {
    $normalquery = "SELECT $fields FROM torrents " . $extraByState('sticky', false) . ' '. $limit;
    $res = sql_query($normalquery) or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_assoc($res)) {//不保存已经被选上的随机种子
      if (!isset($lucky_ids[$row['id']])) {
	$normalrows[] = $row;
      }
    }
    
    if ($torrents_list_cache) {
      $Cache->cache_value($query_key, $normalrows, TORRENT_NORMAL_CACHE_TIME);
    }
  }

  //将置顶的种子和非置顶种子合并
  $rows = array_merge($stickyrows, $normalrows);
}
else {
  unset($rows);
}

#$timer_3_end = microtime(true); // debug
#$timer_4_start = microtime(true); // debug

if ($_GET['format'] == 'json') {
  include('include/torrents_json.php');
}
else {
  include('include/torrents_html.php');
}
#$timer_4_end = microtime(true); // debug
#$timer_0_end = microtime(true); // debug
/* if($_GET['ttimer']) { */
/* 	$ttimer = "[{$time_delta1}](1), [{$time_delta2}](2)"; */
/* 	$ttimer .= ',['.($timer_3_end - $timer_3_start).'](3)'; */
/* 	$ttimer .= ',['.($timer_4_end - $timer_4_start).'](4)'; */
/* 	$ttimer .= ',['.($timer_0_end - $timer_0_start).'](all)'; // debug */
/* 	echo $ttimer; */
/* } */

