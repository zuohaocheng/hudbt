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

$args = [];

$searchstr = trim($_GET["search"]);
if (empty($searchstr)) {
  unset($searchstr);
  $searchstr_ori = null;
}
else {
  $searchstr_ori = htmlspecialchars($searchstr);
}

// sorting by MarkoStamcar

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

if ($_GET['type'] == 'asc') {
  $ascdesc = "ASC"; $linkascdesc = "asc";
}
else {
  $ascdesc = "DESC"; $linkascdesc = "desc"; 
}

if($column == "owner") {
  $orderby[] = "torrents.anonymous, users.username " . $ascdesc;
}
else {
  $orderby[] = "torrents." . $column . " " . $ascdesc;
}

if (isset($_GET['sort']) && isset($_GET['type'])) {
  $pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";
}
else {
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
if (isset($_GET["allsec"])) {
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
if (isset($_GET["inclbookmarked"])) {
  $inclbookmarked = 0 + $_GET["inclbookmarked"];
}
elseif ($CURUSER['notifs']){
  if (strpos($CURUSER['notifs'], "[inclbookmarked=1]") !== false)
    $inclbookmarked = 1;
  elseif (strpos($CURUSER['notifs'], "[inclbookmarked=2]") !== false)
    $inclbookmarked = 2;
  else
    $inclbookmarked = 0;
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
if (isset($_GET["spstate"]))
  $special_state = 0 + $_GET["spstate"];
elseif ($CURUSER['notifs']){
  if (strpos($CURUSER['notifs'], "[spstate=1]") !== false)
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
  elseif (strpos($CURUSER['notifs'], "[spstate=7]") !== false)
    $special_state = 7;
  else
    $special_state = 0;
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
  $wherestoring = true;
  $wherea[] = "storing=1";
  $addparam .= 'storing=1&';
}
else {
  $wherestoring = false;
}

if (isset($_GET["indate"])) {
  $indate = 0 + $_GET["indate"];

  if ($indate) {
    $addparam .= 'indate=' . $indate .'&';
    $wherea[] = " DATEDIFF('".date("Y-m-d")."',torrents.added)<= :indate";
    $args[':indate'] = $indate;
  }
}

function direct_get($item) {
  if (isset($_REQUEST[$item])) {
    $v = $_REQUEST[$item];
    $_REQUEST[$item . $v] = true;
    $_GET[$item . $v] = true;
  }
}

direct_get('cat');
if ($showsubcat){
  if ($showsource) direct_get('source');
  if ($showmedium) direct_get('medium');
  if ($showcodec) direct_get('codec');
  if ($showstandard) direct_get('standard');
  if ($showprocessing) direct_get('processing');
  if ($showteam) direct_get('team');
  if ($showaudiocodec) direct_get('audio');
}

$mainCats = array('1' => array('401', '413', '414', '415', '430'),
		  '2' => array('402', '416', '417', '418'),
		  '3' => array('405', '427', '428', '429'),
		  '4' => array('410', '431'),
		  '5' => array('403', '419', '420', '421'),
		  '6' => array('412', '409'),
		  '7' => array('407'),
		  '8' => array('408', '422', '423', '424', '425'),
		  '9' => array('404'),
		  '10' => array('411', '426'),
		  '11' => array('406'),
		  '12' => ['432'],
		  '13' => ['1037']);
$mainCatsName = array('1' => '电影', '2'=> '剧集', '3' => '动漫', '4'=>'游戏', '5'=>'综艺', '6'=>'资料', '7'=>'体育', '8'=>'音乐', '9'=>'纪录片', '10' =>'软件', '11'=>'MV', '12'=>'电子书', '13' => '华中科技大学');

$all = 0 + $_GET["all"];

if (!$all) {
  if (!$_GET && $CURUSER['notifs']) {
    $notifs = $CURUSER['notifs'];
    function read_notifs($req_key, $keys, $notifs_key) {
      global $notifs;
      foreach ($keys as $key) {
	$k = $key['id'];
	$findme  = '['.$notifs_key. $k .']';
	if (strpos($notifs, $findme) !== false) {
	  $_REQUEST[$req_key . $k] = true;
	}
      }
    }

    read_notifs('cat', $cats, 'cat');

    if ($showsubcat){
      if ($showsource) {
	read_notifs('source', $sources, 'sou');
      }
      if ($showmedium) {
	read_notifs('medium', $media, 'med');
      }
      if ($showcodec) {
	read_notifs('codec', $codecs, 'cod');
      }
      if ($showstandard) {
	read_notifs('standard', $standards, 'sta');
      }
      if ($showprocessing) {
	read_notifs('processing', $processings, 'pro');
      }
      if ($showteam) {
	read_notifs('team', $teams, 'tea');
      }
      if ($showaudiocodec) {
	read_notifs('audiocodec', $audiocodecs, 'aud');
      }
    }	
  }

  function add_where($req_key, $keys, &$vals) {
    global $addparam;
    $all = true;
    $values = [];
    foreach ($keys as $key) {
      $k = $key['id'];
      $req_k = $req_key . $k;
      $v = isset($_REQUEST[$req_k]);
      if ($v) {
	$values[] = $k;
	$addparam .= $req_k . "&";
      }
      else {
	$all = false;
      }
    }

    if ($all || count($values) == 0) {
      $sql = false;
      $vals = [];
    }
    else if (count($values) == 1) {
      $sql = $req_key . '=' . $values[0];
      $vals = $values;
    }
    else {
      $sql = $req_key . ' IN(' . implode(',', $values) . ')';
      $vals = $values;
    }
    return $sql;
  };

  foreach ($cats as $cat) {
    $v = $_REQUEST["cat$cat[id]"];
    if ($v) {
      $wherecatina[] = $cat['id'];
      $addparam .= "cat$cat[id]=1&";
    }
  }

  $selectedMainCat = [];
  foreach ($mainCats as $cat=>$subcats) {
    if ($_REQUEST["cat$cat"]) {
      $selectedMainCat[$cat] = true;
      foreach($subcats as $subcat) {
	$wherecatina[] = $subcat;
      }
      $addparam .= "cat$cat=1&";
    }
  }
  
  if (count($wherecatina) != 0) {
    $wherecatina = array_unique($wherecatina);
    if (count($wherecatina) < count($cats)) {
      if (count($wherecatina) > 1) {
	$wherea[] = "category IN(" . implode(",",$wherecatina) . ")";
      }
      else {
	$wherea[] = "category = " . $wherecatina[0];
      }
    }
  }

  if ($showsubcat) {
    if ($showsource) {
      $wherea[] = add_where('source', $sources, $wheresourceina);
    }

    if ($showmedium) {
      $wherea[] = add_where('medium', $media, $wheremediumina);
    }

    if ($showcodec) {
      $wherea[] = add_where('codec', $codecs, $wherecodecina);
    }

    if ($showstandard) {
      $wherea[] = add_where('standard', $standards, $wherestandardina);
    }

    if ($showprocessing) {
      $wherea[] = add_where('processing', $processings, $whereprocessingina);
    }

    if ($showteam) {
      $wherea[] = add_where('team', $teams, $whereteamina);
    }

    if ($showaudiocodec) {
      $wherea[] = add_where('audiocodec', $audiocodecs, $whereaudiocodecina);
    }
  }
}


if (isset($searchstr)) {  
  if (!$_GET['notnewword']){
    $notnewword="";
  }
  else{
    $notnewword="notnewword=1&";
  }

  $search_area = 0 + $_GET["search_area"];
  if (!in_array($search_area,array(0,1,3,4))) {
    $search_area = 0;
  }

  if ($search_area < 2) {
    // Expand only search title or descr
    $searchstr = add_space_between_words($searchstr);
    $search_mode = 0;
  }
  else {
    if ($search_area == 3 || $search_area == 4) {
      $search_mode = 3; // Use exact mode searching username or imdb
      if ($search_area == 4) {
	$searchstr = (int)parse_imdb_id($searchstr);
      }
    }
  }

  unset($like_expression_array);
  $matches = [];
  $likes = [];
  $exact = [];

  function canMatch($a) {
    $len = strlen($a);
    $allAlnum = true;
    for ($i = 0; $i < $len; ++$i) {
      $ch = $a[$i];
      if (!ctype_alnum($ch) && $ch != '-') {
	$allAlnum = false;
	break;
      }
    }
    return $allAlnum;
  };
    
  function addToken($token, $exact_flag = false) {
    global $matches, $likes, $exact;
    if (empty($token)) {
      return;
    }
    if ($exact_flag) {
      $exact[] = $token;
    }
    else if (canMatch($token)) {
      $matches[] = $token;
    }
    else {
      $likes[] = $token;
    }
  };
    
  function generateMatchSql($fields, $canUseMatch) {
    global $matches, $likes, $exact;
    $search_args = [];
    $out = [];
    $counter = 0;
    $allnot = true;
    if ($canUseMatch) {
      if (count($matches)) {
	$matches = array_filter(array_map(function($o) use (&$allnot) {
	      if ($o[0] == '-') {
		if (strlen($o) == 1) {
		  return null;
		}
		$minus = true;
		$o = substr($o, 1);
	      }
	      else {
		$minus = false;
	      }

	      if (stopwords($o)) {
		return null;
	      }

	      $single = Inflector::singularize($o);
	      $plural = Inflector::pluralize($o);
	      $both = ($single != $plural && ($single == $o || $plural == $o));

	      if ($minus) {
		if ($both) {
		  return '-' . $single . ' -' . $plural;
		}
		else {
		  return '-(' . $o . ')';
		}
	      }
	      else {
		$allnot = false;
		if ($both) {
		  if ($single == $o) {
		    $o_other = $plural;
		  }
		  else if ($plural == $o) {
		    $o_other = $single;
		  }
		}
		
		if ($both) {
		  // Rank original search in first
		  return '+(>' . $o . ',<' . $o_other . ')';
		}
		else {
		  return '+(' . $o . ')';
		}
	      }
	    }, $matches));

	$non_minus = array_filter($matches, function($a) {
	    return ($a[0] != '-');
	  });
	
	if (!empty($matches) && !empty($non_minus)) {
	  $match = 'MATCH(' . implode(',', $fields) . ') AGAINST (:matches IN BOOLEAN MODE)';
	  $out[] = $match;
	  $search_args[':matches'] = implode(' ', $matches);
	  global $column, $orderby;
	  if ($column == 'id') {
	    array_unshift($orderby, $match . ' DESC');
	  }
	}
      }
    }
    else {
      $likes = array_merge($matches, $likes);
    }
    $likes = array_map(function($o) use (&$counter, &$allnot, &$search_args) {
	if ($o[0] == '-') {
	  $o = substr($o, 1);
	  $not =true;
	}
	else {
	  $not = false;
	  $allnot = false;
	}
	
	if (strlen($o) > 0) {
	  $key = ':like' . $counter;
	  $search_args[$key] = '%' . $o . '%';
	  $counter += 1;
	  if ($not) {
	    return 'NOT LIKE ' . $key ;
	  }
	  else {
	    return 'LIKE ' . $key;
	  }
	}
	return null;
      }, $likes);
    $exact = array_map(function($o) use (&$counter, &$search_args) {
	$key = ':eq' . $counter;
	$search_args[$key] =  $o;
	$counter += 1;
	return '= ' . $key;
      }, $exact);

    $likes_sql = implode('AND', array_map(function($o) use ($fields) {
	  return '(' . implode(' OR ', array_map(function($field) use ($o) {
		return $field . ' ' . $o;
	      }, $fields)) . ')';
	}, array_filter(array_merge($likes, $exact))));
    
    if ($likes_sql) {
      $out[] = $likes_sql;
    }
    
    if (!$allnot || count($exact)) { // Don't output where if all keywords is negative
      global $args;
      $args = array_merge($args, $search_args);
    }
    else {
      $out = [];
    }

    return implode('AND', $out);
  };

  switch ($search_mode) {
  case 0:	// Normal
    { 
      $searchstr = preg_replace("![^-\w\d\s]!u", " ", $searchstr);
      $searchstr_exploded = explode(" ", $searchstr);
      $searchstr_exploded_count= 0;
      foreach ($searchstr_exploded as $searchstr_element) {
	$searchstr_element = trim($searchstr_element);	// furthur trim to ensure that multi space seperated words still work
	$searchstr_exploded_count++;
	if ($searchstr_exploded_count > 10)	// maximum 10 keywords
	  break;
	addToken(mb_strtolower($searchstr_element, 'utf-8'));
      }
      break;
    }
  case 3 : {	// exact, using equal
    addToken($searchstr, true);
    break;
  }
  }

  switch ($search_area) {
  case 0 : {	// torrent name
    $wherea[] =  generateMatchSql(['torrents.name', 'torrents.small_descr'], true);
    break;
  }
  case 1 : {	// torrent description
    $wherea[] =  generateMatchSql(['torrents.descr'], false);
    break;
  }
  case 3 : {	// torrent uploader
    $basic =  generateMatchSql(['users.username'], false);
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
    $wherea[] =  generateMatchSql(['torrents.url'], false);
    break;
  default : {	// unkonwn
    $search_area = 0;
    $wherea[] =  generateMatchSql(['torrents.name'], true);
    #	  write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_area field in" . $_SERVER['SCRIPT_NAME'], 'mod');
    break;
  }
  }
  $addparam .= "search_area=" . $search_area . "&";
  $addparam .= "search=" . rawurlencode($searchstr) . "&".$notnewword;
}
else {
  $search_area = 0;
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
$wherea = array_filter($wherea);
$where = generateWhere($wherea);

if (isset($orderby)) {
  $orderby = 'ORDER BY ' . implode(',', $orderby);
}

$sql_extra = $joins . $where;
$sql_args = $sql_extra . serialize($args);
$sql_extra_md5 = MD5($sql_args);

if($torrent_count_cache) {
#  $timer_1_start = microtime(true);
  $sql_key = 'torrent-count-' . $sql_extra_md5;
  $count = $Cache->get_value($sql_key);
#  var_dump($sql_key, $count);
  if(empty($count)) {
#    $timer_2_start = microtime(true);
    $count = get_row_count('torrents', $sql_extra, $args);
    $Cache->cache_value($sql_key, $count, TORRENT_COUNT_CACHE_TIME);
    
#    $timer_2_end = microtime(true);
  }
#  $timer_1_end = microtime(true);
#  $time_delta1 = $timer_1_end - $timer_1_start;
#  $time_delta2 = $timer_2_end - $timer_2_start;
}
else {
  $count = get_row_count('torrents', $sql_extra, $args);
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

  $sql_extra_order_md5 = md5($sql_args . $orderby . $start);
  
  $fields = 'torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.oday, torrents.storing, torrents.pos_state_until';

    //Modified by bluemonster 20111026 & by Eggsorer 20120517
  function extraByState($state, $eq = true) {
    global $joins, $wherea, $orderby;
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
    $stickyquery = "SELECT $fields FROM torrents " . extraByState('sticky');
    $randomquery = "SELECT $fields FROM torrents " . extraByState('random') . " LIMIT 50";
  
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
      $stickynum = get_row_count('torrents', $joins . $where_sticky, $args);
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
      $stickyrows = sql_fetchAll($stickyquery, $args);
      //查询随机置顶的种子
      $res = sql_query($randomquery, $args) or sqlerr(__FILE__, __LINE__);
      $randomrows = [];
      while ($row = _mysql_fetch_assoc($res)) {
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
    $normalquery = "SELECT $fields FROM torrents " . extraByState('sticky', false) . ' '. $limit;
    $res = sql_query($normalquery, $args) or sqlerr(__FILE__, __LINE__);
    $normalrows = [];
    while ($row = _mysql_fetch_assoc($res)) {//不保存已经被选上的随机种子
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

  // insert suggest only if there's result
  if (isset($searchstr)) {
    insert_suggest($searchstr, $CURUSER['id']);
  }
}
else {
  $rows = [];
}

#$timer_3_end = microtime(true); // debug
#$timer_4_start = microtime(true); // debug

$progress = [];
if (isset($rows)) {
  $ids = array_map(function($r) {
      return $r['id'];
    }, $rows);
  if (!empty($ids)) {
    $sql = 'SELECT torrentid, snatched.to_go, finished, peers.id AS peer_id FROM snatched LEFT JOIN peers ON peers.torrent = snatched.torrentid AND peers.userid = snatched.userid WHERE torrentid IN (' . implode(',', $ids) . ') AND snatched.userid=' . $CURUSER['id'];
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

    while ($row = _mysql_fetch_assoc($res)) {
      $progress[$row['torrentid']] = $row;
    }
  }
}

if ($_REQUEST['format'] == 'json') {
  include('include/torrents_json.php');
}
else if ($_REQUEST['format'] == 'xhr') { 
  include('include/torrents_xhr.php');
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

function stopwords($w) {
  static $stopwords = [
   "able" => true,
   "about" => true,
   "above" => true,
   "according" => true,
   "accordingly" => true,
   "across" => true,
   "actually" => true,
   "after" => true,
   "afterwards" => true,
   "again" => true,
   "against" => true,
   "aint" => true,
   "all" => true,
   "allow" => true,
   "allows" => true,
   "almost" => true,
   "alone" => true,
   "along" => true,
   "already" => true,
   "also" => true,
   "although" => true,
   "always" => true,
   "am" => true,
   "among" => true,
   "amongst" => true,
   "an" => true,
   "and" => true,
   "another" => true,
   "any" => true,
   "anybody" => true,
   "anyhow" => true,
   "anyone" => true,
   "anything" => true,
   "anyway" => true,
   "anyways" => true,
   "anywhere" => true,
   "apart" => true,
   "appear" => true,
   "appreciate" => true,
   "appropriate" => true,
   "are" => true,
   "arent" => true,
   "around" => true,
   "as" => true,
   "aside" => true,
   "ask" => true,
   "asking" => true,
   "associated" => true,
   "at" => true,
   "available" => true,
   "away" => true,
   "awfully" => true,
   "be" => true,
   "became" => true,
   "because" => true,
   "become" => true,
   "becomes" => true,
   "becoming" => true,
   "been" => true,
   "before" => true,
   "beforehand" => true,
   "behind" => true,
   "being" => true,
   "believe" => true,
   "below" => true,
   "beside" => true,
   "besides" => true,
   "best" => true,
   "better" => true,
   "between" => true,
   "beyond" => true,
   "both" => true,
   "brief" => true,
   "but" => true,
   "by" => true,
   "cmon" => true,
   "came" => true,
   "can" => true,
   "cannot" => true,
   "cant" => true,
   "cause" => true,
   "causes" => true,
   "certain" => true,
   "certainly" => true,
   "changes" => true,
   "clearly" => true,
   "co" => true,
   "com" => true,
   "come" => true,
   "comes" => true,
   "concerning" => true,
   "consequently" => true,
   "consider" => true,
   "considering" => true,
   "contain" => true,
   "containing" => true,
   "contains" => true,
   "corresponding" => true,
   "could" => true,
   "couldnt" => true,
   "course" => true,
   "currently" => true,
   "definitely" => true,
   "described" => true,
   "despite" => true,
   "did" => true,
   "didnt" => true,
   "different" => true,
   "do" => true,
   "does" => true,
   "doesnt" => true,
   "doing" => true,
   "dont" => true,
   "done" => true,
   "down" => true,
   "downwards" => true,
   "during" => true,
   "each" => true,
   "edu" => true,
   "eg" => true,
   "eight" => true,
   "either" => true,
   "else" => true,
   "elsewhere" => true,
   "enough" => true,
   "entirely" => true,
   "especially" => true,
   "et" => true,
   "etc" => true,
   "even" => true,
   "ever" => true,
   "every" => true,
   "everybody" => true,
   "everyone" => true,
   "everything" => true,
   "everywhere" => true,
   "ex" => true,
   "exactly" => true,
   "example" => true,
   "except" => true,
   "far" => true,
   "few" => true,
   "fifth" => true,
   "first" => true,
   "five" => true,
   "followed" => true,
   "following" => true,
   "follows" => true,
   "for" => true,
   "former" => true,
   "formerly" => true,
   "forth" => true,
   "four" => true,
   "from" => true,
   "further" => true,
   "furthermore" => true,
   "get" => true,
   "gets" => true,
   "getting" => true,
   "given" => true,
   "gives" => true,
   "go" => true,
   "goes" => true,
   "going" => true,
   "gone" => true,
   "got" => true,
   "gotten" => true,
   "greetings" => true,
   "had" => true,
   "hadnt" => true,
   "happens" => true,
   "hardly" => true,
   "has" => true,
   "hasnt" => true,
   "have" => true,
   "havent" => true,
   "having" => true,
   "he" => true,
   "hes" => true,
   "hello" => true,
   "help" => true,
   "hence" => true,
   "her" => true,
   "here" => true,
   "heres" => true,
   "hereafter" => true,
   "hereby" => true,
   "herein" => true,
   "hereupon" => true,
   "hers" => true,
   "herself" => true,
   "hi" => true,
   "him" => true,
   "himself" => true,
   "his" => true,
   "hither" => true,
   "hopefully" => true,
   "how" => true,
   "howbeit" => true,
   "however" => true,
   "id" => true,
   "ill" => true,
   "im" => true,
   "ive" => true,
   "ie" => true,
   "if" => true,
   "ignored" => true,
   "immediate" => true,
   "in" => true,
   "inasmuch" => true,
   "inc" => true,
   "indeed" => true,
   "indicate" => true,
   "indicated" => true,
   "indicates" => true,
   "inner" => true,
   "insofar" => true,
   "instead" => true,
   "into" => true,
   "inward" => true,
   "is" => true,
   "isnt" => true,
   "it" => true,
   "itd" => true,
   "itll" => true,
   "its" => true,
   "its" => true,
   "itself" => true,
   "just" => true,
   "keep" => true,
   "keeps" => true,
   "kept" => true,
   "know" => true,
   "knows" => true,
   "known" => true,
   "last" => true,
   "lately" => true,
   "later" => true,
   "latter" => true,
   "latterly" => true,
   "least" => true,
   "less" => true,
   "lest" => true,
   "let" => true,
   "lets" => true,
   "like" => true,
   "liked" => true,
   "likely" => true,
   "little" => true,
   "look" => true,
   "looking" => true,
   "looks" => true,
   "ltd" => true,
   "mainly" => true,
   "many" => true,
   "may" => true,
   "maybe" => true,
   "me" => true,
   "mean" => true,
   "meanwhile" => true,
   "merely" => true,
   "might" => true,
   "more" => true,
   "moreover" => true,
   "most" => true,
   "mostly" => true,
   "much" => true,
   "must" => true,
   "my" => true,
   "myself" => true,
   "name" => true,
   "namely" => true,
   "nd" => true,
   "near" => true,
   "nearly" => true,
   "necessary" => true,
   "need" => true,
   "needs" => true,
   "neither" => true,
   "never" => true,
   "nevertheless" => true,
   "new" => true,
   "next" => true,
   "nine" => true,
   "no" => true,
   "nobody" => true,
   "non" => true,
   "none" => true,
   "noone" => true,
   "nor" => true,
   "normally" => true,
   "not" => true,
   "nothing" => true,
   "novel" => true,
   "now" => true,
   "nowhere" => true,
   "obviously" => true,
   "of" => true,
   "off" => true,
   "often" => true,
   "oh" => true,
   "ok" => true,
   "okay" => true,
   "old" => true,
   "on" => true,
   "once" => true,
   "one" => true,
   "ones" => true,
   "only" => true,
   "onto" => true,
   "or" => true,
   "other" => true,
   "others" => true,
   "otherwise" => true,
   "ought" => true,
   "our" => true,
   "ours" => true,
   "ourselves" => true,
   "out" => true,
   "outside" => true,
   "over" => true,
   "overall" => true,
   "own" => true,
   "particular" => true,
   "particularly" => true,
   "per" => true,
   "perhaps" => true,
   "placed" => true,
   "please" => true,
   "plus" => true,
   "possible" => true,
   "presumably" => true,
   "probably" => true,
   "provides" => true,
   "que" => true,
   "quite" => true,
   "qv" => true,
   "rather" => true,
   "rd" => true,
   "re" => true,
   "really" => true,
   "reasonably" => true,
   "regarding" => true,
   "regardless" => true,
   "regards" => true,
   "relatively" => true,
   "respectively" => true,
   "right" => true,
   "said" => true,
   "same" => true,
   "saw" => true,
   "say" => true,
   "saying" => true,
   "says" => true,
   "second" => true,
   "secondly" => true,
   "see" => true,
   "seeing" => true,
   "seem" => true,
   "seemed" => true,
   "seeming" => true,
   "seems" => true,
   "seen" => true,
   "self" => true,
   "selves" => true,
   "sensible" => true,
   "sent" => true,
   "serious" => true,
   "seriously" => true,
   "seven" => true,
   "several" => true,
   "shall" => true,
   "she" => true,
   "should" => true,
   "shouldnt" => true,
   "since" => true,
   "six" => true,
   "so" => true,
   "some" => true,
   "somebody" => true,
   "somehow" => true,
   "someone" => true,
   "something" => true,
   "sometime" => true,
   "sometimes" => true,
   "somewhat" => true,
   "somewhere" => true,
   "soon" => true,
   "sorry" => true,
   "specified" => true,
   "specify" => true,
   "specifying" => true,
   "still" => true,
   "sub" => true,
   "such" => true,
   "sup" => true,
   "sure" => true,
   "take" => true,
   "taken" => true,
   "tell" => true,
   "tends" => true,
   "th" => true,
   "than" => true,
   "thank" => true,
   "thanks" => true,
   "thanx" => true,
   "that" => true,
   "thats" => true,
   "thats" => true,
   "the" => true,
   "their" => true,
   "theirs" => true,
   "them" => true,
   "themselves" => true,
   "then" => true,
   "thence" => true,
   "there" => true,
   "theres" => true,
   "thereafter" => true,
   "thereby" => true,
   "therefore" => true,
   "therein" => true,
   "theres" => true,
   "thereupon" => true,
   "these" => true,
   "they" => true,
   "theyd" => true,
   "theyll" => true,
   "theyre" => true,
   "theyve" => true,
   "think" => true,
   "third" => true,
   "this" => true,
   "thorough" => true,
   "thoroughly" => true,
   "those" => true,
   "though" => true,
   "three" => true,
   "through" => true,
   "throughout" => true,
   "thru" => true,
   "thus" => true,
   "to" => true,
   "together" => true,
   "too" => true,
   "took" => true,
   "toward" => true,
   "towards" => true,
   "tried" => true,
   "tries" => true,
   "truly" => true,
   "try" => true,
   "trying" => true,
   "twice" => true,
   "two" => true,
   "un" => true,
   "under" => true,
   "unfortunately" => true,
   "unless" => true,
   "unlikely" => true,
   "until" => true,
   "unto" => true,
   "up" => true,
   "upon" => true,
   "us" => true,
   "use" => true,
   "used" => true,
   "useful" => true,
   "uses" => true,
   "using" => true,
   "usually" => true,
   "value" => true,
   "various" => true,
   "very" => true,
   "via" => true,
   "viz" => true,
   "vs" => true,
   "want" => true,
   "wants" => true,
   "was" => true,
   "wasnt" => true,
   "way" => true,
   "we" => true,
   "wed" => true,
   "well" => true,
   "were" => true,
   "weve" => true,
   "welcome" => true,
   "well" => true,
   "went" => true,
   "were" => true,
   "werent" => true,
   "what" => true,
   "whats" => true,
   "whatever" => true,
   "when" => true,
   "whence" => true,
   "whenever" => true,
   "where" => true,
   "wheres" => true,
   "whereafter" => true,
   "whereas" => true,
   "whereby" => true,
   "wherein" => true,
   "whereupon" => true,
   "wherever" => true,
   "whether" => true,
   "which" => true,
   "while" => true,
   "whither" => true,
   "who" => true,
   "whos" => true,
   "whoever" => true,
   "whole" => true,
   "whom" => true,
   "whose" => true,
   "why" => true,
   "will" => true,
   "willing" => true,
   "wish" => true,
   "with" => true,
   "within" => true,
   "without" => true,
   "wont" => true,
   "wonder" => true,
   "would" => true,
   "wouldnt" => true,
   "yes" => true,
   "yet" => true,
   "you" => true,
   "youd" => true,
   "youll" => true,
   "youre" => true,
   "youve" => true,
   "your" => true,
   "yours" => true,
   "yourself" => true,
   "yourselves" => true,
   "zero" => true,
];

  return (isset($stopwords[$w]));
}
