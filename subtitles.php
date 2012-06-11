<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
require_once(get_langfile_path("",true));
loggedinorreturn();

if (!isset($CURUSER))
  stderr($lang_subtitles['std_error'],$lang_subtitles['std_must_login_to_upload']);

stdhead($lang_subtitles['head_subtitles']);

$detail_torrent_id = 0 + $_REQUEST['torrent'];

function isInteger($n) {
  if (preg_match("/[^0-^9]+/",$n) > 0) {
    return false;
  }
  return true;
}

$search = trim($_GET['search']);
$letter = trim($_GET["letter"]);
if (strlen($letter) > 1 || $letter == "" || strpos("abcdefghijklmnopqrstuvwxyz", $letter) === false) {
  $letter = "";
}

$lang_id = $_GET['lang_id'];
if (!is_valid_id($lang_id))
  $lang_id = '';

$query = "";
if ($search != '') {
  $query = "subs.title LIKE " . sqlesc("%$search%") . "";
  if ($search)
    $q = "search=" . rawurlencode($search);
}
elseif ($letter != '') {
  $query = "subs.title LIKE ".sqlesc("$letter%");
  $q = "letter=$letter";
}

if ($lang_id) {
  $query .= ($query ? " AND " : "")."subs.lang_id=".sqlesc($lang_id);
  $q = ($q ? $q."&amp;" : "") . "lang_id=".sqlesc($lang_id);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["action"] == "upload") {
  //start process upload file
  $file = $_FILES['file'];

  if (!$file || $file["size"] == 0 || $file["name"] == "") {
    echo($lang_subtitles['std_nothing_received']);
    exit;
  }

  if ($file["size"] > $maxsubsize_main && $maxsubsize_main > 0) {
    echo($lang_subtitles['std_subs_too_big']);
    exit;
  }

  $accept_ext = array('sub' => "sub", 'srt' => "srt", 'zip' => "zip", 'rar' => "rar", 'ace' => "ace", 'txt' => "txt", 'ssa' => "ssa", 'ass' => "ass", 'cue' => "cue",'7z'  => "7z",'bz2' => "bz2",'gz' => "gz",'tar' => "tar",'tbz' => "tbz",'tgz' => "tgz");
  $ext_l = strrpos($file['name'], ".");
  $ext = strtolower(substr($file['name'], $ext_l+1, strlen($file['name'])-($ext_l+1)));

  if (!array_key_exists($ext, $accept_ext)) {
    echo($lang_subtitles['std_wrong_subs_format']);
    exit;
  }

  /*
    if (file_exists("$SUBSPATH/$file[name]"))
    {
    echo($lang_subtitles['std_file_already_exists']);
    exit;
    }
  */
  
  //end process upload file

  //start process torrent ID
  if(!$_POST["torrent_id"]) {
    echo($lang_subtitles['std_missing_torrent_id']."$file[name]</b></font> !");
    exit;
  }
  else {
    $torrent_id = 0 + $_POST["torrent_id"];

    $r = sql_query("SELECT owner FROM torrents WHERE id = ". sqlesc($torrent_id)) or sqlerr(__FILE__, __LINE__);
    if(!mysql_num_rows($r)) {
      echo($lang_subtitles['std_invalid_torrent_id']);
      exit;
    }
    else {
      $r_a = mysql_fetch_assoc($r);
      if($r_a["owner"] != $CURUSER["id"] && get_user_class() < $uploadsub_class) {
	echo($lang_subtitles['std_no_permission_uploading_others']);
	exit;
      }
    }
  }
  //end process torrent ID

  //start process title
  $title = trim($_POST["title"]);
  if ($title == "") {
    $title = substr($file["name"], 0, strrpos($file["name"], "."));
    if (!$title)
      $title = $file["name"];

    $file["name"] = str_replace(" ", "_", htmlspecialchars("$file[name]"));
  }

  /*
    $r = sql_query("SELECT id FROM subs WHERE title=" . sqlesc($title)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($r) > 0)
    {
    echo($lang_subtitles['std_file_same_name_exists']."<font color=red><b>" . htmlspecialchars($title) . "</b></font> ");
    exit;
    }
  */
  //end process title

  //start process language
  if($_POST['sel_lang'] == 0) {
    echo($lang_subtitles['std_must_choose_language']);
    exit;
  }
  else {
    $lang_id = $_POST['sel_lang'];
  }
  //end process language

  if ($_POST['uplver'] == 'yes' && get_user_class()>=$beanonymous_class) {
    $anonymous = "yes";
    $anon = "Anonymous";
  }
  else {
    $anonymous = "no";
    $anon = $CURUSER["username"];
  }
  
  //$file["name"] = str_replace("", "_", htmlspecialchars("$file[name]"));
  //$file["name"] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $file[name]);
  
  //make_folder($SUBSPATH."/",$detail_torrent_id);
  //stderr("",$file["name"]);
  
  $r = sql_query("SELECT lang_name from language WHERE sub_lang=1 AND id = " . sqlesc($lang_id)) or sqlerr(__FILE__, __LINE__);
  $arr = mysql_fetch_assoc($r);

  $filename = $file["name"];
  $added = date("Y-m-d H:i:s");
  $uppedby = $CURUSER["id"];
  $size = $file["size"];

  sql_query("INSERT INTO subs (torrent_id, lang_id, title, filename, added, uppedby, anonymous, size, ext) VALUES (" . implode(",", array_map("sqlesc", array($torrent_id, $lang_id, $title, $filename, $added, $uppedby, $anonymous, $size, $ext))). ")") or sqlerr();
  
  $id = mysql_insert_id();
  
  //stderr("",make_folder($SUBSPATH."/",$torrent_id). "/" . $id . "." .$ext);
  if (!move_uploaded_file($file["tmp_name"], make_folder($SUBSPATH."/",$torrent_id). "/" . $id . "." .$ext))
    echo($lang_subtitles['std_failed_moving_file']);
  
  KPS("+",$uploadsubtitle_bonus,$uppedby); //subtitle uploader gets bonus
  
  write_log("$arr[lang_name] Subtitle $id ($title) was uploaded by $anon");
  $msg_bt = "$arr[lang_name] Subtitle $id ($title) was uploaded by $anon, Download: " . get_protocol_prefix() . "$BASEURL/downloadsubs.php/".$file["name"]."";
}

if (get_user_class() >= $delownsub_class) {
  $delete = $_GET["delete"];
  if (is_valid_id($delete)) {
    $r = sql_query("SELECT id,torrent_id,ext,lang_id,title,filename,uppedby,anonymous FROM subs WHERE id=".sqlesc($delete)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($r) == 1) {
      $a = mysql_fetch_assoc($r);
      if (get_user_class() >= $submanage_class || $a["uppedby"] == $CURUSER["id"]) {
	$sure = $_GET["sure"];
	if ($sure == 1) {
	  $reason = $_POST["reason"];
	  sql_query("DELETE FROM subs WHERE id=$delete") or sqlerr(__FILE__, __LINE__);
	  if (!unlink("$SUBSPATH/$a[torrent_id]/$a[id].$a[ext]")) {
	    stdmsg($lang_subtitles['std_error'], $lang_subtitles['std_this_file']."$a[filename]".$lang_subtitles['std_is_invalid']);
	    stdfoot();
	    die;
	  }
	  else {
	    KPS("-",$uploadsubtitle_bonus,$a["uppedby"]); //subtitle uploader loses bonus for deleted subtitle
	  }
	  if ($CURUSER['id'] != $a['uppedby']) {
	    $msg = $CURUSER['username'].$lang_subtitles_target[get_user_lang($a['uppedby'])]['msg_deleted_your_sub']. $a['title'].($reason != "" ? $lang_subtitles_target[get_user_lang($a['uppedby'])]['msg_reason_is'].$reason : "");
	    $subject = $lang_subtitles_target[get_user_lang($a['uppedby'])]['msg_your_sub_deleted'];
	    $time = date("Y-m-d H:i:s");
	    sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $a[uppedby], '" . $time . "', " . sqlesc($msg) . ", ".sqlesc($subject).")") or sqlerr(__FILE__, __LINE__);
	  }
	  $res = sql_query("SELECT lang_name from language WHERE sub_lang=1 AND id = " . sqlesc($a["lang_id"])) or sqlerr(__FILE__, __LINE__);
	  $arr = mysql_fetch_assoc($res);
	  write_log("$arr[lang_name] Subtitle $delete ($a[title]) was deleted by ". (($a["anonymous"] == 'yes' && $a["uppedby"] == $CURUSER["id"]) ? "Anonymous" : $CURUSER['username']). ($a["uppedby"] != $CURUSER["id"] ? ", Mod Delete":"").($reason != "" ? " (".$reason.")" : ""));
	}
	else {
	  stdmsg($lang_subtitles['std_delete_subtitle'], $lang_subtitles['std_delete_subtitle_note']."<br /><form method=post action=subtitles.php?delete=$delete&sure=1>".$lang_subtitles['text_reason_is']."<input type=text style=\"width: 200px\" name=reason><input type=submit value=\"".$lang_subtitles['submit_confirm']."\"></form>");
	  stdfoot();
	  die;
	}
      }
    }
  }
}


if (get_user_class() >= UC_PEASANT) {
  //$url = $_COOKIE["subsurl"];

  if (!$size = $Cache->get_value('subtitle_sum_size')) {
    $res = sql_query("SELECT SUM(size) FROM subs");
    $row5 = mysql_fetch_row($res);
    $size = $row5[0];
    $Cache->cache_value('subtitle_sum_size', $size, 3600);
  }
  

  $langs = langlist("sub_lang");

  foreach ($langs as $row) {
    $lang_ids[] = $row['id'];
    $lang_names[] = $row['lang_name'];
  }

  $perpage = 30;
  $query = ($query ? " WHERE ".$query : "");
  $res = sql_query("SELECT COUNT(*) FROM subs $query") or sqlerr(__FILE__, __LINE__);
  $arr = mysql_fetch_row($res);
  $num = $arr[0];
  $rows = array();

  if ($num) {
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $num, "subtitles.php?".$q."&");

    $i = 0;
    $res = sql_query("SELECT subs.*, torrents.name AS torrent_name, language.flagpic, language.lang_name FROM subs LEFT JOIN torrents ON subs.torrent_id = torrents.id LEFT JOIN language ON subs.lang_id=language.id $query ORDER BY id DESC $limit") or sqlerr();

    $mod = get_user_class() >= $submanage_class;
    $pu = get_user_class() >= $delownsub_class;
    while ($arr = mysql_fetch_assoc($res)) {
      // the number $start_subid is just for legacy support of prevoiusly uploaded subs, if the site is completely new, it should be 0 or just remove it
      $arr['candelete'] = ($mod || ($pu && $arr["uppedby"] == $CURUSER["id"]));
      $rows[] = $arr;
    }
  }
}

$s = smarty(0);
$s->assign(array('lang' => $lang_subtitles,
		 'size' => $size,
		 'maxsubsize_main' => $maxsubsize_main,
		 'lang_ids' => $lang_ids,
		 'lang_names' => $lang_names,
		 'beanonymous_class' => $beanonymous_class,
		 'viewanonymous_class' => $viewanonymous_class,
		 'letter' => $letter,
		 'pagertop' => $pagertop,
		 'rows' => $rows,
		 'pagerbottom' => $pagerbottom
		 ));
if ($detail_torrent_id) {
  $query = "SELECT name FROM torrents WHERE id=$detail_torrent_id";
  $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
  $row = mysql_fetch_row($res);
  if ($row) {
    $torrentname = $row[0];
    $s->assign(array(
		     'detail_torrent_id' => $detail_torrent_id,
		     'torrent_name' => $torrentname
		     ));
  }
}

$s->display('subtitles.tpl', json_encode(array('userid' => $CURUSER['id'],
						   'torrent' => $detail_torrent_id,
						   'search' => $search,
						   'letter' => $letter,
						   'lang_id' => $lang_id,
						   'page' => $limit)));

stdfoot();
?>