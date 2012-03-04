<?php
require "include/bittorrent.php";
dbconn();

//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: application/json; charset=utf-8");

if (isset($_GET['term']) && $_GET['term'] != '') {
  $searchstr = unesc(trim($_GET['term']));
}

if ($searchstr) {
  $suggest_query = sql_query("SELECT keywords AS suggest, COUNT(*) AS count FROM suggest WHERE keywords LIKE " . sqlesc($searchstr . "%")." GROUP BY keywords ORDER BY count DESC, keywords DESC LIMIT 5") or sqlerr(__FILE__,__LINE__);
  $result = array();
  $i = 0;
  while($suggest = mysql_fetch_array($suggest_query)){
    if (strlen($suggest['suggest']) > 25) continue;
    $result[] = array('value' => $suggest['suggest'], 'count' => $suggest['count']);
    $i++;
  }
  echo php_json_encode($result);
}
?>
