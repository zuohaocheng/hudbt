<?php
require_once('lib/jsminplus.php');
require_once('lib/CSSMin.php');

//Mind the sequence of loading
$js_files = ['jquery-1.7.1.min', 'jquery.json-2.3.min', 'jstorage.min', 'jquery-ui-1.8.18.custom.min', 'jquery.tablesorter', 'ajaxbasic', 'common', 'domLib', 'domTT', 'domTT_drag', 'fadomatic', 'pm', 'pager'];
$css_files = ['styles/sprites.css', 'styles/common.css', 'styles/jquery.tablesorter/jquery.tablesorter.css', 'styles/font.css'];

function array_kronecker_product($arrays, $delimiter = '', $obj = ['' => []]) {
  $array = array_pop($arrays);
  $nobj = [];

  foreach ($array as $nk => $no) {
      foreach ($obj as $k => $o) {
	$nobj[$k . $delimiter . $nk] = array_merge($o, $no);
      }
  }
  if (empty($arrays)) {
    return $nobj;
  }
  else {
    return array_kronecker_product($arrays, $delimiter, $nobj);
  }
}

function dependence($name) {
  $dependence = ['mybonus' => ['jscolor/jscolor']];
  $dep = $dependence[$name];
  if ($dep) {
    return $dep;
  }
}

function load_file($name, $type, $fullpath=false, $minify = true, $basepath = '') {
  if ($fullpath) {
    $path = $name;
  }
  else {
    if (preg_match('/\.min$/', $name)) {
      if (!$minify) {
	$nname = preg_replace('/\.min$/', '', $name);
	if (file_exists(get_path($nname, $type))) {
	  $name = $nname;
	}
      }
      $minify = false;
    }
    
    $path = get_path($name, $type);
  }

  
  $f = read_file($path);

  if ($type == 'css') {
    $f = css_remap($f, $basepath . $path);
  }

  if ($minify) {
      $f = minify($f, $type, $path);
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

function minify($s, $type, $path) {
  if ($type == 'css') {
    return CSSMin::minify($s);
  }
  elseif ($type == 'js') {
    return JSMinPlus::minify($s) . ';';
  }
  else {
    return $s;
  }
}

