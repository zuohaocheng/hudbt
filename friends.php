<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();


function purge_neighbors_cache()
{
	global $CURUSER;
	$cachefile = "cache/" . get_langfolder_cookie() . "/neighbors/" . $CURUSER['id'] . ".html";
	if (file_exists($cachefile))
		unlink($cachefile);
}

//make_folder("cache/" , get_langfolder_cookie());
//make_folder("cache/" , get_langfolder_cookie() . "/neighbors");

$userid = $CURUSER['id'];
$action = $_GET['action'];

if (!is_valid_id($userid))
stderr($lang_friends['std_error'], $lang_friends['std_invalid_id']."$userid.");

$user = $CURUSER;
// action: add -------------------------------------------------------------

if ($action == 'add')
{
	$targetid = $_GET['targetid'];
	$type = $_GET['type'];

	if (!is_valid_id($targetid))
	stderr($lang_friends['std_error'], $lang_friends['std_invalid_id']."$targetid.");

	if ($type == 'friend')
	{
		$table_is = $frag = 'friends';
		$field_is = 'friendid';
	}
	elseif ($type == 'block')
	{
		$table_is = $frag = 'blocks';
		$field_is = 'blockid';
	}
	else
	stderr($lang_friends['std_error'], $lang_friends['std_unknown_type']."$type");

	$r = sql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($r) == 1)
	stderr($lang_friends['std_error'], $lang_friends['std_user_id'].$targetid.$lang_friends['std_already_in'].$table_is.$lang_friends['std_list']);

	sql_query("INSERT INTO $table_is VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);
	
	purge_neighbors_cache();
	
	header("Location: " . get_protocol_prefix() . "$BASEURL/friends.php?id=$userid#$frag");
	die;
}

// action: delete ----------------------------------------------------------

if ($action == 'delete')
{
	$targetid = $_GET['targetid'];
	$sure = $_GET['sure'];
	$type = $_GET['type'];

	if ($type == 'friend')
	$typename = $lang_friends['text_friend'];
	else $typename = $lang_friends['text_block'];
	if (!is_valid_id($targetid))
	stderr($lang_friends['std_error'], $lang_friends['std_invalid_id']."$userid.");

	if (!$sure)
	stderr($lang_friends['std_delete'].$type, $lang_friends['std_delete_note'].$typename.$lang_friends['std_click'].
	"<a href=?id=$userid&action=delete&type=$type&targetid=$targetid&sure=1>".$lang_friends['std_here_if_sure'],false);

	if ($type == 'friend')
	{
		sql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
		if (mysql_affected_rows() == 0)
		stderr($lang_friends['std_error'], $lang_friends['std_no_friend_found']."$targetid");
		$frag = "friends";
	}
	elseif ($type == 'block')
	{
		sql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
		if (mysql_affected_rows() == 0)
		stderr($lang_friends['std_error'], $lang_friends['std_no_block_found']."$targetid");
		$frag = "blocks";
	}
	else
	stderr($lang_friends['std_error'], $lang_friends['std_unknown_type']."$type");


	purge_neighbors_cache();

	header("Location: " . get_protocol_prefix() . "$BASEURL/friends.php?id=$userid#$frag");
	die;
}

// main body  -----------------------------------------------------------------

stdhead($lang_friends['head_personal_lists_for']. $user['username']);

print('<h1 id="page-title">' . $lang_friends['text_personallist'] . " ".get_username($user[id])."</h1>\n");

//Start: Friends

print('<div id="friends" class="minor-list"><h2 class="transparentbg"><a name="friends">' . $lang_friends['text_friendlist'] . "</a></h2>\n");


$i = 0;

unset($friend_id_arr);
$res = sql_query("SELECT f.friendid as id, u.last_access, u.class, u.avatar, u.title FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$userid ORDER BY id") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($res) == 0) {
  $friends = $lang_friends['text_friends_empty'];
}
else {
  print('<ul>');
  while ($friend = mysql_fetch_array($res)) {
    $friend_id_arr[] = $friend["id"];
    $title = $friend["title"];
    if (!$title)
      $title = get_user_class_name($friend["class"],false,true,true);
    $body1 = get_username($friend["id"]) .
      " ($title)<br /><br />".$lang_friends['text_last_seen_on']. gettime($friend['last_access'],true, false);
    $body2 = "<a href=friends.php?id=$userid&action=delete&type=friend&targetid=" . $friend['id'] . ">".$lang_friends['text_remove_from_friends']."</a>".
      "<br /><br /><a href=sendmessage.php?receiver=" . $friend['id'] . ">".$lang_friends['text_send_pm']."</a>";

    $avatar = htmlspecialchars($friend["avatar"]);
    if (!$avatar)
      $avatar = "pic/default_avatar.png";



    print('<li><table style="width:400px;height:75px;" class="tablea main">');
    print("<tr valign=top class=tableb><td width=75 align=center style='padding: 0px'>" .
	  ($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width=75px src=\"$avatar\"></div>" : ""). "</td><td>\n");
    print('<table class="main">');
    print("<tr><td class=embedded style='padding: 5px' width=80%>$body1</td>\n");
    print("<td class=embedded style='padding: 5px' width=20%>$body2</td></tr>\n");
    print("</table>");
    print("</td></tr></table>\n");

    print('</li>');

    $i++;
  }
  print('</ul>');
}

  print('</div>');

//End: Friends


$res = sql_query("SELECT blockid as id FROM blocks WHERE userid=$userid ORDER BY id") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($res) == 0)
$blocks = $lang_friends['text_blocklist_empty'];
else {
	$i = 0;
	$blocks = '<ul>';
	while ($block = mysql_fetch_array($res)) {
		if ($i % 6 == 0)
		$blocks .= "<li>[<font class=small><a href=friends.php?id=$userid&action=delete&type=block&targetid=" .
		$block['id'] . ">D</a></font>] " . get_username($block["id"]) . "</li>";
		if ($i % 6 == 5)
		$i++;
	}
	$blocks .= "</ul>\n";
}

print('<div id="blocks" class="minor-list list-seperator"><h2 class="transparentbg"><a name="blocks">'.$lang_friends['text_blocked_users']."</a></h2>");
print($blocks);
print('</div>');


if (get_user_class() >= $viewuserlist_class)
	print('<div class="minor-nav"><a href="users.php">'.$lang_friends['text_find_user']."</div>");
stdfoot();
?>
