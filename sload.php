<?php
require_once("include/static_resources.php");
require_once("include/bittorrent.php");
dbconn(true);
if (php_sapi_name() != 'cli') {
  $web = true;
  if (!checkPrivilege(['Maintenance', 'staticResources'])) {
    header('Location: //' . $BASEURL);
    exit(0);
  }
}
else {
  $web = false;
}

css();
js();

if ($web) {
  stdhead();
  echo '<h1>Static resources re-generated.<h1>';
  stdfoot();
}
else {
  echo "Done.\n";
}

function css() {
  global $css_files;
  $themes = [];
  foreach (get_css_rows() as $obj) {
    $uri = get_css_uri('', $obj['id']);
    $jqui = 'styles/jqui/' . $obj['jqui'];
    $themes['theme' . $obj['id']] = [$uri . 'theme.css', $jqui . '/jquery-ui.min.css', $jqui . '/jquery.ui.theme.css'];
  }

  $caticons = [];
  foreach (get_category_icon_rows() as $obj) {
    $caticons['cat' . $obj['id']] = ['pic/category/chd/' . $obj['folder'] . 'sprite.css'];
  }

  $langs = [];
  foreach (get_langlist() as $obj) {
    $langs[$obj] = [get_forum_pic_folder_for($obj) . '/forumsprites.css'];
  }

  $basics = $css_files;

  foreach (array_kronecker_product([$themes, $caticons, $langs], '-', ['' => $basics]) as $filename =>$files) {
    $out = load_files($files, 'css', true);
    write_file('cache/css' . $filename . '.css', $out);
  }
}

function js() {
  //langs
  $langs = get_langlist();
  
  global $js_files;
  $out = load_files($js_files, 'js');
  foreach ($langs as $lang) {
    global $rootpath;
    global $promotion_text;
    global $torrentmanage_class;
    global $BASEURL, $CAKEURL, $SITENAME;
    $o = $out . ";\n/* constants */\nhb.constant = (";
    include($rootpath . get_langfile_path("functions.php", false, $lang));
    $o .= json_encode(['torrentmanage_class' => $torrentmanage_class, 'cat_class' => get_category_row(), 'lang' => $lang_functions, 'pr' => $promotion_text, 'url' =>['base' => $BASEURL, 'cake' => $CAKEURL]]) . ');';
    write_file('cache/js-common-' . $lang . '.js', $o);
  }
}

function write_file($f, $content) {
  $ref = file_get_contents($f);
  if ($ref != $content) {
    file_put_contents($f, $content);
  }
}


function load_files($files, $type, $path = false, $minify=true) {
  $out = '';
  foreach ($files as $file) {
    $out .= "\n/* file: " . $file . " */\n";
    $out .= load_file($file, $type, $path, $minify, '../');
  }
  return $out;
}
