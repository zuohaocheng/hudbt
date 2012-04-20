<?php
if (php_sapi_name() != 'cli') die('This program can be run only in php_cli mode.');
require "include/bittorrent.php";
dbconn();
sql_query("UPDATE attachments SET inuse = 0 ") or sqlerr(__FILE__, __LINE__);

  $tables = array('torrents'=> 'descr',
	     'posts'=> 'body',
	     'offers'=> 'descr',
	     'comments'=> 'text',
	     'fun'=> 'body',
	     'messages'=> 'msg',
	     'staffmessages'=> 'msg',
	     'users'=> 'signature',
	     'requests'=> 'descr');

  foreach ($tables as $table => $col) {
      echo $table, '.', $col , "\n";
    $atts=array();
    dbconn();
    $res = sql_query("SELECT `$col` FROM $table WHERE `$col` LIKE  '%attach%'") or sqlerr(__FILE__, __LINE__);
    while($row = mysql_fetch_array($res)){
      $attstemp = array();
      preg_match_all('/\[attach\](.*?)\[\/attach\]/', $row[0], $attstemp);
      $atts = array_merge($atts, $attstemp[1]);
    }

    if (count($atts) != 0) {
      dbconn();
      sql_query("UPDATE attachments SET inuse = 1 WHERE dlkey IN (" . implode(",", array_map("sqlesc",$atts)) . ")") or sqlerr(__FILE__, __LINE__);
    }
  }
  
 
$res=sql_query("SELECT count(*), SUM(filesize) AS filesizes FROM attachments WHERE inuse = 0") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res);
$deletecount=$row[0];
$filesizes=$row[1];
echo "\n\nAll:".$deletecount."COUNTS\nSIZE:".($filesizes/1000)."KB\n";

if(in_array($argv[1], array('-delall'))){
		$filepath = $savedirectory_attachment."/";
		$res = sql_query("SELECT location FROM attachments WHERE inuse = 0") or sqlerr(__FILE__, __LINE__);
		while($row = mysql_fetch_array($res)){
			@unlink($filepath.$row[0]);
			@unlink($filepath.$row[0].".thumb.jpg");
			}
		sql_query("DELETE FROM attachments WHERE inuse = 0") or sqlerr(__FILE__, __LINE__);
		ECHO "\nDEL OVER\n";
}else echo "\nyou may del  attachments by adding the parameter '-delall'\n";
