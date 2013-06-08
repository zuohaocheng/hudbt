<?php
require "include/bittorrent.php";
dbconn();
checkHTTPMethod('POST');

//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: application/json; charset=utf-8");

$torrentid = 0 + $_REQUEST['torrentid'];
if ($torrentid == 0) {
  header('HTTP/1.1 400 Bad Request');
  $result = 'failed';
}
else if (isset($CURUSER)) {
  if ($_REQUEST['action'] == 'del'){
    sql_query("DELETE FROM bookmarks WHERE torrentid= ? AND userid=?", [$torrentid, $CURUSER['id']]);
    $result = "deleted";
    $Cache->delete_value('user_'.$CURUSER['id'].'_bookmark_array');
  }
  else {
    if (get_row_count('bookmarks', 'WHERE torrentid=? AND userid=?', [$torrentid, $CURUSER['id']]) == 0) {
      sql_query("INSERT INTO bookmarks (torrentid, userid) VALUES (?, ?)", [$torrentid, $CURUSER['id']]);
      $result = "added";
      $Cache->delete_value('user_'.$CURUSER['id'].'_bookmark_array');
    }
    else {
      header('HTTP/1.1 409 Conflict');
      $result = 'failed';
    }
  }

}
else {
  header('HTTP/1.1 401 Unauthorized');
  $result = "failed";
}
echo json_encode(array('status' => $result));

