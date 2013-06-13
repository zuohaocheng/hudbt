<?php
# IMPORTANT: Do not edit below unless you know what you are doing!
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');
require_once($rootpath . 'include/globalfunctions.php');
require_once($rootpath . 'classes/class_advertisement.php');
require($rootpath . get_langfile_path("functions.php"));

$privilegeConfig = ['Maintenance'=>['staticResources' => UC_MODERATOR],
		    'Tcategory' => ['lock' => UC_UPLOADER,'delete' => UC_VIP,],
		    'Torrent' => ['edit' => $torrentmanage_class,
				  'delete'=> [$torrentmanage_class,
					      function($id) {
	if (is_null($id)) {
	  return true;
	}
	global $CURUSER, $self_deletion_before_torrent;
        App::uses('Torrent', 'Model');
	$Torrent = new Torrent;
	$Torrent->id = $id;
	$data = $Torrent->read(['owner', 'added'], $id);
	return (($CURUSER["id"] == $data["Torrent"]['owner']) && ((TIMENOW - strtotime($data['Torrent']['added'])) < $self_deletion_before_torrent));
      }
					      ], 'startseed' => UC_VIP, 'pr' => $torrentonpromotion_class,'sticky' => $torrentsticky_class,'oday' => UC_VIP,'setstoring'=>UC_MODERATOR],
		    'Subs' => ['delete' => [$submanage_class,
					    function($id) {
	global $delownsub_class, $CURUSER;
	if (is_null($id)) {
	  return true;
	}
	if (get_user_class() < $delownsub_class) {
	  return false;
	}
	App::uses('Sub', 'Model');
	$Sub = new Sub;
	$Sub->id = $id;
	$data = $Sub->read(['uppedby'], $id);
	return ($data['Sub']['uppedby'] == $CURUSER['id']);
      }]],
// ---------------------------
		    'Posts'=>['editnotseen'=>UC_MODERATOR,'seeeditnotseen'=>UC_UPLOADER,],
// ---------------------------		    
		    'Requests'=>[
				 'deleteres' => [UC_FORUM_MODERATOR,
						 function($args) {
				     list($pre, $torrent) = $args;
				     if (!$pre) {
				       return false;
				     }
				     $owner = get_single_value('torrents', 'owner', 'WHERE id=?', [$torrent]);
				     global $CURUSER;
				     return ($CURUSER['id'] == $owner);
				   }],
				 'delete' => [UC_FORUM_MODERATOR, function($id) {
				     if (is_null($id)) {
				       return true;
				     }
				     global $CURUSER;
				     $arr = sql_query('SELECT userid, finish, added FROM requests WHERE id = ?', [$id])->fetch();
				     $result = ($arr['userid']==$CURUSER["id"] && $arr['finish']=='no' && (TIMENOW - strtotime($arr['added'])) > 7 * 86400);
				     $result = $result && (get_row_count('resreq', 'WHERE reqid = ?', [$id]) == 0);
				     return $result;
				   }]],
// ---------------------------
		    'Misc' => ['fun' => $funmanage_class],
		    'ManagePanels' => [
				       'staffPanel' => UC_UPLOADER,
				       'deletedisabled' => UC_SYSOP,
				       'forummanage' => $forummanage_class,
				       'mysql_stats' => UC_SYSOP,
				       'massmail' => UC_SYSOP,
				       'docleanup' => UC_SYSOP,
				       'bans' => UC_SYSOP,
				       'maxlogin' => UC_ADMINISTRATOR,
				       'bitbucketlog' => UC_ADMINISTRATOR,
				       'bannedemails' => UC_SYSOP,
				       'allowedemails' => UC_SYSOP,
				       'location' => UC_SYSOP,
				       'amountupload' => UC_SYSOP,
				       'adduser' => UC_ADMINISTRATOR,
				       'staffmess' => UC_ADMINISTRATOR,
				       'polloverview' => $pollmanage_class,
				       'makepoll' => $pollmanage_class,
				       'warned' => UC_MODERATOR,
				       'freeleech' => UC_ADMINISTRATOR,
				       'faqmanage' => UC_MODERATOR,
				       'faqactions' => UC_MODERATOR,
				       'modrules' => UC_ADMINISTRATOR,
				       'catmanage' => UC_ADMINISTRATOR,
				       'cheaters' => UC_MODERATOR,
				       'ipcheck' => UC_MODERATOR,
				       'allagents' => UC_MODERATOR,
				       'admanage' => UC_MODERATOR,
				       'uploaders' => UC_UPLOADER,
				       'stats' => UC_MODERATOR,
				       'testip' => UC_MODERATOR,
				       'amountbonus' => UC_ADMINISTRATOR,
				       'clearcache' => UC_MODERATOR,
				       'hustip' => UC_MODERATOR,
				       'ssh' => UC_SYSOP,
				       'staffbox' => UC_UPLOADER,
				       'settings' => UC_SYSOP,
				       'keepers' => function() {
      global $CURUSER;
      return permissionAuth('viewkeepers',$CURUSER['usergroups'],$CURUSER['class']);
    },
]
		    ];

function smarty($cachetime=300, $debug = false) {
  require_once('lib/Smarty/Smarty.class.php');
  static $smarty;
  if (!$smarty) {
    $smarty = new Smarty;
    if (extension_loaded('memcache')) {
      $smarty->caching_type = 'memcache';
    }
    else if (extension_loaded('apc') && function_exists('apc_dec') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
      $smarty->caching_type = 'apc';
    }
    $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
  }
  
  $smarty->debugging = $debug;
  if (!$debug && $cachetime) {
    $smarty->setCacheLifetime($cachetime);
  }
  else {
    $smarty->setCacheLifetime(0);
  }
  return $smarty;
}

function get_langfolder_cookie() {
  global $deflang;
  return $deflang;
  /*if (!isset($_COOKIE["c_lang_folder"])) {
    return $deflang;
  } else {
    $langfolder_array = get_langfolder_list();
    foreach($langfolder_array as $lf)
      {
	if($lf == $_COOKIE["c_lang_folder"])
	  return $_COOKIE["c_lang_folder"];
      }
    return $deflang;
    }*/
}

function get_langlist() {
  $langs = [];
  $res = sql_query('SELECT site_lang_folder FROM language WHERE site_lang=1;') or sqlerr(__FILE__, __LINE__);
  while ($a = _mysql_fetch_row($res)) {
    $langs[] = $a[0];
  }
  return $langs;
}

function get_user_lang($user_id) {
  global $deflang;
  return $deflang;

  /* $lang_res = sql_query("SELECT site_lang_folder FROM language LEFT JOIN users ON language.id = users.lang WHERE language.site_lang=1 AND users.id= ". sqlesc($user_id) ." LIMIT 1") or sqlerr(__FILE__, __LINE__); */
  /* $lang = _mysql_fetch_assoc($lang_res); */
  /* return $lang['site_lang_folder']; */
}

function get_load_uri($type, $script_name ="", $absolute = true) {
  global $CURUSER, $BASEURL;
  $name = ($script_name == "" ? substr(strrchr($_SERVER['SCRIPT_NAME'],'/'),1) : $script_name);

  $addition = '';
  if (array_key_exists('purge', $_GET) && $_GET['purge']) {
    $addition .= '&amp;purge=1';
  }
  if (array_key_exists('debug', $_GET) && $_GET['debug']) {
    $addition .= '&amp;debug=1';
    $debug = true;
  }
  else {
    include('include/loadRevision.php');
    $addition .= '&amp;rev=' . $loadRevision;
    $debug = false;
  }

  $pagename = 'load.php';
  if ($absolute) {
    $pagename = '//' . $BASEURL . '/' . $pagename;
  }
  
  if ($type == 'js') {
    $hrefs = [];
    if ($script_name == '') {
      if ($debug) {
	$hrefs[] = $pagename . '?format=js'  . $addition;
      }
      else {
	$lang = get_langfolder_cookie();
	$hrefs[] ='//' . $BASEURL . '/cache/js-common-' . $lang . '.js';
      }
    }
    $filename = preg_replace('/\.php$/i', '.js', $name);

    if (file_exists('js/' . $filename)) {
      $hrefs[] = $pagename . '?format=js&amp;name=' . $name . $addition;
    }

    $userjs = 'cache/users/' . $CURUSER['id'] . '.js';
    if (file_exists($userjs) && $name != 'usercp.php') {
      if ($debug) {
	$hrefs[] = $pagename . '?format=js&amp;debug=1&amp;user=' . $CURUSER['id'];
      }
      else {
	$hrefs[] = '//' . $BASEURL . '/' . $userjs;
      }
    }
    
    return join(array_map(function($href) {
	  return '<script type="text/javascript" src="' . $href . '"></script>';
	}, $hrefs));
  }
  elseif ($type == 'css') {
    if ($debug) {
      $addition .= '&amp;theme=' . get_css_id();
      if ($CURUSER) {
	$addition .= '&amp;caticon=' . $CURUSER['caticon'];
      }
      $hrefs= [$pagename . '?format=css' . $addition];
    }
    else {
      if ($CURUSER) {
	$cat = $CURUSER['caticon'];
      }
      else {
	$cat = 1;
      }
      $lang = get_langfolder_cookie();
      $hrefs = ['//' . $BASEURL . '/cache/css-' . $lang . '-cat' . $cat . '-theme' . get_css_id() . '.css'];
    }

    $usercss = 'cache/users/' . $CURUSER['id'] . '.css';
    if (file_exists($usercss)) {
      if ($debug) {
	$hrefs[] = $pagename . '?format=css&amp;debug=1&amp;user=' . $CURUSER['id'];
      }
      else {
	$hrefs[] = '//' . $BASEURL . '/' . $usercss;
      }
    }
    return join(array_map(function($href) {
	  return '<link rel="stylesheet" href="' . $href . '" type="text/css" media="screen" />';
	}, $hrefs));
  }
  return '';
}

function get_langfile_path($script_name ="", $target = false, $lang_folder = "") {
  global $CURLANGDIR;
  $CURLANGDIR = get_langfolder_cookie();
  if($lang_folder == "") {
    $lang_folder = $CURLANGDIR;
  }
  return "lang/" . ($target == false ? $lang_folder : "_target") ."/lang_". ( $script_name == "" ? substr(strrchr($_SERVER['SCRIPT_NAME'],'/'),1) : $script_name);
}

function get_row_count($table, $suffix = "", $args = []) {
  $a = sql_query("SELECT COUNT(1) FROM $table $suffix", $args)->fetch();
  return 0 + $a[0];
}

function get_row_sum($table, $field, $suffix = "", $args = []) {
  $a = sql_query("SELECT SUM($field) FROM $table $suffix", $args)->fetch();
  return $a[0];
}

function get_single_value($table, $field, $suffix = "", $args = []) {
  $a = sql_query("SELECT $field FROM $table $suffix LIMIT 1", $args)->fetch();
  if ($a) {
    return $a[0];
  } else {
    return false;
  }
}

function checkPrivilegePanel($item = '') {
  if ($item == '') {
    $file = substr(strrchr($_SERVER['SCRIPT_NAME'],'/'),1);
    $item = str_replace('.php', '', $file);
  }
  if (!checkPrivilege(['ManagePanels', $item])) {
    permissiondenied();
  }
}

function checkSubPrivilege($obj, $opts) {
  if (is_array($obj)) {
    $result = false;
    foreach ($obj as $sobj) {
      if (checkSubPrivilege($sobj, $opts)) {
	$result = true;
	break;
      }
    }
    return $result;
  }
  else if (is_callable($obj)) {
    return $obj($opts);
  }
  else {
    return (get_user_class() >= $obj);
  }
};

// deprecated
$permissionConfig = [
"keeper" => [
	"boss" =>["setstoring","edittorrent","viewkeepers"],
	"member" => ["storing","viewkeepers"]
	],
"helper" =>[],
"former" =>[
	UC_MODERATOR => ["edittorrent","viewstaffpanel"],
	UC_ADMINISTRATOR => ["setstoring","viewkeepers"],
	UC_SYSOP => ["viewsetting","storing"]
	]
];

// deprecated
function permissionAuth($needle,$usergroups,$userclass){
	global $permissionConfig;
	
	if(isset($usergroups)){
		foreach ($usergroups as $groupname => $value) {
			$userrole = $usergroups[$groupname]['role'];
			$removed = $usergroups[$groupname]['removed'];
			if(isset($permissionConfig[$groupname][$userrole]) && in_array($needle,$permissionConfig[$groupname][$userrole]) && $removed==NULL)
				return true;
		}
	}
	
	//to cope with linear permission system, i.e. override
	$formerPermission = $permissionConfig['former'];
	foreach($formerPermission as $class => $value){
		if(in_array($needle,$formerPermission[$class])){
			if($userclass>=$class)
				return true;
			else
				return false;
		}
	}
	return false;
}

function get_user_group($userid){
  global $Cache;
  $key = 'user_groups_' . $userid;
  $user_groups = $Cache->get_value($key);
  if ($user_groups === false) {
    $user_groups = sql_query("SELECT gp.group_name,u_gp.role,u_gp.removed_by,u_gp.removed_date FROM users_usergroups AS u_gp JOIN usergroups AS gp ON u_gp.usergroup_id = gp.group_id WHERE u_gp.user_id = ?", [$userid])->fetchAll();
    $Cache->cache_value($key, $user_groups, 120);
  }

  foreach ($user_groups as $row) {
    if($row['removed_by']==NULL){
      $user_groups[$row['group_name']] = array('role' => $row['role']);
    }
    else{
      $user_groups[$row['group_name']] = array('role' => $row['role'],'removed' => array('removed_by' => $row['removed_by'], 'removed_date' => $row['removed_date'] ));
    }
  }
  return $user_groups;
}

function checkPrivilege($item, $opts = null) {
  global $privilegeConfig;

  if (is_array($item)) {
    $config = $privilegeConfig;
    for($i = 0; $i < count($item) - 1; $i += 1) {
      $key = $item[$i];
      if (array_key_exists($key, $config)) {
	$config = $config[$key];
      }
      else {
	$config = null;
	break;
      }
    }

    $last_key = array_pop($item);
    if ($config && array_key_exists($last_key, $config)) {
      $obj = $config[$last_key];

      return checkSubPrivilege($obj, $opts);
    }
  }
  elseif (is_string($item)) {
    if (array_key_exists($item, $privilegeConfig)) {
      return checkSubPrivilege($privilegeConfig[$item], $opts);
    }
  }
  throw new Exception('Invalid input');
}

function stdmsg($heading, $text, $htmlstrip = false) {
  if ($htmlstrip) {
    $heading = htmlspecialchars(trim($heading));
    $text = htmlspecialchars(trim($text));
  }

  if ($heading)
    print('<div id="stderr"><h2>'.$heading.'</h2><div class="table td frame">');

  print($text . '</div></div>');
}

function stderr($heading, $text, $htmlstrip = true, $head = true, $foot = true, $die = true) {
  global $format;
  if (isset($format) && $format == 'json') {
    echo json_encode(['success' => false, 'message' => $text]);
    die;
  }
  elseif (php_sapi_name() == 'cli') {
    echo '== ', $heading, ' ==';
    echo "\n";
    echo $text;
    echo "\n";
    die;
  }
  if ($head) stdhead();
  stdmsg($heading, $text, $htmlstrip);
  if ($foot) stdfoot();
  if ($die) die;
}

function sqlerr($file = '', $line = '', $stack = true, $q = '', $args = [], $e = null) {
  echo "<div style=\"font-family: menlo, monaco, courier, monospace; background: blue;color:white;position:fixed;overflow-y:scroll;width:100%;height:100%;top: 0%;left: 0%;\"><h1>出错啦，出错啦</h1><p>请保存此页面(菜单/文件/保存，<span style=\"font-size:xx-large\">请勿截图</span>)，<a href=\"sendmessage.php?receiver=26058\" style=\"color:yellow\">点此发送给管理员</a>，多谢!</p><div style=\"display:none\">";
  echo '<h2>', date('d M Y H:i:s'), '</h2>';
  if ($q) {
    echo '<h2>SQL Statement</h2><p>' , $q;
    if ($args) {
      echo '</p><h2>Arguments</h2><pre>';
      var_dump($args);
    }
    if ($e) {
      echo '</pre><h2>Exception</h2><pre>';
      var_dump($e);
    }
    echo '</pre>';
  }

  if ($stack) {
    echo '<h2>Stack</h2><pre>';
    debug_print_backtrace();
    echo '</pre>';
  }

  echo _mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</div></div>";

  die;
}

function format_comment($text, $null0 = null, $null1 = null, $newtab = false, $null2 = null, $image_max_width = 0, $enableimage = true, $enableflash = true , $null3 = null, $image_max_height = 0, $null4 = null) {
  global $Cache;

  extract(['warnings' => false, 'cachekey' => false, $cache = true]);
  $cnt_args = func_num_args();
  if ($cnt_args > 1) {
    $arg = func_get_args()[$cnt_args - 1]; // Last argument is a hash
    if (is_array($arg)) {
      $defaults = [[], [], ['newtab' => false], [], ['image_max_width' => 0], ['enableimage' => true], ['enableflash' => true ], [], ['image_max_height' => 0], []];

      extract($defaults[$cnt_args - 2]); //Restore default
      extract($arg);
    }
  }

  $md5 = md5($text);
  $key = 'bbcode-' . implode('-', [$enableimage, $enableflash, $image_max_width, $image_max_height, $newtab]) . '-' . $md5;
  if (!isset($_REQUEST['purge']) && $cache) {
    $out = $Cache->get_value($key);
  }
  else {
    $out = false;
  }

  if ($out === false) {
    global $SITENAME, $BASEURL, $enableattach_attachment;
    require_once('HTML/BBCodeParser.php');
    $filters = [];
    if ($enableimage) {
      $filters[] = 'Template';
      $filters[] = 'Span';
    }
    $filters = array_merge($filters, ['Extended', 'Basic', 'Email', 'Lists', 'Attachments', 'Refs', 'Smiles']);
    if ($enableimage) {
      $filters[] = 'Images';
    }
    if ($enableflash) {
      $filters[] = 'Flash';
    }
    $filters[] = 'Links';

    $text = htmlspecialchars($text, ENT_HTML401 | ENT_NOQUOTES);
    $text = str_replace("\r", "", $text);
    # There has to be a space before <br>
    # It has to be exactly '<br />', not '<br>' or something else
    $text = str_replace("\n", " <br />", $text); 

    $opts = array('filters' => $filters, 'imgMaxW' => $image_max_width, 'imgMaxH' => $image_max_height);
    if ($newtab) {
      $opts['aTarget'] = '_blank';
    }
    $parser = new HTML_BBCodeParser($opts);
    $out = '<div class="bbcode">' . $parser->qparse($text) . '</div>';
    $Cache->cache_value($key, $out, 86400 * 7);
  }

  if ($warnings || $cachekey) {
    require_once('classes/class_string.php');
    $out = new HBString($out);
    if ($warnings && isset($parser)) {
      $warnings = $parser->getWarnings();
      if ($warnings) {
	$out->warnings = $warnings;
      }
    }
    
    if ($cachekey) {
      $out->cachekey = $key;
    }
  }
  return $out;
}

function highlight($search,$subject,$hlstart='<b><font class="striking">',$hlend="</font></b>") {
  $srchlen=strlen($search);    // lenght of searched string
  if ($srchlen==0) return $subject;
  $find = $subject;
  while ($find = stristr($find,$search)) {    // find $search text in $subject -case insensitiv
    $srchtxt = substr($find,0,$srchlen);    // get new search text
    $find=substr($find,$srchlen);
    $subject = str_replace($srchtxt,"$hlstart$srchtxt$hlend",$subject);    // highlight founded case insensitive search text
  }
  return $subject;
}

function get_user_class() {
  global $CURUSER;
  return $CURUSER["class"];
}

function get_user_class_name($class, $compact = false, $b_colored = false, $I18N = false) {
  global $Cache, $CURLANGDIR;
  $key = 'user_class_name_' . $class . '_' . $compact . '_' . $b_colored . '_' . $I18N . '_' . $CURLANGDIR;
  return $Cache->get_value($key, 86400*365, function() use ($class, $compact, $b_colored, $I18N) {
      static $en_lang_functions;
      static $current_user_lang_functions;
      if (!$en_lang_functions) {
	require(get_langfile_path("functions.php",false,"en"));
	$en_lang_functions = $lang_functions;
      }

      if(!$I18N) {
	$this_lang_functions = $en_lang_functions;
      } else {
	if (!$current_user_lang_functions) {
	  require(get_langfile_path("functions.php"));
	  $current_user_lang_functions = $lang_functions;
	}
	$this_lang_functions = $current_user_lang_functions;
      }
      
      switch ($class) {
      case UC_PEASANT: {$class_name = $this_lang_functions['text_peasant']; break;}
      case UC_USER: {$class_name = $this_lang_functions['text_user']; break;}
      case UC_POWER_USER: {$class_name = $this_lang_functions['text_power_user']; break;}
      case UC_ELITE_USER: {$class_name = $this_lang_functions['text_elite_user']; break;}
      case UC_CRAZY_USER: {$class_name = $this_lang_functions['text_crazy_user']; break;}
      case UC_INSANE_USER: {$class_name = $this_lang_functions['text_insane_user']; break;}
      case UC_VETERAN_USER: {$class_name = $this_lang_functions['text_veteran_user']; break;}
      case UC_EXTREME_USER: {$class_name = $this_lang_functions['text_extreme_user']; break;}
      case UC_ULTIMATE_USER: {$class_name = $this_lang_functions['text_ultimate_user']; break;}
      case UC_NEXUS_MASTER: {$class_name = $this_lang_functions['text_nexus_master']; break;}
      case UC_VIP: {$class_name = $this_lang_functions['text_vip']; break;}
      case UC_UPLOADER: {$class_name = $this_lang_functions['text_uploader']; break;}
      case UC_RETIREE: {$class_name = $this_lang_functions['text_retiree']; break;}
      case UC_FORUM_MODERATOR: {$class_name = $this_lang_functions['text_forum_moderator']; break;}
      case UC_MODERATOR: {$class_name = $this_lang_functions['text_moderators']; break;}
      case UC_ADMINISTRATOR: {$class_name = $this_lang_functions['text_administrators']; break;}
      case UC_SYSOP: {$class_name = $this_lang_functions['text_sysops']; break;}
      case UC_STAFFLEADER: {$class_name = $this_lang_functions['text_staff_leader']; break;}
      default: {return ''; break;}
      }
      
      if ($compact) {
	$class_name = str_replace(" ", "",$class_name);
      }
      
      if ($b_colored) {
	switch ($class) {
	case UC_PEASANT: {$class_name_color = $en_lang_functions['text_peasant']; break;}
	case UC_USER: {$class_name_color = $en_lang_functions['text_user']; break;}
	case UC_POWER_USER: {$class_name_color = $en_lang_functions['text_power_user']; break;}
	case UC_ELITE_USER: {$class_name_color = $en_lang_functions['text_elite_user']; break;}
	case UC_CRAZY_USER: {$class_name_color = $en_lang_functions['text_crazy_user']; break;}
	case UC_INSANE_USER: {$class_name_color = $en_lang_functions['text_insane_user']; break;}
	case UC_VETERAN_USER: {$class_name_color = $en_lang_functions['text_veteran_user']; break;}
	case UC_EXTREME_USER: {$class_name_color = $en_lang_functions['text_extreme_user']; break;}
	case UC_ULTIMATE_USER: {$class_name_color = $en_lang_functions['text_ultimate_user']; break;}
	case UC_NEXUS_MASTER: {$class_name_color = $en_lang_functions['text_nexus_master']; break;}
	case UC_VIP: {$class_name_color = $en_lang_functions['text_vip']; break;}
	case UC_UPLOADER: {$class_name_color = $en_lang_functions['text_uploader']; break;}
	case UC_RETIREE: {$class_name_color = $en_lang_functions['text_retiree']; break;}
	case UC_FORUM_MODERATOR: {$class_name_color = $en_lang_functions['text_forum_moderator']; break;}
	case UC_MODERATOR: {$class_name_color = $en_lang_functions['text_moderators']; break;}
	case UC_ADMINISTRATOR: {$class_name_color = $en_lang_functions['text_administrators']; break;}
	case UC_SYSOP: {$class_name_color = $en_lang_functions['text_sysops']; break;}
	case UC_STAFFLEADER: {$class_name_color = $en_lang_functions['text_staff_leader']; break;}
	}
	$style_class = str_replace(" ", "",$class_name_color);
	$class_name = "<span class='" . $style_class . "_Name'>" . $class_name . '</span>';
      }
      
      return $class_name;
    });
}

function is_valid_user_class($class) {
  return is_numeric($class) && floor($class) == $class && $class >= UC_PEASANT && $class <= UC_STAFFLEADER;
}

function int_check($value,$stdhead = false, $stdfoot = true, $die = true, $log = true) {
  global $lang_functions;
  global $CURUSER;
  if (is_array($value))
    {
      foreach ($value as $val) int_check ($val);
    }
  else
    {
      if (!is_valid_id($value)) {
	$msg = "Invalid ID Attempt: Username: ".$CURUSER["username"]." - UserID: ".$CURUSER["id"]." - UserIP : ".getip();
	if ($log)
	  write_log($msg,'mod');

	if ($stdhead)
	  stderr($lang_functions['std_error'],$lang_functions['std_invalid_id']);
	else
	  {
	    print ("<h2>".$lang_functions['std_error']."</h2><table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\">");
	    print ($lang_functions['std_invalid_id']."</td></tr></table>");
	  }
	if ($stdfoot)
	  stdfoot();
	if ($die)
	  die;
      }
      else
	return true;
    }
}

function is_valid_id($id) {
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}


//-------- Begins a main frame
function begin_main_frame($caption = "", $center = false, $width = 100) {
  $tdextra = "";
  if ($caption)
    print('<h2 id="page-title">'.$caption."</h2>");

  if ($center)
    $tdextra .= 'text-align:center;';

  $width = $width;
  print('<div style="width:' . $width . '%;' . $tdextra . '">');

}

function end_main_frame() {
  print('</div>');
}

function begin_frame($caption = "", $center = false, $padding = 10, $width="100%", $caption_center="left", $table = false) {
  $caption = $caption ? '<h2 class="page-titles">'.$caption."</h2>" : "";
  if ($table) {
    echo $caption;
  }
  echo '<div class="frame';
  if ($table) {
    echo ' table td';
  }
  echo '">';
  if (!$table) {
    echo $caption;
  }
}

function end_frame() {
  print('</div>');
}

function begin_table($fullwidth = false, $padding = 5) {
  $width = "";

  if ($fullwidth)
    $width .= " width=50%";
  print("<table class=\"main".$width."\" border=\"1\" cellspacing=\"0\" cellpadding=\"".$padding."\">");
}

function end_table() {
  print("</table>\n");
}

FUNCTION insert_smilies_frame() {
  global $lang_functions;
  begin_frame($lang_functions['text_smilies'], true);
  begin_table(false, 5);
  print("<tr><td class=\"colhead\">".$lang_functions['col_type_something']."</td><td class=\"colhead\">".$lang_functions['col_to_make_a']."</td></tr>\n");
  for ($i=1; $i<192; $i++) {
    print("<tr><td>[em$i]</td><td><img src=\"pic/smilies/".$i.".gif\" alt=\"[em$i]\" /></td></tr>\n");
  }
  end_table();
  end_frame();
}

function get_ratio_color($ratio) {
  if ($ratio < 0.1) return "#ff0000";
  if ($ratio < 0.2) return "#ee0000";
  if ($ratio < 0.3) return "#dd0000";
  if ($ratio < 0.4) return "#cc0000";
  if ($ratio < 0.5) return "#bb0000";
  if ($ratio < 0.6) return "#aa0000";
  if ($ratio < 0.7) return "#990000";
  if ($ratio < 0.8) return "#880000";
  if ($ratio < 0.9) return "#770000";
  if ($ratio < 1) return "#660000";
  return "";
}

function get_slr_color($ratio) {
  if ($ratio < 0.025) return "#ff0000";
  if ($ratio < 0.05) return "#ee0000";
  if ($ratio < 0.075) return "#dd0000";
  if ($ratio < 0.1) return "#cc0000";
  if ($ratio < 0.125) return "#bb0000";
  if ($ratio < 0.15) return "#aa0000";
  if ($ratio < 0.175) return "#990000";
  if ($ratio < 0.2) return "#880000";
  if ($ratio < 0.225) return "#770000";
  if ($ratio < 0.25) return "#660000";
  if ($ratio < 0.275) return "#550000";
  if ($ratio < 0.3) return "#440000";
  if ($ratio < 0.325) return "#330000";
  if ($ratio < 0.35) return "#220000";
  if ($ratio < 0.375) return "#110000";
  return "";
}

function write_log($text, $security = "normal") {
  $text = sqlesc($text);
  $added = sqlesc(date("Y-m-d H:i:s"));
  $security = sqlesc($security);
  sql_query("INSERT INTO sitelog (added, txt, security_level) VALUES($added, $text, $security)") or sqlerr(__FILE__, __LINE__);
}

function write_forum_log($text, $security = "normal") {
  $text = sqlesc($text);
  $added = sqlesc(date("Y-m-d H:i:s"));
  $security = sqlesc($security);
  sql_query("INSERT INTO forumlog (added, txt, security_level) VALUES($added, $text, $security)") or sqlerr(__FILE__, __LINE__);
}

function get_elapsed_time($ts,$shortunit = false) {
  global $lang_functions;
  $mins = floor(abs(TIMENOW - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $months = floor($days / 30);
  $days2 = $days - $months * 30;
  $years = floor($days / 365);
  $months -= $years * 12;
  $t = "";
  if ($years > 0)
    return $years.($shortunit ? $lang_functions['text_short_year'] : $lang_functions['text_year'] . add_s($years)) ."&nbsp;".$months.($shortunit ? $lang_functions['text_short_month'] : $lang_functions['text_month'] . add_s($months));
  if ($months > 0)
    return $months.($shortunit ?  $lang_functions['text_short_month'] : $lang_functions['text_month'] . add_s($months)) ."&nbsp;".$days2.($shortunit ? $lang_functions['text_short_day'] : $lang_functions['text_day'] . add_s($days2));
  if ($days > 0)
    return $days.($shortunit ? $lang_functions['text_short_day'] : $lang_functions['text_day'] . add_s($days))."&nbsp;".$hours.($shortunit ? $lang_functions['text_short_hour'] : $lang_functions['text_hour'] . add_s($hours));
  if ($hours > 0)
    return $hours.($shortunit ? $lang_functions['text_short_hour'] : $lang_functions['text_hour'] . add_s($hours))."&nbsp;".$mins.($shortunit ? $lang_functions['text_short_min'] : $lang_functions['text_min'] . add_s($mins));
  if ($mins > 0)
    return $mins.($shortunit ? $lang_functions['text_short_min'] : $lang_functions['text_min'] . add_s($mins));
  return "&lt; 1".($shortunit ? $lang_functions['text_short_min'] : $lang_functions['text_min']);
}

function textbbcode($form,$text,$content="",$hastitle=false, $col_num = 130) {
  global $lang_functions;
  global $subject, $BASEURL, $CURUSER, $enableattach_attachment;
  $config = array('form' => $form, 'text' => $text);
  $s = smarty();
  $s->assign(array(
		   'config' => $config,
		   'attach' => $enableattach_attachment,
		   'smilies' => array(1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 16, 17, 19, 20, 21, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 39, 40, 41),
		   'content' => $content
		   ));
#  echo '<script type="text/javascript" src="js/userAutoTips.js"></script>';
#  echo '<link rel="js/userAutoTips.css" href="url" type="text/css" media="screen" />';
  $s->display('bbcode.tpl', json_encode(array('form' => $form, 'text' => $text)));
#  echo '<script type="text/javascript">userAutoTips({id:"body"});</script>';
}

function begin_compose($title = "",$type="new", $body="", $hassubject=true, $subject="", $maxsubjectlength=100) {
  global $lang_functions;
  if ($title)
    print('<h1>'.$title."</h1>");
  switch ($type){
  case 'new': {
    $framename = $lang_functions['text_new'];
    break;
  }
  case 'reply': {
    $framename = $lang_functions['text_reply'];
    break;
  }
  case 'quote': {
    $framename = $lang_functions['text_quote'];
    break;
  }
  case 'edit': {
    $framename = $lang_functions['text_edit'];
    break;
  }
  default: {
    $framename = $lang_functions['text_new'];
    break;
  }
  }
  begin_frame($framename, true);
  print("<table class=\"main\" width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
  if ($hassubject)
    print("<tr><td class=\"rowhead\">".$lang_functions['row_subject']."</td>" .
	  "<td class=\"rowfollow\" align=\"left\"><input type=\"text\" style=\"width: 650px;\" name=\"subject\" maxlength=\"".$maxsubjectlength."\" value=\"".$subject."\" /></td></tr>\n");
  print("<tr><td class=\"rowhead\" valign=\"top\">".$lang_functions['row_body']."</td><td class=\"rowfollow\" align=\"left\"><div style=\"display: none;\" id=\"previewouter\"></div><div id=\"editorouter\">");
  textbbcode("compose","body", $body, false);
  print("</div></td></tr>");
}

function end_compose() {
  global $lang_functions;
  print("<tr><td colspan=\"2\" align=\"center\"><div id=\"commit-btn\"><input id=\"qr\" type=\"submit\" class=\"btn\" value=\"".$lang_functions['submit_submit']."\" /></div>");
  print("</td></tr>");
  print("</table>\n");
  end_frame();
  print('<div class="minor-list list-seperator minor-nav">编辑帮助：<ul><li><a href="tags.php" target="_blank">'.$lang_functions['text_tags'].'</a></li><li><a href="smilies.php" target="_blank">'.$lang_functions['text_smilies'].'</a></li></ul></div>');
}

function insert_suggest($keyword, $userid) {
  if(mb_strlen($keyword,"UTF-8") >= 2)
    {
      $userid = 0 + $userid;
      if($userid)
	sql_query("INSERT INTO suggest(keywords, userid, adddate) VALUES (?,?,NOW())", [$keyword, $userid]);
    }
}

function get_external_tr($imdb_url = "", $dl = false) {
      global $lang_functions;
      global $showextinfo;
      $imdbNumber = parse_imdb_id($imdb_url);
      if ($showextinfo['imdb'] == 'yes') {
	if ($dl) {
	  dl_item($lang_functions['row_imdb_url'],  "<input type=\"text\" style=\"width: 650px;\" name=\"url\" value=\"".($imdbNumber ? "http://www.imdb.com/title/tt".parse_imdb_id($imdb_url) : "")."\" /><br /><font class=\"medium\">".$lang_functions['text_imdb_url_note']."</font>", 1);
	}
	else {
	  tr($lang_functions['row_imdb_url'],  "<input type=\"text\" style=\"width: 650px;\" name=\"url\" value=\"".($imdbNumber ? "http://www.imdb.com/title/tt".parse_imdb_id($imdb_url) : "")."\" /><br /><font class=\"medium\">".$lang_functions['text_imdb_url_note']."</font>", 1);
	}
      }
}

function get_torrent_extinfo_identifier($torrentid) {
  $torrentid = 0 + $torrentid;

  $result = array('imdb_id');
  unset($result);

  if($torrentid) {
    $res = sql_query("SELECT url FROM torrents WHERE id=" . $torrentid) or sqlerr(__FILE__,__LINE__);
    if(_mysql_num_rows($res) == 1)
      {
	$arr = _mysql_fetch_array($res) or sqlerr(__FILE__,__LINE__);

	$imdb_id = parse_imdb_id($arr["url"]);
	$result['imdb_id'] = $imdb_id;
      }
  }
  return $result;
}

function parse_imdb_id($url) {
  if ($url != "" && preg_match("/[0-9]{7}/i", $url, $matches)) {
    return $matches[0];
  } elseif ($url && is_numeric($url) && strlen($url) < 7) {
    return str_pad($url, 7, '0', STR_PAD_LEFT);
  } else {
    return false;
  }
}

function build_imdb_url($imdb_id) {
  return $imdb_id == "" ? "" : "http://www.imdb.com/title/tt" . $imdb_id . "/";
}

    // it's a stub implemetation here, we need more acurate regression analysis to complete our algorithm
function get_torrent_2_user_value($user_snatched_arr) {
  // check if it's current user's torrent
  $torrent_2_user_value = 1.0;

  $torrent_res = sql_query("SELECT * FROM torrents WHERE id = " . $user_snatched_arr['torrentid']) or sqlerr(__FILE__, __LINE__);
  if(_mysql_num_rows($torrent_res) == 1) { // torrent still exists
    $torrent_arr = _mysql_fetch_array($torrent_res) or sqlerr(__FILE__, __LINE__);
    if($torrent_arr['owner'] == $user_snatched_arr['userid']) { // owner's torrent
      $torrent_2_user_value *= 0.7;  // owner's torrent
      $torrent_2_user_value += ($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1 > 0 ? 0.2 - exp(-(($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1)) : ($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1;
      $torrent_2_user_value += min(0.1 , ($user_snatched_arr['seedtime'] / 37*60*60 ) * 0.1);
    }
    else {
      if ($user_snatched_arr['finished'] == 'yes') {
	$torrent_2_user_value *= 0.5;
	$torrent_2_user_value += ($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1 > 0 ? 0.4 - exp(-(($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1)) : ($user_snatched_arr['uploaded'] / $torrent_arr['size'] ) -1;
	$torrent_2_user_value += min(0.1, ($user_snatched_arr['seedtime'] / 22*60*60 ) * 0.1);
      }
      else {
	$torrent_2_user_value *= 0.2;
	$torrent_2_user_value += min(0.05, ($user_snatched_arr['leechtime'] / 24*60*60 ) * 0.1);  // usually leechtime could not explain much
      }
    }
  }
  else { // torrent already deleted, half blind guess, be conservative
    if($user_snatched_arr['finished'] == 'no' && $user_snatched_arr['uploaded'] > 0 && $user_snatched_arr['downloaded'] == 0) { // possibly owner
      $torrent_2_user_value *= 0.55;  //conservative
      $torrent_2_user_value += min(0.05, ($user_snatched_arr['leechtime'] / 31*60*60 ) * 0.1);
      $torrent_2_user_value += min(0.1, ($user_snatched_arr['seedtime'] / 31*60*60 ) * 0.1);
    }
    else if($user_snatched_arr['downloaded'] > 0) { // possibly leecher
      $torrent_2_user_value *= 0.38;  //conservative
      $torrent_2_user_value *= min(0.22, 0.1 * $user_snatched_arr['uploaded'] / $user_snatched_arr['downloaded']);  // 0.3 for conservative
      $torrent_2_user_value += min(0.05, ($user_snatched_arr['leechtime'] / 22*60*60 ) * 0.1);
      $torrent_2_user_value += min(0.12, ($user_snatched_arr['seedtime'] / 22*60*60 ) * 0.1);
    }
    else {
      $torrent_2_user_value *= 0.0;
    }
  }
  return $torrent_2_user_value;
}

function cur_user_check ($redir = '') {
  global $lang_functions;
  global $CURUSER;
  global $securelogin;
  if ($securelogin == "op-yes" || $securelogin == 'yes') {
    // nginx, fastcgi_param HTTPS $https;
    if (!(isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on")) {
      header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
    }
  }
  
  if ($CURUSER) {
    if ($redir) {
      header("Location: $redir");
      exit(0);
    }
    else {
      update_user($CURUSER['id'], 'lang=?', [get_langid_from_langcookie()]);
      stderr ($lang_functions['std_permission_denied'], $lang_functions['std_already_logged_in']);
    }  
  }
}

function KPS($type, $point, $id) {
  global $bonus_tweak;
  if ($point != 0){
    if ($bonus_tweak == "enable" || $bonus_tweak == "disablesave"){
      update_user($id, "seedbonus = seedbonus $type ?", [$point]);
    }
  }
  else return;
}

function get_agent($peer_id, $agent) {
      return substr($agent, 0, (strpos($agent, ";") == false ? strlen($agent) : strpos($agent, ";")));
    }

function EmailBanned($newEmail) {
  $newEmail = trim(strtolower($newEmail));
  $sql = sql_query("SELECT * FROM bannedemails") or sqlerr(__FILE__, __LINE__);
  $list = _mysql_fetch_array($sql);
  $addresses = explode(' ', preg_replace("/[[:space:]]+/", " ", trim($list['value'])) );

  if(count($addresses) > 0) {
    foreach ( $addresses as $email) {
      $email = trim(strtolower(preg_replace('/\./', '\\.', $email)));
      if(strstr($email, "@")) {
	if(preg_match('/^@/', $email))
	  {// Any user @host?
	    // Expand the match expression to catch hosts and
	    // sub-domains
	    $email = preg_replace('/^@/', '[@\\.]', $email);
	    if(preg_match("/".$email."$/", $newEmail))
	      return true;
	  }
      }
      elseif(preg_match('/@$/', $email)) {    // User at any host?
	if(preg_match("/^".$email."/", $newEmail))
	  return true;
      }
      else {                // User@host
	if(strtolower($email) == $newEmail)
	  return true;
      }
    }
  }

  return false;
}

function EmailAllowed($newEmail) {
  global $restrictemaildomain;
  if ($restrictemaildomain == 'yes') {
    $newEmail = trim(strtolower($newEmail));
    $sql = sql_query("SELECT * FROM allowedemails") or sqlerr(__FILE__, __LINE__);
    $list = _mysql_fetch_array($sql);
    $addresses = explode(' ', preg_replace("/[[:space:]]+/", " ", trim($list[value])) );

    if (count($addresses) > 0) {
      foreach ( $addresses as $email ) {
	$email = trim(strtolower(preg_replace('/\./', '\\.', $email)));
	if(strstr($email, "@")) {
	  if(preg_match('/^@/', $email))
	    {// Any user @host?
	      // Expand the match expression to catch hosts and
	      // sub-domains
	      $email = preg_replace('/^@/', '[@\\.]', $email);
	      if(preg_match('/'.$email.'$/', $newEmail))
		return true;
	    }
	}
	elseif(preg_match('/@$/', $email)) {    // User at any host?
	  if(preg_match("/^".$email."/", $newEmail))
	    return true;
	}
	else {                // User@host
	  if(strtolower($email) == $newEmail)
	    return true;
	}
      }
    }
    return false;
  }
  else return true;
}

function allowedemails() {
  $sql = sql_query("SELECT * FROM allowedemails") or sqlerr(__FILE__, __LINE__);
  $list = _mysql_fetch_array($sql);
  return $list['value'];
}

function redirect($url) {
  if(!headers_sent()){
    header("Location : $url");
  }
  else
    echo "<script type=\"text/javascript\">window.location.href = '$url';</script>";
  exit;
}

function set_cachetimestamp($id, $field = "cache_stamp") {
  sql_query("UPDATE torrents SET $field = " . time() . " WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
}

function reset_cachetimestamp($id, $field = "cache_stamp") {
  sql_query("UPDATE torrents SET $field = 0 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
}

function get_email_encode($lang) {
  if($lang == 'chs' || $lang == 'cht')
    return "gbk";
  else
    return "utf-8";
}

function change_email_encode($lang, $content) {
  return iconv("utf-8", get_email_encode($lang) . "//IGNORE", $content);
}

function safe_email($email) {
  $email = str_replace("<","",$email);
  $email = str_replace(">","",$email);
  $email = str_replace("\'","",$email);
  $email = str_replace('\"',"",$email);
  $email = str_replace("\\\\","",$email);

  return $email;
}

function check_email ($email) {
  if(preg_match('/^[A-Za-z0-9][A-Za-z0-9_.+\-]*@[A-Za-z0-9][A-Za-z0-9_+\-]*(\.[A-Za-z0-9][A-Za-z0-9_+\-]*)+$/', $email))
    return true;
  else
    return false;
}

function sent_mail_core($to,$fromname,$fromemail,$subject,$body,$type = "confirmation",$showmsg=true,$multiple=false,$multiplemail='',$hdr_encoding = 'UTF-8', $specialcase = '') {
  global $lang_functions;
  global $rootpath,$SITENAME,$SITEEMAIL,$smtptype,$smtp,$smtp_host,$smtp_port,$smtp_from,$smtpaddress,$smtpport,$accountname,$accountpassword;
  # Is the OS Windows or Mac or Linux?
  if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
    $eol="\r\n";
    $windows = true;
  }
  elseif (strtoupper(substr(PHP_OS,0,3)=='MAC'))
    $eol="\r";
  else
    $eol="\n";
  if ($smtptype == 'none')
    return false;
  if ($smtptype == 'default') {
    if (! @mail($to, "=?".$hdr_encoding."?B?".base64_encode($subject)."?=", $body, "From: ".$SITEEMAIL.$eol."Content-type: text/html; charset=".$hdr_encoding.$eol, "-f$SITEEMAIL")) {
      throw new Exception('Can\'t send mail');
    }
  }
  elseif ($smtptype == 'advanced') {
    $mid = md5(getip() . $fromname);
    $name = $_SERVER["SERVER_NAME"];
    $headers .= "From: $fromname <$fromemail>".$eol;
    $headers .= "Reply-To: $fromname <$fromemail>".$eol;
    $headers .= "Return-Path: $fromname <$fromemail>".$eol;
    $headers .= "Message-ID: <$mid thesystem@$name>".$eol;
    $headers .= "X-Mailer: PHP v".phpversion().$eol;
    $headers .= "MIME-Version: 1.0".$eol;
    $headers .= "Content-type: text/html; charset=".$hdr_encoding.$eol;
    $headers .= "X-Sender: PHP".$eol;
    if ($multiple)
      {
	$bcc_multiplemail = "";
	foreach ($multiplemail as $toemail)
	  $bcc_multiplemail = $bcc_multiplemail . ( $bcc_multiplemail != "" ? "," : "") . $toemail;

	$headers .= "Bcc: $multiplemail.$eol";
      }
    if ($smtp == "yes") {
      ini_set('SMTP', $smtp_host);
      ini_set('smtp_port', $smtp_port);
      if ($windows)
	ini_set('sendmail_from', $smtp_from);
    }

    if (!@mail($to,"=?".$hdr_encoding."?B?".base64_encode($subject)."?=",$body,$headers)) {
	throw new Exception('Can\'t send mail');
      }

    ini_restore(SMTP);
    ini_restore(smtp_port);
    if ($windows)
      ini_restore(sendmail_from);
  }
  elseif ($smtptype == 'external') {
    require_once ($rootpath . 'include/smtp/smtp.lib.php');
    $mail = new smtp($hdr_encoding,'eYou');

    $mail->debug(false);
    $mail->open($smtpaddress, $smtpport);
    $mail->auth($accountname, $accountpassword);
    //  $mail->bcc($multiplemail);
    $mail->from($SITEEMAIL);
    if ($multiple)
      {
	$mail->multi_to_head($to);
	foreach ($multiplemail as $toemail)
	  $mail->multi_to($toemail);
      }
    else
      $mail->to($to);
    $mail->mime_content_transfer_encoding();
    $mail->mime_charset('text/html', $hdr_encoding);
    $mail->subject($subject);
    $mail->body($body);
    if (!$mail->send()) {
      $mail->close();
      throw new Exception('Can\'t send mail');
    }
    $mail->close();
  }
  if ($showmsg) {
    if ($type == "confirmation")
      stderr($lang_functions['std_success'], $lang_functions['std_confirmation_email_sent']."<b>". htmlspecialchars($to) ."</b>.\n" .
	     $lang_functions['std_please_wait'],false);
    elseif ($type == "details")
      stderr($lang_functions['std_success'], $lang_functions['std_account_details_sent']."<b>". htmlspecialchars($to) ."</b>.\n" .
	     $lang_functions['std_please_wait'],false);
  }else
    return true;
}

function sent_mail($to,$fromname,$fromemail,$subject,$body,$type = "confirmation",$showmsg=true,$multiple=false,$multiplemail='',$hdr_encoding = 'UTF-8', $specialcase = '') {
  try {
    sent_mail_core($to,$fromname,$fromemail,$subject,$body,$type,$showmsg,$multiple,$multiplemail,$hdr_encoding, $specialcase);
  } catch (Exception $e) {
    global $lang_functions;
    stderr($lang_functions['std_error'], $lang_functions['text_unable_to_send_mail']);
  }
}

function failedloginscheck ($type = 'Login') {
  global $lang_functions;
  global $maxloginattempts;
  $ip = getip();
  $total = get_row_sum('loginattempts', 'attempts', 'WHERE ip=?', [$ip]);
  if ($total >= $maxloginattempts) {
    sql_query("UPDATE loginattempts SET banned = 'yes' WHERE ip=?", [$ip]);
    stderr($type.$lang_functions['std_locked'].$type.$lang_functions['std_attempts_reached'], $lang_functions['std_your_ip_banned']);
  }
  else {
    return $total;
  }
}

function failedlogins ($type = 'login', $recover = false, $head = true) {
  global $lang_functions;
  $ip = getip();
  $added = date("Y-m-d H:i:s");
  $a = get_row_count('loginattempts', 'WHERE ip=?', [$ip]);
  if ($a == 0)
    sql_query("INSERT INTO loginattempts (ip, added, attempts) VALUES (?, ?, 1)", [$ip, $added]);
  else {
    $sql = "UPDATE loginattempts SET attempts = attempts + 1";
    if ($recover) {
      $sql .= ",type = 'recover'";
    }
    $sql .= " where ip=?";
    sql_query($sql, [$ip]);
  }

  if ($type == 'silent')
    return;
  elseif ($type == 'login') {
    stderr($lang_functions['std_login_failed'],$lang_functions['std_login_failed_note'],false);
  }
  else {
    stderr($lang_functions['std_failed'],$type,false, $head);
  }
}

function login_failedlogins($type = 'login', $recover = false, $head = true) {
  global $lang_functions;
  $ip = sqlesc(getip());
  $added = sqlesc(date("Y-m-d H:i:s"));
  $a = (@_mysql_fetch_row(@sql_query("select count(*) from loginattempts where ip=$ip"))) or sqlerr(__FILE__, __LINE__);
  if ($a[0] == 0)
    sql_query("INSERT INTO loginattempts (ip, added, attempts) VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
  else
    sql_query("UPDATE loginattempts SET attempts = attempts + 1 where ip=$ip") or sqlerr(__FILE__, __LINE__);
  if ($recover)
    sql_query("UPDATE loginattempts SET type = 'recover' WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);
  if ($type == 'silent')
    return;
  elseif ($type == 'login')
    {
      stderr($lang_functions['std_login_failed'],$lang_functions['std_login_failed_note'],false);
    }
  else
    stderr($lang_functions['std_recover_failed'],$type,false, $head);
}

function remaining ($type = 'login') {
  global $maxloginattempts;
  $total = get_row_sum('loginattempts', 'attempts', 'WHERE ip=?', [getip()]);
  $remaining = $maxloginattempts - $total;
  if ($remaining <= 2 )
    $remaining = "<font color=\"red\" size=\"2\">[".$remaining."]</font>";
  else
    $remaining = "<font color=\"green\" size=\"2\">[".$remaining."]</font>";

  return $remaining;
}

function registration_check($type = "invitesystem", $maxuserscheck = true, $ipcheck = true) {
  global $lang_functions;
  global $invitesystem, $registration, $maxusers, $SITENAME, $maxip;
  if ($type == "invitesystem") {
    if ($invitesystem == "no") {
      stderr($lang_functions['std_oops'], $lang_functions['std_invite_system_disabled'], 0);
    }
  }

  if ($type == "normal") {
    if ($registration == "no") {
      stderr($lang_functions['std_sorry'], $lang_functions['std_open_registration_disabled'], 0);
    }
  }

  if ($maxuserscheck) {
    $res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = _mysql_fetch_row($res);
    if ($arr[0] >= $maxusers)
      stderr($lang_functions['std_sorry'], $lang_functions['std_account_limit_reached'], 0);
  }

  if ($ipcheck) {
    $ip = getip () ;
    if (get_row_count('users', 'where ip=?', [$ip]) > $maxip)
      stderr($lang_functions['std_sorry'], $lang_functions['std_the_ip']."<b>" . htmlspecialchars($ip) ."</b>". $lang_functions['std_used_many_times'],false);
  }
  return true;
}

function random_str($length="6") {
  $set = array("A","B","C","D","E","F","G","H","P","R","M","N","1","2","3","4","5","6","7","8","9");
  $str = '';
  for($i=1;$i<=$length;$i++)
    {
      $ch = rand(0, count($set)-1);
      $str .= $set[$ch];
    }
  return $str;
}
function image_code () {
  $randomstr = random_str();
  $imagehash = md5($randomstr);
  $dateline = time();
  $sql = 'INSERT INTO `regimages` (`imagehash`, `imagestring`, `dateline`) VALUES (\''.$imagehash.'\', \''.$randomstr.'\', \''.$dateline.'\');';
  sql_query($sql) or die(_mysql_error());
  return $imagehash;
}

function check_code ($imagehash, $imagestring, $where = 'signup.php',$maxattemptlog=false,$head=true) {
  global $lang_functions;
  $sql = sql_query("SELECT * FROM regimages WHERE imagehash=? AND imagestring=?", [$imagehash, $imagestring]);
  $imgcheck = _mysql_fetch_array($sql);
  if(!$imgcheck['dateline']) {
    sql_query("DELETE FROM regimages WHERE imagehash=?", [$imagehash]);
    if (!$maxattemptlog)
      bark($lang_functions['std_invalid_image_code']."<a href=\"".htmlspecialchars($where)."\">".$lang_functions['std_here_to_request_new']);
    else
      failedlogins($lang_functions['std_invalid_image_code']."<a href=\"".htmlspecialchars($where)."\">".$lang_functions['std_here_to_request_new'],true,$head);
  }else{
    sql_query('DELETE FROM regimages WHERE imagehash=?', [$imagehash]);
    return true;
  }
}
function show_image_code () {
  global $lang_functions;
  global $iv;
  if ($iv == "yes") {
    unset($imagehash);
    $imagehash = image_code () ;
    print ("<tr><td class=\"rowhead\">".$lang_functions['row_security_image']."</td>");
    print ("<td align=\"left\"><img src=\"".htmlspecialchars("image.php?action=regimage&imagehash=".$imagehash)."\" border=\"0\" alt=\"CAPTCHA\" /></td></tr>");
    print ("<tr><td class=\"rowhead\">".$lang_functions['row_security_code']."</td><td align=\"left\">");
    print("<input type=\"text\" autocomplete=\"off\" style=\"width: 180px; border: 1px solid gray\" name=\"imagestring\" value=\"\" />");
    print("<input type=\"hidden\" name=\"imagehash\" value=\"$imagehash\" /></td></tr>");
  }
}

function get_ip_location($ip) {
  global $lang_functions;
  global $Cache;
  if (!$ret = $Cache->get_value('location_list')){
    $ret = array();
    $res = sql_query("SELECT * FROM locations") or sqlerr(__FILE__, __LINE__);
    while ($row = _mysql_fetch_array($res))
      $ret[] = $row;
    $Cache->cache_value('location_list', $ret, 152800);
  }
  $location = array($lang_functions['text_unknown'],"");

  foreach($ret AS $arr) {
    if(in_ip_range(false, $ip, $arr["start_ip"], $arr["end_ip"])) {
      $location = array($arr["name"], $lang_functions['text_user_ip'].":&nbsp;" . $ip . ($arr["location_main"] != "" ? "&nbsp;".$lang_functions['text_location_main'].":&nbsp;" . $arr["location_main"] : ""). ($arr["location_sub"] != "" ? "&nbsp;".$lang_functions['text_location_sub'].":&nbsp;" . $arr["location_sub"] : "") . "&nbsp;".$lang_functions['text_ip_range'].":&nbsp;" . $arr["start_ip"] . "&nbsp;~&nbsp;". $arr["end_ip"]);
      break;
    }
  }
  return $location;
}

function in_ip_range($long, $targetip, $ip_one, $ip_two=false) {
  // if only one ip, check if is this ip
  if ($ip_two===false) {
    if (($long ? (long2ip($ip_one) == $targetip) : ( $ip_one == $targetip))) {
      $ip=true;
    }
    else{
      $ip=false;
    }
  }
  else{
    if ($long ? ($ip_one<=ip2long($targetip) && $ip_two>=ip2long($targetip)) : (ip2long($ip_one)<=ip2long($targetip) && ip2long($ip_two)>=ip2long($targetip))) {
      $ip=true;
    }
    else{
      $ip=false;
    }
  }
  return $ip;
}


function validip_format($ip) {
  $ipPattern =
    '/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
    '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
    '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
    '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';

  return preg_match($ipPattern, $ip);
}

function maxslots () {
  global $lang_functions;
  global $CURUSER, $maxdlsystem;
  $downloaded = $CURUSER["downloaded"];
  $gigs = $downloaded / (1024*1024*1024);
  $ratio = (($downloaded > 0) ? ($CURUSER["uploaded"] / $downloaded) : 1);
  $max = get_maxslots($gigs, $ratio);
  if ($maxdlsystem == "yes") {
    if (get_user_class() < UC_VIP) {
      if ($max > 0)
	print ("<span class='color_slots'>".$lang_functions['text_slots']."</span><a href=\"faq.php#id66\">$max</a>");
      else
	print ("<span class='color_slots'>".$lang_functions['text_slots']."</span>".$lang_functions['text_unlimited']);
    }else
      print ("<span class='color_slots'>".$lang_functions['text_slots']."</span>".$lang_functions['text_unlimited']);
  }else
    print ("<span class='color_slots'>".$lang_functions['text_slots']."</span>".$lang_functions['text_unlimited']);
}

function WriteConfig ($configname = NULL, $config = NULL) {
  global $lang_functions, $CONFIGURATIONS;

  if (file_exists('config/allconfig.php')) {
    require('config/allconfig.php');
  }
  if ($configname) {
    $$configname=$config;
  }
  $path = './config/allconfig.php';
  if (!file_exists($path) || !is_writable ($path)) {
    stdmsg($lang_functions['std_error'], $lang_functions['std_cannot_read_file']."[<b>".htmlspecialchars($path)."</b>]".$lang_functions['std_access_permission_note']);
  }
  $data = "<?php\n";
  foreach ($CONFIGURATIONS as $CONFIGURATION) {
    $data .= "\$$CONFIGURATION=".getExportedValue($$CONFIGURATION).";\n";
  }
  $fp = @fopen ($path, 'w');
  if (!$fp) {
    stdmsg($lang_functions['std_error'], $lang_functions['std_cannot_open_file']."[<b>".htmlspecialchars($path)."</b>]".$lang_functions['std_to_save_info'].$lang_functions['std_access_permission_note']);
  }
  $Res = @fwrite($fp, $data);
  if (empty($Res)) {
    stdmsg($lang_functions['std_error'], $lang_functions['text_cannot_save_info_in']."[<b>".htmlspecialchars($path)."</b>]".$lang_functions['std_access_permission_note']);
  }
  fclose($fp);
  return true;
}

function getExportedValue($input,$t = null) {
  switch (gettype($input)) {
  case 'string':
    return "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'";
  case 'array':
    $output = "array(\r";
    foreach ($input as $key => $value) {
      $output .= $t."\t".getExportedValue($key,$t."\t").' => '.getExportedValue($value,$t."\t");
      $output .= ",\n";
    }
    $output .= $t.')';
    return $output;
  case 'boolean':
    return $input ? 'true' : 'false';
  case 'NULL':
    return 'NULL';
  case 'integer':
  case 'double':
  case 'float':
    return "'".(string)$input."'";
  }
  return 'NULL';
}

function dbconn($autoclean = false, $test = false) {
  global $useCronTriggerCleanUp;

  userlogin();

  if (!$useCronTriggerCleanUp && $autoclean) {
    register_shutdown_function("autoclean");
  }
}

function cache_user_row() {
  global $Cache, $CURUSER;
  $id = $CURUSER['id'];
  $key = 'user_' . $id . '_content';

  $Cache->cache_value($key, $CURUSER, 900);
}

function get_user_row($id = null) {
  global $Cache;

  $key = 'user_' . $id . '_content';
  $row = $Cache->get_value($key);
  if ($row === false) {
    $row = sql_query("SELECT * FROM users WHERE users.id = ? LIMIT 1", [$id])->fetch();
    $Cache->cache_value($key, $row, 900);
  }
  if (!$row) {
    return null;
  }
  return $row;
}

function userlogin_core($cookie) {
  if (empty($cookie["c_secure_pass"]) || empty($cookie["c_secure_uid"]) || empty($cookie["c_secure_login"])) {
    return null;
  }

  $b_id = base64($cookie["c_secure_uid"],false);
  $id = 0 + $b_id;
  if (!$id || !is_valid_id($id) || strlen($cookie["c_secure_pass"]) != 32) {
    return null;
  }

  $row = get_user_row($id);
  if (!$row || $row['enabled'] != 'yes' || $row['status'] != 'confirmed') {
    return null;
  }

  $sec = hash_pad($row["secret"]);
  if ($cookie["c_secure_login"] == base64("yeah")) { //Check IP
    if ($cookie["c_secure_pass"] != md5($row["passhash"].$_SERVER["REMOTE_ADDR"])) {
      return null;
    }
  }
  else {
    if ($cookie["c_secure_pass"] !== md5($row["passhash"])) {
      return null;
    }
  }

  if (!$row["passkey"]){
    $passkey = md5($row['username'].date("Y-m-d H:i:s").$row['passhash']);
    update_user($row['id'], 'passkey = ?', [$passkey]);
  }

  return $row;
}

function userlogin() {
  global $Cache;
  global $oldip, $iplog1, $USERUPDATESET;
  global $enablesqldebug_tweak, $sqldebug_tweak;
  unset($GLOBALS["CURUSER"]);

  $ip = getip();
  $nip = ip2long($ip);
  if ($nip) { //$nip would be false for IPv6 address
    $key = 'banned_ip_' . $nip;
    $count = $Cache->get_value($key);
    if ($count === false) {
      $count = sql_query("SELECT * FROM bans WHERE ? >= first AND ? <= last", [$nip, $nip])->rowCount();
      $Cache->cache_value($key, $count, 365 * 86400);
    }
    if ($count > 0) {
      throw new Exception($lang_functions['text_unauthorized_ip'], 403);
    }
  }
  
  try {
    $row = userlogin_core($_COOKIE);

  } catch (Exception $e) {
    header("HTTP/1.0 " . $e->getCode() . " Forbidden");
    print("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>".$e->getMessage()."</body></html>\n");
    die;
  }

  if (is_null($row)) {
    return;
  }

  $key = 'user_ip_'.$row['id'];
  $oldip = $Cache->get_value($key);
  if ($oldip === false) {
    $oldip = $row['ip'];
  }

  if ($oldip != $ip) {
    $Cache->cache_value($key, $ip, 900);
    $row['ip'] = $ip;

  //Insert old ip into iplog
    $USERUPDATESET[] = "ip = ".sqlesc($row['ip']);
    if ($iplog1 == "yes" && $oldip) {
      sql_query("INSERT INTO iplog (ip, userid, access) VALUES (?, ?, ?)", [$oldip, $row['id'], $row['last_access']]);
    }
  }
  $USERUPDATESET[] = "last_access = ".sqlesc(date("Y-m-d H:i:s"));
  
  $usergroups = get_user_group($row['id']);
  if ($usergroups !=NULL) {
    $row['usergroups'] = $usergroups;
  }

  //initialize global $CURUSER which will be used frequently in other operations
  //noted by bluemonster 20111107
  $GLOBALS["CURUSER"] = $row;

  if (array_key_exists('purge', $_GET) && $_GET['purge'] && get_user_class() >= UC_MODERATOR) {
    $Cache->setClearCache(1);
  }
  if ($enablesqldebug_tweak == 'yes' && get_user_class() >= $sqldebug_tweak) {
    error_reporting(E_ALL);
  }
}

function autoclean() {
  global $autoclean_interval_one, $rootpath;
  global $lang_cleanup_target;
  $now = TIMENOW;

  $res = sql_query("SELECT value_u FROM avps WHERE arg = 'lastcleantime'");
  $row = _mysql_fetch_array($res);
  if (!$row) {
    sql_query("INSERT INTO avps (arg, value_u) VALUES ('lastcleantime',$now)") or sqlerr(__FILE__, __LINE__);
    return false;
  }
  $ts = $row[0];
  if ($ts + $autoclean_interval_one > $now) {
    return false;
  }
  sql_query("UPDATE avps SET value_u=$now WHERE arg='lastcleantime' AND value_u = $ts") or sqlerr(__FILE__, __LINE__);
  if (!_mysql_affected_rows()) {
    return false;
  }

  /* if (php_sapi_name() == 'fpm-fcgi') { */
  /*   fastcgi_finish_request(); */
  /* } */
  require_once($rootpath . 'include/cleanup.php');
  return docleanup();
}

function unesc($x) {
  return $x;
}


function getsize_int($amount, $unit = "G") {
  if ($unit == "B")
    return floor($amount);
  elseif ($unit == "K")
    return floor($amount * 1024);
  elseif ($unit == "M")
    return floor($amount * 1048576);
  elseif ($unit == "G")
    return floor($amount * 1073741824);
  elseif($unit == "T")
    return floor($amount * 1099511627776);
  elseif($unit == "P")
    return floor($amount * 1125899906842624);
}

function mksize_compact($bytes) {
  if ($bytes < 1000 * 1024)
    return number_format($bytes / 1024, 2) . "<br />KiB";
  elseif ($bytes < 1000 * 1048576)
    return number_format($bytes / 1048576, 2) . "<br />MiB";
  elseif ($bytes < 1000 * 1073741824)
    return number_format($bytes / 1073741824, 2) . "<br />GiB";
  elseif ($bytes < 1000 * 1099511627776)
    return number_format($bytes / 1099511627776, 3) . "<br />TiB";
  else
    return number_format($bytes / 1125899906842624, 3) . "<br />PiB";
}

function mksize_loose($bytes) {
      if ($bytes < 1000 * 1024)
	return number_format($bytes / 1024, 2) . "&nbsp;KiB";
      elseif ($bytes < 1000 * 1048576)
	return number_format($bytes / 1048576, 2) . "&nbsp;MiB";
      elseif ($bytes < 1000 * 1073741824)
	return number_format($bytes / 1073741824, 2) . "&nbsp;GiB";
      elseif ($bytes < 1000 * 1099511627776)
	return number_format($bytes / 1099511627776, 3) . "&nbsp;TiB";
      else
	return number_format($bytes / 1125899906842624, 3) . "&nbsp;PiB";
}

function mksize($bytes) {
  if ($bytes < 1000 * 1024)
    return number_format($bytes / 1024, 2) . " KiB";
  elseif ($bytes < 1000 * 1048576)
    return number_format($bytes / 1048576, 2) . " MiB";
  elseif ($bytes < 1000 * 1073741824)
    return number_format($bytes / 1073741824, 2) . " GiB";
  elseif ($bytes < 1000 * 1099511627776)
    return number_format($bytes / 1099511627776, 3) . " TiB";
  else
    return number_format($bytes / 1125899906842624, 3) . " PiB";
}


function mksizeint($bytes) {
  $bytes = max(0, $bytes);
  if ($bytes < 1000)
    return floor($bytes) . " B";
  elseif ($bytes < 1000 * 1024)
    return floor($bytes / 1024) . " KiB";
  elseif ($bytes < 1000 * 1048576)
    return floor($bytes / 1048576) . " MiB";
  elseif ($bytes < 1000 * 1073741824)
    return floor($bytes / 1073741824) . " GiB";
  elseif ($bytes < 1000 * 1099511627776)
    return floor($bytes / 1099511627776) . " TiB";
  else
    return floor($bytes / 1125899906842624) . " PiB";
}

function deadtime() {
  global $anninterthree;
  return time() - floor($anninterthree * 1.1);
}

function mkprettytime($s) {
  global $lang_functions;
  if ($s < 0)
    $s = 0;
  $t = array();
  foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
    $y = explode(":", $x);
    if ($y[0] > 1) {
      $v = $s % $y[0];
      $s = floor($s / $y[0]);
    }
    else
      $v = $s;
    $t[$y[1]] = $v;
  }

  if ($t["day"])
    return $t["day"] . $lang_functions['text_day'] . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
  if ($t["hour"])
    return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
  //    if ($t["min"])
  return sprintf("%d:%02d", $t["min"], $t["sec"]);
  //    return $t["sec"] . " secs";
}

function mkglobal($vars) {
  if (!is_array($vars))
    $vars = explode(":", $vars);
  foreach ($vars as $v) {
    if (isset($_REQUEST[$v])) {
      $GLOBALS[$v] = unesc($_REQUEST[$v]);
    }
    else {
      return 0;
    }
  }
  return 1;
}

function tr($x,$y,$noesc=0,$relation='') {
  if ($noesc)
    $a = $y;
  else {
    $a = htmlspecialchars($y);
    $a = str_replace("\n", "<br />\n", $a);
  }
  print("<tr".( $relation ? " relation = \"$relation\"" : "")."><td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">$x</td><td class=\"rowfollow\" valign=\"top\" align=\"left\">".$a."</td></tr>\n");
}

function tr_small($x,$y,$noesc=0,$relation='') {
  if ($noesc)
    $a = $y;
  else {
    $a = htmlspecialchars($y);
    //$a = str_replace("\n", "<br />\n", $a);
  }
  print("<tr".( $relation ? " relation = \"$relation\"" : "")."><td width=\"1%\" class=\"rowhead nowrap\" valign=\"top\" align=\"right\">".$x."</td><td width=\"99%\" class=\"rowfollow\" valign=\"top\" align=\"left\">".$a."</td></tr>\n");
}

function dl_item($title, $desc, $noesc = false, $class = '', $id = '') {
  static $first = true;
  if (!$noesc) {
    $desc = htmlspecialchars($desc);
    //$a = str_replace("\n", "<br />\n", $a);
  }
  echo '<dt';
  if ($class) {
    echo ' class="' . $class . '"';
  }
  if ($id) {
    echo ' id="' . $id . '"';
  }
  echo '>' . $title . '</dt><dd';
  if ($first) {
    $first = false;
    echo ' class="first-child"';
  }
  echo '>' . $desc .  '</dd>';
}

function twotd($x,$y,$noesc=0) {
  if ($noesc)
    $a = $y;
  else {
    $a = htmlspecialchars($y);
    $a = str_replace("\n", "<br />\n", $a);
  }
  print("<td class=\"rowhead\">".$x."</td><td class=\"rowfollow\">".$y."</td>");
}

function validfilename($name) {
  return preg_match('/^[^\0-\x1f:\\\\\/?*#<>|]+$/si', $name);
# Because of unknown reason  
#  return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name); 
}

function validemail($email) {
  return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function validlang($langid) {
  global $deflang;
  $langid = 0 + $langid;
  $res = sql_query("SELECT site_lang_folder FROM language WHERE site_lang = 1 AND id = " . sqlesc($langid)) or sqlerr(__FILE__, __LINE__);
  if(_mysql_num_rows($res) == 1)
    {
      $arr = _mysql_fetch_array($res)  or sqlerr(__FILE__, __LINE__);
      return $arr['site_lang_folder'];
    }
  else return $deflang;
}

function get_if_restricted_is_open()
{
  global $sptime; 
  // it's sunday
  if($sptime == 'yes' && (date("w",time()) == '0' || (date("w",time()) == 6) && (date("G",time()) >=12 && date("G",time()) <=23)))
    {
      return true;
    }
  else
    return false;
}

function menu ($selected = "home") {
  global $lang_functions;
  global $BASEURL,$CURUSER;
  global $enableoffer, $enablerequest, $enablespecial, $enableextforum, $extforumurl;
  $script_name = $_SERVER["SCRIPT_FILENAME"];

  if (preg_match("/index/i", $script_name) && ! preg_match('/cake/i', $script_name)) {
    $selected = "home";
  }elseif (preg_match("/forums/i", $script_name)) {
    $selected = "forums";
  }elseif (preg_match("/torrents/i", $script_name)) {
    $selected = "torrents";
  }elseif (preg_match("/music/i", $script_name)) {
    $selected = "music";
  }elseif (preg_match("/offers/i", $script_name) OR preg_match("/offcomment/i", $script_name)) {
    $selected = "offers";
  }elseif (preg_match("/viewrequests/i", $script_name)) {
    $selected = "requests";
  }elseif (preg_match("/upload/i", $script_name)) {
    $selected = "upload";
  }elseif (preg_match("/subtitles/i", $script_name)) {
    $selected = "subtitles";
  }elseif (preg_match("/topten/i", $script_name)) {
    $selected = "topten";
  }elseif (preg_match("/log/i", $script_name)) {
    $selected = "log";
  }elseif (preg_match("/rules/i", $script_name)) {
    $selected = "rules";
  }elseif (preg_match("/faq/i", $script_name)) {
    $selected = "faq";
  }elseif (preg_match("/staff/i", $script_name)) {
    $selected = "staff";
  }else
     $selected = "";
  print ("<div id=\"nav\"><ul id=\"mainmenu\" class=\"menu\">");
  echo navbar_item('index.php', $lang_functions['text_home'], $selected == "home");
  
  if ($enableextforum != 'yes') {
    echo navbar_item('forums.php', $lang_functions['text_forums'], $selected == "forums");
  }
  else {
    echo navbar_item($extforumurl, $lang_functions['text_forums'], $selected == "forums");
  }
  echo navbar_item('torrents.php', $lang_functions['text_torrents'], $selected == "torrents");
  if ($enablespecial == 'yes') {
    echo navbar_item('music.php', $lang_functions['text_music'], $selected == "music");
  }
  if ($enableoffer == 'yes') {
    echo navbar_item('offers.php', $lang_functions['text_offers'], $selected == "offers");
  }
  
  if ($enablerequest == 'yes') {
    echo navbar_item('viewrequests.php', $lang_functions['text_request'], $selected == "requests");
  }

  echo navbar_item('upload.php', $lang_functions['text_upload'], $selected == "upload");
  echo navbar_item('subtitles.php', $lang_functions['text_subtitles'], $selected == "subtitles");
 // echo navbar_item('usercp.php', $lang_functions['text_user_cp'], $selected == "usercp");
  echo navbar_item('topten.php', $lang_functions['text_top_ten'], $selected == "topten");
  echo navbar_item('log.php', $lang_functions['text_log'], $selected == "log");
  echo navbar_item('rules.php', $lang_functions['text_rules'], $selected == "rules");
  echo navbar_item('faq.php', $lang_functions['text_faq'], $selected == "faq");
  echo navbar_item('staff.php', $lang_functions['text_staff'], $selected == "staff");
  print ("</ul></div>");
}
    
function get_css_id() {
  global $CURUSER, $defcss;
  $cssid = $CURUSER ? $CURUSER["stylesheet"] : $defcss;
  return $cssid;
}

function get_css_rows() {
  global $Cache;
  static $rows;

  if (!$rows && !$rows = $Cache->get_value('stylesheet_content')){
    $rows = array();
    $res = sql_query("SELECT * FROM stylesheets ORDER BY id ASC");
    while($row = _mysql_fetch_array($res)) {
      $rows[$row['id']] = $row;
    }
    $Cache->cache_value('stylesheet_content', $rows, 95400);
  }
  return $rows;
}

function get_css_row($cssid = -1) {
    if ($cssid == -1) {
      $cssid = get_css_id();
    }
    return get_css_rows()[$cssid];
  }
  
function get_css_uri($file = "", $theme = -1) {
  global $defcss;
  $cssRow = get_css_row($theme);

  $ss_uri = $cssRow['uri'];
  if (!$ss_uri) {
    $ss_uri = get_single_value("stylesheets","uri","WHERE id=".sqlesc($defcss));
  }
  if ($file == "") {
    return $ss_uri;
  }
  else {
    return $ss_uri.$file;
  }
}

function jqui_css_name($theme = -1) {
  global $defcss;
  $cssRow = get_css_row($theme);
  $ss_uri = $cssRow['jqui'];
  if (!$ss_uri) {
    $ss_uri = get_single_value("stylesheets","jqui","WHERE id=".sqlesc($defcss));
  }
  return $ss_uri;
}

function get_cat_folder($cat = 401, $caticon = -1) {
  static $catPath = array();
  if (!array_key_exists($cat, $catPath)) {
    global $CURUSER, $CURLANGDIR;
    $catrow = get_category_row($cat);
    $catmode = $catrow['catmodename'];
    if ($caticon == -1) {
      $caticon = $CURUSER['caticon'];
    }
    $caticonrow = get_category_icon_row($caticon);
    $catPath[$cat] = "category/".$catmode."/".$caticonrow['folder'] . ($caticonrow['multilang'] == 'yes' ? $CURLANGDIR."/" : "");
  }
  return $catPath[$cat];
}

function get_style_highlight() {
  global $CURUSER;
  if ($CURUSER) {
    $ss_a = @_mysql_fetch_array(@sql_query("select hltr from stylesheets where id=" . $CURUSER["stylesheet"]));
    if ($ss_a) $hltr = $ss_a["hltr"];
  }
  if (!$hltr) {
    $r = sql_query("SELECT hltr FROM stylesheets WHERE id=5") or die(_mysql_error());
    $a = _mysql_fetch_array($r) or die(_mysql_error());
    $hltr = $a["hltr"];
  }
  return $hltr;
}

function php_json_encode( $data ) {
  return json_encode( $data );
}

function js_hb_config() {
  global $torrentmanage_class, $CURUSER;

  $class = get_user_class();
  if ($class) {
    $user = array('id' => $CURUSER['id'], 'class' => $class, 'canonicalClass' => get_user_class_name($class, false), 'bonus' => $CURUSER['seedbonus']);
    $config = array('user' => $user);

    $out = 'hb = {config : ' . php_json_encode($config) . '}';
  }
  else {
    $out = 'hb={}';
  }

  return $out;
}

function navbar_item($href, $text, $selected, $selected_a = true) {
  global $BASEURL;
  if ($selected) {
    if ($selected_a) {
      return '<li class="selected"><a href="//' . $BASEURL . '/' . htmlspecialchars($href) .  '">' . $text . '</a></li>';
    }
    else {
      return '<li class="selected"><span>' . $text . '</span></li>';
    }
  }
  else {
    return '<li><a href="//' . $BASEURL . '/' . htmlspecialchars($href) . '">' . $text . '</a></li>';
  }
}

function no_login_navbar() {
  global $lang_functions;
  $t = explode('/', $_SERVER['PHP_SELF']);
  $file = array_pop($t);
  return join('', [
	  navbar_item('login.php', $lang_functions['text_login'], ($file == 'login.php')),
	  navbar_item('signup.php', $lang_functions['text_signup'], ($file == 'signup.php')),
	  navbar_item('rules.php', $lang_functions['text_rules_link'], ($file == 'rules.php')),	  
	  ]);
}

function msgalert($url, $text, $bgcolor = '', $id='') {
  global $BASEURL;
  $out = ('<li');
  if ($bgcolor != '') {
    $out .= ' style="background-color:' . $bgcolor . ';"';
  }
  if ($id != '') {
    $out .= ' id="' . $id . '"';
  }
  $out .= '><a href="//' . $BASEURL . '/' . $url . '">' . $text . '</a></li>';
  return $out;
}

function stdhead($title = "", $msgalert = true, $script = "", $place = "") {
  global $lang_functions;
  global $CURUSER, $CURLANGDIR, $SITE_ONLINE, $FUNDS, $SITENAME, $SLOGAN, $logo_main, $BASEURL, $offlinemsg, $showversion,$enabledonation, $staffmem_class, $titlekeywords_tweak, $metakeywords_tweak, $metadescription_tweak, $cssdate_tweak, $deletenotransfertwo_account, $neverdelete_account, $iniupload_main;
  global $tstart;
  global $Cache;
  global $Advertisement;

  static $called = false;
  if ($called) {
    return;
  }
  else {
    $called = true;
  }

  $Cache->setLanguage($CURLANGDIR);
  $Advertisement = new ADVERTISEMENT($CURUSER['id']);
  $cssupdatedate = $cssdate_tweak;
  // Variable for Start Time
  $tstart = getmicrotime(); // Start time

  header("Content-Type: text/html; charset=utf-8; Cache-control:private");
  header('X-Frame-Options:SAMEORIGIN');
  header('X-UA-Compatible:edge');
  //header("Pragma: No-cache");
  if ($title == "")
    $title = $SITENAME;
  else
    $title = $SITENAME."  " . htmlspecialchars($title);
  if ($titlekeywords_tweak)
    $title .= " ".htmlspecialchars($titlekeywords_tweak);
  $title .= $showversion;
  if ($SITE_ONLINE == "no") {
    if (get_user_class() < UC_ADMINISTRATOR) {
      echo '<h1>' . $lang_functions['std_site_down_for_maintenance'] . '</h1>';

      echo '<div id="sns"><h2 class="page-titles">社交网站</h2><div class="minor-list text table td center"><ul>';
      global $sns;
      foreach ($sns as $s) {
	echo '<li><a href="' . $s['url'] . '">' . $s['text'] . '</a></li>';
      }
      echo '</ul></div></div>';

      die();
    }
    else
      {
	$offlinemsg = true;
      }
  }

  $s = smarty();

  $meta = '';
  if ($metakeywords_tweak){
    $meta .= '<meta name="keywords" content="' . htmlspecialchars($metakeywords_tweak) . '" />';
  }
  if ($metadescription_tweak){
    $meta .= '<meta name="description" content="' . htmlspecialchars($metadescription_tweak) . '" />';
  }
  $meta .= '<meta name="generator" content="' . PROJECTNAME . '" />';

  $s->assign(array(
		   'lang' => $lang_functions,
		   'meta' => $meta,
		   'title' => $title,
		   'SITENAME' => $SITENAME,
		   'SLOGAN' => $SLOGAN,
		   'BASEURL' => $BASEURL,
		   'UC_MODERATOR' => UC_MODERATOR,
		   'UC_SYSOP' => UC_SYSOP,
		   'staffmem_class' => $staffmem_class,
		   'forum_pic' => get_forum_pic_folder(),
		   'logo_main' => $logo_main,
		   'enabledonation' => ($enabledonation == 'yes'),
		   ));
  if ($Advertisement->enable_ad()) {
    $s->assign(array(
		     'headerad' => $Advertisement->get_ad('header'),
		     'belownavad' => $Advertisement->get_ad('belownav')
		     ));
  }
  
  if ($CURUSER) {
    $ratio = get_ratio($CURUSER['id']);

    //// check every 15 minutes //////////////////
    $messages = $Cache->get_value('user_'.$CURUSER["id"].'_inbox_count');
    if ($messages === false){
      $messages = get_row_count("messages", "WHERE receiver=" . sqlesc($CURUSER["id"]) . " AND location<>0");
      $Cache->cache_value('user_'.$CURUSER["id"].'_inbox_count', $messages, 900);
    }
    $outmessages = $Cache->get_value('user_'.$CURUSER["id"].'_outbox_count');
    if ($outmessages === false){
      $outmessages = get_row_count("messages","WHERE sender=" . sqlesc($CURUSER["id"]) . " AND saved='yes'");
      $Cache->cache_value('user_'.$CURUSER["id"].'_outbox_count', $outmessages, 900);
    }
    if (!$connect = $Cache->get_value('user_'.$CURUSER["id"].'_connect')){
      $res3 = sql_query("SELECT connectable FROM peers WHERE userid=" . sqlesc($CURUSER["id"]) . " LIMIT 1");
      if($row = _mysql_fetch_row($res3))
	$connect = $row[0];
      else $connect = 'unknown';
      $Cache->cache_value('user_'.$CURUSER["id"].'_connect', $connect, 900);
    }

    if($connect == "yes")
      $connectable = "<span class=\"green\">".$lang_functions['text_yes']."</span>";
    elseif ($connect == 'no')
      $connectable = "<a href=\"faq.php#id21\" class=\"striking\">".$lang_functions['text_no']."</a>";
    else
      $connectable = $lang_functions['text_unknown'];

    //// check every 60 seconds //////////////////
    $activeseed = $Cache->get_value('user_'.$CURUSER["id"].'_active_seed_count');
    if ($activeseed === false){
      $activeseed = get_row_count("peers","WHERE userid=" . sqlesc($CURUSER["id"]) . " AND seeder='yes'");
      $Cache->cache_value('user_'.$CURUSER["id"].'_active_seed_count', $activeseed, 60);
    }
    $activeleech = $Cache->get_value('user_'.$CURUSER["id"].'_active_leech_count');
    if ($activeleech === false){
      $activeleech = get_row_count("peers","WHERE userid=" . sqlesc($CURUSER["id"]) . " AND seeder='no'");
      $Cache->cache_value('user_'.$CURUSER["id"].'_active_leech_count', $activeleech, 60);
    }
    $unread = $Cache->get_value('user_'.$CURUSER["id"].'_unread_message_count');
    if ($unread === false){
      $unread = get_row_count("messages","WHERE receiver=" . sqlesc($CURUSER["id"]) . " AND unread='yes'");
      $Cache->cache_value('user_'.$CURUSER["id"].'_unread_message_count', $unread, 60);
    }

    $s->assign(compact('activeseed', 'messages', 'unread', 'outmessages', 'ratio', 'activeleech', 'connectable'));

    if (get_user_class() >= $staffmem_class){
      $totalreports = $Cache->get_value('staff_report_count');
      if ($totalreports === false){
	$totalreports = get_row_count("reports");
	$Cache->cache_value('staff_report_count', $totalreports, 900);
      }
      $totalsm = $Cache->get_value('staff_message_count');
      if ($totalsm === false){
	$totalsm = get_row_count("staffmessages");
	$Cache->cache_value('staff_message_count', $totalsm, 900);
      }
      $totalcheaters = $Cache->get_value('staff_cheater_count');
      if ($totalcheaters === false){
	$totalcheaters = get_row_count("cheaters");
	$Cache->cache_value('staff_cheater_count', $totalcheaters, 900);
      }
      $s->assign(array('totalreports' => $totalreports,
		       'totalsm' => $totalsm,
		       'totalcheaters' => $totalcheaters));
    }

    $alerts = array();
    if ($msgalert) {
      if ($CURUSER['leechwarn'] == 'yes') {
	$kicktimeout = gettime($CURUSER['leechwarnuntil'], false, false, true);
	$text = $lang_functions['text_please_improve_ratio_within'].$kicktimeout.$lang_functions['text_or_you_will_be_banned'];
	$alerts[] = array('href' => "faq.php#id17",
			  'text' => $text,
			  'color' => "orange");
      }
      
      if($deletenotransfertwo_account) { //inactive account deletion notice
	if ($CURUSER['downloaded'] == 0 && ($CURUSER['uploaded'] == 0 || $CURUSER['uploaded'] == $iniupload_main)) {
	  
	  $neverdelete_account = ($neverdelete_account <= UC_VIP ? $neverdelete_account : UC_VIP);
	  if (get_user_class() < $neverdelete_account) {
	    $secs = $deletenotransfertwo_account*24*60*60;
	    $addedtime = strtotime($CURUSER['added']);
	    if (TIMENOW > $addedtime+($secs/3)) { // start notification if one third of the time has passed
	      
	      $kicktimeout = gettime(date("Y-m-d H:i:s", $addedtime+$secs), false, false, true);
	      $text = $lang_functions['text_please_download_something_within'].$kicktimeout.$lang_functions['text_inactive_account_be_deleted'];
	      $alerts[] = array('href' => "rules.php",
				'text' => $text,
				'color' => "gray");
	    }
	  }
	}
      }
      if($CURUSER['showclienterror'] == 'yes') {
	$text = $lang_functions['text_banned_client_warning'];
	$alerts[] = array('href' => "faq.php#id29",
			  'text' => $text,
			  'color' => "black");
      }

      $cache_key = 'user_' . $CURUSER['id'] . '_startseed_count';
      $no_startseed_count = $Cache->get_value($cache_key);
      if ($no_startseed_count === false) {
	$no_startseed_count = get_row_count('torrents', 'WHERE owner = ' . $CURUSER['id'] . ' AND startseed = "no"');
	$Cache->cache_value($cache_key, $no_startseed_count, 300);
      }
      if ($no_startseed_count) {
	$alerts[] = ['href' => 'torrents.php?search_area=3&amp;search_mode=3&amp;incldead=3&amp;search=' . $CURUSER['username'],
		     'text' => sprintf($lang_functions['text_no_startseed'], $no_startseed_count),
		     'color' => 'black'];
      }
      
      if ($unread) {
	$text = $lang_functions['text_you_have'].$unread.$lang_functions['text_new_message'] . add_s($unread) . $lang_functions['text_click_here_to_read'];
	$alerts[] = array('href' => "messages.php",
			  'text' => $text,
			  'color' => "red",
			  'id' => 'alert-message');
      }

      $settings_script_name = $_SERVER["SCRIPT_FILENAME"];
      if (!preg_match("/index/i", $settings_script_name))
	{
	  $new_news = $Cache->get_value('user_'.$CURUSER["id"].'_unread_news_count');
	  if ($new_news === false){
	    $new_news = get_row_count("news","WHERE notify = 'yes' AND added > ".sqlesc($CURUSER['last_home']));
	    $Cache->cache_value('user_'.$CURUSER["id"].'_unread_news_count', $new_news, 300);
	  }
	  if ($new_news > 0) {
	    $text = $lang_functions['text_there_is'].is_or_are($new_news).$new_news.$lang_functions['text_new_news'];
	    $alerts[] = array('href' => "index.php",
			      'text' => $text,
			      'color' => "green");
	  }
	}

      if (get_user_class() >= $staffmem_class) {
	$numreports = $Cache->get_value('staff_new_report_count');
	if ($numreports === false) {
	  $numreports = get_row_count("reports","WHERE dealtwith=0");
	  $Cache->cache_value('staff_new_report_count', $numreports, 900);
	}
	if ($numreports) {
	  $text = $lang_functions['text_there_is'].is_or_are($numreports).$numreports.$lang_functions['text_new_report'] .add_s($numreports);
	  $alerts[] = array('href' => "reports.php",
			    'text' => $text,
			    'color' => "blue");
	}
	$nummessages = $Cache->get_value('staff_new_message_count');
	if ($nummessages === false) {
	  $nummessages = get_row_count("staffmessages","WHERE answered='no'");
	  $Cache->cache_value('staff_new_message_count', $nummessages, 900);
	}
	if ($nummessages > 0) {
	  $text = $lang_functions['text_there_is'].is_or_are($nummessages).$nummessages.$lang_functions['text_new_staff_message'] . add_s($nummessages);
	  $alerts[] = array('href' => "staffbox.php",
			    'text' => $text,
			    'color' => "blue");
	}
	$numcheaters = $Cache->get_value('staff_new_cheater_count');
	if ($numcheaters === false) {
	  $numcheaters = get_row_count("cheaters","WHERE dealtwith=0");
	  $Cache->cache_value('staff_new_cheater_count', $numcheaters, 900);
	}
	if ($numcheaters) {
	  $text = $lang_functions['text_there_is'].is_or_are($numcheaters).$numcheaters.$lang_functions['text_new_suspected_cheater'] .add_s($numcheaters);
	  $alerts[] = array('href' => "cheaterbox.php",
			    'text' => $text,
			    'color' => "blue");
	}
      }
    }

    if ($offlinemsg) {
      $alerts[] = array('href' => "",
			'text' => $lang_functions['text_website_offline_warning'],
			'color' => "white");		  
    }
  
    $s->assign(['alerts'=> $alerts,
	       'id' => $CURUSER['id'],
	       'CURUSER' => $CURUSER,
	       ]);
  }
  
  if ($CURUSER) {
    $key = $CURUSER['id'];
  }
  else {
    $key = '0';
  }
  $s->display('stdhead.tpl', $key);
}

function stdfoot() {
  global $SITENAME,$BASEURL,$Cache,$datefounded,$tstart,$icplicense_main,$add_key_shortcut,$query_name, $USERUPDATESET, $CURUSER, $enablesqldebug_tweak, $sqldebug_tweak, $Advertisement, $analyticscode_tweak, $VERSION, $icplicense, $cnzz;

  if ($CURUSER && $USERUPDATESET){
    update_user($CURUSER['id'], implode(",", $USERUPDATESET), [], false);
  }
  // Variables for End Time
  $tend = getmicrotime();
  $totaltime = ($tend - $tstart);
  $alltotaltime=0+($tend-TIMENOWSTART);
  $year = substr($datefounded, 0, 4);
  $yearfounded = ($year ? $year : 2007);

  if (isset($Advertisement)) {
    $ad = $Advertisement->get_ad('footer');
  }
  else {
    $ad = '';
  }
  
  $s = smarty();
  $s->assign(array('footerad' => $ad,
		   'SITENAME' => $SITENAME,
		   'BASEURL' => $BASEURL,
		   'VERSION' => $VERSION,
		   'icplicense' => $icplicense,
		   'icplicense_main' => $icplicense_main,
		   'yearfounded' => $yearfounded,
		   'totaltime' => $totaltime,
		   'alltotaltime' => $alltotaltime,
		   'queries' => $query_name,
		   'Cache' => $Cache,
		   'details' => ($enablesqldebug_tweak == 'yes' && get_user_class() >= $sqldebug_tweak),
		   'key_shortcut' => $add_key_shortcut,
		   'analyticscode_tweak' => $analyticscode_tweak,
		   'cnzz' => $cnzz
		   ));
  $s->display('stdfoot.tpl');
}

function mksecret($len = 20) {
  $ret = "";
  for ($i = 0; $i < $len; $i++)
  $ret .= chr(mt_rand(100, 120));
  return $ret;
}

function httperr($code = 404) {
  header("HTTP/1.0 404 Not found");
  print("<h1>Not Found</h1>\n");
  exit();
}

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff, $securelogin=false, $ssl=false, $trackerssl=false)
{
  if ($expires != 0x7fffffff && $expires != 0)
  $expires = time()+$expires;

  setcookie("c_secure_uid", base64($id), $expires, "/", NULL, $ssl, true);
  setcookie("c_secure_pass", $passhash, $expires, "/", NULL, $ssl, true);
  if($ssl)
  setcookie("c_secure_ssl", base64("yeah"), $expires, "/");
  else
  setcookie("c_secure_ssl", base64("nope"), $expires, "/");

  if($trackerssl)
  setcookie("c_secure_tracker_ssl", base64("yeah"), $expires, "/");
  else
  setcookie("c_secure_tracker_ssl", base64("nope"), $expires, "/");

  if ($securelogin)
  setcookie("c_secure_login", base64("yeah"), $expires, "/");
  else
  setcookie("c_secure_login", base64("nope"), $expires, "/");

  if ($updatedb)
    update_user($id, "last_login = NOW(), lang=?", [get_langid_from_langcookie()]);
}

function set_langfolder_cookie($folder, $expires = 0x7fffffff)
{
  if ($expires != 0x7fffffff)
  $expires = time()+$expires;

  setcookie("c_lang_folder", $folder, $expires, "/");
}

function get_protocol_prefix()
{
  global $securelogin;
  if ($securelogin == "yes") {
    return "https://";
  } elseif ($securelogin == "no") {
    return "http://";
  } else {
    if (!isset($_COOKIE["c_secure_ssl"])) {
      return "//";
    } else {
      return base64_decode($_COOKIE["c_secure_ssl"]) == "yeah" ? "https://" : "http://";
    }
  }
}

function get_langid_from_langcookie()
{
  global $CURLANGDIR;
  $row = _mysql_fetch_array(sql_query("SELECT id FROM language WHERE site_lang = 1 AND site_lang_folder = " . sqlesc($CURLANGDIR) . "ORDER BY id ASC")) or sqlerr(__FILE__, __LINE__);
  return $row['id'];
}

function make_folder($pre, $folder_name)
{
  $path = $pre . $folder_name;
  if(!file_exists($path))
  mkdir($path,0777,true);
  return $path;
}

function logoutcookie() {
  checkHTTPMethod('POST');
  setcookie("c_secure_uid", "", 0x7fffffff, "/");
  setcookie("c_secure_pass", "", 0x7fffffff, "/");
// setcookie("c_secure_ssl", "", 0x7fffffff, "/");
  setcookie("c_secure_tracker_ssl", "", 0x7fffffff, "/");
  setcookie("c_secure_login", "", 0x7fffffff, "/");
//  setcookie("c_lang_folder", "", 0x7fffffff, "/");
}

function base64 ($string, $encode=true) {
  if ($encode)
  return base64_encode($string);
  else
  return base64_decode($string);
}

function loggedinorreturn($mainpage = false) {
  global $CURUSER,$BASEURL;
  if (!$CURUSER) {
    if ($mainpage)
    header("Location: " . get_protocol_prefix() . "$BASEURL/login.php");
    else {
      $to = $_SERVER["REQUEST_URI"];
      $to = basename($to);
      header("Location: " . get_protocol_prefix() . "$BASEURL/login.php?returnto=" . rawurlencode($to));
    }
    exit();
  }
}

function loggedinorreturn2($mainpage = false) {
  global $CURUSER,$BASEURL;
  if (!$CURUSER) {
    if ($mainpage)
       Header("Location:/carsi/login.php");
    else {
      $to = $_SERVER["REQUEST_URI"];
      $to = basename($to);
      header("Location: " . get_protocol_prefix() . "$BASEURL/carsi/login.php?returnto=" . rawurlencode($to));
    }
    exit();
  }
}


function loggedinorreturn3($mainpage = false) {
  global $CURUSER,$BASEURL;

  // Check the tongji public account client ip
  if($CURUSER && $CURUSER['id'] == 53281) {

    $in_tongji = false;
    
    $current_ip = ip2long($CURUSER['ip']);
    
    if(($current_ip >= ip2long('202.114.136.1') && $current <= ip2long('202.114.136.255'))
       || ($current_ip >= ip2long('202.114.128.1') && $current <= ip2long('202.114.128.255'))
       || ($current_ip >= ip2long('202.114.13.1') && $current <= ip2long('202.114.13.255'))
       || ($current_ip >= ip2long('202.114.129.1') && $current <= ip2long('202.114.129.255'))
       || ($current_ip >= ip2long('122.205.16.1') && $current <= ip2long('122.205.16.255'))
       || $CURUSER['ip'] == '202.114.135.147') {
      $in_tongji = true;
    }
    
    if($in_tongji === false) {
      header("Location: " . get_protocol_prefix() . "$BASEURL/tj_notice.html");
    }
  }
  // End ||

  if (!$CURUSER) {
    if ($mainpage)
    header("Location: " . get_protocol_prefix() . "$BASEURL/login.php");
    else {
      $to = $_SERVER["REQUEST_URI"];
      $to = basename($to);
      header("Location: " . get_protocol_prefix() . "$BASEURL/login.php?returnto=" . rawurlencode($to));
    }
    exit();
  }
}

function send_pm($from, $to, $subject, $msg, $save = 'no') {
  static $client;
  global $Cache;

  sql_query("INSERT INTO messages (sender, receiver, subject, msg, added, saved) VALUES(?, ?, ?, ?, ?, ?)", [$from, $to, $subject, $msg, date("Y-m-d H:i:s"), $save]);

  $Cache->delete_value('user_'.$to.'_unread_message_count');
  $Cache->delete_value('user_'.$to.'_inbox_count');

  if (!$client) {
    require_once('ws/class.websocket_client.php');
    $client = new WebsocketClient;
    $client->connect('127.0.0.1', 12345, '/newpm', 'hudbt.local');
  }
  if ($client->isConnected()) {
    $client->sendData(strval($to));
  }
}

function pager($rpp, $count, $href, $opts = array(), $pagename = "page") {
  global $lang_functions,$add_key_shortcut;
  $prev_page_href = '';
  $next_page_href = '';
  $pages = ceil($count / $rpp);

  if (array_key_exists('page', $opts)) {
    $page = $opts['page'];
  }
  else {
    if (!array_key_exists("lastpagedefault", $opts))
      $pagedefault = 0;
    else {
      $pagedefault = floor(($count - 1) / $rpp);
      if ($pagedefault < 0)
        $pagedefault = 0;
    }

    if (isset($_GET[$pagename])) {
      $page = 0 + $_GET[$pagename];
      if ($page < 0)
        $page = $pagedefault;
    }
    else
      $page = $pagedefault;
  }

  if (isset($opts['anchor'])) {
    $surfix = '#' . $opts['anchor'];
  }
  else {
    $surfix = '';
  }

  $mp = $pages - 1;
  $pagerprev = '';
  $pagernext = '';

  //Opera (Presto) doesn't know about event.altKey
  $is_presto = strpos($_SERVER['HTTP_USER_AGENT'], 'Presto');
  $as = "&lt;&lt;&nbsp;".$lang_functions['text_prev'];
  if ($page >= 1) {
    $prev_page_href = $href . $pagename . "=" . ($page - 1) . $surfix;
    $pagerprev = "<a href=\"".htmlspecialchars($prev_page_href). "\" title=\"".($is_presto ? $lang_functions['text_shift_pageup_shortcut'] : $lang_functions['text_alt_pageup_shortcut'])."\">";
    $pagerprev .= $as;
    $pagerprev .= "</a>";
  }
  else {
    $pagerprev = "<span class=\"selected\">".$as."</span>";
  }

  $as = $lang_functions['text_next']."&nbsp;&gt;&gt;";
  if ($page < $mp && $mp >= 0) {
    $next_page_href = $href . $pagename."=" . ($page + 1) . $surfix;
    $pagernext .= "<a href=\"".htmlspecialchars($next_page_href). "\" title=\"".($is_presto ? $lang_functions['text_shift_pagedown_shortcut'] : $lang_functions['text_alt_pagedown_shortcut'])."\">";
    $pagernext .= $as;
    $pagernext .= "</a>";
  }
  else {
       $pagernext = "<span class=\"selected\">".$as."</span>";
  }

  if ($count) {
    $pagerarr = array($pagerprev);
    $dotted = 0;
    $dotspace = 3;
    $startdotspace = 2;
    $dotend = $pages - $startdotspace;
    $curdotend = $page - $dotspace;
    $curdotstart = $page + $dotspace;
    for ($i = 0; $i < $pages; $i++) {
      if (($i >= $startdotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
        if (!$dotted)
        $pagerarr[] = '<a href="#" class="pager-more">...</a>';
        $dotted = 1;
        continue;
      }
      $dotted = 0;
      $start = $i * $rpp + 1;
      $end = $start + $rpp - 1;
      if ($end > $count) {
	$end = $count;
      }
      $text = "$start&nbsp;-&nbsp;$end";
      if ($i != $page) {
	$pagerarr[] = "<a class=\"pagenumber\" href=\"".htmlspecialchars($href . $pagename . "=" . $i . $surfix)."\">$text</a>";
      }
      else {
	$pagerarr[] = "<span class=\"selected\">$text</span>";
      }
    }
    $pagerarr[] = $pagernext;
    
    $pagerstr = '<ul><li>'.join("</li><li>", $pagerarr).'</li></ul>';
    $pagertop = "<div id='pagertop' class=\"pages minor-list list-seperator\">$pagerstr";
    $pagerbottom = "<div id='pagerbottom' class=\"pages minor-list list-seperator\" style=\"margin-bottom:0.6em;\">$pagerstr";

    $links = '<link rel="start" href="' . substr($href, 0, -1) . '" />';
    if ($prev_page_href) {
      $links .= '<link rel="prev" href="' . $prev_page_href . '" />';
    }
    if ($next_page_href) {
      $links .= '<link rel="next" href="' . $next_page_href . '" />';
    }

    if (isset($opts['link']) && $opts['link'] == 'bottom') {
      $pagerbottom .= $links;
    }
    else {
      $pagertop .= $links;
    }
    $pagertop .= "</div>";
    $pagerbottom .= "</div>";
  }
  else {
    $pagertop = "<div id='pagertop' class=\"pages minor-list\"></div>\n";
    $pagerbottom = "<div id='pagerbottom' class=\"pages minor-list\"></div>\n";
  }

  $start = $page * $rpp;
  $add_key_shortcut = key_shortcut($page,$pages-1);
  return array($pagertop, $pagerbottom, "LIMIT $start,$rpp", $next_page_href, $start);
}

function post_author_stats($id, $idarr) {
  global $lang_functions;
  global $Cache;
  $uploaded = mksize($idarr["uploaded"]);
  $downloaded = mksize($idarr["downloaded"]);
  $ratio = get_ratio($id);

  if (!$forumposts = $Cache->get_value('user_'.$id.'_post_count')){
    $forumposts = get_row_count("posts","WHERE userid=".$id);
    $Cache->cache_value('user_'.$id.'_post_count', $forumposts, 3600);
  }
  
  $stats = '<li>'.$lang_functions['text_posts'].$forumposts . '</li><li>'.$lang_functions['text_uploaded'].$uploaded.'</li><li>'.$lang_functions['text_downloaded'].$downloaded.'</li><li>'.$lang_functions['text_ratio'].$ratio.'</li>';
  return $stats;
}

function user_class_image($class) {
  $uclass = get_user_class_image($class);
  $userclassimg = "<img alt=\"".get_user_class_name($class,false,false,true)."\" title=\"".get_user_class_name($class,false,false,true)."\" src=\"".$uclass."\" />";
  return $userclassimg;
}

function post_author_toolbox($arr2) {
  global $lang_functions;
  global $BASEURL;
  $secs = 900;
  $dt = sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs))); // calculate date.

  $online_status = ("'".$arr2['last_access']."'") > $dt ? "<img class=\"f_online\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Online\" title=\"".$lang_functions['title_online']."\" />":"<img class=\"f_offline\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Offline\" title=\"".$lang_functions['title_offline']."\" />";

  $url = 'sendmessage.php?receiver='.$arr2["id"];
  $toolbox_user = '<li>' . $online_status . "</li><li><a href=\"".htmlspecialchars($url)."\"><img class=\"f_pm\" src=\"//$BASEURL/pic/trans.gif\" alt=\"PM\" title=\"".$lang_functions['title_send_message_to'].htmlspecialchars($arr2["username"])."\" /></a></li>";
  return $toolbox_user;
}

function post_format_author_info($id, $stat = false) {
  global $CURUSER;
  static $users = [];
  //---- Get poster details
  if (!array_key_exists($id, $users)) {
    $users[$id] = true;
    $first = true;
  }
  else {
    $first = false;
  }
  
  $arr2 = get_user_row($id);

  $avatar = htmlspecialchars($arr2["avatar"]);

  if (!$avatar) {
    $avatar = "pic/default_avatar.png";
  }
  
  if ($first) {
    $signature = $arr2["signature"];
  }
  else {
    $signature = '';
  }

  $href_id = 'user-info-' . $id;
  $out = '<div class="forum-author-info"';
  if ($first) {
    $out .= ' id="' . $href_id . '"';
  }
  $out .= '>';
  $out .= '<div class="post-avatar">';
  if (!$first) {
    $out .= '<a href="#' . $href_id . '">';
  }
  $out .= return_avatar_image($avatar);
  if (!$first) {
    $out .= '</a>';
  }
  $out .= '</div>';
  if ($first) {
    if ($stat) {
      $out .= '<div class="user-stats minor-list-vertical"><ul>' . post_author_stats($id, $arr2) . '</ul></div>';
    }
    $out .= '<div class="forum-user-toolbox minor-list horizon-compact compact"><ul>' . post_author_toolbox($arr2) . '</ul></div>';
  }
  $out .= '</div>';
  return array($out, $signature);
}

function post_header($type, $authorid, $topicid, $postid, $added, $floor, $showonly = NULL, $postname = '') {
#!
  global $lang_functions;
  $sadded = gettime($added,true,false);
  $by = get_username($authorid,false,true,true,false,false,true);
  
  if ($type == 'post') {
    $abbrtype = 'p';
    $href = "forums.php?action=viewtopic&topicid=".$topicid."&page=p".$postid."#pid".$postid;
  }
  else {
    $abbrtype = 'c';
    #! page
    if ($type == 'torrent') {
      $href = 'details.php?cmtpage=1';
    }
    elseif ($type == 'offer') {
      $href = 'offers.php?off_details=1';
    }
    elseif ($type == 'request') {
      $href = 'viewrequests.php?req_details=1';
    }
    $href .= '&page=p' . $postid . '&id=' . $topicid . '#cid' . $postid;
  }
  
  $header = '<div class="forum-post-header" id="' . $abbrtype . 'id' . $postid. '"><div class="minor-list"><ul><li><span class="gray">'.$lang_functions['text_by'].'</span>'.$by."</li>";

  if ($type == 'post') {
    $header .= '<li class="list-seperator">';
    if ($showonly) {
      $header .= "<a href=\"?action=viewtopic&topicid=".$topicid."\">".$lang_functions['text_view_all_posts']."</a>";
    }
    else {
      $header .= "<a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$topicid."&authorid=".$authorid)."\">".$lang_functions['text_view_this_author_only']."</a>";
    }
    $header .= '</li>';
  }
  $header .= '</ul></div>';

  if ($floor == -1) {
    $floor_text = $postname;
  }
  else {
    $floor_text = $lang_functions['text_number']. $floor . $lang_functions['text_lou'];
  }
  $header .= '<div class="forum-floor minor-list list-seperator"><ul><li><span class="gray">'.$lang_functions['text_at'].'</span>'.$sadded.'</li><li><a href="' . htmlspecialchars($href).'" title="' . $floor_text . '">'.$floor_text.'</a></li></ul></div>';
  $header .= '</div>';
  return $header;
}

function post_body($postid, $body, $highlight) {
  $fbody = format_comment($body);
  if ($highlight) {
    $fbody = highlight($highlight, $fbody);
  }
  
  $b = '<div id="pid'. $postid .'body" class="forum-post-body">';
  $b .= $fbody;
  $b .= '</div>';
  return $b;
}

function post_body_edited($edit) {
  global $lang_functions;
  $id = $edit['editor'];
  $date = $edit['date'];
  $editnotseen = $edit['editnotseen'];
  $lastedittime = gettime($date,true,false);
  if((!Checkprivilege(["Posts","seeeditnotseen"])) && $editnotseen == 1)//To see if editting can be seen or not
  return '';
	else
	return '<div class="post-edited">'.$lang_functions['text_last_edited_by'].get_username($id).$lang_functions['text_last_edit_at'].$lastedittime."</div>\n";;
}

function post_body_toolbox_href($postid, $type, $pid) {
  if ($type == 'post') { //forum
    $surfix = '&postid=' . $postid;
    return array(
		 'forums.php?action=quotepost' . $surfix,
		 'forums.php?action=deletepost' . $surfix,
		 'forums.php?action=editpost' . $surfix,
		 'report.php?forumpost=' . $postid
		 );
  }
  else {
    $surfix = '&cid='. $postid . '&type=' . $type;
    return array(
		 'comment.php?action=add&sub=quote&pid=' . $pid . $surfix,
		 'comment.php?action=delete' . $surfix,
		 'comment.php?action=edit' . $surfix,
		 'report.php?commentid=' . $postid
		 );

  }
}

function post_body_toolbox($postid, $privilege, $type='', $pid = '', $extra='') {
  global $lang_functions;
  global $BASEURL;

  $toolbox_post = '';
  list($canquote, $candelete, $canedit) = $privilege;
  $hrefs = post_body_toolbox_href($postid, $type, $pid);

  $toolbox_post .= '<li><a href="' . $hrefs[3] . '"><img class="f_report" src="//' . $BASEURL . '/pic/trans.gif" alt="Report" title="'.$lang_functions['title_report_this_comment'].'" /></a></li>';
  
  if ($canquote) {
    $toolbox_post .= '<li><a href="'.htmlspecialchars($hrefs[0]).'"><img class="f_quote" src="//' . $BASEURL . '/pic/trans.gif" alt="Quote" title="'.$lang_functions['title_reply_with_quote'].'" /></a></li>';
  }

  if ($candelete) {
    $toolbox_post .= ('<li><a href="'.htmlspecialchars($hrefs[1]).'"><img class="f_delete" src="//' . $BASEURL . '/pic/trans.gif" alt="Delete" title="'.$lang_functions['title_delete_post'].'" /></a></li>');
  }
  
  if ($canedit) {
    $toolbox_post .= ('<li><a href="' . htmlspecialchars($hrefs[2]).'"><img class="f_edit" src="//' . $BASEURL . '/pic/trans.gif" alt="Edit" title="' . $lang_functions['title_edit_post'].'" /></a></li>');
  }

  if ($toolbox_post) {
    return '<div class="forum-post-toolbox-container"><div class="forum-post-toolbox minor-list horizon-compact"><ul>' . $toolbox_post . '</ul></div>' . $extra . '</div>';
  }
  return '';
}

function post_body_container($postid, $body, $highlight, $edit, $signature, $privilege, $type, $pid, $extra = '') {
  global $CURUSER;
  
  $container = '<div class="forum-post-body-container">';
  $container .= post_body($postid, $body, $highlight);
  $container .= '<div class="forum-post-postfix">';
  if ($edit) {
    $container .= post_body_edited($edit);
  }
  if ($signature && $type == 'post') {
    $container .= '<div class="signature">' . format_comment($signature,false,false,false,true,550,true,false, 1,150) . '</div>';
  }
  $container .= '</div>';

  $container .= post_body_toolbox($postid, $privilege, $type, $pid, $extra);

  $container .= '</div>';
  return $container;
}

function post_format($args, $privilege, $extra = '') {
  $post = '<li class="forum-post td table">';
  if (array_key_exists('last', $args) && $args['last']) {
    $post .= '<a id="last"></a>';
  }
  $type = $args['type'];
  $post .= post_header($type, $args['posterid'], $args['topicid'], $args['postid'], $args["added"], $args['floor'], $args['authorid'], $args['postname']);
  list($author_info, $signature) = post_format_author_info($args['posterid'], ($type =='post'));
  $post .= $author_info;

  $post .= post_body_container($args['postid'], $args['body'], $args['highlight'], $args['edit'], $signature, $privilege, $args['type'], $args['topicid'], $extra);
  $post .= '</li>';
  return $post;
}

function get_forum_privilege($id, $locked = false) {
  global $postmanage_class, $CURUSER;
  $is_forummod = is_forum_moderator($id,'forum');
  $row = get_forum_row($id);

  $read = (get_user_class() >= $row["minclassread"]);
  $post = (((get_user_class() >= $row["minclasswrite"] && !$locked) || get_user_class() >= $postmanage_class || $is_forummod) && $CURUSER["forumpost"] == 'yes');
  $modify = get_user_class() >= $postmanage_class || $is_forummod;
  return array($read, $post, $modify);
}

function get_forum_row($forumid = 0) {
  global $Cache;
  if (!$forums = $Cache->get_value('forums_list')){
    $forums = array();
    $res2 = sql_query("SELECT * FROM forums ORDER BY forid ASC, sort ASC") or sqlerr(__FILE__, __LINE__);
    while ($row2 = _mysql_fetch_array($res2))
      $forums[$row2['id']] = $row2;
    $Cache->cache_value('forums_list', $forums, 86400);
  }
  if (!$forumid)
    return $forums;
  else return $forums[$forumid];
}

function get_forum_id($query_id = 0,$query_type = "topic") {
	switch($query_type){
	case "post":
		$query_res = sql_query("SELECT topicid FROM posts WHERE id = $query_id") or sqlerr(__FILE__, __LINE__);
		$row = _mysql_fetch_array($query_res);
		if(!$row){
			return -1;
		}
		$query_id = $row['topicid'];
		//deliberately fall through
	case "topic":
		$query_res = sql_query("SELECT forumid FROM topics WHERE id = $query_id") or sqlerr(__FILE__, __LINE__);
		$row = _mysql_fetch_array($query_res);
		if(!$row){
			return -1;
		}
		$forum_id = $row['forumid'];
		return $forum_id;	
	}
}

function single_post($arr, $maypost, $maymodify, $locked, $highlight = '', $last = false, $floor = -1, $extra) {
  global $CURUSER;
  $editor = $arr['editedby'];
  if (!is_valid_id($editor)) {
    $edit = false;
  }
  else {
    $edit = array('editor' => $editor, 'date' => $arr['editdate'],'editnotseen' =>$arr['editnotseen']);
  }
  $posterid = $arr['userid'];

  $privilege = array($maypost, $maymodify, $maymodify || ($CURUSER["id"] == $posterid && !$locked));
  $post_f = array('type' => 'post', 'posterid' => $posterid, 'topicid' => $arr['topicid'], 'postid' => $arr['id'], 'added' => $arr['added'], 'floor' => $floor, 'body' => $arr['body'], 'highlight' => $highlight, 'edit' => $edit, 'last' => $last, 'postname' => $arr['postname']);
  echo post_format($post_f, $privilege, $extra);
}

function single_comment($row, $parent_id, $type, $floor = -1) {
  global $commanage_class, $CURUSER;

  if ($row["editedby"]) {
    $edit = array('editor' => $row["editedby"], 'date' => $row['editdate'],'editnotseen' => $row['editnotseen']);
  }
  else {
    $edit = false;
  }

  $post_f = ['type' => $type, 'posterid' => $row['user'], 'topicid' => $parent_id, 'postid' => $row['id'], 'added' => $row['added'], 'floor' => $floor, 'body' => $row['text'], 'highlight' => false, 'edit' => $edit, 'postname' => null, 'authorid' => null];
  if (array_key_exists('postname', $row)) {
    $post_f['postname'] = $row['postname'];
  }
  $maymodify = (get_user_class() >= $commanage_class);
  $privilege = array(true, $maymodify, ($row["user"] == $CURUSER["id"] || $maymodify));
  echo post_format($post_f, $privilege);
}

function commenttable($rows, $type, $parent_id, $review = false, $offset=0) {
  global $lang_functions;
  global $CURUSER, $Advertisement;

  echo '<div id="forum-posts"><ol>';

  $count = 0;
  if ($Advertisement->enable_ad()) {
    $commentad = $Advertisement->get_ad('comment');
  }
  
  foreach ($rows as $row) {
    if ($count>=1 && isset($commentad) && isset($commentad[$count - 1])) {
      echo '<div class="forum-ad table td" id="ad_comment_'.$count."\">".$commentad[$count-1]."</div>";
    }

    $floor = $offset+$count+1;
    single_comment($row, $parent_id, $type, $floor);
    $count++;
  }
  echo '</ol></div>';
}

function dequote($s) {
#  $MAX_LEN = 40;
  
  $s = preg_replace('/\[quote(=[a-z0-9\'"\[\]=]+)?\].*\[\/quote\]/smi', '', $s);
#  if (iconv_strlen($s, 'utf-8') > $MAX_LEN) {
#    $s = iconv_substr($s, 0, $MAX_LEN, 'utf-8') . '...';
#  }
  return $s;
}

function searchfield($s) {
  return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist($catmode = 1) {
  global $Cache;
  if (!$ret = $Cache->get_value('category_list_mode_'.$catmode)){
    $ret = array();
    $res = sql_query("SELECT id, mode, name FROM categories WHERE mode = ".sqlesc($catmode)." ORDER BY sort_index, id");
    while ($row = _mysql_fetch_array($res))
      $ret[] = $row;
    $Cache->cache_value('category_list_mode_'.$catmode, $ret, 152800);
  }
  return $ret;
}

function searchbox_item_list($table = "sources"){
  global $Cache;
  if (!$ret = $Cache->get_value($table.'_list')){
    $ret = array();
    $res = sql_query("SELECT * FROM ".$table." ORDER BY sort_index, id");
    while ($row = _mysql_fetch_array($res))
      $ret[] = $row;
    $Cache->cache_value($table.'_list', $ret, 152800);
  }
  return $ret;
}

function langlist($type) {
  global $Cache;
  if (!$ret = $Cache->get_value($type.'_lang_list')){
    $ret = array();
    $res = sql_query("SELECT id, lang_name, flagpic, site_lang_folder FROM language WHERE ". $type ."=1 ORDER BY site_lang DESC, id ASC");
    while ($row = _mysql_fetch_array($res))
      $ret[] = $row;
    $Cache->cache_value($type.'_lang_list', $ret, 152800);
  }
  return $ret;
}

function linkcolor($num) {
  if (!$num)
  return "red";
  //    if ($num == 1)
  //        return "yellow";
  return "green";
}

function writecomment($userid, $comment) {
  $modcomment = date("d-m-Y") . " - " . $comment . "\n\n";
  return update_user($userid, 'modcomment = CONCAT(modcomment, ?)', [$modcomment]);
}

function return_torrent_bookmark_array($userid) {
  global $Cache;
  $ret = $Cache->get_value('user_'.$userid.'_bookmark_array');
  if ($ret === false){
    $ret = array_fill_keys(array_map(function($a) {
	  return $a['torrentid'];
	}, sql_query("SELECT torrentid FROM bookmarks WHERE userid=?", [$userid])->fetchAll()), true);

    $Cache->cache_value('user_'.$userid.'_bookmark_array', $ret, 132800);
  }
  return $ret;
}

function get_is_torrent_bookmarked($userid, $torrentid) {
  $userid = 0 + $userid;
  $torrentid = 0 + $torrentid;
  $ret = return_torrent_bookmark_array($userid);
  return isset($ret[$torrentid]); // already bookmarked
}

function get_torrent_bookmark_state($userid, $torrentid, $text = false) {
  global $lang_functions;
  global $BASEURL;

  if (get_is_torrent_bookmarked($userid, $torrentid)) {
    $act = ($text == true ? $lang_functions['title_delbookmark_torrent'] : "<img class=\"bookmark\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Bookmarked\" title=\"".$lang_functions['title_delbookmark_torrent']."\" />");
  }
  else {
    $act = ($text == true ?  $lang_functions['title_bookmark_torrent']  : "<img class=\"delbookmark\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Unbookmarked\" title=\"".$lang_functions['title_bookmark_torrent']."\" />");
  }
  return $act;
}

function torrentTableCake($torrents) {
  $ids = [];
  foreach($torrents as $torrent) {
    $ids[] = $torrent['Torrent']['id'];
  }
  $query = 'SELECT torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, torrents.medium, torrents.codec, torrents.standard, torrents.processing, torrents.team, torrents.audiocodec, torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.anonymous,torrents.owner,torrents.url,torrents.oday FROM torrents WHERE id IN (' . implode(',', $ids) . ') ORDER BY pos_state DESC, torrents.id DESC';
  $rows = sql_query($query)->fetchAll();
  torrenttable($rows);
}

function torrenttable($rows, $var) {
  global $Cache;
  global $lang_functions;
  global $CURUSER, $waitsystem;
  global $showextinfo;
  global $torrentmanage_class, $smalldescription_main, $enabletooltip_tweak;
  global $CURLANGDIR, $browsecatmode;
  // Added Br BruceWolf. 2011-04-24
  // Filter banned torrents
  global $seebanned_class;
  global $torrent_tooltip, $BASEURL;
  global $USERUPDATESET;

  $forcemode = 0;
  $variant = "torrent";
  $swap_headings = false;
  $onlyhead=false;
  $counter = 0;
  $header = true;
  $splitcomment = false;
  $progress = [];
  $is_new = false;
  extract($var);
  
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
  if ($header) {
?>
<table id="torrents" class="torrents no-vertical-line" cellspacing="0" cellpadding="5" width="100%" <?php echo ($onlyhead ? 'style="display:none;"':'') ?>>
<thead><tr>
<?php
$count_get = 0;
$args = $_REQUEST;
unset($args['sort']);
unset($args['type']);
$oldlink = http_build_query($args);
if ($oldlink > 0) {
  $oldlink = $oldlink . "&amp;";
}


if (array_key_exists('sort', $_REQUEST)) {
  $sort = $_REQUEST['sort'];
}
else {
  $sort = 0;
}

if (array_key_exists('type', $_REQUEST)) {
  $sorttype = $_REQUEST['type'];
  if ($sorttype == 'desc') {
    $sorttype = 'asc';
  }
  else {
    $sorttype = 'desc';
  }
}

$link = array();
for ($i=1; $i<=9; $i++){
  if ($sort == $i) {
    $link[$i] = $sorttype;
  }
  else {
    $link[$i] = ($i == 1 ? "asc" : "desc");
  }
}
?>
<th style="padding: 0px;width:45px;" class="unsortable"><?php echo $lang_functions['col_type'] ?></th>
<th value="1"><a href="?<?php echo $oldlink?>sort=1&amp;type=<?php echo $link[1]?>"><?php echo $lang_functions['col_name'] ?></a></th>
<?php

if (isset($wait) && $wait)
{
  print('<th class="unsortable">'.$lang_functions['col_wait']."</th>\n");
}
 ?>
<th value="3"><a href="?<?php echo $oldlink?>sort=3&amp;type=<?php echo $link[3]?>"><img class="comments" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="comments" title="<?php echo $lang_functions['title_number_of_comments'] ?>" /></a></th>

<th value="4"><a href="?<?php echo $oldlink?>sort=4&amp;type=<?php echo $link[4]?>"><img class="time" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="time" title="<?php echo  $lang_functions['title_time_added']?>" /></a></th>
<th value="5"><a href="?<?php echo $oldlink?>sort=5&amp;type=<?php echo $link[5]?>"><img class="size" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="size" title="<?php echo $lang_functions['title_size'] ?>" /></a></th>
<th value="7"><a href="?<?php echo $oldlink?>sort=7&amp;type=<?php echo $link[7]?>"><img class="seeders" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="seeders" title="<?php echo $lang_functions['title_number_of_seeders'] ?>" /></a></th>
<th value="8"><a href="?<?php echo $oldlink?>sort=8&amp;type=<?php echo $link[8]?>"><img class="leechers" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="leechers" title="<?php echo $lang_functions['title_number_of_leechers'] ?>" /></a></th>
<th value="6"><a href="?<?php echo $oldlink?>sort=6&amp;type=<?php echo $link[6]?>"><img class="snatched" src="//<?php echo $BASEURL; ?>/pic/trans.gif" alt="snatched" title="<?php echo $lang_functions['title_number_of_snatched']?>" /></a></th>
<th value="9"><a href="?<?php echo $oldlink?>sort=9&amp;type=<?php echo $link[9]?>"><?php echo $lang_functions['col_uploader']?></a></th>
<?php if (get_user_class() >= $torrentmanage_class): ?>
<th class="unsortable"><?php echo $lang_functions['col_action']; ?></th>
<?php endif; ?>
</tr>
</thead>
<tbody>
<?php
  }

  if (!$onlyhead) {
$caticonrow = get_category_icon_row($CURUSER['caticon']);
if ($caticonrow['secondicon'] == 'yes')
$has_secondicon = true;
else $has_secondicon = false;

if ($smalldescription_main == 'no')
  $displaysmalldescr = false;
else $displaysmalldescr = true;
GLOBAL $stickylimit;//置顶种子总数限制
foreach($rows as $row)
{
  if($row['banned'] == 'no' 
     || ($row['banned'] == 'yes' 
        && (get_user_class() >= $seebanned_class 
            || $CURUSER['id'] == $row['owner']))) {
    $id = $row["id"];
#    $sphighlight = get_torrent_bg_color($row['sp_state']);
#    print("<tr" . $sphighlight . ">\n");

    print('<tr><td class="rowfollow nowrap category-icon">');
    if (isset($row["category"])) {
      print(return_category_image($row["category"], "//$BASEURL/torrents.php?"));
      if ($has_secondicon){
        print(get_second_icon($row, "pic/".$catimgurl."additional/"));
      }
    }
    else
      print("-");
    print("</td>\n");

    //torrent name
    $dispname = trim($row["name"]);
    $short_torrent_name_alt = "";
    $mouseovertorrent = "";
    $tooltipblock = "";
    $has_tooltip = false;

    if (!$has_tooltip)
      $short_torrent_name_alt = "title=\"".htmlspecialchars($dispname)."\"";
    /* else{ */
    /* $torrent_tooltip[$counter]['id'] = "torrent_" . $counter; */
    /* $torrent_tooltip[$counter]['content'] = ""; */
    /* $mouseovertorrent = "onmouseover=\"get_ext_info_ajax('".$torrent_tooltip[$counter]['id']."','".$url."','".$cache."','".$type."'); domTT_activate(this, event, 'content', document.getElementById('" . $torrent_tooltip[$counter]['id'] . "'), 'trail', false, 'delay',600,'lifetime',6000,'fade','both','styleClass','niceTitle', 'fadeMax',87, 'maxWidth', 500);\""; */
    /* } */
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
    if ($row['pos_state'] =='sticky' ||  (($row['pos_state']=='random')&&($row['lucky']=="true"))){
      $stickyicon = "<img class=\"sticky\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Sticky\" title=\"".$lang_functions['title_sticky'].$lang_functions['text_until'].$row['pos_state_until']."\" />";
    }
    else $stickyicon = "";
    
    if ($displaysmalldescr) {
      $dissmall_descr = trim($row["small_descr"]);
      $count_dissmall_descr=mb_strlen($dissmall_descr,"UTF-8");
      $max_lenght_of_small_descr=$max_length_of_torrent_name; // maximum length
      if($count_dissmall_descr > $max_lenght_of_small_descr)
      {
        $dissmall_descr=mb_substr($dissmall_descr, 0, $max_lenght_of_small_descr-2,"UTF-8") . "..";
      }

    if ($swap_headings && $dissmall_descr != '') {
       $buf = $dispname;
       $dispname = $dissmall_descr;
       $dissmall_descr = $buf;
    }
    }
    echo '<td class="torrent"><div>';

    $color = '';
    $title = '';
    if ($row['owner'] == $CURUSER['id']) {
      $color = 'uploaded';
      $width = 125;
      $title = '由我上传';
      if (isset($progress[$row['id']])) {
	$p = $progress[$row['id']];
	if ($p['peer_id'] && $p['to_go'] == 0) {
	  $color .= ' seeding';
	  $title .= ', 正在做种';
	}
      }
    }
    else if (isset($progress[$row['id']])) {
      $p = $progress[$row['id']];
      if ($p['finished'] == 'yes') {
	$width = 125;
	if ($p['peer_id']) {
	  $color = 'seeding';
	  $title = '正在做种';
	}
	else {
	  $color = 'finished';
	  $title = '已完成';
	}
      }
      else if ($p['to_go'] == 0) {
	// 未下载，只做种
	$width = 125;
	if ($p['peer_id']) {
	  $color = 'seeding';
	  $title = '正在做种';
	}
	else {
	  $color = 'seeded';
	  $title = '曾做种';
	}
      }
      else {
	if ($p['peer_id']) {
	  $color = 'downloading';
	  $title = '正在下载';
	}
	else {
	  $color = 'downloaded';
	  $title = '已下载';
	}
	$percentage = round($p['to_go'] * 100 / $row['size']);
	$title .= ', 进度约为' . (100 - $percentage) . '%';
	$width = 95 - $percentage;
      }
    }

    if ($title) {
      echo '<div class="progress '.$color.'" title="' . $title . '" style="width:' . $width . '%"></div>';
    }
    
    echo '<div class="limit-width minor-list"';
    if ($title) {
      echo ' title="' . $title . '"';
    }
    echo '><div class="torrent-title">'.$stickyicon."<h2 class='transparentbg'><a $short_torrent_name_alt $mouseovertorrent href=\"//$BASEURL/details.php?id=".$id."&amp;hit=1\">".htmlspecialchars($dispname).'</a></h2><ul class="prs">';
    $sp_torrent = get_torrent_promotion_append($row['sp_state'],"",true,$row["added"], $row['promotion_time_type'], $row['promotion_until']);
    if ($sp_torrent != '') {
      $sp_torrent = '<li>' . $sp_torrent . '</li>';
    }

    $picked_torrent = "";

    if($row['picktype']=="hot")
    $picked_torrent = "<li>[<span class='hot'>".$lang_functions['text_hot']."</span>]</li>";
    elseif($row['picktype']=="classic")
    $picked_torrent = "<li>[<span class='classic'>".$lang_functions['text_classic']."</span>]</li>";
    elseif($row['picktype']=="recommended")
    $picked_torrent = "<li>[<span class='recommended'>".$lang_functions['text_recommended']."</span>]</li>";
    //Added by bluemonster 20111026
    if($row['oday']=="yes")
    {
      if ($forcemode == "" || $forcemode == 'icon'){  
      //$picked_torrent = " <b>[<font class='recommended'>".$lang_functions['text_oday']."</font>]</b>";
      $sp_torrent.="<li><img class=\"oday\" src=\"pic/trans.gif\" border=0 alt=\"0day\" title=\"".$lang_functions['text_oday']."\" /></li>";
      }
      else if ($forcemode == 'word'){
-     $sp_torrent.= "<li>[<span class='oday' ".$onmouseover.">".$lang_functions['text_oday']."</span>]</li>";
      }
    }

    if($row['storing']==1)
    {
      if ( $forcemode == "" || $forcemode == 'icon'){  
      $sp_torrent.="<li><img class=\"storing\" src=\"pic/trans.gif\" alt=\"storing\" title=\"".$lang_functions['text_storing']."\" /></li>";
      }
      else if ($forcemode == 'word'){
-     $sp_torrent.= "<li>[<span class='storing' ".$onmouseover.">".$lang_functions['text_storing']."</span>]</li>";
			}
    }
    
    if (strtotime($row["added"]) >= $last_browse) {
      print("<li>(<span class='new'>".$lang_functions['text_new_uppercase']."</span>)</li>");
      $is_new = true;
    }

    $banned_torrent = ($row["banned"] == 'yes' ? " <li>(<span class=\"striking\">".$lang_functions['text_banned']."</span>)</li>" : "");
    print($banned_torrent.$picked_torrent.$sp_torrent .'</ul></div><div class="torrent-title">' );
    if ($displaysmalldescr){
      //small descr
      $escape_desc = htmlspecialchars($dissmall_descr);
      print($dissmall_descr == "" ? "<h3 class='placeholder'></h3>" : '<h3 title="' . $escape_desc . '">'. $escape_desc . '</h3>');
    }
    echo '</div>';
    
      $act = "";
      if ($CURUSER["downloadpos"] != "no")
      $act .= "<li><a href=\"//$BASEURL/download.php?id=".$id."\"><img class=\"download\" src=\"//$BASEURL/pic/trans.gif\" style='padding-bottom: 2px;' alt=\"download\" title=\"".$lang_functions['title_download_torrent']."\" /></a></li>" ;
        $act .= "<li><a class=\"bookmark\" torrent=\"$id\" href=\"#\">".get_torrent_bookmark_state($CURUSER['id'], $id)."</a></li>";
      

    print('<div class="torrent-utilty-icons minor-list-vertical"><ul>'.$act."</ul></div>\n");

    print('</td>');
    if (isset($wait) && $wait) {
      $elapsed = floor((TIMENOW - strtotime($row["added"])) / 3600);
      if ($elapsed < $wait)
      {
        $color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
        print("<td class=\"rowfollow nowrap\"><a href=\"//$BASEURL/faq.php#id46\"><font color=\"".$color."\">" . number_format($wait - $elapsed) . $lang_functions['text_h']."</font></a></td>\n");
      }
      else
      print("<td class=\"rowfollow nowrap\">".$lang_functions['text_none']."</td>\n");
    }
    

    print("<td class=\"rowfollow\">");
    $nl = "";

    //comments

    $nl = "<br />";
    if (!$row["comments"]) {
      print("<a href=\"//$BASEURL/comment.php?action=add&amp;pid=".$id."&amp;type=torrent\" title=\"".$lang_functions['title_add_comments']."\">" . $row["comments"] .  "</a>");
    } else {
      $onmouseover = "";

      if ($enabletooltip_tweak == 'yes') {
        if (!$lastcom = $Cache->get_value('torrent_'.$id.'_last_comment_content')){
          $res2 = sql_query("SELECT user, added, text FROM comments WHERE torrent = $id ORDER BY id DESC LIMIT 1");
          $lastcom = _mysql_fetch_array($res2);
          $Cache->cache_value('torrent_'.$id.'_last_comment_content', $lastcom, 1855);
        }
        $timestamp = strtotime($lastcom["added"]);
        $hasnewcom = ($lastcom['user'] != $CURUSER['id'] && $timestamp >= $last_browse);
        if ($lastcom)
        {
            $lastcomtime = $lang_functions['text_at_time'].$lastcom['added'];
            $lastcom_tooltip[$counter]['id'] = "lastcom_" . $counter;
            $lastcom_tooltip[$counter]['content'] = ($hasnewcom ? "<b>(<font class='new'>".$lang_functions['text_new_uppercase']."</font>)</b> " : "").$lang_functions['text_last_commented_by'].get_username($lastcom['user']) . $lastcomtime."<br />". format_comment(mb_substr($lastcom['text'],0,100,"UTF-8") . (mb_strlen($lastcom['text'],"UTF-8") > 100 ? " ......" : "" ),true,false,false,true,600,false,false);
            $onmouseover = "onmouseover=\"domTT_activate(this, event, 'content', document.getElementById('" . $lastcom_tooltip[$counter]['id'] . "'), 'trail', false, 'delay', 500,'lifetime',3000,'fade','both','styleClass','niceTitle','fadeMax', 87,'maxWidth', 400);\"";
        }
      } else {
        $hasnewcom = false;
      }
      print("<b><a href=\"//$BASEURL/details.php?id=".$id."&amp;hit=1&amp;cmtpage=1#startcomments\" ".$onmouseover.">". ($hasnewcom ? "<span class='new'>" : ""). $row["comments"] .($hasnewcom ? "</span>" : ""). "</a></b>");
      if ($hasnewcom) {
	$is_new = true;
      }
    }

    print("</td>");
    

    $time = $row["added"];
    $time = gettime($time,false,true);
    print("<td class=\"rowfollow nowrap\">". $time. "</td>");

    //size
    print("<td class=\"rowfollow\">" . mksize_compact($row["size"])."</td>");

    if ($row["leechers"] < 0) {
      $row["leechers"] = 0;
    }
    if ($row["seeders"] < 0) {
      $row["seeders"] = 0;
    }
    
    if ($row["seeders"]) {
        $ratio = ($row["leechers"] ? ($row["seeders"] / $row["leechers"]) : 1);
        $ratiocolor = get_slr_color($ratio);
        print("<td class=\"rowfollow\" align=\"center\"><b><a href=\"//$BASEURL/details.php?id=".$id."&amp;hit=1&amp;dllist=1#seeders\"".($ratiocolor ? " style=\"color:" . $ratiocolor . "\">" : '>') . number_format($row["seeders"])."</a></b></td>\n");
    }
    else
      print("<td class=\"rowfollow\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . number_format($row["seeders"]) . "</span></td>\n");

    if ($row["leechers"]) {
      print("<td class=\"rowfollow\"><b><a href=\"//$BASEURL/details.php?id=".$id."&amp;hit=1&amp;dllist=1#leechers\">" .
      number_format($row["leechers"]) . "</a></b></td>\n");
    }
    else
      print("<td class=\"rowfollow\">0</td>\n");

    if ($row["times_completed"] >=1)
    print("<td class=\"rowfollow\"><a href=\"//$BASEURL/viewsnatches.php?id=".$row['id']."\"><b>" . number_format($row["times_completed"]) . "</b></a></td>\n");
    else
    print("<td class=\"rowfollow\">" . number_format($row["times_completed"]) . "</td>\n");

      if ($row["anonymous"] == "yes" && get_user_class() >= $torrentmanage_class)
      {
        print("<td class=\"rowfollow\" align=\"center\">".$lang_functions['text_anonymous']."<br />".(isset($row["owner"]) ? "(" . get_username($row["owner"]) .")" : $lang_functions['text_orphaned']) . "</td>\n");
      }
      elseif ($row["anonymous"] == "yes")
      {
        print("<td class=\"rowfollow\"><i>".$lang_functions['text_anonymous']."</i></td>\n");
      }
      else
      {
        print("<td class=\"rowfollow\">" . (isset($row["owner"]) ? get_username($row["owner"]) : "<i>".$lang_functions['text_orphaned']."</i>") . "</td>\n");
      }

    if (get_user_class() >= $torrentmanage_class) {
      if (isset($_REQUEST['format'])) {
	$returnto = $_REQUEST;
	unset($returnto['format']);
	unset($returnto['counter']);
	$returnto = $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($returnto);
      }
      else {
	$returnto = $_SERVER["REQUEST_URI"];
      }
      print('<td class="rowfollow"><div class="minor-list-vertical"><ul><li><a class="staff-quick-delete" href="'.htmlspecialchars('//' . $BASEURL . '/edit.php?id='.$row['id']).'#delete"><img class="staff_delete" src="//' . $BASEURL . '/pic/trans.gif" alt="D" title="'.$lang_functions['text_delete'].'" /></a></li>');
      print("<li><a class=\"staff-quick-edit\" href=\"//$BASEURL/edit.php?returnto=" . rawurlencode($returnto) . "&amp;id=" . $row["id"] . "\"><img class=\"staff_edit\" src=\"//$BASEURL/pic/trans.gif\" alt=\"E\" title=\"".$lang_functions['text_edit']."\" /></a></li></ul></div></td>\n");
    }
    print("</tr>\n");
    $counter++;
  }
}
}

if ($header) {
  echo '</tbody></table>';
}

if ($is_new) {
  if($variant == "music"){
    $USERUPDATESET[] = "last_music = ".TIMENOW;
    $CURUSER['last_music'] = TIMENOW;
  }
  else {
    $USERUPDATESET[] = "last_browse = ".TIMENOW;
    $CURUSER['last_browse'] = TIMENOW;
  }
  cache_user_row();
}

if($enabletooltip_tweak == 'yes') {
  if ($splitcomment) {
    ob_start();
  }

  if (isset($lastcom_tooltip)) {
    create_tooltip_container($lastcom_tooltip, 400);
  }

  if ($splitcomment) {
    return ob_get_clean();
  }
}
}

function get_user_prop($id) {
  $user = get_user_row($id);
  if ($user) {
    $out = array();
    $out['id'] = $user['id'];
    $out['username'] = $user['username'];
    $out['class'] = array('raw' => $user['class'], 'canonical' => get_user_class_name($user['class'],false));

    if ($user['donor'] == 'yes') {
      $out['donor'] = true;
    }
  }
  else {
    $out = null;
  }
  return $out;
}

function get_username($id, $big = false, $link = true, $bold = true, $target = false, $bracket = false, $withtitle = false, $link_ext = "", $underline = false) {
  static $usernameArray = array();
  global $lang_functions;
  global $BASEURL;

  $id = 0+$id;

  if (func_num_args() == 1 && array_key_exists($id, $usernameArray)) {  //One argument=is default display of username. Get it directly from static array if available
    return $usernameArray[$id];
  }
  $arr = get_user_row($id);

  if ($arr){
    if ($big) {
      $donorpic = "starbig";
      $leechwarnpic = "leechwarnedbig";
      $warnedpic = "warnedbig";
      $disabledpic = "disabledbig";
      $style = "style='margin-left: 4pt'";
    }
    else {
      $donorpic = "star";
      $leechwarnpic = "leechwarned";
      $warnedpic = "warned";
      $disabledpic = "disabled";
      $style = 'style="margin-left: 2pt"';
    }
    $pics = $arr["donor"] == "yes" ? "<img class=\"".$donorpic."\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Donor\" ".$style." />" : "";

    if ($arr["enabled"] == "yes")
      $pics .= ($arr["leechwarn"] == "yes" ? "<img class=\"".$leechwarnpic."\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Leechwarned\" ".$style." />" : "") . ($arr["warned"] == "yes" ? "<img class=\"".$warnedpic."\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Warned\" ".$style." />" : "");
    else
      $pics .= "<img class=\"".$disabledpic."\" src=\"//$BASEURL/pic/trans.gif\" alt=\"Disabled\" ".$style." />\n";

    $username = $arr['username'];
    $name_style = '';
    if ($bold) {
      $name_style .= 'font-weight:bold;';
    }
    if ($underline) {
      $name_style .= 'text-decoration:underline;';
    }
    if(!is_null($arr["color"])){
      $name_style .= 'color:#'.$arr['color'] . ';';
    }

    if ($name_style) {
      $link_ext .= ' style="' . $name_style . '"';
    }

    if ($link) {
      $link_ext .= ' class="'. get_user_class_name($arr['class'],true) . '_Name"';
      $username = '<a ' . $link_ext . ' href="//' . $BASEURL . '/userdetails.php?id=' . $id . '">' . $username . '</a>';
    }
    $username .= $pics;

    if ($withtitle) {
      $username .= "(";
      if ($arr['title'] == "") {
	$username .= get_user_class_name($arr['class'],false,true,true);
      }
      else {
	$username .= "<span class='".get_user_class_name($arr['class'],true) . "_Name'>".htmlspecialchars($arr['title']) . "</span>";
      }
      $username .= ")";
    }
  }
  else {
    $username = $lang_functions['text_orphaned'];
  }
  $username = "<span class=\"username nowrap\" userid=\"" . $id . "\">" . ( $bracket == true ? "(" . $username . ")" : $username) . "</span>";
  
  if (func_num_args() == 1) { //One argument=is default display of username, save it in static array
    $usernameArray[$id] = $username;
  }
  return $username;
}

function get_percent_completed_image($p) {
  global $BASEURL;

  $maxpx = "45"; // Maximum amount of pixels for the progress bar

  if ($p == 0) $progress = "<img class=\"progbarrest\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($maxpx) . "px;\" alt=\"\" />";
  if ($p == 100) $progress = "<img class=\"progbargreen\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($maxpx) . "px;\" alt=\"\" />";
  if ($p >= 1 && $p <= 30) $progress = "<img class=\"progbarred\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($p*($maxpx/100)) . "px;\" alt=\"\" /><img class=\"progbarrest\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ((100-$p)*($maxpx/100)) . "px;\" alt=\"\" />";
  if ($p >= 31 && $p <= 65) $progress = "<img class=\"progbaryellow\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($p*($maxpx/100)) . "px;\" alt=\"\" /><img class=\"progbarrest\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ((100-$p)*($maxpx/100)) . "px;\" alt=\"\" />";
  if ($p >= 66 && $p <= 99) $progress = "<img class=\"progbargreen\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($p*($maxpx/100)) . "px;\" alt=\"\" /><img class=\"progbarrest\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ((100-$p)*($maxpx/100)) . "px;\" alt=\"\" />";
  return "<img class=\"bar_left\" src=\"//$BASEURL/pic/trans.gif\" alt=\"\" />" . $progress ."<img class=\"bar_right\" src=\"//$BASEURL/pic/trans.gif\" alt=\"\" />";
}

function get_ratio_img($ratio)
{
  if ($ratio >= 16)
  $s = "163";
  else if ($ratio >= 8)
  $s = "117";
  else if ($ratio >= 4)
  $s = "5";
  else if ($ratio >= 2)
  $s = "3";
  else if ($ratio >= 1)
  $s = "2";
  else if ($ratio >= 0.5)
  $s = "34";
  else if ($ratio >= 0.25)
  $s = "10";
  else
  $s = "52";

  return "<img src=\"pic/smilies/".$s.".gif\" alt=\"\" />";
}

function GetVar ($name) {
  if ( is_array($name) ) {
    foreach ($name as $var) GetVar ($var);
  } else {
    if ( !isset($_REQUEST[$name]) )
    return false;
    $GLOBALS[$name] = $_REQUEST[$name];
    return $GLOBALS[$name];
  }
}

function ssr ($arg) {
  if (is_array($arg)) {
    foreach ($arg as $key=>$arg_bit) {
      $arg[$key] = ssr($arg_bit);
    }
  } else {
    $arg = stripslashes($arg);
  }
  return $arg;
}

function parked()
{
  global $lang_functions;
  global $CURUSER;
  if ($CURUSER["parked"] == "yes")
  stderr($lang_functions['std_access_denied'], $lang_functions['std_your_account_parked']);
}

function validusername($username)
{
  if ($username == "")
  return false;

  // The following characters are allowed in user names
  $allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  for ($i = 0; $i < strlen($username); ++$i)
  if (strpos($allowedchars, $username[$i]) === false)
  return false;

  return true;
}

//Code for Viewing NFO file

// code: Takes a string and does a IBM-437-to-HTML-Unicode-Entities-conversion.
// swedishmagic specifies special behavior for Swedish characters.
// Some Swedish Latin-1 letters collide with popular DOS glyphs. If these
// characters are between ASCII-characters (a-zA-Z and more) they are
// treated like the Swedish letters, otherwise like the DOS glyphs.
function code($ibm_437, $swedishmagic = false) {
$table437 = array("\200", "\201", "\202", "\203", "\204", "\205", "\206", "\207",
"\210", "\211", "\212", "\213", "\214", "\215", "\216", "\217", "\220",
"\221", "\222", "\223", "\224", "\225", "\226", "\227", "\230", "\231",
"\232", "\233", "\234", "\235", "\236", "\237", "\240", "\241", "\242",
"\243", "\244", "\245", "\246", "\247", "\250", "\251", "\252", "\253",
"\254", "\255", "\256", "\257", "\260", "\261", "\262", "\263", "\264",
"\265", "\266", "\267", "\270", "\271", "\272", "\273", "\274", "\275",
"\276", "\277", "\300", "\301", "\302", "\303", "\304", "\305", "\306",
"\307", "\310", "\311", "\312", "\313", "\314", "\315", "\316", "\317",
"\320", "\321", "\322", "\323", "\324", "\325", "\326", "\327", "\330",
"\331", "\332", "\333", "\334", "\335", "\336", "\337", "\340", "\341",
"\342", "\343", "\344", "\345", "\346", "\347", "\350", "\351", "\352",
"\353", "\354", "\355", "\356", "\357", "\360", "\361", "\362", "\363",
"\364", "\365", "\366", "\367", "\370", "\371", "\372", "\373", "\374",
"\375", "\376", "\377");

$tablehtml = array("&#x00c7;", "&#x00fc;", "&#x00e9;", "&#x00e2;", "&#x00e4;",
"&#x00e0;", "&#x00e5;", "&#x00e7;", "&#x00ea;", "&#x00eb;", "&#x00e8;",
"&#x00ef;", "&#x00ee;", "&#x00ec;", "&#x00c4;", "&#x00c5;", "&#x00c9;",
"&#x00e6;", "&#x00c6;", "&#x00f4;", "&#x00f6;", "&#x00f2;", "&#x00fb;",
"&#x00f9;", "&#x00ff;", "&#x00d6;", "&#x00dc;", "&#x00a2;", "&#x00a3;",
"&#x00a5;", "&#x20a7;", "&#x0192;", "&#x00e1;", "&#x00ed;", "&#x00f3;",
"&#x00fa;", "&#x00f1;", "&#x00d1;", "&#x00aa;", "&#x00ba;", "&#x00bf;",
"&#x2310;", "&#x00ac;", "&#x00bd;", "&#x00bc;", "&#x00a1;", "&#x00ab;",
"&#x00bb;", "&#x2591;", "&#x2592;", "&#x2593;", "&#x2502;", "&#x2524;",
"&#x2561;", "&#x2562;", "&#x2556;", "&#x2555;", "&#x2563;", "&#x2551;",
"&#x2557;", "&#x255d;", "&#x255c;", "&#x255b;", "&#x2510;", "&#x2514;",
"&#x2534;", "&#x252c;", "&#x251c;", "&#x2500;", "&#x253c;", "&#x255e;",
"&#x255f;", "&#x255a;", "&#x2554;", "&#x2569;", "&#x2566;", "&#x2560;",
"&#x2550;", "&#x256c;", "&#x2567;", "&#x2568;", "&#x2564;", "&#x2565;",
"&#x2559;", "&#x2558;", "&#x2552;", "&#x2553;", "&#x256b;", "&#x256a;",
"&#x2518;", "&#x250c;", "&#x2588;", "&#x2584;", "&#x258c;", "&#x2590;",
"&#x2580;", "&#x03b1;", "&#x00df;", "&#x0393;", "&#x03c0;", "&#x03a3;",
"&#x03c3;", "&#x03bc;", "&#x03c4;", "&#x03a6;", "&#x0398;", "&#x03a9;",
"&#x03b4;", "&#x221e;", "&#x03c6;", "&#x03b5;", "&#x2229;", "&#x2261;",
"&#x00b1;", "&#x2265;", "&#x2264;", "&#x2320;", "&#x2321;", "&#x00f7;",
"&#x2248;", "&#x00b0;", "&#x2219;", "&#x00b7;", "&#x221a;", "&#x207f;",
"&#x00b2;", "&#x25a0;", "&#x00a0;");


// 0-9, 11-12, 14-31, 127 (decimalt)
$control =
array("\000", "\001", "\002", "\003", "\004", "\005", "\006", "\007",
"\010", "\011", /*"\012",*/ "\013", "\014", /*"\015",*/ "\016", "\017",
"\020", "\021", "\022", "\023", "\024", "\025", "\026", "\027",
"\030", "\031", "\032", "\033", "\034", "\035", "\036", "\037",
"\177");

/* Code control characters to control pictures.
http://www.unicode.org/charts/PDF/U2400.pdf
(This is somewhat the Right Thing, but looks crappy with Courier New.)
$controlpict = array("&#x2423;","&#x2404;");
$s = str_replace($control,$controlpict,$s); */

// replace control chars with space - feel free to fix the regexp smile.gif
/*echo "[a\\x00-\\x1F]";
//$s = preg_replace("/[ \\x00-\\x1F]/", " ", $s);
$s = preg_replace("/[ \000-\037]/", " ", $s); */
$s = str_replace($control," ",$ibm_437);

$s = str_replace("&", "&amp;", $s); 
$s = str_replace("<", "&lt;", $s); 

if ($swedishmagic){
$s = str_replace("\345","\206",$s);
$s = str_replace("\344","\204",$s);
$s = str_replace("\366","\224",$s);
// $s = str_replace("\304","\216",$s);
//$s = "[ -~]\\xC4[a-za-z]";

// couldn't get ^ and $ to work, even through I read the man-pages,
// i'm probably too tired and too unfamiliar with posix regexps right now.
$s = preg_replace("/([ -~])\305([ -~])/", "\\1\217\\2", $s);
$s = preg_replace("/([ -~])\304([ -~])/", "\\1\216\\2", $s);
$s = preg_replace("/([ -~])\326([ -~])/", "\\1\231\\2", $s);

$s = str_replace("\311", "\220", $s); //
$s = str_replace("\351", "\202", $s); //
}

$s = str_replace($table437, $tablehtml, $s);
return $s;
}


//Tooltip container for hot movie, classic movie, etc
function create_tooltip_container($id_content_arr, $width = 400)
{
  if(count($id_content_arr))
  {
    $result = "<div id=\"tooltipPool\" style=\"display: none\">";
    foreach($id_content_arr as $id_content_arr_each)
    {
      $result .= "<div id=\"" . $id_content_arr_each['id'] . "\">" . $id_content_arr_each['content'] . "</div>";
    }
    $result .= "</div>";
    print($result);
  }
}

function getimdb($imdb_id, $cache_stamp, $mode = 'minor') {
  global $lang_functions;
  global $BASEURL;
  global $showextinfo;

  $thenumbers = $imdb_id;
  $movie = new imdb ($thenumbers);
  $movieid = $thenumbers;
  $movie->setid ($movieid);

  $target = array('Title', 'Credits', 'Plot');
  switch ($movie->cachestate($target))
  {
    case "0": //cache is not ready
      {
      return false;
      break;
      }
    case "1": //normal
      {
        $title = $movie->title ();
        $year = $movie->year ();
        $country = $movie->country ();
        $countries = "";
        $temp = "";
        for ($i = 0; $i < count ($country); $i++)
        {
          $temp .="$country[$i], ";
        }
        $countries = rtrim(trim($temp), ",");

        $director = $movie->director();
        $director_or_creator = "";
        if ($director)
        {
          $temp = "";
          for ($i = 0; $i < count ($director); $i++)
          {
            $temp .= $director[$i]["name"].", ";
          }
          $director_or_creator = "<strong><font color=\"DarkRed\">".$lang_functions['text_director'].": </font></strong>".rtrim(trim($temp), ",");
        }
        else { //for tv series
          $creator = $movie->creator();
          $director_or_creator = "<strong><font color=\"DarkRed\">".$lang_functions['text_creator'].": </font></strong>".$creator;
        }
        $cast = $movie->cast();
        $temp = "";
        for ($i = 0; $i < count ($cast); $i++) //get names of first three casts
        {
          if ($i > 2)
          {
            break;
          }
          $temp .= $cast[$i]["name"].", ";
        }
        $casts = rtrim(trim($temp), ",");
        $gen = $movie->genres();
        $genres = $gen[0].(count($gen) > 1 ? ", ".$gen[1] : ""); //get first two genres;
        $rating = $movie->rating ();
        $votes = $movie->votes ();
        if ($votes)
          $imdbrating = "<b>".$rating."</b>/10 (".$votes.$lang_functions['text_votes'].")";
        else $imdbrating = $lang_functions['text_awaiting_five_votes'];

        $tagline = $movie->tagline ();
        switch ($mode)
        {
        case 'minor' : 
          {
          $autodata = "<font class=\"big\"><b>".$title."</b></font> (".$year.") <br /><strong><font color=\"DarkRed\">".$lang_functions['text_imdb'].": </font></strong>".$imdbrating." <strong><font color=\"DarkRed\">".$lang_functions['text_country'].": </font></strong>".$countries." <strong><font color=\"DarkRed\">".$lang_functions['text_genres'].": </font></strong>".$genres."<br />".$director_or_creator."<strong><font color=\"DarkRed\"> ".$lang_functions['text_starring'].": </font></strong>".$casts."<br /><p><strong>".$tagline."</strong></p>";
          break;
          }
        case 'median':
          {
          if (($photo_url = $movie->photo_localurl() ) != FALSE)
            $smallth = "<img src=\"".$photo_url. "\" width=\"105\" alt=\"poster\" />";
          else $smallth = "";
          $runtime = $movie->runtime ();
          $language = $movie->language ();
          $plot = $movie->plot ();
          $plots = "";
          if(count($plot) != 0){ //get plots from plot page
              $plots .= "<font color=\"DarkRed\">*</font> ".strip_tags($plot[0], '<br /><i>');
              $plots = mb_substr($plots,0,300,"UTF-8") . (mb_strlen($plots,"UTF-8") > 300 ? " ..." : "" );
              $plots .= (strpos($plots,"<i>") == true && strpos($plots,"</i>") == false ? "</i>" : "");//sometimes <i> is open and not ended because of mb_substr;
              $plots = "<font class=\"small\">".$plots."</font>";
            }
          elseif ($plotoutline = $movie->plotoutline ()){ //get plot from title page
            $plots .= "<font color=\"DarkRed\">*</font> ".strip_tags($plotoutline, '<br /><i>');
            $plots = mb_substr($plots,0,300,"UTF-8") . (mb_strlen($plots,"UTF-8") > 300 ? " ..." : "" );
            $plots .= (strpos($plots,"<i>") == true && strpos($plots,"</i>") == false ? "</i>" : "");//sometimes <i> is open and not ended because of mb_substr;
            $plots = "<font class=\"small\">".$plots."</font>";
            }
          $autodata = "<table style=\"background-color: transparent;\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">
".($smallth ? "<td class=\"clear\" valign=\"top\" align=\"right\">
$smallth
</td>" : "")
."<td class=\"clear\" valign=\"top\" align=\"left\">
<table style=\"background-color: transparent;\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"350\">
<tr><td class=\"clear\" colspan=\"2\"><img class=\"imdb\" src=\"//$BASEURL/pic/trans.gif\" alt=\"imdb\" /> <font class=\"big\"><b>".$title."</b></font> (".$year.") </td></tr>
<tr><td class=\"clear\"><strong><font color=\"DarkRed\">".$lang_functions['text_imdb'].": </font></strong>".$imdbrating."</td>
".( $runtime ? "<td class=\"clear\"><strong><font color=\"DarkRed\">".$lang_functions['text_runtime'].": </font></strong>".$runtime.$lang_functions['text_min']."</td>" : "<td class=\"clear\"></td>")."</tr>
<tr><td class=\"clear\"><strong><font color=\"DarkRed\">".$lang_functions['text_country'].": </font></strong>".$countries."</td>
".( $language ? "<td class=\"clear\"><strong><font color=\"DarkRed\">".$lang_functions['text_language'].": </font></strong>".$language."</td>" : "<td class=\"clear\"></td>")."</tr>
<tr><td class=\"clear\">".$director_or_creator."</td>
<td class=\"clear\"><strong><font color=\"DarkRed\">".$lang_functions['text_genres'].": </font></strong>".$genres."</td></tr>
<tr><td class=\"clear\" colspan=\"2\"><strong><font color=\"DarkRed\">".$lang_functions['text_starring'].": </font></strong>".$casts."</td></tr>
".( $plots ? "<tr><td class=\"clear\" colspan=\"2\">".$plots."</td></tr>" : "")."
</table>
</td>
</table>";
          break;
          }
        }
        return $autodata;
      }
      case "2" : 
      {
        return false;
        break;
      }
      case "3" :
      {
        return false;
        break;
      }
  }
}

function quickreply($formname, $taname,$submit, $placeholder=""){
  global $lang_functions;
  echo '<textarea name="', $taname, '" cols="100" rows="8" style="width: 450px" onkeydown="' , "ctrlenter(event,'compose','qr')", '"', ($placeholder ? ' placeholder="' . $placeholder . '"' : ''), '></textarea>';
  print(smile_row($formname, $taname));
  echo '<br />';
  echo '<input type="submit" id="qr" class="btn" value="', $submit, '" />';
}

function smile_row($formname, $taname){
  $quickSmilesNumbers = array(4, 5, 39, 25, 11, 8, 10, 15, 27, 57, 42, 122, 52, 28, 29, 30, 176);
  $smilerow = "<div align=\"center\">";
  foreach ($quickSmilesNumbers as $smilyNumber) {
    $smilerow .= getSmileIt($formname, $taname, $smilyNumber);
  }
  $smilerow .= "</div>";
  return $smilerow;
}
function getSmileIt($formname, $taname, $smilyNumber) {
  return "<a href=\"#\" class=\"smileit\" smile=\"$smilyNumber\" form=\"$formname\"><img style=\"max-width: 25px;\" src=\"pic/smilies/$smilyNumber.gif\" alt=\"\" /></a>";
}

function classlist($selectname,$maxclass, $selected, $minClass = 0){
  $list = "<select name=\"".$selectname."\">";
  for ($i = $minClass; $i <= $maxclass; $i++)
    $list .= "<option value=\"".$i."\"" . ($selected == $i ? " selected=\"selected\"" : "") . ">" . get_user_class_name($i,false,false,true) . "</option>\n";
  $list .= "</select>";
  return $list;
}

function permissiondenied(){
  global $lang_functions;
  header('HTTP/1.1 403 Forbidden');
  stderr($lang_functions['std_error'], $lang_functions['std_permission_denied']);
}

function gettime($time, $withago = true, $twoline = false, $forceago = false, $oneunit = false, $isfuturetime = false){
  global $lang_functions, $CURUSER;
  if (!$forceago){
    $newtime = $time;
    if ($twoline){
    $newtime = str_replace(" ", "<br />", $newtime);
    }
  }
  else{
    $timestamp = strtotime($time);
    if ($isfuturetime && $timestamp < TIMENOW)
      $newtime = false;
    else
    {
      $newtime = get_elapsed_time($timestamp,$oneunit).($withago ? $lang_functions['text_ago'] : "");
      if($twoline){
        $newtime = str_replace("&nbsp;", "<br />", $newtime);
      }
      elseif($oneunit){
        if ($length = strpos($newtime, "&nbsp;"))
          $newtime = substr($newtime,0,$length);
      }
      else $newtime = str_replace("&nbsp;", $lang_functions['text_space'], $newtime);
      $newtime = "<span title=\"".$time."\">".$newtime."</span>";
    }
  }
  return $newtime;
}

function get_forum_pic_folder_for($lang) {
  return "pic/forum_pic/".$lang;
}

function get_forum_pic_folder(){
  global $CURLANGDIR;
  return get_forum_pic_folder_for($CURLANGDIR);
}

function get_category_icon_rows() {
  global $Cache;
  static $rows;
  if (!$rows && !$rows = $Cache->get_value('category_icon_content')){
    $rows = array();
    $res = sql_query("SELECT * FROM caticons ORDER BY id ASC");
    while($row = _mysql_fetch_array($res)) {
      $rows[$row['id']] = $row;
    }
    $Cache->cache_value('category_icon_content', $rows, 156400);
  }
  return $rows;
}

function get_category_icon_row($typeid) {
  if (!$typeid) {
    $typeid=1;
  }

  return get_category_icon_rows()[$typeid];
}
function get_category_row($catid = NULL)
{
  global $Cache;
  static $rows;
  if (!$rows && !$rows = $Cache->get_value('category_content')){
    $res = sql_query("SELECT categories.*, searchbox.name AS catmodename FROM categories LEFT JOIN searchbox ON categories.mode=searchbox.id");
    while($row = _mysql_fetch_array($res)) {
      $rows[$row['id']] = $row;
    }
    $Cache->cache_value('category_content', $rows, 126400);
  }

  if ($catid) {
    return $rows[$catid];
  } else {
    return $rows;
  }
}

function get_second_icon($row, $catimgurl) {//for CHDBits 
  global $CURUSER, $Cache;
  global $BASEURL;

  $source=$row['source'];
  $medium=$row['medium'];
  $codec=$row['codec'];
  $standard=$row['standard'];
  $processing=$row['processing'];
  $team=$row['team'];
  $audiocodec=$row['audiocodec'];
  if (!$sirow = $Cache->get_value('secondicon_'.$source.'_'.$medium.'_'.$codec.'_'.$standard.'_'.$processing.'_'.$team.'_'.$audiocodec.'_content')){
    $res = sql_query("SELECT * FROM secondicons WHERE (source = ".sqlesc($source)." OR source=0) AND (medium = ".sqlesc($medium)." OR medium=0) AND (codec = ".sqlesc($codec)." OR codec = 0) AND (standard = ".sqlesc($standard)." OR standard = 0) AND (processing = ".sqlesc($processing)." OR processing = 0) AND (team = ".sqlesc($team)." OR team = 0) AND (audiocodec = ".sqlesc($audiocodec)." OR audiocodec = 0) LIMIT 1");
    $sirow = _mysql_fetch_array($res);
    if (!$sirow)
      $sirow = 'not allowed';
    $Cache->cache_value('secondicon_'.$source.'_'.$medium.'_'.$codec.'_'.$standard.'_'.$processing.'_'.$team.'_'.$audiocodec.'_content', $sirow, 116400);
  }
  $catimgurl = get_cat_folder($row['category']);
  if ($sirow == 'not allowed')
    return "<img src=\"//$BASEURL/pic/cattrans.gif\" style=\"background-image: url(pic/". $catimgurl. "additional/notallowed.png);\" alt=\"" . $sirow["name"] . "\" alt=\"Not Allowed\" />";
  else {
    return "<img".($sirow['class_name'] ? " class=\"".$sirow['class_name']."\"" : "")." src=\"//$BASEURL/pic/cattrans.gif\" style=\"background-image: url(pic/". $catimgurl. "additional/". $sirow['image'].");\" alt=\"" . $sirow["name"] . "\" title=\"".$sirow['name']."\" />";
  }
}

function get_torrent_bg_color($promotion = 1)
{
  global $CURUSER;
  return "";
}

function get_torrent_promotion_append($promotion = 1,$forcemode = "",$showtimeleft = false, $added = "", $promotionTimeType = 0, $promotionUntil = '') {
  global $BASEURL;
  global $CURUSER,$lang_functions, $promotion_text;
	
	list($pr_state, $expire) = get_pr_state($promotion, $added, $promotionTimeType, $promotionUntil);
  if ($pr_state != 1) {
    if ($expire) {
      $cexpire = date("Y-m-d H:i:s", $expire);
      //$timeout = gettime($cexpire, false, false, true, false, true);
      //$ret = '[<span class="pr-limit" title="' . $cexpire . '">'.$lang_functions['text_will_end_in'].$timeout."</span>]";
    }
    else
    	$cexpire = $lang_functions['text_forever'];
  }
  else 
    return '';
  $cexpire = $lang_functions['text_until'].$cexpire;

  $prDict = $promotion_text[$pr_state -1];
  $text = $lang_functions[$prDict['lang']];

  $ret = '<img class="' . $prDict['name'] . '" alt="' . $text .'"title="'.$cexpire.'" src="//' . $BASEURL . '/pic/trans.gif" />';
  return $ret;
}

function get_user_id_from_name($username){
  global $Cache;
  return $Cache->get_value('user_id_for_name_' . $username, 7*86400, function() use ($username) {
      return get_single_value('users', 'id', "WHERE LOWER(username)=?", [strtolower($username)]);
    });
}

function is_forum_moderator($id, $in = 'post'){
  global $CURUSER;
  switch($in){
    case 'post':{
      $res = sql_query("SELECT topicid FROM posts WHERE id=$id") or sqlerr(__FILE__, __LINE__);
      if ($arr = _mysql_fetch_array($res)){
        if (is_forum_moderator($arr['topicid'],'topic'))
          return true;
      }
      return false;
      break;
    }
    case 'topic':{
      $modcount = sql_query("SELECT COUNT(forummods.userid) FROM forummods LEFT JOIN topics ON forummods.forumid = topics.forumid WHERE topics.id=$id AND forummods.userid=".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
      $arr = _mysql_fetch_array($modcount);
      if ($arr[0])
        return true;
      else return false;
      break;
    }
    case 'forum':{
      $modcount = get_row_count("forummods","WHERE forumid=$id AND userid=".sqlesc($CURUSER['id']));
      if ($modcount)
        return true;
      else return false;
      break;
    }
    default: {
    return false;
    }
  }
}

function get_guest_lang_id(){
  global $CURLANGDIR;
  $langfolder=$CURLANGDIR;
  $res = sql_query("SELECT id FROM language WHERE site_lang_folder=".sqlesc($langfolder)." AND site_lang=1");
  $row = _mysql_fetch_array($res);
  if ($row){
    return $row['id'];
  }
  else return 6;//return English
}

function set_forum_moderators($name, $forumid, $limit=3){
  $name = rtrim(trim($name), ",");
  $users = explode(",", $name);
  $userids = array();
  foreach ($users as $user){
    $userids[]=get_user_id_from_name(trim($user));
  }
  $max = count($userids);
  sql_query("DELETE FROM forummods WHERE forumid=".sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
  for($i=0; $i < $limit && $i < $max; $i++){
    sql_query("INSERT INTO forummods (forumid, userid) VALUES (".sqlesc($forumid).",".sqlesc($userids[$i]).")") or sqlerr(__FILE__, __LINE__);
  }
}

function get_plain_username($id){
  $row = get_user_row($id);  
  if ($row)
    $username = $row['username'];
  else $username = "";
  return $username;
}

function get_searchbox_value($mode = 1, $item = 'showsubcat'){
  global $Cache;
  static $rows;
  if (!$rows && !$rows = $Cache->get_value('searchbox_content')){
    $rows = array();
    $res = sql_query("SELECT * FROM searchbox ORDER BY id ASC");
    while ($row = _mysql_fetch_array($res)) {
      $rows[$row['id']] = $row;
    }
    $Cache->cache_value('searchbox_content', $rows, 100500);
  }
  return $rows[$mode][$item];
}

function get_ratio($userid, $html = true){
  global $lang_functions;
  $row = get_user_row($userid);
  $uped = $row['uploaded'];
  $downed = $row['downloaded'];
  if ($html == true){
    if ($downed > 0)
    {
      $ratio = $uped / $downed;
      $color = get_ratio_color($ratio);
      $ratio = number_format($ratio, 3);

      if ($color)
        $ratio = "<font color=\"".$color."\">".$ratio."</font>";
    }
    elseif ($uped > 0)
      $ratio = $lang_functions['text_inf'];
    else
      $ratio = "---";
  }
  else{
    if ($downed > 0)
    {
      $ratio = $uped / $downed;
    }
    else $ratio = 1;
  }
  return $ratio;
}

function add_s($num, $es = false)
{
  global $lang_functions;
  return ($num > 1 ? ($es ? $lang_functions['text_es'] : $lang_functions['text_s']) : "");
}

function is_or_are($num)
{
  global $lang_functions;
  return ($num > 1 ? $lang_functions['text_are'] : $lang_functions['text_is']);
}

function getmicrotime(){
  return microtime(true);
}

function get_user_class_image($class){
  static $UC = array(
    "Staff Leader" => "pic/staffleader.gif",
    "SysOp" => "pic/sysop.gif",
    "Administrator" => "pic/administrator.gif",
    "Moderator" => "pic/moderator.gif",
    "Forum Moderator" => "pic/forummoderator.gif",
    "Uploader" => "pic/uploader.gif",
    "Retiree" => "pic/retiree.gif",
    "VIP" => "pic/vip.gif",
    "Nexus Master" => "pic/nexus.gif",
    "Ultimate User" => "pic/ultimate.gif",
    "Extreme User" => "pic/extreme.gif",
    "Veteran User" => "pic/veteran.gif",
    "Insane User" => "pic/insane.gif",
    "Crazy User" => "pic/crazy.gif",
    "Elite User" => "pic/elite.gif",
    "Power User" => "pic/power.gif",
    "User" => "pic/user.gif",
    "Peasant" => "pic/peasant.gif"
  );
  if (isset($class))
    $uclass = $UC[get_user_class_name($class,false,false,false)];
  else $uclass = "pic/banned.gif";
  return $uclass;
}

function user_can_upload($where = "torrents"){
  global $CURUSER,$upload_class,$enablespecial,$uploadspecial_class;

  if ($CURUSER["uploadpos"] != 'yes')
    return false;
  if ($where == "torrents")
  {
    if (get_user_class() >= $upload_class)
      return true;
    if (get_if_restricted_is_open())
      return true;
  }
  if ($where == "music")
  {
    if ($enablespecial == 'yes' && get_user_class() >= $uploadspecial_class)
      return true;
  }
  return false;
}

function torrent_selection($name,$selname,$listname,$selectedid = 0)
{
  global $lang_functions;
  $selection = "<b>".$name."</b>&nbsp;<select name=\"".$selname."\">\n<option value=\"0\">".$lang_functions['select_choose_one']."</option>\n";
  $listarray = searchbox_item_list($listname);
  foreach ($listarray as $row)
    $selection .= "<option value=\"" . $row["id"] . "\"". ($row["id"]==$selectedid ? " selected=\"selected\"" : "").">" . htmlspecialchars($row["name"]) . "</option>\n";
  $selection .= "</select>&nbsp;&nbsp;&nbsp;\n";
  return $selection;
}

function get_hl_color($color=0)
{
  switch ($color){
    case 0: return false;
    case 1: return "Black";
    case 2: return "Sienna";
    case 3: return "DarkOliveGreen";
    case 4: return "DarkGreen";
    case 5: return "DarkSlateBlue";
    case 6: return "Navy";
    case 7: return "Indigo";
    case 8: return "DarkSlateGray";
    case 9: return "DarkRed";
    case 10: return "DarkOrange";
    case 11: return "Olive";
    case 12: return "Green";
    case 13: return "Teal";
    case 14: return "Blue";
    case 15: return "SlateGray";
    case 16: return "DimGray";
    case 17: return "Red";
    case 18: return "SandyBrown";
    case 19: return "YellowGreen";
    case 20: return "SeaGreen";
    case 21: return "MediumTurquoise";
    case 22: return "RoyalBlue";
    case 23: return "Purple";
    case 24: return "Gray";
    case 25: return "Magenta";
    case 26: return "Orange";
    case 27: return "Yellow";
    case 28: return "Lime";
    case 29: return "Cyan";
    case 30: return "DeepSkyBlue";
    case 31: return "DarkOrchid";
    case 32: return "Silver";
    case 33: return "Pink";
    case 34: return "Wheat";
    case 35: return "LemonChiffon";
    case 36: return "PaleGreen";
    case 37: return "PaleTurquoise";
    case 38: return "LightBlue";
    case 39: return "Plum";
    case 40: return "White";
    default: return false;
  }
}

function get_forum_moderators($forumid, $plaintext = true)
{
  global $Cache;
  static $moderatorsArray;

  if (!$moderatorsArray && !$moderatorsArray = $Cache->get_value('forum_moderator_array')) {
    $moderatorsArray = array();
    $res = sql_query("SELECT forumid, userid FROM forummods ORDER BY forumid ASC") or sqlerr(__FILE__, __LINE__);
    while ($row = _mysql_fetch_array($res)) {
      $moderatorsArray[$row['forumid']][] = $row['userid'];
    }
    $Cache->cache_value('forum_moderator_array', $moderatorsArray, 86200);
  }
  $ret = (array)$moderatorsArray[$forumid];

  $moderators = "";
  foreach($ret as $userid) {
    if ($plaintext)
      $moderators .= get_plain_username($userid).", ";
    else $moderators .= get_username($userid).", ";
  }
  $moderators = rtrim(trim($moderators), ",");
  return $moderators;
}
function key_shortcut($page=1,$pages=1) {
  $key_shortcut_block = '<script type="text/javascript">hb.config.pager=(' . json_encode(['current' => 0 + $page, 'max' => $pages]) . ');</script>';
  
  return $key_shortcut_block;
}
function promotion_selection($selected = 0, $hide = 0)
{
  global $lang_functions;
  $selection = "";
  if ($hide != 1)
    $selection .= "<option value=\"1\"".($selected == 1 ? " selected=\"selected\"" : "").">".$lang_functions['text_normal']."</option>";
  if ($hide != 2)
    $selection .= "<option value=\"2\"".($selected == 2 ? " selected=\"selected\"" : "").">".$lang_functions['text_free']."</option>";
  if ($hide != 3)
    $selection .= "<option value=\"3\"".($selected == 3 ? " selected=\"selected\"" : "").">".$lang_functions['text_two_times_up']."</option>";
  if ($hide != 4)
    $selection .= "<option value=\"4\"".($selected == 4 ? " selected=\"selected\"" : "").">".$lang_functions['text_free_two_times_up']."</option>";
  if ($hide != 5)
    $selection .= "<option value=\"5\"".($selected == 5 ? " selected=\"selected\"" : "").">".$lang_functions['text_half_down']."</option>";
  if ($hide != 6)
    $selection .= "<option value=\"6\"".($selected == 6 ? " selected=\"selected\"" : "").">".$lang_functions['text_half_down_two_up']."</option>";
  if ($hide != 7)
    $selection .= "<option value=\"7\"".($selected == 7 ? " selected=\"selected\"" : "").">".$lang_functions['text_thirty_percent_down']."</option>";
  return $selection;
}

function get_post_row($postid)
{
  global $Cache;
  if (!$row = $Cache->get_value('post_'.$postid.'_content')){
    $res = sql_query("SELECT * FROM posts WHERE id=".sqlesc($postid)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($res);
    $Cache->cache_value('post_'.$postid.'_content', $row, 7200);
  }
  if (!$row)
    return false;
  else return $row;
}

function get_country_row($id)
{
  global $Cache;
  if (!$row = $Cache->get_value('country_'.$id.'_content')){
    $res = sql_query("SELECT * FROM countries WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($res);
    $Cache->cache_value('country_'.$id.'_content', $row, 86400);
  }
  if (!$row)
    return false;
  else return $row;
}

function get_downloadspeed_row($id)
{
  global $Cache;
  if (!$row = $Cache->get_value('downloadspeed_'.$id.'_content')){
    $res = sql_query("SELECT * FROM downloadspeed WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($res);
    $Cache->cache_value('downloadspeed_'.$id.'_content', $row, 86400);
  }
  if (!$row)
    return false;
  else return $row;
}

function get_uploadspeed_row($id)
{
  global $Cache;
  if (!$row = $Cache->get_value('uploadspeed_'.$id.'_content')){
    $res = sql_query("SELECT * FROM uploadspeed WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($res);
    $Cache->cache_value('uploadspeed_'.$id.'_content', $row, 86400);
  }
  if (!$row)
    return false;
  else return $row;
}

function get_isp_row($id)
{
  global $Cache;
  if (!$row = $Cache->get_value('isp_'.$id.'_content')){
    $res = sql_query("SELECT * FROM isp WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($res);
    $Cache->cache_value('isp_'.$id.'_content', $row, 86400);
  }
  if (!$row)
    return false;
  else return $row;
}

function valid_file_name($filename)
{
  $allowedchars = "abcdefghijklmnopqrstuvwxyz0123456789_./";

  $total=strlen($filename);
  for ($i = 0; $i < $total; ++$i)
  if (strpos($allowedchars, $filename[$i]) === false)
    return false;
  return true;
}

function valid_class_name($filename)
{
  $allowedfirstchars = "abcdefghijklmnopqrstuvwxyz";
  $allowedchars = "abcdefghijklmnopqrstuvwxyz0123456789_";

  if(strpos($allowedfirstchars, $filename[0]) === false)
    return false;
  $total=strlen($filename);
  for ($i = 1; $i < $total; ++$i)
  if (strpos($allowedchars, $filename[$i]) === false)
    return false;
  return true;
}

function return_avatar_image($url) {
  global $CURLANGDIR;
  return "<img src=\"".$url."\" alt=\"avatar\" class=\"avatar\" />";
}

function return_category_image($categoryid, $link="") {
  global $BASEURL;

  static $catImg = array();
  if (array_key_exists($categoryid, $catImg)) {
    $catimg = $catImg[$categoryid];
  } else {
    $categoryrow = get_category_row($categoryid);
    $catimgurl = get_cat_folder($categoryid);
    $catImg[$categoryid] = $catimg = "<img".($categoryrow['class_name'] ? " class=\"".$categoryrow['class_name']."\"" : "")." src=\"//$BASEURL/pic/cattrans.gif\" alt=\"" . $categoryrow["name"] . "\" title=\"" .$categoryrow["name"]. "\" />";
  }
  if ($link) {
    $catimg = "<a href=\"".$link."cat=" . $categoryid . "\">".$catimg."</a>";
  }
  return $catimg;
}

function parsetable($width, $bgcolor, $message) {
  if(!in_array(substr($message, 0, 4), array('[tr]', '<tr>'))) {
    return $message;
  }
  $width = substr($width, -1) == '%' ? (substr($width, 0, -1) <= 98 ? $width : '98%') : ($width <= 560 ? $width : '98%');
  return '<table cellspacing="0" '.
    ($width == '' ? NULL : 'width="'.$width.'" ').
    'align="center" class="t_table"'.($bgcolor ? ' style="background: '.$bgcolor.'">' : '>').
    str_replace('\\"', '"', preg_replace(array(
        "/\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
        "/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
        "/\[\/td\]\s*\[\/tr\]/i"
      ), array(
        "parsetrtd('\\1', '\\2', '\\3', '\\4')",
        "parsetrtd('td', '\\1', '\\2', '\\3')",
        '</td></tr>'
      ), $message)
    ).'</table>';
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
  return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' style="background: '.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
}

function bbcodeurl($url, $tags) {
  if(!preg_match("/<.+?>/s", $url)) {
    if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) {
      $url = 'http://'.$url;
    }
    return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
  } else {
    return '&nbsp;'.$url;
  }
}

function get_torrent_promotion_append_sub($promotion = 1,$forcemode = "",$showtimeleft = false, $added = "", $promotionTimeType = 0, $promotionUntil = ''){
  global $CURUSER,$lang_functions;
  
  list($pr_state, $expire) = get_pr_state($promotion, $added, $promotionTimeType, $promotionUntil);

  if ($pr_state != 1) {
    if ($expire) {
      $cexpire = date("Y-m-d H:i:s", $expire);
      $timeout = gettime($cexpire, false, false, true, false, true);
      $ret = '[<span class="pr-limit" title="' . $cexpire . '">'.$lang_functions['text_will_end_in'].$timeout."</span>]";
    }
    else {
      $ret = "[<span class='pr-eternal'>".$lang_functions['text_will_end_in'].$lang_functions['text_forever']."</span>]";
    }
  }
  else {
    $ret = '';
  }
  return $ret;
}

function lang_choice_before_login($extra='') {
  return;
  /*  global $lang_functions;

  $s = "<select name=\"sitelanguage\" onchange='submit()'>\n";

  $langs = langlist("site_lang");

  foreach ($langs as $row) {
    if ($row["site_lang_folder"] == get_langfolder_cookie()) $se = "selected=\"selected\""; else $se = "";
    $s .= "<option value=\"". $row["id"] ."\" ". $se. ">" . htmlspecialchars($row["lang_name"]) . "</option>\n";
  }
  $s .= "\n</select>";

  echo '<form method="get" action="' . $_SERVER['PHP_SELF'] . '">';

  print($extra);
  print('<div id="lang-choice">'.$lang_functions['text_select_lang']. $s . "</div>");
  echo ('</form>');*/
}

function a_to_z_index($letter='', $query = '') {
  $out = '<div class="minor-list"><ul>';

  for ($i = 97; $i < 123; ++$i) {
    $l = chr($i);
    $L = chr($i - 32);
    if ($l == $letter) {
      $out .= ('<li class="selected">' . $L . '</li>');
    }
    else {
      $out .= ('<li><a href="?letter=' . $l . $query . '">' . $L . '</a></li>');
    }
  }
  $out .= '</ul></div>';
  return $out;
}

function checkHTTPMethod($method) {
  if (strtoupper($_SERVER['REQUEST_METHOD']) != strtoupper($method)) {
    header('HTTP/1.1 405 Method Not Allowed');
    stderr('No hacking allowed!', 'This method allows ' . $method . ' request only.');
    die();
  }
}

function votes($poll, $uservote = 255) {
  global $lang_functions, $pollmanage_class;
  global $BASEURL;
  
  $pollanswers_count = sql_query("SELECT selection, COUNT(selection) FROM pollanswers WHERE pollid=" . $poll["id"] . " AND selection < 20 GROUP BY selection") or sqlerr();

  $tvotes = 0;

  $os = array(); // votes and options: array(array(123, "Option 1"), array(45, "Option 2"))
  for ($i = 0; $i<20; $i += 1) {
    $text = $poll["option" . $i];
    if ($text) {
      $os[$i] = array(0, $text, $i);
    }
  }

  // Count votes
  while ($poll_itm = _mysql_fetch_row($pollanswers_count)) {
    $idx = $poll_itm[0];
    $count = $poll_itm[1];
    if (array_key_exists($idx, $os)) {
      $os[$idx][0] = $count;
    }
    $tvotes += $count;
  }

  $out = '<div class="poll-opts minor-list-vertical"><ul>';
  $i = 0;
  while ($a = $os[$i]) {
    if ($tvotes > 0) {
      $p = round($a[0] / $tvotes * 100);
    }
    else {
      $p = 0;
    }

    if ($a[2] == $uservote) {
      $class = 'sltbar';
    }
    else {
      $class = 'unsltbar';
    }
    
    $out .= ('<li><span class="opt-text">' . $a[1] . '</span><span class="opt-percent nowrap">' . "<img class=\"bar_end\" src=\"//$BASEURL/pic/trans.gif\" alt=\"\" /><img class=\"" . $class . "\" src=\"//$BASEURL/pic/trans.gif\" style=\"width: " . ($p * 3) . "px\" /><img class=\"bar_end\" src=\"//$BASEURL/pic/trans.gif\" alt=\"\" /> $p%</span></li>\n");
    ++$i;
  }
  $out .= ("</ul></div>\n");
  $tvotes = number_format($tvotes);
  $out .= ("<div>".$lang_functions['text_poll_votes'].$tvotes.'</div>');
  return $out;
}

function get_fun($id = 0, $pager_count = null) {
  global $Cache, $BASEURL, $lang_functions, $CURUSER;

  $is_cache = false;
  $https = (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on");
  if ($id == 0) {
    if (is_null($pager_count)) {
      $key = 'current_fun_content';
      if ($https) {
	$key .= '_https';
      }
      $content = $Cache->get_value($key);
      $id = $Cache->get_value('current_fun_content_id');
      $is_cache = true;
    }

    $sql = "SELECT fun.*, IF(ADDTIME(added, '1 0:0:0') < NOW(),true,false) AS neednew FROM fun WHERE status != 'banned' AND status != 'dull' ORDER BY added DESC LIMIT 1";
  }
  else {
    $sql = "SELECT * FROM fun WHERE id = ". $id;
  }
  $content = null;

  if (!$content) {
    $result = sql_query($sql)  or sqlerr(__FILE__,__LINE__);
    $row = _mysql_fetch_array($result);

    if (!$row) {
      return '无';
    }

    $id = $row['id'];
    $username = get_username($row["userid"],false,true,true,true,false,false,"",false);
    $time = $lang_functions['text_on'].$row['added'];

    $content = '<div class="page-titles"><h3><a href="//' . $BASEURL . '/fun.php?id=' . $id . '">'.$row['title'].'</a></h3><h4>'.$lang_functions['text_posted_by'];
    $content .= $username . ' ' . $time;
    $content .= '</h4></div>';
    $body = format_comment($row['body'], ['cachekey' => true, 'newtab' => true]);

    // embed tag of http in https is often not allowed
    if ($https && strstr($body, '<embed') && strstr($body, 'src="http://')) {
      $filename = 'cache/funbox-' . $id . '.html';
      if (!is_null($pager_count)) {
	$height = '700px';
      }
      else {
	$height = '400px';
      }
      $content .= '<iframe seamless id="funbox-content" style="height:'.$height.'" src="http://' . $BASEURL . '/' . $filename . '?' . $body->cachekey . '" ></iframe>';
      if (!file_exists($filename)) {
	$h = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="stylesheet" href="../styles/common.css" type="text/css" media="screen" /><link rel="stylesheet" href="../styles/font.css" type="text/css" media="screen" /></head><body style="background-color:transparent;background-image:none">' . $body . '</body></html>';
	file_put_contents($filename, $h);
      }
    }
    else {
      if (is_null($pager_count)) {
	$content .= '<div id="funbox-content">';
      }
      $content .= $body;
      if (is_null($pager_count)) {
	$content .= "</div>";
      }
    }
    

    if ($is_cache) {
      $Cache->cache_value($key, $content, 900);
      $Cache->cache_value('current_fun_content_id', $id, 900);
      $Cache->cache_value('current_fun_content_neednew', $row['neednew'], 900);
    }
  }

  if ($is_cache && (!isset($_REQUEST['funpage']) || $_REQUEST['funpage'] == 0) ) {
    $key = 'current_fun_content_comment';
    if (checkPrivilege(['Misc', 'fun'])) {
      $key .= '_delete';
    }
    $funcomment = $Cache->get_value($key);
  }
  else {
    $funcomment = null;
  }

  if (!$funcomment) {
    $where = 'WHERE funid=' . $id;
    $funcomment = '<h4>评论';
    $query = "SELECT * FROM funcomment " . $where . " ORDER BY id DESC ";
    if (!is_null($pager_count)) {
      $count = get_row_count('funcomment', $where);
      $href= '?';
      $href .= 'id=' . $id . '&';
      list($pt, $pb, $limit) = pager($pager_count, $count, $href, ['anchor' => 'funcomment'], 'funpage');
    }
    else {
      $limit = 'LIMIT 10';
      $funcomment .= ' [<a href="//' . $BASEURL . '/fun.php#funcomment">查看更多</a>]';
    }
    $funcomment .= '</h4>';
    $subres = sql_query($query . $limit) or sqlerr(__FILE__, __LINE__);

    $funcomment .= ('<dl class="table midt" id="funcomment">');
    ob_start();

    while ($subrow = _mysql_fetch_array($subres)) {
      /* if ($subrow["text"]==$temptxt && $tempuserid==$subrow["userid"])continue; */
      
      /* $temptxt=$subrow["text"]; */
      /* $tempuserid=$subrow["userid"]; */
      
      /* $temp=$subrow["text"]; */

      if (checkPrivilege(['Misc', 'fun'])) {
	$del=' <form class="a" action="fun.php" method="post"><input type="hidden" name="action" value="delcomment" /><input type="hidden" name="commentid" value="'. $subrow['id'] . '" /><input type="submit" value="删除" class="a" /></form>';
      }
      dl_item(get_username($subrow['userid'],false,true,true,true,false,false,"",false).$del, format_comment($subrow['text'],true,false,true,true,600,false,false), true);
    }
    $funcomment .= ob_get_clean();
    $funcomment .= "</dl>";
    if (isset($pb)) {
      $funcomment .= $pb;
    }

    $funcomment .="<form action='fun.php#bottom' method='post' name='funboxcomment'><input type='hidden' name='funid' value='" . $id . "' /><input type='hidden' name='action' value='comment' />";
    $funcomment .= '<input type="hidden" name="returnto" value="' . $_SERVER["REQUEST_URI"] . '" />';
    $funcomment .="<input type='text' name='fun_text' placeholder='你有什么看法?' id='fun_text' size='100' style='width: 80%;' />";
      $funcomment .= "<input type='submit' class='btn' value=\"评论\"  name='tofunboxcomment'  /></form>";
      if ($is_cache) {
	$Cache->cache_value($key, $funcomment, 900);
      }
  }

  {
    $userid = $CURUSER['id'];
    $totalvote = $Cache->get_value('fun_vote_count_'.$id, 756, function() use($id) {
	return get_row_count("funvotes", "WHERE funid = ?", [$id]);
      });

    $funvote = $Cache->get_value('fun_vote_funny_count_'.$id, 756, function() use ($id) {
	return get_row_count("funvotes", "WHERE funid = ? AND vote='fun'", [$id]);
      });
    //check whether current user has voted
    $funvoted = $Cache->get_value('funvote_'.$id . '_' . $userid, 1800, function() use ($id, $userid) {
	return get_row_count("funvotes", "WHERE funid = ? AND userid= ?", [$id, $userid]);
      });

    
    $content_vote = '<div id="funvote" class="minor-list compact">';
    $content_vote .= '<span id="funvote-fun">'.$funvote."</span>" . $lang_functions['text_out_of'] . '<span id="funvote-total">' . $totalvote . '</span>' . $lang_functions['text_people_found_it'];

    if (!$funvoted) {
      $content_vote .= "<span id=\"funvote-form\"><span class=\"striking\">".$lang_functions['text_your_opinion']."</span>";

      $content_vote .= '<ul>';
      $content_vote .= '<li><form action="fun.php?action=vote" method="post"><input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="yourvote" value="fun" /><input type="submit" class="btn" value="' . $lang_functions['submit_fun'] . '" /></form></li>';
      $content_vote .= '<li><form action="fun.php?action=vote" method="post"><input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="yourvote" value="dull" /><input type="submit" class="btn" value="' . $lang_functions['submit_dull'] . '" /></form></li>';
      $content_vote .= '</ul></span>';
    }
    $content_vote .= '</div>';
  }
  
  return $content . $funcomment . $content_vote;
}

function storing_keeper_list($torrentid){
  return array_map(function($keeperid) {
      return $keeperid['keeper_id'];
    }, sql_query("SELECT keeper_id FROM storing_records WHERE checkout = 0 AND torrent_id = ?", [$torrentid])->fetchAll());
}

function strip_local_domain($link) {
  global $possibleUrls;
  $regpre = '!^https?://(?:' . implode('|', $possibleUrls) . ')/!i';
  return preg_replace($regpre, '/', $link);
}

function add_space_between_words($str) {
  $oldEncoding = mb_internal_encoding();
  mb_internal_encoding('UTF-8');
  $l = mb_strlen($str);
  if ($l <= 1) {
    $out = $str;
  }
  else {
    $out = mb_substr($str, 0, 1);
    $lch = $out;
    $flag = (strlen($out) > 1);
    for ($i =1; $i<$l; $i += 1) {
      $ch = mb_substr($str, $i, 1);
      $f = (strlen($ch) > 1);
      if ($f != $flag) {
	if ((!$f && ctype_alnum($ch)) ||
	    ($f && ctype_alnum($lch))) {
	  $out .= ' ';
	}
	$flag = $f;
      }
      $lch = $ch;
      $out .= $ch;
    }
  }
  mb_internal_encoding($oldEncoding);
  return $out;
}

// Use for stat w/ stat.php
function make_stat_url($url, $type, $args=[]) {
  global $BASEURL;
  $a = ['redir' => $url,
	'type' => $type,
	'arg' => $args];
  return '//' . $BASEURL . '/stat.php?' . http_build_query($a);
}

function write_file_log($name, $content) {
  $h = fopen('log/' . $name . '.log', 'a');
  if ($h) {
    fwrite($h, $content . "\n");
    fclose($h);
  }
}