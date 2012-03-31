<?php
#$timer_0_start = microtime(true); // debug
require_once("include/bittorrent.php");
dbconn(true);
require_once(get_langfile_path("torrents.php"));

loggedinorreturn();
parked();
if ($showextinfo['imdb'] == 'yes')
  require_once ("imdb/imdb.class.php");
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
  default: $ascdesc = "DESC"; $linkascdesc = "desc"; break;
  }

  if($column == "owner") {
    $orderby = "ORDER BY pos_state DESC, torrents.anonymous, users.username " . $ascdesc;
  }
  else {
    $orderby = "ORDER BY pos_state DESC, torrents." . $column . " " . $ascdesc;
  }

  $pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";

} else {

  $orderby = "ORDER BY pos_state DESC, torrents.id DESC";
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
    write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking inclbookmarked field in" . $_SERVER['SCRIPT_NAME'], 'mod');
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

if (!in_array($include_dead,array(0,1,2,3))) {
  $include_dead = 0;
  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking incldead field in" . $_SERVER['SCRIPT_NAME'], 'mod');
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
elseif ($include_dead == 3) {		//no startseed
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
  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking spstate field in " . $_SERVER['SCRIPT_NAME'], 'mod');
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

$mainCats = array('1' => array('401', '413', '414', '415'),
		  '2' => array('402', '416', '417', '418'),
		  '3' => array('405', '427', '428', '429'),
		  '4' => array('410'),
		  '5' => array('403', '419', '420', '421'),
		  '6' => array('412', '409'),
		  '7' => array('407'),
		  '8' => array('408', '422', '423', '425'),
		  '9' => array('404'),
		  '10' => array('411', '426'),
		  '11' => array('406'));
$mainCatsName = array('1' => '电影', '2'=> '剧集', '3' => '动漫', '4'=>'游戏', '5'=>'综艺', '6'=>'资料', '7'=>'体育', '8'=>'音乐', '9'=>'纪录片', '10' =>'软件', '11'=>'MV');

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
	$wherecatina[] = $cat[id];
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
	      $wheresourceina[] = $source[id];
	      $addparam .= "source$source[id]=1&";
	    }
	}
      }

      if ($showmedium) {
	foreach ($media as $medium) {
	  $all &= $_GET["medium$medium[id]"];
	  if ($_GET["medium$medium[id]"])
	    {
	      $wheremediumina[] = $medium[id];
	      $addparam .= "medium$medium[id]=1&";
	    }
	}
      }

      if ($showcodec) {
	foreach ($codecs as $codec) {
	  $all &= $_GET["codec$codec[id]"];
	  if ($_GET["codec$codec[id]"])
	    {
	      $wherecodecina[] = $codec[id];
	      $addparam .= "codec$codec[id]=1&";
	    }
	}
      }

      if ($showstandard) {
	foreach ($standards as $standard) {
	  $all &= $_GET["standard$standard[id]"];
	  if ($_GET["standard$standard[id]"])
	    {
	      $wherestandardina[] = $standard[id];
	      $addparam .= "standard$standard[id]=1&";
	    }
	}
      }

      if ($showprocessing) {
	foreach ($processings as $processing) {
	  $all &= $_GET["processing$processing[id]"];
	  if ($_GET["processing$processing[id]"])
	    {
	      $whereprocessingina[] = $processing[id];
	      $addparam .= "processing$processing[id]=1&";
	    }
	}
      }

      if ($showteam) {
	foreach ($teams as $team) {
	  $all &= $_GET["team$team[id]"];
	  if ($_GET["team$team[id]"])
	    {
	      $whereteamina[] = $team[id];
	      $addparam .= "team$team[id]=1&";
	    }
	}
      }

      if ($showaudiocodec) {
	foreach ($audiocodecs as $audiocodec) {
	  $all &= $_GET["audiocodec$audiocodec[id]"];
	  if ($_GET["audiocodec$audiocodec[id]"])
	    {
	      $whereaudiocodecina[] = $audiocodec[id];
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
	write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_mode field in" . $_SERVER['SCRIPT_NAME'], 'mod');
      }

    $search_area = 0 + $_GET["search_area"];

    if ($search_area == 4) {
      $searchstr = (int)parse_imdb_id($searchstr);
    }
    $like_expression_array =array();
    unset($like_expression_array);

    switch ($search_mode)
      {
      case 0:	// AND, OR
      case 1	:
	{
	  $searchstr = str_replace(".", " ", $searchstr);
	  $searchstr_exploded = explode(" ", $searchstr);
	  $searchstr_exploded_count= 0;
	  foreach ($searchstr_exploded as $searchstr_element)
	    {
	      $searchstr_element = trim($searchstr_element);	// furthur trim to ensure that multi space seperated words still work
	      $searchstr_exploded_count++;
	      if ($searchstr_exploded_count > 10)	// maximum 10 keywords
		break;
	      $like_expression_array[] = " LIKE '%" . $searchstr_element. "%'";
	    }
	  break;
	}
      case 2	:	// exact
	{
	  $like_expression_array[] = " LIKE '%" . $searchstr. "%'";
	  break;
	}
      case 3 : {	// complete
	$like_expression_array[] = " LIKE '" . $searchstr. "'";
	break;
      }
      }
    $ANDOR = ($search_mode == 0 ? " AND " : " OR ");	// only affects mode 0 and mode 1

    switch ($search_area)
      {
      case 0   :	// torrent name
	{
	  foreach ($like_expression_array as &$like_expression_array_element)
	    $like_expression_array_element = "(torrents.name" . $like_expression_array_element." OR torrents.small_descr". $like_expression_array_element.")";
	  $wherea[] =  implode($ANDOR, $like_expression_array);
	  break;
	}
      case 1	:	// torrent description
	{
	  foreach ($like_expression_array as &$like_expression_array_element)
	    $like_expression_array_element = "torrents.descr". $like_expression_array_element;
	  $wherea[] =  implode($ANDOR,  $like_expression_array);
	  break;
	}
	/*case 2	:	// torrent small description
	  {
	  foreach ($like_expression_array as &$like_expression_array_element)
	  $like_expression_array_element =  "torrents.small_descr". $like_expression_array_element;
	  $wherea[] =  implode($ANDOR, $like_expression_array);
	  break;
	  }*/
      case 3	:	// torrent uploader
	{
	  foreach ($like_expression_array as &$like_expression_array_element)
	    $like_expression_array_element =  "users.username". $like_expression_array_element;

	  if(!isset($CURUSER))	// not registered user, only show not anonymous torrents
	    {
	      $wherea[] =  implode($ANDOR, $like_expression_array) . " AND torrents.anonymous = 'no'";
	    }
	  else
	    {
	      if(get_user_class() > $torrentmanage_class)	// moderator or above, show all
		{
		  $wherea[] =  implode($ANDOR, $like_expression_array);
		}
	      else // only show normal torrents and anonymous torrents from hiself
		{
		  $wherea[] =   "(" . implode($ANDOR, $like_expression_array) . " AND torrents.anonymous = 'no') OR (" . implode($ANDOR, $like_expression_array). " AND torrents.anonymous = 'yes' AND users.id=" . $CURUSER["id"] . ") ";
		}
	    }
	  break;
	}
      case 4  :  //imdb url
	foreach ($like_expression_array as &$like_expression_array_element)
	  $like_expression_array_element = "torrents.url". $like_expression_array_element;
	$wherea[] =  implode($ANDOR,  $like_expression_array);
	break;
      default :	// unkonwn
	{
	  $search_area = 0;
	  $wherea[] =  "torrents.name LIKE '%" . $searchstr . "%'";
	  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_area field in" . $_SERVER['SCRIPT_NAME'], 'mod');
	  break;
	}
      }
    $addparam .= "search_area=" . $search_area . "&";
    $addparam .= "search=" . rawurlencode($searchstr) . "&".$notnewword;
    $addparam .= "search_mode=".$search_mode."&";
  }

$where = implode(" AND ", $wherea);

if ($wherecatin)
  $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
if ($showsubcat){
  if ($wheresourcein)
    $where .= ($where ? " AND " : "") . "source IN(" . $wheresourcein . ")";
  if ($wheremediumin)
    $where .= ($where ? " AND " : "") . "medium IN(" . $wheremediumin . ")";
  if ($wherecodecin)
    $where .= ($where ? " AND " : "") . "codec IN(" . $wherecodecin . ")";
  if ($wherestandardin)
    $where .= ($where ? " AND " : "") . "standard IN(" . $wherestandardin . ")";
  if ($whereprocessingin)
    $where .= ($where ? " AND " : "") . "processing IN(" . $whereprocessingin . ")";
  if ($whereteamin)
    $where .= ($where ? " AND " : "") . "team IN(" . $whereteamin . ")";
  if ($whereaudiocodecin)
    $where .= ($where ? " AND " : "") . "audiocodec IN(" . $whereaudiocodecin . ")";
}


if ($allsec == 1 || $enablespecial != 'yes')
  {
    if ($where != "")
      $where = "WHERE $where ";
    else $where = "";
    $sql = "SELECT COUNT(1) FROM torrents " . ($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents.owner = users.id " : "") . $where;
  }
else
  {
    if ($where != "")
      $where = "WHERE $where AND categories.mode = '$sectiontype'";
    else $where = "WHERE categories.mode = '$sectiontype'";
    $sql = "SELECT COUNT(*), categories.mode FROM torrents LEFT JOIN categories ON category = categories.id " . ($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents.owner = users.id " : "") . $where." GROUP BY categories.mode";
  }

if(true) {
	$timer_1_start = microtime(true);
	
	$sql_key = MD5($sql);
	$count = $Cache->get_value($sql_key);
//	var_dump($sql_key, $count);
	if(empty($count)) {
		$timer_2_start = microtime(true);
		$res = sql_query($sql) or die(mysql_error());
		$count = 0;
		while($row = mysql_fetch_array($res))
		  $count += $row[0];
	
		$Cache->cache_value($sql_key, $count, 1800);
		$timer_2_end = microtime(true);
	}
	$timer_1_end = microtime(true);
	$time_delta1 = $timer_1_end - $timer_1_start;
	$time_delta2 = $timer_2_end - $timer_2_start;

} else {
	$res = sql_query('/* FILE: '.__FILE__.' LINE: '.__LINE__.'*/ '.$sql) or die(mysql_error());
	$count = 0;
	while($row = mysql_fetch_array($res))
	  $count += $row[0];
}

if ($CURUSER["torrentsperpage"])
  $torrentsperpage = (int)$CURUSER["torrentsperpage"];
elseif ($torrentsperpage_main)
  $torrentsperpage = $torrentsperpage_main;
else $torrentsperpage = 50;



#$timer_3_start = microtime(true); // debug
if ($count)
{
	if ($addparam != "")
	  {
		if ($pagerlink != "")
		  {
			if ($addparam{strlen($addparam)-1} != ";")
			  { // & = &amp;
			$addparam = $addparam . "&" . $pagerlink;
			  }
			else
			  {
			$addparam = $addparam . $pagerlink;
			  }
		  }
	  }
	else
	  {
	//stderr("in else","");
	$addparam = $pagerlink;
	  }
	//stderr("addparam",$addparam);
	//echo $addparam;

	list($pagertop, $pagerbottom, $limit, $next_page_href) = pager($torrentsperpage, $count, "?" . $addparam);

	if ($allsec == 1 || $enablespecial != 'yes'){
	  //Modified by bluemonster 20111026
	  $query = "SELECT torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.cache_stamp,torrents.oday,torrents.storing FROM torrents ".($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents.owner = users.id " : "")." $where $orderby $limit";
	}
	else{
	  //Modified by bluemonster 20111026
	  $query = "SELECT torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.cache_stamp,torrents.oday,torrents.storing FROM torrents ".($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents.owner = users.id " : "")." LEFT JOIN categories ON torrents.category=categories.id $where $orderby $limit";
	}
	
	$query_key = MD5($query);
	$rows = $Cache->get_value($query_key);

	if(empty($rows)) {
		$res = sql_query('/* FILE: '.__FILE__.' LINE: '.__LINE__.'*/ '.$query) or die(mysql_error());

		while ($row = mysql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		$Cache->cache_value($query_key, $rows, 30);
	}
}
else
  unset($rows);

#$timer_3_end = microtime(true); // debug
#$timer_4_start = microtime(true); // debug
if ($_REQUEST['format'] == 'json') { 
  include('include/torrents_json.php');
}
else {
  include('include/torrents_html.php');
}
/* $timer_4_end = microtime(true); // debug */
/* $timer_0_end = microtime(true); // debug */
/* if($_GET['ttimer']) { */
/* 	$ttimer = "[{$time_delta1}](1), [{$time_delta2}](2)"; */
/* 	$ttimer .= ',['.($timer_3_end - $timer_3_start).'](3)'; */
/* 	$ttimer .= ',['.($timer_4_end - $timer_4_start).'](4)'; */
/* 	$ttimer .= ',['.($timer_0_end - $timer_0_start).'](all)'; // debug */
/* 	echo $ttimer; */
/* } */

