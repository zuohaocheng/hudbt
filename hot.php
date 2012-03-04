<?php
require_once("include/bittorrent.php");
$args = $_SERVER['QUERY_STRING'];
if ($args) {
  $args = '&' . $args;
}

header("Location: //$BASEURL/torrents.php?hot=1$args");

?>

