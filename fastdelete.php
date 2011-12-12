<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
require_once(get_langfile_path("",true));
loggedinorreturn();

$json = ($_REQUEST['format'] == 'json');

function bark($msg) {
  global $lang_fastdelete;
  global $json;
  if ($json) {
    echo php_json_encode(array('success' => false, 'title' => $lang_fastdelete['std_delete_failed'], 'text' => $msg));
  }
  else {
    stderr($lang_fastdelete['std_delete_failed'], $msg, false);
  }
  exit;
}

if (!mkglobal("id")) {
  bark($lang_fastdelete['std_missing_form_data']);
}

$id = 0 + $id;
int_check($id);
$sure = $_REQUEST["sure"];

if (get_user_class() < $torrentmanage_class) {
  bark($lang_fastdelete['text_no_permission']);
}

if (!$sure || $_SERVER['REQUEST_METHOD'] != 'POST') {
  if ($json) {
    echo php_json_encode(array('success' => false));
  }
  else {
    stderr($lang_fastdelete['std_delete_torrent'], '<form action="?id=' . $id . '" method="POST">' . $lang_fastdelete['std_delete_torrent_note'].'<input type="hidden" name="sure" value="1" />' . $lang_fastdelete['std_here_if_sure'] . '</form>',false);
  }
}

$res = sql_query("SELECT name,owner,seeders,anonymous, added FROM torrents WHERE id = $id");
$row = mysql_fetch_array($res);
if (!$row) {
  bark($lang_fastdelete['std_no_torrent']);
}

//Added by bluemonster 20111107
//Send pm to torrent peers
//Modified by bluemonster 20111116
//some users in snatched may have been deleted by admin,we must handle this excepption
$users_of_torrent_res=sql_query("SELECT userid FROM snatched WHERE torrentid=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
while($users_of_torrent=mysql_fetch_array($users_of_torrent_res)) {
  $user_accepttdpms_sql=sql_query("SELECT accepttdpms FROM users WHERE id=".sqlesc($users_of_torrent["userid"]));
  if($user_accepttdpms=mysql_fetch_array($user_accepttdpms_sql)) {
    if($user_accepttdpms["accepttdpms"]!="no") {
      $dt = sqlesc(date("Y-m-d H:i:s"));
      $subject = sqlesc($lang_fastdelete_target[get_user_lang($users_of_torrent["userid"])]['msg_torrent_deleted']);
      $msg = sqlesc($lang_fastdelete_target[get_user_lang($users_of_torrent["userid"])]['msg_the_torrent_you_downloaded'].$row['name'].$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_was_deleted_by']."[url=userdetails.php?id=".$CURUSER['id']."]".$CURUSER['username']."[/url]".$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_blank']);
      sql_query("INSERT INTO messages (sender, receiver, subject, added, msg) VALUES(0, $users_of_torrent[userid], $subject, $dt, $msg)") or sqlerr(__FILE__, __LINE__);
    }	
  }
}

$interval_no_deduct_bonus_on_deletion = 30* 86400;
$tadded = strtotime($row['added']);

deletetorrent($id, ((TIMENOW - $tadded) > $interval_no_deduct_bonus_on_deletion));

if ($row['anonymous'] == 'yes' && $CURUSER["id"] == $row["owner"]) {
  write_log("Torrent $id ($row[name]) was deleted by its anonymous uploader",'normal');
}
else {
  write_log("Torrent $id ($row[name]) was deleted by $CURUSER[username]",'normal');
}

//Send pm to torrent uploader
if ($CURUSER["id"] != $row["owner"]){
  $dt = sqlesc(date("Y-m-d H:i:s"));
  $subject = sqlesc($lang_fastdelete_target[get_user_lang($row["owner"])]['msg_torrent_deleted']);
  $msg = sqlesc($lang_fastdelete_target[get_user_lang($row["owner"])]['msg_the_torrent_you_uploaded'].$row['name'].$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_was_deleted_by']."[url=userdetails.php?id=".$CURUSER['id']."]".$CURUSER['username']."[/url]".$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_blank']);
  sql_query("INSERT INTO messages (sender, receiver, subject, added, msg) VALUES(0, $row[owner], $subject, $dt, $msg)") or sqlerr(__FILE__, __LINE__);
}

if ($json) {
  header('Content-type: application/json');
  print('{"success" : true}');
}
else {
  header("Location:torrents.php");
}
?>
