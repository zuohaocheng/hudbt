<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

$filename = $_GET["subid"];
$dirname = $_GET["torrentid"];

if (!$filename || !$dirname)
die("File name missing\n");

$filename = 0 + $filename;
$dirname = 0 + $dirname;

$res = sql_query("SELECT * FROM subs WHERE id=$filename") or sqlerr(__FILE__, __LINE__);
$arr = _mysql_fetch_assoc($res);
if (!$arr)
die("Not found\n");

sql_query("UPDATE subs SET hits=hits+1 WHERE id=$filename") or sqlerr(__FILE__, __LINE__);
$file = "$SUBSPATH/$dirname/$filename.$arr[ext]";

if (!is_file($file))
die("File not found\n");
$f = fopen($file, "rb");
if (!$f) {
  die("Cannot open file\n");
}
header("Content-Length: " . filesize($file));
header("Content-Type: application/octet-stream");
header_download_file($arr['filename']);

do
{
$s = fread($f, 4096);
print($s);
} while (!feof($f));
fclose($f);
exit;
