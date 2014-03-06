<?php
require_once("include/bittorrent.php");
dbconn();

$id = (int)$_GET["id"];
if (!$id)
	httperr();

if (isset($_GET['passkey']) && preg_match('/[a-f0-9]{1,32}/', $_GET['passkey'])){
$passkey = $_GET['passkey'];

$res = sql_query("SELECT * FROM users WHERE passkey=? LIMIT 1", [$passkey]);
	$user = _mysql_fetch_array($res);

	if (!$user)
		die("invalid passkey");
	elseif ($user['enabled'] == 'no' || $user['parked'] == 'yes')
		die("account disabed or parked");
	$oldip = $user['ip'];
	$user['ip'] = getip();
	$CURUSER = $user;
}
else
{
	loggedinorreturn();
	parked();
	// Added by BruceWolf. 2011-03-11
	if($CURUSER['username']=="guest"){
	   stderr("错误", "联盟用户请到论坛联盟专区发帖申请注册下载帐号");
		die();
	}
	// End ||
	
	$letdown = $_GET['letdown'];
	if (!$letdown) {
	  if ($CURUSER['showclienterror'] == 'yes') {
	    header("Location: " . get_protocol_prefix() . "$BASEURL/downloadnotice.php?torrentid=".$id."&type=client");
	  }
	  elseif ($CURUSER['leechwarn'] == 'yes') {
	    header("Location: " . get_protocol_prefix() . "$BASEURL/downloadnotice.php?torrentid=".$id."&type=ratio");
	  }
	  elseif (get_row_count('snatched', 'WHERE userid = ?', [$CURUSER['id']]) == 0) {
	    header("Location: " . get_protocol_prefix() . "$BASEURL/downloadnotice.php?torrentid=".$id."&type=firsttime");
	  }
	}
}
//User may choose to download torrent from RSS. So log ip changes when downloading torrents.
if ($iplog1 == "yes") {
	if (($oldip != $CURUSER["ip"]) && $CURUSER["ip"])
	sql_query("INSERT INTO iplog (ip, userid, access) VALUES (" . sqlesc($CURUSER['ip']) . ", " . $CURUSER['id'] . ", '" . $CURUSER['last_access'] . "')");
}
//User may choose to download torrent from RSS. So update his last_access and ip when downloading torrents.
update_user($CURUSER['id'], 'last_access =?, ip=?', [date("Y-m-d H:i:s"), $CURUSER['ip']]);

/*
@ini_set('zlib.output_compression', 'Off');
@set_time_limit(0);

if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false)
{	// if output_handler = ob_gzhandler, turn it off and remove the header sent by PHP
	@ob_end_clean();
	header('Content-Encoding:');
}
*/

$tracker_ssl = ($_COOKIE["c_secure_tracker_ssl"] == base64("yeah"));

if ($tracker_ssl){
	$ssl_torrent = "https://";
	if ($https_announce_urls[0] != "")
		$base_announce_url = $https_announce_urls[0];
	else
		$base_announce_url = $announce_urls[0];
}
else{
	$ssl_torrent = "http://";
	$base_announce_url = $announce_urls[0];
}



$res = sql_query("SELECT name, filename, save_as,  size, owner,banned FROM torrents WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = _mysql_fetch_assoc($res);
$fn = "$torrent_dir/$id.torrent";
if ($CURUSER['downloadpos']=="no")
	permissiondenied();
if (!$row || !is_file($fn) || !is_readable($fn))
	httperr();
if ($row['banned'] == 'yes' && get_user_class() < $seebanned_class)
	permissiondenied();
sql_query("UPDATE LOW_PRIORITY torrents SET hits = hits + 1 WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);

require_once "include/benc.php";

if (strlen($CURUSER['passkey']) != 32) {
	$CURUSER['passkey'] = md5($CURUSER['username'].date("Y-m-d H:i:s").$CURUSER['passhash']);
	update_user($CURUSER['id'], 'passkey = ?', [$CURUSER['passkey']]);
}

$tracker = $ssl_torrent . $base_announce_url . "?passkey=$CURUSER[passkey]";
$trackerlen = strlen($tracker);

$torrent = file_get_contents($fn);

// Replace announce string in torrent w/o parsing whole torrent, faster
$announce_header = '8:announce';
$ap = strpos($torrent, $announce_header);
if ($ap !== false) {
  $ap += strlen($announce_header);
  $otorrent = substr($torrent, 0, $ap);

  $slen = '';
  while (true) { // Get length of original tracker string
    $ch = $torrent[$ap];
    $ap += 1;
    $slen .= $ch;
    if (!is_numeric($ch)) { // The char here should be ':'
      break;
    }
  }
  $len = 0 + $slen;
  $ap += $len;

  $otorrent .= $trackerlen . ':' . $tracker;
  $otorrent .= substr($torrent, $ap);
}
else { // old way, slow for big torrent
  $dict = bdec_file($fn, $max_torrent_size);
  $dict['value']['announce']['value'] = $tracker;
  $dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
  $dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

  $otorrent = benc($dict);
}

/*if ($announce_urls[1] != "") // add multi-tracker
{
	$dict['value']['announce-list']['type'] = "list";
	$dict['value']['announce-list']['value'][0]['type'] = "list";
	$dict['value']['announce-list']['value'][0]['value'][0]["type"] = "string";
	$dict['value']['announce-list']['value'][0]['value'][0]["value"] = $ssl_torrent . $announce_urls[0] . "?passkey=$CURUSER[passkey]";
	$dict['value']['announce-list']['value'][0]['value'][0]["string"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["value"]).":".$dict['value']['announce-list']['value'][0]['value'][0]["value"];
	$dict['value']['announce-list']['value'][0]['value'][0]["strlen"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["string"]);
	$dict['value']['announce-list']['value'][0]['string'] = "l".$dict['value']['announce-list']['value'][0]['value'][0]["string"]."e";
	$dict['value']['announce-list']['value'][0]['strlen'] = strlen($dict['value']['announce-list']['value'][0]['string']);
	$dict['value']['announce-list']['value'][1]['type'] = "list";
	$dict['value']['announce-list']['value'][1]['value'][0]["type"] = "string";
	$dict['value']['announce-list']['value'][1]['value'][0]["value"] = $ssl_torrent . $announce_urls[1] . "?passkey=$CURUSER[passkey]";
	$dict['value']['announce-list']['value'][1]['value'][0]["string"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["value"]).":".$dict['value']['announce-list']['value'][0]['value'][0]["value"];
	$dict['value']['announce-list']['value'][1]['value'][0]["strlen"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["string"]);
	$dict['value']['announce-list']['value'][1]['string'] = "l".$dict['value']['announce-list']['value'][0]['value'][0]["string"]."e";
	$dict['value']['announce-list']['value'][1]['strlen'] = strlen($dict['value']['announce-list']['value'][0]['string']);
	$dict['value']['announce-list']['string'] = "l".$dict['value']['announce-list']['value'][0]['string'].$dict['value']['announce-list']['value'][1]['string']."e";
	$dict['value']['announce-list']['strlen'] = strlen($dict['value']['announce-list']['string']);
}*/
/*
header ("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
header ("Last-Modified: ".date("D, d M Y H:i:s"));
header ("Cache-Control: no-store, no-cache, must-revalidate");
header ("Cache-Control: post-check=0, pre-check=0", false);
header ("Pragma: no-cache");
header ("X-Powered-By: ".VERSION." (c) ".date("Y")." ".$SITENAME."");
header ("Accept-Ranges: bytes");
header ("Connection: close");
header ("Content-Transfer-Encoding: binary");
*/

header("Content-Type: application/x-bittorrent");
header_download_file($torrentnameprefix . '.' . $row["save_as"] . '.torrent');
print($otorrent);

