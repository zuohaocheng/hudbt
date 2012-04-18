<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

parked();
$userid = $_REQUEST["id"];
if (!$userid) {
  $userid = $CURUSER['id'];
}
int_check($userid,true);

if ($CURUSER["id"] != $userid && get_user_class() < $viewhistory_class) {
  permissiondenied();
}

$action = htmlspecialchars($_GET["action"]);
$page = array();

//-------- Global variables

$perpage = 15;

//-------- Action: View posts
function navbar_json() {
  global $action, $lang_userhistory;
  $texts = array($lang_userhistory['head_posts_history'],
		 $lang_userhistory['head_quoted_posts_history'],
		 $lang_userhistory['head_comments_history'],
		 $lang_userhistory['head_quoted_comments_history']
		 );
  $actions = array('viewposts',
		 'viewquotedposts',
		 'viewcomments',
		 'viewquotedcomments');
  for ($i=0; $i < count($actions); $i+=1) {
    if ($action == $actions[$i]) {
      $selected = $i;
    }
  }
  return array($texts, $actions, $selected);
}

function navbar() {
  global $userid;
  list($texts, $actions, $selected) = navbar_json();

  $list = array();
  for ($i=0; $i < count($texts); $i += 1) {
    $item = '<li>';
    if ($i != $selected) {
      $item .= '<a href="?action=' . $actions[$i] . '&id=' . $userid . '">';
    }
    else {
      $item .= '<span class="gray">';
    }
    $item .= $texts[$i];
    if ($i != $selected) {
      $item .= '</a>';
    }
    else {
      $item .= '</span>';
    }
    $list[] = $item;
  }
  
  echo '<div id="navbar" class="minor-list list-seperator minor-nav"><ul>';
  echo implode('', $list);
  echo '</ul></div>';
}

function show_single_comment($arr) {
  $type = '';
  if ($arr['t_id']) {
    $id = $arr['t_id'];
    $arr['postname'] = $arr['t_name'];
    $type = 'torrent';
  }
  elseif ($arr['o_id']) {
    $id = $arr['o_id'];
    $arr['postname'] = $arr['o_name'];
    $type = 'offer';
  }

  if ($type) {
    single_comment($arr, $id, $type);
  }
}

if ($action == "viewposts") {
	$select_is = "COUNT(DISTINCT p.id)";

	$from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";

	$where_is = "p.userid = $userid AND f.minclassread <= " . $CURUSER['class'];

	$order_is = "p.id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_posts_found']);

	$postcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, $_SERVER["PHP_SELF"] . "?action=viewposts&id=$userid&");

	//------ Get user data

	$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 1)
	{
		$arr = mysql_fetch_assoc($res);

		$subject = get_username($userid);
	}
	else
	$subject = "unknown[$userid]";

	//------ Get posts

	$from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";

	$select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_posts_found']);

	$page['title'] = $lang_userhistory['head_posts_history'];
	$page['h1'] = $lang_userhistory['text_posts_history_for'].$subject;
	ob_start();

	if ($postcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysql_fetch_assoc($res)) {
		$postid = $arr["id"];
		$posterid = $arr["userid"];
		$topicid = $arr["t_id"];
		$topicname = $arr["subject"];
		$forumid = $arr["f_id"];
		$forumname = $arr["name"];
		$newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;
		$added = gettime($arr["added"], true, false, false);

		print('<div class="forum-author minor-list"><ul><li>' . $added . '</li><li>' . $lang_userhistory['text_forum'].
	    "<a href=forums.php?action=viewforum&forumid=$forumid>$forumname</a></li><li>" . $lang_userhistory['text_topic'].
	    "<a href=forums.php?action=viewtopic&topicid=$topicid>$topicname</a> </li><li>".$lang_userhistory['text_post'].
      "<a href=forums.php?action=viewtopic&topicid=$topicid&page=p$postid#pid$postid>#$postid</a>" .
      ($newposts ? '</li><li><b>(<span class="new">'.$lang_userhistory['text_new']."</span>)</b>" : "") .
      "</li></ul></div>\n");

      
      print('<div class="frame table td">');

      $body = format_comment($arr["body"]);

      if (is_valid_id($arr['editedby']))
      {
      	$subres = sql_query("SELECT username FROM users WHERE id=$arr[editedby]");
      	if (mysql_num_rows($subres) == 1)
      	{
      		$subrow = mysql_fetch_assoc($subres);
      		$body .= '<div class="post-edited">'.$lang_userhistory['text_last_edited'].get_username($arr['editedby']).$lang_userhistory['text_at']."$arr[editdate]</div>\n";
      	}
      }

      print($body);
      echo '</div>';
	}

	end_frame();

	end_main_frame();

	if ($postcount > $perpage) echo $pagerbottom;

  $page['content'] = ob_get_clean();

}
elseif ($action == 'viewquotedposts') {
  $select = 'COUNT(*)';
  $from = 'posts p INNER JOIN posts p1 ON p.quote = p1.id INNER JOIN topics t ON p.topicid = t.id';
  $where = 'p1.userid = ' . $userid;
  $order = 'p.id DESC';
  $query = "SELECT $select FROM $from WHERE $where ORDER BY $order";
  $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
  $arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_posts_found']);
  $postcount = $arr[0];
  list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, "?action=viewquotedposts&id=$userid&");
  
  $select = 'p.userid, p.topicid, p.id, p.added, p.body, p.editedby, p.editdate, t.subject AS postname, t.forumid, t.locked';
  $subres = sql_query("SELECT $select FROM $from WHERE $where ORDER BY $order $limit") or sqlerr(__FILE__, __LINE__);
  
  $page['title'] = $lang_userhistory['head_quoted_posts_history'];
  $page['h1'] = $lang_userhistory['text_quoted_posts_history_for'];
  ob_start();
  if ($postcount > $perpage) {
    echo $pagertop;
  }
  echo '<div id="forum-posts"><ol>';
  while ($arr = mysql_fetch_assoc($subres)) {
    list($read, $post, $modify) = get_forum_privilege($arr['forumid']);
    if (!$read) {
      continue;
    }
    $forum = get_forum_row($arr['forumid']);
    $arr['postname'] = $forum['name'] . ' - ' . $arr['postname'];
    $locked = ($arr["locked"] == 'yes');
    single_post($arr, $post, $modify, $locked);
  }
  echo '</ol></div>';
  if ($postcount > $perpage) {
    echo $pagerbottom;
  }
  $page['content'] = ob_get_clean();

}

//-------- Action: View comments

elseif ($action == "viewcomments") {
  $select_is = "COUNT(*)";

  // LEFT due to orphan comments
  $from_is = "comments AS c LEFT JOIN torrents as t ON c.torrent = t.id LEFT JOIN offers o ON c.offer = o.id";

  $where_is = "c.user = $userid";
  $order_is = "c.id DESC";

  $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";

  $res = sql_query($query) or sqlerr(__FILE__, __LINE__);

  $arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_comments_found']);

  $commentcount = $arr[0];

  //------ Make page menu

  list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, $_SERVER["PHP_SELF"] . "?action=viewcomments&id=$userid&");

  //------ Get user data

  $res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

  if (mysql_num_rows($res) == 1) {
    $arr = mysql_fetch_assoc($res);
    $subject = get_username($userid);
  }
  else {
    $subject = "unknown[$userid]";
  }

  //------ Get comments

  $select_is = "c.torrent AS t_id, c.offer AS o_id, c.id, c.text, c.user, c.added, c.editedby, c.editdate, t.name AS t_name, o.name AS o_name";

  $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

  $res = sql_query($query) or sqlerr(__FILE__, __LINE__);

  if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_comments_found']);

  $page['title'] = $lang_userhistory['head_comments_history'];
  $page['h1'] = $lang_userhistory['text_comments_history_for']."$subject</h1>\n";
  ob_start();

  if ($commentcount > $perpage) echo $pagertop;

  //------ Print table

  echo '<div id="forum-posts"><ol>';
  while ($arr = mysql_fetch_assoc($res)) {
    if ($arr['t_name']) {
      $arr['postname'] = $arr['t_name'];
    }
    else {
      $arr['postname'] = $arr['o_name'];
    }
    show_single_comment($arr);
  }
  echo '</ol></div>';

  if ($commentcount > $perpage) echo $pagerbottom;
  $page['content'] = ob_get_clean();
}
elseif ($action == 'viewquotedcomments') {
  $res = sql_query('SELECT COUNT(*) AS postname FROM comments c INNER JOIN comments c1 ON c.quote = c1.id WHERE c1.user=' . $userid . ' ORDER BY c.id') or sqlerr(__FILE__, __LINE__);
  $arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_comments_found']);
  $commentcount = $arr[0];
  $perpage = 10;
  list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, $_SERVER["PHP_SELF"] . "?action=viewquotedcomments&id=$userid&");


  $subres = sql_query('SELECT c.torrent AS t_id, c.offer AS o_id, c.id, c.text, c.user, c.added, c.editedby, c.editdate, t.name AS t_name, o.name AS o_name FROM comments c INNER JOIN comments c1 ON c.quote = c1.id LEFT JOIN torrents t ON c.torrent = t.id LEFT JOIN offers o ON c.offer = o.id WHERE c1.user=' . $userid . ' ORDER BY c.id ' . $limit) or sqlerr(__FILE__, __LINE__);
  $allrows = array();
  
  $page['title'] = $lang_userhistory['head_quoted_comments_history'];
  $page['h1'] = $lang_userhistory['text_quoted_comments_history_for'];

  ob_start();

  if ($commentcount > $perpage) echo $pagertop;
  echo '<div id="forum-posts"><ol>';
  while ($arr = mysql_fetch_assoc($subres)) {
    if ($arr['t_name']) {
      $arr['postname'] = $arr['t_name'];
    }
    else {
      $arr['postname'] = $arr['o_name'];
    }
    show_single_comment($arr);
  }
  echo '</ol></div>';
  if ($commentcount > $perpage) echo $pagerbottom;
  $page['content'] = ob_get_clean();
}
else {
  $page['title'] = $lang_userhistory['head'];
  $page['h1'] = $lang_userhistory['head'];
}

if ($_REQUEST['format'] == 'json') {
  header('Content-type:application/json');
  $page['title'] = $SITENAME . ' ' . $page['title'];
  $page['navbar'] = navbar_json();
  echo php_json_encode($page);
}
else {
  stdhead($page['title']);
  echo '<h1 id="page-title">' . $page['h1'] . '</h1>';
  navbar();
  echo '<div id="contents">' . $page['content'] . '</div>';
  stdfoot();
}

//-------- Handle unknown action

/* if ($action != "") */
/* stderr($lang_userhistory['std_history_error'], $lang_userhistory['std_unkown_action']); */

/* //-------- Any other case */

/* stderr($lang_userhistory['std_history_error'], $lang_userhistory['std_invalid_or_no_query']); */

?>
