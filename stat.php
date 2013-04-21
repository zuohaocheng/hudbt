<?php
// Check usage of something
// Can be used in JavaScript like
// `$.get('stat.php?type=xxx&arg[]=yyy&arg=zzz')`
// Time & args will be recorded in `log/xxx.log`

$filename = $_REQUEST['type'];
if (!preg_match('/[a-z0-9]+/i', $filename)) {
  header('HTTP/1.0 403 Forbidden');
  die;
}

if (isset($_REQUEST['arg'])) {
  $arg = $_REQUEST['arg'];
}
else {
  $arg = [];
}

array_unshift($arg, date('r', time()));

$text = implode("\t", $arg) . "\n";


$h = fopen('log/' . $filename . '.log', 'a');
if (!$h) {
  header('HTTP/1.0 500 Internal Server Error');
  die;
}
fwrite($h, $text);
fclose($h);

