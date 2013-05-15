<?php
require_once('include/bittorrent_announce.php');
require_once('include/benc.php');

// BLOCK ACCESS WITH WEB BROWSERS AND CHEATS!
block_browser();

preg_match_all('/info_hash=([^&]*)/i', $_SERVER["QUERY_STRING"], $info_hash_array);

$r = "d" . benc_str("files") . "d";

$err = true;
if (isset($info_hash_array[1])) {
  foreach ($info_hash_array[1] as $infohash) {
    $infohash = urldecode($infohash);
    $row = torrent_for_infohash($infohash);
    if ($row) {
      $err = false;
      $r .= "20:" . hash_pad($infohash) . "d" .
	benc_str("complete") . "i" . $row["seeders"] . "e" .
	benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
	benc_str("incomplete") . "i" . $row["leechers"] . "e" .
	"e";
    }
  }
}

if ($err) {
  err("Torrent not registered with this tracker.");
}

$r .= "ee";

benc_resp_raw($r);
