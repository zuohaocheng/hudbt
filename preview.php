<?php
require_once("include/bittorrent.php");
dbconn();
$body = $_REQUEST['body'];
echo format_comment($body);
