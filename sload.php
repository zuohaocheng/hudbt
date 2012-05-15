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
    $themes['theme' . $obj['id']] = [$uri . 'theme.css', $uri . 'DomTT.css', 'styles/jqui/' . $obj['jqui'] . '/jquery-ui-1.8.18.custom.css'];
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
    $fd = fopen('cache/css' . $filename . '.css', 'w');
    fwrite($fd, $out);
    fclose($fd);

    /* $out = load_files($files, 'css', true, false); */
    /* $fd = fopen('load/css' . $filename . '-debug.css', 'w'); */
    /* fwrite($fd, $out); */
    /* fclose($fd); */
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
    $fd = fopen('cache/js-common-' . $lang . '.js', 'w');
    fwrite($fd, $o);
    fclose($fd);
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
