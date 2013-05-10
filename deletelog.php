<?php
$args = $_SERVER['QUERY_STRING'];
if ($args) {
  $args .= '&';
}
header("Location: /log.php?{$args}action=deletelog", true, 301);
