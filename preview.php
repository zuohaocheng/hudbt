<?php
require_once("include/bittorrent.php");
dbconn();
$body = $_REQUEST['body'];
$a = format_comment($body, ['warnings' => true, 'cache' => false]);
echo $a;
if (isset($a->warnings)) {
  $messages = [];
  $pos = [];
  foreach ($a->warnings as $warn) {
    $messages[] = $warn['message'];
    if (!isset($pos[$warn['pos']])) {
      $pos[$warn['pos']] = $warn['length'];
    }
  }
  krsort($pos);
  foreach ($pos as $p => $l) {
    $body = substr($body, 0, $p) . '<span class="warning">' . substr($body, $p, $l) . '</span>' . substr($body, $p + $l);
  }
  echo '<div class="bbcode-warnings"><h2>BBCode警告</h2><ul><li>', implode('</li><li>', $messages), '</li></ul><h3>原BBCode</h3><pre>', $body, '</pre></div>';
}

