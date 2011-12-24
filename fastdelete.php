<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
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

delete_single_torrent($id, $row);

if ($json) {
  header('Content-type: application/json');
  print('{"success" : true}');
}
else {
  header("Location:torrents.php");
}
?>
