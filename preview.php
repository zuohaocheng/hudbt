<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
$body = $_REQUEST['body'];
echo format_comment($body);
?>
