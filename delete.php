<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

function bark($msg) {
  global $lang_delete;
  stdhead();
  stdmsg($lang_delete['std_delete_failed'], $msg);
  stdfoot();
  exit;
}

if (!mkglobal("id"))
	bark($lang_delete['std_missing_form_date']);

$id = 0 + $id;
if (!$id)
	die();

$res = sql_query("SELECT name,owner,seeders,anonymous,added FROM torrents WHERE id = ".sqlesc($id));
$row = mysql_fetch_array($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && get_user_class() < $torrentmanage_class)
	bark($lang_delete['std_not_owner']);

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 5)
	bark($lang_delete['std_invalid_reason']."$rt.");

$r = $_POST["r"];
$reason = $_POST["reason"];

if ($rt == 1) {
  $reasonstr = "Dead: 0 seeders, 0 leechers = 0 peers total";
}
elseif ($rt == 2) {
  $reasonstr = "Dupe" . ($reason[0] ? (": " . trim($reason[0])) : "!");
}
elseif ($rt == 3) {
  $reasonstr = "Nuked" . ($reason[1] ? (": " . trim($reason[1])) : "!");
}
elseif ($rt == 4) {
  if (!$reason[2])
    bark($lang_delete['std_describe_violated_rule']);
  $reasonstr = $SITENAME." rules broken: " . trim($reason[2]);
}
else {
  if (!$reason[3]) {
    bark($lang_delete['std_enter_reason']);
  }
}

delete_single_torrent($id, $row, $reasonstr);

stdhead($lang_delete['head_torrent_deleted']);

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">".$lang_delete['text_go_back']."</a>";
else
	$ret = "<a href=\"index.php\">".$lang_delete['text_back_to_index']."</a>";

?>
<h1><?php echo $lang_delete['text_torrent_deleted'] ?></h1>
<div style="text-align:center;"><?php echo $ret ?></div>
<?php
stdfoot();
