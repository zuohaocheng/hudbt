<?php
require_once('lib/jsminplus.php');
require_once('lib/CSSMin.php');
require_once("include/bittorrent.php");
dbconn(true);

$debug = $_REQUEST['debug'];
$purge = $_REQUEST['purge'];
$fullname = strtolower($_REQUEST['name']);
$format = strtolower($_REQUEST['format']);

$tokens = preg_split('/\./', $fullname);
$type = array_pop($tokens);
$name = implode('.', $tokens);

if ($type == 'php' || $fullname == '') {
  $multifile = true;
  $type = $format;
}

if ($type == 'js') {
  header('Content-type: text/javascript');
}
elseif ($type == 'css') {
  header('Content-type: text/css');
  $font = $_REQUEST['font'];
  $theme = 0 + $_REQUEST['theme'];
  $caticon = 0 + $_REQUEST['caticon'];
}
else {
  die('Invalid format');
}

function etag($etag, $notModifiedExit = true) {
  $ret = false;
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag == $_SERVER['HTTP_IF_NONE_MATCH']) {
      if ($notModifiedExit) {
	header("HTTP/1.0 304 Not Modified");
        exit();
      }
      else {
	$ret = true;
      }
    }
    header('Etag: ' . $etag);
    return $ret;
}
 
function lastModified($modifiedTime, $notModifiedExit = true) {
#    $modifiedTime = date('D, d M Y H:i:s', $modifiedTime) . ' GMT';
  $ret = false;
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $modifiedTime == $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
      if ($notModifiedExit) {
	header("HTTP/1.0 304 Not Modified");
        exit();
      }
      else {
	$ret = true;
      }
    }
    header("Last-Modified: $modifiedTime");
    return $ret;
}
 
function expires($seconds = 1800) {
    $time = date('D, d M Y H:i:s', time() + $seconds) . ' GMT';
    header("Expires: $time");
}

$cache_key = generate_key($name, $type, $multifile);
$lastMod = false;
if (!$debug && !$purge) {
  expires(900);
  $modified = $Cache->get_value($cache_key . '_md');
  if ($modified) {
    lastModified($modified,  etag($cache_key, false));
    $lastMod = true;
  }
}

if ($multifile) {
  $out = load_files_cache($name, $type, $debug, $purge);
}
else {
  $out = load_file_cache($name, $type, $debug, $purge);
}

if (!$lastMode && !$debug) {
  $stamp = $Cache->get_value($cache_key . '_md');
  header("Last-Modified: " . $stamp);
  header('Etag: ' . $cache_key);
}
echo $out;

function generate_key($name, $type, $multiple) {
  $key = 'load_';
  if ($multiple) {
    $key .= 'm_';
  }
  if ($type =='css' && $multiple) {
    global $font, $theme, $caticon;
    $key .= 'css_' . $font . '-' . $theme . '-' . $caticon;
  }
  else {
    $key .= $name . $type;
  }
  return $key;
}

function dependence($name) {
  $dependence = array(
		    'torrents' => array('jquery.json-2.3.min', 'jstorage.min'),
		    );

  $dep = $dependence[$name];
  if ($dep) {
    return $dep;
  }
  return array();
}

function load_files_cache($name, $type, $debug, $purge) {
  global $Cache;
  global $cache_key;
  $key = $cache_key;
  if (!$debug) {
    if ($purge) {
      $Cache->delete_value($key);
      #    echo 'purge';
    }
    else {
      $c = $Cache->get_value($key);
      if ($c) {
	#echo '/* from cache, key: '. $key . '*/';
	return $c;
      }
    }
  }

  if ($type == 'js') {
    //Mind the sequence of loading
    $out .= load_files(array('jquery-1.7.1.min', 'jquery-ui-1.8.18.custom.min', 'jquery.tablesorter'), $type, $debug, $purge, false, false);
    $out .= load_files(array('ajaxbasic', 'common', 'domLib', 'domTT', 'domTT_drag', 'fadomatic', 'pm', 'pager'), $type, $debug, $purge);

    $out .= load_files(dependence($name), $type, $debug, $purge);
    $out .= load_files(array($name), $type, $debug, $purge);
    $out .= ";/* constants */";
    $out .= load_constant();
  }
  elseif ($type == 'css') {
    global $font, $theme, $caticon;
    $css_uri = get_css_uri('', $theme);
    $files = array(get_font_css_uri($font), 'styles/sprites.css', get_forum_pic_folder().'/forumsprites.css', $css_uri."theme.css", $css_uri."DomTT.css", 'pic/' . get_cat_folder(401, $caticon) . "sprite.css", 'styles/jqui/' . jqui_css_name($theme) . '/jquery-ui-1.8.18.custom.css', 'styles/jquery.tablesorter/jquery.tablesorter.css');
    $out .= load_files($files, $type, $debug, $purge, true);

    if ($CURUSER){
      $caticonrow = get_category_icon_row($CURUSER['caticon']);
      if($caticonrow['cssfile']){
	$file = $caticonrow['cssfile'];
	load_file_cache($file, $type, $debug, $purge, true);
      }
    }
  }

  if (!$debug) {
    $Cache->cache_value($key, $out, 2592000);
    $Cache->cache_value($key . '_md', date('D, d M Y H:i:s') . ' GMT', 2592000);
  }
  return $out;
}

function load_constant() {
  //Consider lang
  global $Cache, $CURUSER;
  if (!$CURUSER) {
    return;
  }
  $const = $Cache->get_value('js_hb_constant');
  if (!$const) {
    global $lang_functions;
    global $promotion_text;
    global $torrentmanage_class;
    global $BASEURL, $CAKEURL;

    $const = 'hb.constant = ';
    $const .= php_json_encode(['torrentmanage_class' => $torrentmanage_class, 'cat_class' => get_category_row(), 'lang' => $lang_functions, 'pr' => $promotion_text, 'url' =>['base' => $BASEURL, 'cake' => $CAKEURL]]);
    $const .= ';';
    $Cache->cache_value('js_hb_constant', $const, 2592000);
  }
  return $const;
}

function load_files($files, $type, $debug, $purge, $path = false, $minify=true) {
  $out = '';
  foreach ($files as $file) {
    $out .= "\n/* file: " . $file . " */\n";
    $out .= load_file_cache($file, $type, $debug, $purge, $path, $minify);
  }
  return $out;
}

function load_file_cache($name, $type, $debug, $purge, $path = false, $minify=true) {
  if ($debug) {
    return load_file($name, $type, $debug, $path, $minify);
  }
  
  global $Cache;
  $key = generate_key($name, $type, false);
  if ($purge) {
    $Cache->delete_value($key);
    #    echo 'purge';
  }
  else {
    $c = $Cache->get_value($key);
    if ($c) {
       #     echo 'from cache';
      return $c;
    }
  }
  $c = load_file($name, $type, $debug, $path, $minify);
  $Cache->cache_value($key, $c, 2592000);
  $Cache->cache_value($key . '_md', date('D, d M Y H:i:s') . ' GMT', 2592000);
  #  echo 'direct';
  return $c;
}

function load_file($name, $type, $debug, $fullpath=false, $minify = true) {
  if ($fullpath) {
    $path = $name;
  }
  else {
    $path = get_path($name, $type);
  }
  $f = read_file($path);

  if ($type == 'css') {
    $f = css_remap($f, $path);
  }
  
  if ($minify && !preg_match('/\.min$/', $name)) {
    $f = minify($f, $type, $debug, $path);
  }
  return $f;
}

function css_remap($s, $path) {
  $p = preg_replace('/^(.*\/)[^\/]+$/', '\1', $path);
  $s = CSSMin::remap($s, false, $p);
  return $s;
}

function get_path($resname, $type) {
  if ($type == 'js') {
    return 'js/'. $resname . '.js';
  }
  elseif ($type == 'css') {
    return 'styles/'. $resname . '.css';
  }
  return '';
}

function read_file($file) {
  if (!is_file($file)) {
    return '';
  }

  $f = fopen($file, 'r');
  if (!$f) {
    return '';
  }

  $s = '';
  do {
    $s .= fread($f, 4096);
  } while (!feof($f));
  fclose($f);
  return $s;
}

function minify($s, $type, $debug, $path) {
  if ($debug) {
    return $s;
  }
  elseif ($type == 'css') {
    return CSSMin::minify($s);
  }
  elseif ($type == 'js') {
    return JSMinPlus::minify($s) . ';';
  }
  else {
    return $s;
  }
}



?>