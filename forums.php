<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
if ($enableextforum == 'yes') //check whether internal forum is disabled
	permissiondenied();

if ($_REQUEST['format'] == 'json') {
  $format = 'json';
}
else {
  $format = 'html';
}

// ------------- start: functions ------------------//
//print forum stats
function forum_stats () {
	global $lang_forums, $Cache, $today_date;

	if (!$activeforumuser_num = $Cache->get_value('active_forum_user_count')){
		$secs = 900;
		$dt = date("Y-m-d H:i:s",(TIMENOW - $secs));
		$activeforumuser_num = get_row_count("users","WHERE forum_access >= ".sqlesc($dt));
		$Cache->cache_value('active_forum_user_count', $activeforumuser_num, 300);
	}
	if ($activeforumuser_num){
		$forumusers = $lang_forums['text_there'].is_or_are($activeforumuser_num)."<b>".$activeforumuser_num."</b>".$lang_forums['text_online_user'].add_s($activeforumuser_num).$lang_forums['text_in_forum_now'];
	}
	else
		$forumusers = $lang_forums['text_no_active_users'];
?>
<h2 style="text-align:left;"><?php echo $lang_forums['text_stats'] ?></h2>
<table width="100%"><tr><td class="text">
<?php
	if (!$postcount = $Cache->get_value('total_posts_count')){
		$postcount = get_row_count("posts");
		$Cache->cache_value('total_posts_count', $postcount, 96400);
	}
	if (!$topiccount = $Cache->get_value('total_topics_count')){
		$topiccount = get_row_count("topics");
		$Cache->cache_value('total_topics_count', $topiccount, 96500);
	}
	if (!$todaypostcount = $Cache->get_value('today_'.$today_date.'_posts_count')) {
		$todaypostcount = get_row_count("posts", "WHERE added > ".sqlesc(date("Y-m-d")));
		$Cache->cache_value('today_'.$today_date.'_posts_count', $todaypostcount, 700);
	}
	print($lang_forums['text_our_members_have'] ."<b>".$postcount."</b>". $lang_forums['text_posts_in_topics']."<b>".$topiccount."</b>".$lang_forums['text_in_topics']."<b><font class=\"new\">".$todaypostcount."</font></b>".$lang_forums['text_new_post'].add_s($todaypostcount).$lang_forums['text_posts_today']."<br /><br />");
	print($forumusers);
?>
</td></tr></table>
<?php
}

//set all topics as read
function catch_up()
{
	global $CURUSER, $Cache;

	if (!$CURUSER)
		die;
	sql_query("DELETE FROM readposts WHERE userid=".sqlesc($CURUSER['id']));
	$Cache->delete_value('user_'.$CURUSER['id'].'_last_read_post_list');
	$lastpostid=get_single_value("posts","id","ORDER BY id DESC");
	if ($lastpostid){
		$CURUSER['last_catchup'] = $lastpostid;
		sql_query("UPDATE LOW_PRIORITY users SET last_catchup = ".sqlesc($lastpostid)." WHERE id=".sqlesc($CURUSER['id']));
	}
}

//return image
function get_topic_image($status= "read"){
	global $lang_forums;
	switch($status){
		case "read": {
			return "<img class=\"unlocked\" src=\"pic/trans.gif\" alt=\"read\" title=\"".$lang_forums['title_read']."\" />";
			break;
			}
		case "unread": {
			return "<img class=\"unlockednew\" src=\"pic/trans.gif\" alt=\"unread\" title=\"".$lang_forums['title_unread']."\" />";
			break;
		}
		case "locked": {
			return "<img class=\"locked\" src=\"pic/trans.gif\" alt=\"locked\" title=\"".$lang_forums['title_locked']."\" />";
			break;
		}
		case "lockednew": {
			return "<img class=\"lockednew\" src=\"pic/trans.gif\" alt=\"lockednew\" title=\"".$lang_forums['title_locked_new']."\" />";
			break;
		}
	}
}

function highlight_topic($subject, $hlcolor=0) {
	$colorname=get_hl_color($hlcolor);
	if ($colorname) {
	  $subject = '<span style="font-weight:bold;color:' . $colorname . '">'.$subject."</span>";
	}
	return $subject;
}

function check_whether_exist($id, $place='forum') {
	global $lang_forums;
	int_check($id,true);
	switch ($place){
		case 'forum':
		{
			$count = get_row_count("forums","WHERE id=".sqlesc($id));
			if (!$count)
				stderr($lang_forums['std_error'],$lang_forums['std_no_forum_id']);
			break;
		}
		case 'topic':
		{
			$count = get_row_count("topics","WHERE id=".sqlesc($id));
			if (!$count)
				stderr($lang_forums['std_error'],$lang_forums['std_bad_topic_id']);
			$forumid = get_single_value("topics","forumid","WHERE id=".sqlesc($id));
			check_whether_exist($forumid, 'forum');
			break;
		}
		case 'post':
		{
			$count = get_row_count("posts","WHERE id=".sqlesc($id));
			if (!$count)
				stderr($lang_forums['std_error'],$lang_forums['std_no_post_id']);
			$topicid = get_single_value("posts","topicid","WHERE id=".sqlesc($id));
			check_whether_exist($topicid, 'topic');
			break;
		}
	}
}

//update the last post of a topic
function update_topic_last_post($topicid)
{
	global $lang_forums;
	$res = sql_query("SELECT id FROM posts WHERE topicid=".sqlesc($topicid)." ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_row($res) or die($lang_forums['std_no_post_found']);
	$postid = $arr[0];
	sql_query("UPDATE topics SET lastpost=".sqlesc($postid)." WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
}

function get_last_read_post_id($topicid) {
	global $CURUSER, $Cache;
	static $ret;
	if (!$ret && !$ret = $Cache->get_value('user_'.$CURUSER['id'].'_last_read_post_list')){
		$ret = array();
		$res = sql_query("SELECT * FROM readposts WHERE userid=" . sqlesc($CURUSER['id']));
		if (mysql_num_rows($res) != 0){
			while ($row = mysql_fetch_array($res))
			$ret[$row['topicid']] = $row['lastpostread'];
			$Cache->cache_value('user_'.$CURUSER['id'].'_last_read_post_list', $ret, 900);
		}
		else $Cache->cache_value('user_'.$CURUSER['id'].'_last_read_post_list', 'no record', 900);
	}
	if ($ret != "no record" && $ret[$topicid] && $CURUSER['last_catchup'] < $ret[$topicid]){
		return $ret[$topicid];
	}
	elseif ($CURUSER['last_catchup'])
		return $CURUSER['last_catchup'];
	else return 0;
}

//-------- Inserts a compose frame
function insert_compose_frame($id, $type = 'new')
{
	global $maxsubjectlength, $CURUSER;
	global $lang_forums;
	$hassubject = false;
	$subject = "";
	$body = "";
	$edit = "";
	print("<form id=\"compose\" method=\"post\" name=\"compose\" action=\"?action=post\">\n");
	switch ($type){
		case 'new':
		{
			$forumname = get_single_value("forums","name","WHERE id=".sqlesc($id));
			$title = $lang_forums['text_new_topic_in']." <a href=\"".htmlspecialchars("?action=viewforum&forumid=".$id)."\">".htmlspecialchars($forumname)."</a> ".$lang_forums['text_forum'];
			$hassubject = true;
			break;
		}
		case 'reply':
		{
			$topicname = get_single_value("topics","subject","WHERE id=".sqlesc($id));
			$title = $lang_forums['text_reply_to_topic']." <a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$id)."\">".htmlspecialchars($topicname)."</a> ";
			break;
		}
		case 'quote':
		{
			$topicid=get_single_value("posts","topicid","WHERE id=".sqlesc($id));
			$topicname = get_single_value("topics","subject","WHERE id=".sqlesc($topicid));
			$title = $lang_forums['text_reply_to_topic']." <a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$topicid)."\">".htmlspecialchars($topicname)."</a> ";
			$res = sql_query("SELECT posts.body, users.username FROM posts LEFT JOIN users ON posts.userid = users.id WHERE posts.id=$id") or sqlerr(__FILE__, __LINE__);
			if (mysql_num_rows($res) != 1) {
			  stderr($lang_forums['std_error'], $lang_forums['std_no_post_id']);
			}

			echo '<input type="hidden" name="quote" value="' . $id . '" />';
			$arr = mysql_fetch_assoc($res);
			$body = "[quote=".htmlspecialchars($arr["username"])."]".htmlspecialchars(dequote(unesc($arr["body"])))."[/quote]";
			$id = $topicid;
			$type = 'reply';

			break;
		}
		case 'edit':
		{
			$res = sql_query("SELECT topicid, body FROM posts WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$topicid=$row['topicid'];
			$firstpost = get_single_value("posts","MIN(id)", "WHERE topicid=".sqlesc($topicid));
			if ($firstpost == $id){
				$subject = get_single_value("topics","subject","WHERE id=".sqlesc($topicid));
				$hassubject = true;
			}
			$body = htmlspecialchars(unesc($row["body"]));
			$title = $lang_forums['text_edit_post'];
			break;
		}
		default:
		{
			die;
		}
	}
	print("<input type=\"hidden\" name=\"id\" value=\"".$id."\" />");
	print("<input type=\"hidden\" name=\"type\" value=\"".$type."\" />");
	begin_compose($title, $type, $body, $hassubject, $subject);
	if($type==edit){
		$resedit=sql_query("SELECT editnotseen,userid FROM posts WHERE id = ".$id)or sqlerr(__FILE__, __LINE__);
 		$arredit = mysql_fetch_assoc($resedit) or stderr($lang_forums['std_forum_error'], $lang_forums['std_topic_not_found']);
	  $editnotseen=$arredit['editnotseen'];
	  $owner =$arredit['userid'];
		if(checkprivilege(["Posts","editnotseen"]) && ($CURUSER['id']==$owner)){
  		echo "<tr><td class=\"center\" colspan=\"2\"><label><input type=\"checkbox\" value=\"1\" name=\"editnotseen\"";
			echo ($editnotseen?" checked=\"checked\"" : "" ).">".$lang_forums[text_editnotseen]."</label></td></tr>";
 		}	
 	}

	end_compose();
	print("</form>");
}
// ------------- end: functions ------------------//
// ------------- start: Global variables ------------------//
$maxsubjectlength = 100;

if (is_numeric($forumpostsperpage))
	$postsperpage = $forumpostsperpage;//system-wide setting
else $postsperpage = 10;
//get topics per page
$topicsperpage = 0;

if (is_numeric($forumtopicsperpage_main))
	$topicsperpage = $forumtopicsperpage_main;//system-wide setting
else $topicsperpage = 20;

$today_date = date("Y-m-d",TIMENOW);
// ------------- end: Global variables ------------------//

$action = htmlspecialchars(trim($_GET["action"]));

//-------- Action: New topic
if ($action == "newtopic") {
	$forumid = 0+$_GET["forumid"];
	check_whether_exist($forumid, 'forum');
	stdhead($lang_forums['head_new_topic']);
	begin_main_frame();
	insert_compose_frame($forumid,'new');
	end_main_frame();
	stdfoot();
	die;
}
elseif ($action == "quotepost") {
	$postid = 0 + $_GET["postid"];
	check_whether_exist($postid, 'post');
	stdhead($lang_forums['head_post_reply']);
	begin_main_frame();
	insert_compose_frame($postid, 'quote');
	end_main_frame();
	stdfoot();
	die;
}

//-------- Action: Reply

elseif ($action == "reply") {
	$topicid = 0+$_GET["topicid"];
	check_whether_exist($topicid, 'topic');
	stdhead($lang_forums['head_post_reply']);
	begin_main_frame();
	insert_compose_frame($topicid, 'reply');
	end_main_frame();
	stdfoot();
	die;
}

//-------- Action: Edit post

elseif ($action == "editpost") {
	$postid = 0+$_GET["postid"];
	check_whether_exist($postid, 'post');

	$res = sql_query("SELECT userid, topicid FROM posts WHERE id=".sqlesc($postid)) or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_assoc($res);

	$res2 = sql_query("SELECT locked FROM topics WHERE id = " . $arr["topicid"]) or sqlerr(__FILE__, __LINE__);
	$arr2 = mysql_fetch_assoc($res2);
	$locked = ($arr2["locked"] == 'yes');

	$ismod = is_forum_moderator($postid, 'post'
);
	if (($CURUSER["id"] != $arr["userid"] || $locked) && get_user_class() < $postmanage_class && !$ismod)
		permissiondenied();

	stdhead($lang_forums['text_edit_post']);
	begin_main_frame();
  insert_compose_frame($postid, 'edit');
	end_main_frame();
	stdfoot();
	die;
}

//-------- Action: Post
elseif ($action == "post") {
	if ($CURUSER["forumpost"] == 'no')
	{
		stderr($lang_forums['std_sorry'], $lang_forums['std_unauthorized_to_post'],false);
		die;
	}
	$id = 0 + $_REQUEST["id"];
	$type = $_POST["type"];
	$subject = $_POST["subject"];
	$body = trim($_POST["body"]);
	$hassubject = false;
	$quote = 'NULL';

	switch ($type){
		case 'new':
		{
			check_whether_exist($id, 'forum');
			$forumid = $id;
			$hassubject = true;
			break;
		}
		case 'reply':
		{
			check_whether_exist($id, 'topic');
			$topicid = $id;
			$forumid = get_single_value("topics", "forumid", "WHERE id=".sqlesc($topicid));

			if (array_key_exists('quote', $_REQUEST)) {
			  $quote = 0 + $_REQUEST['quote'];
			  $res = sql_query('SELECT p.userid FROM posts p INNER JOIN users u ON p.userid=u.id WHERE p.id=' . $quote . ' AND p.topicid=' . $id) or sqlerr(__FILE__, __LINE__);
			  if (mysql_num_rows($res) != 1) {
			    $quote = 'NULL';
			  }
			  $quoteduser = mysql_fetch_array($res);
			}

			break;
		}
		case 'edit':
		{
			check_whether_exist($id, 'post');
			$res = sql_query("SELECT topicid FROM posts WHERE id=".sqlesc($id)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$topicid=$row['topicid'];
			$forumid = get_single_value("topics", "forumid", "WHERE id=".sqlesc($topicid));
			$firstpost = get_single_value("posts","MIN(id)", "WHERE topicid=".sqlesc($topicid));
			if ($firstpost == $id){
				$hassubject = true;
			}
			break;
		}
		default:
		{
			die;
		}
	}

	if ($hassubject){
		$subject = trim($subject);
		if (!$subject)
			stderr($lang_forums['std_error'], $lang_forums['std_must_enter_subject']);
		if (strlen($subject) > $maxsubjectlength)
			stderr($lang_forums['std_error'], $lang_forums['std_subject_limited']);
	}

	//------ Make sure sure user has write access in forum
	$arr = get_forum_row($forumid) or die($lang_forums['std_bad_forum_id']);

	if (get_user_class() < $arr["minclasswrite"] || ($type =='new' && get_user_class() < $arr["minclasscreate"]))
		permissiondenied();

	if ($body == "")
		stderr($lang_forums['std_error'], $lang_forums['std_no_body_text']);

	$userid = 0+$CURUSER["id"];
	$date = date("Y-m-d H:i:s");

	if ($type != 'new'){
		//---- Make sure topic is unlocked

		$res = sql_query("SELECT locked FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
		$arr = mysql_fetch_assoc($res) or die("Topic id n/a");
		if ($arr["locked"] == 'yes' && get_user_class() < $postmanage_class && !is_forum_moderator($topicid, 'topic'))
			stderr($lang_forums['std_error'], $lang_forums['std_topic_locked']);
	}

	if ($type == 'edit')
	{
		if ($hassubject){
			sql_query("UPDATE topics SET subject=".sqlesc($subject)." WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
			$forum_last_replied_topic_row = $Cache->get_value('forum_'.$forumid.'_last_replied_topic_content');
			if ($forum_last_replied_topic_row && $forum_last_replied_topic_row['id'] == $topicid)
				$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
		}		
		$editnotseen = empty($_REQUEST['editnotseen'])?'0':$_REQUEST['editnotseen'];
		$editnotseen = $editnotseen + 0;
		if ((!checkprivilege(["Posts","editnotseen"]))&& $editnotseen==1){
			permissiondenied();
		permissiondenied();
		}
		$forum_row = get_forum_row($forumid);
		if($forum_row["minclassread"]>=$confiforumlog_class)
			$permi = "high";
		else
			$permi = "normal";
		sql_query("UPDATE posts SET body=".sqlesc($body).", editdate=".sqlesc($date).", editedby=".sqlesc($CURUSER[id]).",editnotseen = ".$editnotseen." WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
		if(mysql_affected_rows())
			write_forum_log("Post:$id in Topic:$topicid was edited by $CURUSER[username].", $permi);
		$postid = $id;
		$Cache->delete_value('post_'.$postid.'_content');
	}
	else
	{
		// Anti Flood Code
		// To ensure that posts are not entered within 10 seconds limiting posts
		// to a maximum of 360*6 per hour.
		if (get_user_class() < $postmanage_class) {
			if (strtotime($CURUSER['last_post']) > (TIMENOW - 10))
			{
				$secs = 10 - (TIMENOW - strtotime($CURUSER['last_post']));
				stderr($lang_forums['std_error'],$lang_forums['std_post_flooding'].$secs.$lang_forums['std_seconds_before_making'],false);
			}
		}
		if ($type == 'new') { //new topic
			//add bonus
			KPS("+",$starttopic_bonus,$userid);

			//---- Create topic
			sql_query("INSERT INTO topics (userid, forumid, subject) VALUES($userid, $forumid, ".sqlesc($subject).")") or sqlerr(__FILE__, __LINE__);
			$topicid = mysql_insert_id() or stderr($lang_forums['std_error'],$lang_forums['std_no_topic_id_returned']);
			sql_query("UPDATE forums SET topiccount=topiccount+1, postcount=postcount+1 WHERE id=".sqlesc($forumid));
		}
		else { // new post
			//add bonus
			KPS("+",$makepost_bonus,$userid);
			sql_query("UPDATE forums SET postcount=postcount+1 WHERE id=".sqlesc($forumid));
//
		}
		$values = array($topicid, $userid, sqlesc($date), sqlesc($body), sqlesc($body), $quote);
		sql_query("INSERT INTO posts (topicid, userid, added, body, ori_body, quote) VALUES (" . implode(',', $values) . ')') or sqlerr(__FILE__, __LINE__);
		$postid = mysql_insert_id() or die($lang_forums['std_post_id_not_available']);
		$Cache->delete_value('forum_'.$forumid.'_post_'.$today_date.'_count');
		$Cache->delete_value('today_'.$today_date.'_posts_count');
		$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
		$Cache->delete_value('topic_'.$topicid.'_post_count');
		$Cache->delete_value('user_'.$userid.'_post_count');

		if ($type == 'new') {
			// update the first post of topic
			sql_query("UPDATE topics SET firstpost=$postid, lastpost=$postid WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
		}
		else
		{
			sql_query("UPDATE topics SET lastpost=$postid WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
		}
		sql_query("UPDATE LOW_PRIORITY users SET last_post=".sqlesc($date)." WHERE id=".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
	}

	//------ All done, redirect user to the post

	$headerstr = "Location: " . get_protocol_prefix() . "$BASEURL/forums.php?action=viewtopic&topicid=$topicid";

	if ($type == 'edit') {
	  header($headerstr."&page=p".$postid."#pid".$postid, true, 303);
	}
	else {
	  header($headerstr."&page=last#pid$postid", true, 303);
	}
	die;
}

//-------- Action: View topic

elseif ($action == "viewtopic") {
	$highlight = htmlspecialchars(trim($_REQUEST["highlight"]));
	$topicid = 0+$_REQUEST["topicid"];
	int_check($topicid,true);
	$page = $_REQUEST["page"];
	$authorid = 0+$_REQUEST["authorid"];
	if ($authorid)
	{
		$where = "WHERE topicid=".sqlesc($topicid)." AND userid=".sqlesc($authorid);
		$addparam = "action=viewtopic&topicid=".$topicid."&authorid=".$authorid;
	}
	else
	{
		$where = "WHERE topicid=".sqlesc($topicid);
		$addparam = "action=viewtopic&topicid=".$topicid;
	}
	$userid = $CURUSER["id"];

	//------ Get topic info

	$res = sql_query("SELECT * FROM topics WHERE id=".sqlesc($topicid)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_assoc($res) or stderr($lang_forums['std_forum_error'], $lang_forums['std_topic_not_found']);

	$forumid = $arr['forumid'];
	$locked = $arr['locked'] == "yes";
	$orgsubject = $arr['subject'];
	$subject = htmlspecialchars($arr['subject']);
	if ($highlight){
		$subject = highlight($highlight,$orgsubject);
	}
	$sticky = $arr['sticky'] == "yes";
	$selected = ($arr['selected'] == "yes");
	$hlcolor = $arr['hlcolor'];
	$views = $arr['views'];
	$forumid = $arr["forumid"];

	$row = get_forum_row($forumid);
	//------ Get forum name, moderators
	$forumname = $row['name'];

	list($read, $maypost, $maymodify) = get_forum_privilege($forumid, $locked);
	if (!$read) {
	  stderr($lang_forums['std_error'], $lang_forums['std_unpermitted_viewing_topic']);
	}
	
	
	//------ Update hits column
	sql_query("UPDATE topics SET views = views + 1 WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

	//------ Get post count
	$r = sql_query("SELECT COUNT(*) AS count, MAX(id) AS maxid FROM posts $where") or sqlerr(__FILE__, __LINE__);
	$a = mysql_fetch_assoc($r) or die(mysql_error());
	$postcount = $a['count'];
	$maxid = $a['maxid'];

	if (!$authorid)
		$Cache->cache_value('topic_'.$topicid.'_post_count', $postcount, 3600);

	//------ Make page menu

	$pages = ceil($postcount / $postsperpage);

	if ($page[0] == "p") {
	  $findpost = 0 + substr($page, 1);
	  $res = sql_query("SELECT COUNT(*) FROM posts $where AND id < $findpost ORDER BY added") or sqlerr(__FILE__, __LINE__);
	  $arr = mysql_fetch_row($res);
	  $i = $arr[0];
	  $page = floor($i / $postsperpage);
	}
	elseif ($page === "last") {
	  $page = $pages-1;
	}
	elseif(isset($page)) {
	  if($page < 0){
	    $page = 0;
	  }
	  elseif ($page > $pages - 1){
	    $page = $pages - 1;
	  }
	}
	else {
		$page = 0;
	}
	list($pagertop, $pagerbottom, $limit, $next_page_href, $offset) = pager($postsperpage, $postcount, "?".$addparam . '&' , array('page' => $page));
	//------ Get posts

	$res = sql_query("SELECT * FROM posts $where ORDER BY id $limit") or sqlerr(__FILE__, __LINE__);

	stdhead($lang_forums['head_view_topic']." \"".$orgsubject."\"");
	begin_main_frame("",true);

	print("<h1 id=\"page-title\"><a class=\"faqlink\" href=\"forums.php\">".$SITENAME."&nbsp;".$lang_forums['text_forums']."</a> --> <a class=\"faqlink\" href=\"".htmlspecialchars("?action=viewforum&forumid=".$forumid)."\">".$forumname."</a> --> <span id=\"top\">".$subject."</span>");
	echo ' <span class="minor-list properties horizon-compact"><ul>';
	if ($locked) {
	  echo '<li>' . $lang_forums['text_locked'] . '</li>';
	}
	if ($sticky) {
	  echo '<li>' . $lang_forums['title_sticky'] . '</li>';
	}
	if ($selected) {
	  echo '<li>' . $lang_forums['title_selected'] . '</li>';
	}
	echo '</ul></span>';
	echo '</h1>';
	end_main_frame();

	//------ Print table

	print('<div id="forum-header"><div id="post-viewed-count-container">');
	print($lang_forums['there_is'].'<span id="post-viewed-count">'.$views.'</span>'.$lang_forums['hits_on_this_topic']);
	print("</div>\n");

	if ($maypost) {
		print('<div id="reply-post"><a href="'.htmlspecialchars("?action=reply&topicid=".$topicid)."\"><img class=\"f_reply\" src=\"pic/trans.gif\" alt=\"Add Reply\" title=\"".$lang_forums['title_reply_directly'].'" /></a></div>');
	}

	print($pagertop);
	print("</div>\n");

	$pc = mysql_num_rows($res);

	$pn = 0;
	$lpr = get_last_read_post_id($topicid);

	if ($Advertisement->enable_ad()) {
	  $forumpostad=$Advertisement->get_ad('forumpost');
	}

	echo '<div class="forum-posts"><ol>';

	if ($authorid) {
	  /* can be replaced by
SELECT id, rownum FROM (SELECT @x:=@x+1 AS rownum, id, userid FROM (SELECT @x:=0, id, userid FROM posts WHERE topicid=10153) AS data ORDER BY id) AS tmp WHERE tmp.userid=66551;
	  */
	  $query = "SELECT shows.id AS shows_id FROM posts AS ref LEFT JOIN (SELECT id FROM posts $where ORDER BY id $limit) AS shows ON ref.id=shows.id WHERE ref.topicid=$topicid AND ref.id <= $maxid;";
	  $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
	  $i = 0;
	  $floorsDict = [];
	  while ($arr = mysql_fetch_row($r)) {
	    $t = $arr[0];
	    if ($t) {
	      $floorsDict[$t] = $i;
	    }
	    $i += 1;
	  }
	}
	
	while ($arr = mysql_fetch_assoc($res)) {
	  if ($pn>=1) {
	    if ($Advertisement->enable_ad()) {
	      if ($forumpostad[$pn-1])
		echo '<div class="forum-ad table td" id="ad_forumpost_'.$pn."\">".$forumpostad[$pn-1]."</div>";
	    }
	  }
	  ++$pn;

	  $postid = $arr["id"];
	  $posterid = $arr["userid"];

	  if ($pn == $pc) {
	    if ($postid > $lpr) {
	      if ($lpr == $CURUSER['last_catchup']) { // There is no record of this topic
		sql_query("INSERT INTO readposts(userid, topicid, lastpostread) VALUES (".$userid.", ".$topicid.", ".$postid.")") or sqlerr(__FILE__, __LINE__);
	      }
	      elseif ($lpr > $CURUSER['last_catchup']) { //There is record of this topic
		sql_query("UPDATE readposts SET lastpostread=$postid WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);
	      }
	      $Cache->delete_value('user_'.$CURUSER['id'].'_last_read_post_list');
	    }
	  }

	  $arr['topicid'] = $topicid;
	  if ($authorid) {
	    $floor = $floorsDict[$arr['id']] + 1;
	  }
	  else {
	    $floor = $pn + $offset;
	  }
	  $showori=($_REQUEST['showori_body']=='1'?"1":"0");
		$showori=$showori+0;
	  if($showori&&$maymodify){
	  	$arr['body']=$arr['ori_body'];
	  	$arr['editedby']="";
	  }
	  single_post($arr, $maypost, $maymodify, $locked, $highlight, ($pn == $pc), $floor);
	}
	echo '</ol></div>';

	//------ Mod options

	if ($maymodify) {
	  function generateForm($action) {
	    
	  }
	  
		print('<div id="forum-toolbox" class="minor-list"><ul>');
		print("<li><form method=\"post\" action=\"?action=setsticky\">\n");
		print("<input type=\"hidden\" name=\"topicid\" value=\"".$topicid."\" />\n");
		print("<input type=\"hidden\" name=\"returnto\" value=\"".htmlspecialchars($_SERVER[REQUEST_URI])."\" />\n");
		print("<input type=\"hidden\" name=\"value\" value=\"".($sticky ? 'no' : 'yes')."\" /><input type=\"submit\" class=\"medium\" value=\"".($sticky ? $lang_forums['submit_unsticky'] : $lang_forums['submit_sticky'])."\" /></form></li>\n");
		
		echo '<li><form method="POST" action="?action=setselected">';
		print("<input type=\"hidden\" name=\"topicid\" value=\"".$topicid."\" />\n");
		print("<input type=\"hidden\" name=\"returnto\" value=\"".htmlspecialchars($_SERVER[REQUEST_URI])."\" />\n");
		print("<input type=\"hidden\" name=\"value\" value=\"".($selected ? 'no' : 'yes')."\" /><input type=\"submit\" class=\"medium\" value=\"".($selected ? $lang_forums['submit_unselected'] : $lang_forums['submit_selected'])."\" /></form></li>\n");
		echo '</form></li>';
		
		print("<li><form method=\"post\" action=\"?action=setlocked\">\n");
		print("<input type=\"hidden\" name=\"topicid\" value=\"".$topicid."\" />\n");
		print("<input type=\"hidden\" name=\"returnto\" value=\"".htmlspecialchars($_SERVER[REQUEST_URI])."\" />\n");
		print("<input type=\"hidden\" name=\"locked\" value=\"".($locked ? 'no' : 'yes')."\" /><input type=\"submit\" class=\"medium\" value=\"".($locked ? $lang_forums['submit_unlock'] : $lang_forums['submit_lock'])."\" /></form></li>\n");

		print("<li><form method=\"get\" action=\"?\">\n");
		print("<input type=\"hidden\" name=\"action\" value=\"deletetopic\" />\n");
		print("<input type=\"hidden\" name=\"topicid\" value=\"".$topicid."\" />\n");
		print("<input type=\"hidden\" name=\"forumid\" value=\"".$forumid."\" />\n");
		print("<input type=\"submit\" class=\"medium\" value=\"".$lang_forums['submit_delete_topic']."\" /></form></li>\n");

		print("<li><form method=\"get\" action=\"?\">\n");
		print("<input type=\"hidden\" name=\"action\" value=\"viewtopic\" />\n");
		print("<input type=\"hidden\" name=\"topicid\" value=\"".$topicid."\" />\n");
		if($authorid){
		print("<input type=\"hidden\" name=\"authorid\" value=\"".$authorid."\" />\n");}
		if($page){
		print("<input type=\"hidden\" name=\"page\" value=\"".$page."\" />\n");}//These ifs are just to make the url cleaner.Feel no guilty to delete them if you don't like them
		print("<input type=\"hidden\" name=\"showori_body\" value=\"".($showori ? '0' : '1')."\" /><input type=\"submit\" class=\"medium\" value=\"".($showori ? $lang_forums[text_shownow] : $lang_forums['text_showori'])."\" /></form></li>\n");
		
		print("<li><form method=\"post\" id=\"forum-movetopic\" action=\"".htmlspecialchars("?action=movetopic&topicid=".$topicid)."\">\n"."&nbsp;".$lang_forums['text_move_thread_to']."&nbsp;<select class=\"med\" name=\"forumid\">");
		$forums = get_forum_row();
		foreach ($forums as $arr){
			if ($arr["id"] != $forumid && get_user_class() >= $arr["minclasswrite"])
				print("<option value=\"" . $arr["id"] . "\">" . htmlspecialchars($arr["name"]) . "</option>\n");
		}
		print("</select> <input type=\"submit\" class=\"medium\" value=\"".$lang_forums['submit_move']."\" /></form></li>");

		print("<li><form method=\"post\" action=\"".htmlspecialchars("?action=hltopic&topicid=".$topicid)."\">\n"."&nbsp;".$lang_forums['text_highlight_topic']."&nbsp;<select class=\"med\" name=\"color\">");
		print("<option value='0'>".$lang_forums['select_color']."</option>
<option style='background-color: black' value=\"1\">Black</option>
<option style='background-color: sienna' value=\"2\">Sienna</option>
<option style='background-color: darkolivegreen' value=\"3\">Dark Olive Green</option>
<option style='background-color: darkgreen' value=\"4\">Dark Green</option>
<option style='background-color: darkslateblue' value=\"5\">Dark Slate Blue</option>
<option style='background-color: navy' value=\"6\">Navy</option>
<option style='background-color: indigo' value=\"7\">Indigo</option>
<option style='background-color: darkslategray' value=\"8\">Dark Slate Gray</option>
<option style='background-color: darkred' value=\"9\">Dark Red</option>
<option style='background-color: darkorange' value=\"10\">Dark Orange</option>
<option style='background-color: olive' value=\"11\">Olive</option>
<option style='background-color: green' value=\"12\">Green</option>
<option style='background-color: teal' value=\"13\">Teal</option>
<option style='background-color: blue' value=\"14\">Blue</option>
<option style='background-color: slategray' value=\"15\">Slate Gray</option>
<option style='background-color: dimgray' value=\"16\">Dim Gray</option>
<option style='background-color: red' value=\"17\">Red</option>
<option style='background-color: sandybrown' value=\"18\">Sandy Brown</option>
<option style='background-color: yellowgreen' value=\"19\">Yellow Green</option>
<option style='background-color: seagreen' value=\"20\">Sea Green</option>
<option style='background-color: mediumturquoise' value=\"21\">Medium Turquoise</option>
<option style='background-color: royalblue' value=\"22\">Royal Blue</option>
<option style='background-color: purple' value=\"23\">Purple</option>
<option style='background-color: gray' value=\"24\">Gray</option>
<option style='background-color: magenta' value=\"25\">Magenta</option>
<option style='background-color: orange' value=\"26\">Orange</option>
<option style='background-color: yellow' value=\"27\">Yellow</option>
<option style='background-color: lime' value=\"28\">Lime</option>
<option style='background-color: cyan' value=\"29\">Cyan</option>
<option style='background-color: deepskyblue' value=\"30\">Deep Sky Blue</option>
<option style='background-color: darkorchid' value=\"31\">Dark Orchid</option>
<option style='background-color: silver' value=\"32\">Silver</option>
<option style='background-color: pink' value=\"33\">Pink</option>
<option style='background-color: wheat' value=\"34\">Wheat</option>
<option style='background-color: lemonchiffon' value=\"35\">Lemon Chiffon</option>
<option style='background-color: palegreen' value=\"36\">Pale Green</option>
<option style='background-color: paleturquoise' value=\"37\">Pale Turquoise</option>
<option style='background-color: lightblue' value=\"38\">Light Blue</option>
<option style='background-color: plum' value=\"39\">Plum</option>
<option style='background-color: white' value=\"40\">White</option>");
		print("</select>");
		print("<input type=\"hidden\" name=\"returnto\" value=\"".htmlspecialchars($_SERVER[REQUEST_URI])."\" />\n");
		print("<input type=\"submit\" class=\"medium\" value=\"".$lang_forums['submit_change']."\" /></form></li>");
		print('</ul></div>');	
}

	print($pagerbottom);
	if ($maypost){
		if(!$locked){
			print('<div id="forum-reply-post" class="table td"><h2><a class="index" href="'.htmlspecialchars('?action=reply&topicid='.$topicid).'">'.$lang_forums['text_add_reply'].'</a></h2><form id="compose" name="compose" method="post" action="?action=post" onsubmit="return postvalid(this);"><input type="hidden" name="id" value='.$topicid.' /><input type="hidden" name="type" value="reply" />');
			quickreply('compose', 'body',$lang_forums['submit_add_reply']);
			print("</form></div>");
		}
		else{
			print('<center><h2><a class="index" href="'.htmlspecialchars('?action=reply&topicid='.$topicid).'">'.$lang_forums['text_add_reply'].'</a></h2><form id="compose" name="compose" method="post" action="?action=post" onsubmit="return postvalid(this);"><input type="hidden" name="id" value='.$topicid.' /></center><input type="hidden" name="type" value="reply" />');	
		}
	}
	elseif ($locked)
		print($lang_forums['text_topic_locked_new_denied']);
	else print($lang_forums['text_unpermitted_posting_here']);

	print(key_shortcut($page,$pages-1));
	stdfoot();
	die;
}

//-------- Action: Move topic

elseif ($action == "movetopic") {
	$forumid = 0+$_REQUEST["forumid"];

	$topicid = 0+$_GET["topicid"];
	$ismod = is_forum_moderator($topicid,'topic');
	if (!is_valid_id($forumid) || !is_valid_id($topicid) || (get_user_class() < $postmanage_class && !$ismod)) {
	  permissiondenied();
	}

	// Make sure topic and forum is valid

	$res = @sql_query("SELECT minclasswrite,minclassread FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) != 1)
	stderr($lang_forums['std_error'], $lang_forums['std_forum_not_found']);

	$arr = mysql_fetch_row($res);

	if (get_user_class() < $arr[0])
		permissiondenied();
	$des_mincread = $arr[1];

	$res = @sql_query("SELECT forumid FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) != 1)
		stderr($lang_forums['std_error'], $lang_forums['std_topic_not_found']);
	$arr = mysql_fetch_row($res);
	$old_forumid=$arr[0];
	$old_forum_row = get_forum_row($old_forumid);
	$old_mincread = $old_forum_row["minclassread"];
	if($des_mincread>=$confiforumlog_class||$old_mincread>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";
	// get posts count
	$res = sql_query("SELECT COUNT(id) AS nb_posts FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) != 1)
	stderr($lang_forums['std_error'], $lang_forums['std_cannot_get_posts_count']);
	$arr = mysql_fetch_row($res);
	$nb_posts = $arr[0];

	// move topic
	if ($old_forumid != $forumid)
	{
		@sql_query("UPDATE topics SET forumid=$forumid WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
		// update counts
		@sql_query("UPDATE forums SET topiccount=topiccount-1, postcount=postcount-$nb_posts WHERE id=$old_forumid") or sqlerr(__FILE__, __LINE__);
		$Cache->delete_value('forum_'.$old_forumid.'_post_'.$today_date.'_count');
		$Cache->delete_value('forum_'.$old_forumid.'_last_replied_topic_content');
		@sql_query("UPDATE forums SET topiccount=topiccount+1, postcount=postcount+$nb_posts WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);
		$Cache->delete_value('forum_'.$forumid.'_post_'.$today_date.'_count');
		$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
		write_forum_log("Topic:$topicid was moved to Forum:$forumid by $CURUSER[username].",$permi);
	}

	// Redirect to forum page

	if ($format == 'json') {
	  header('Content-type: application/json');
	  echo json_encode(['success' => true]);
	}
	else {
	  header("Location: " . get_protocol_prefix() . "$BASEURL/forums.php?action=viewforum&forumid=$forumid");
	}

	die;
}

//-------- Action: Delete topic

elseif ($action == "deletetopic") {
	$topicid = 0+$_GET["topicid"];
	$res1 = sql_query("SELECT forumid, userid FROM topics WHERE id=".sqlesc($topicid)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
	$row1 = mysql_fetch_array($res1);
	if (!$row1){
		die;
	}
	else {
		$forumid = $row1['forumid'];
		$userid = $row1['userid'];
	}
	$forum_row = get_forum_row($forumid);
	if($forum_row['minclassread']>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";
	$ismod = is_forum_moderator($topicid,'topic');
	if (!is_valid_id($topicid) || (get_user_class() < $postmanage_class && !$ismod))
		permissiondenied();

	$sure = 0+$_GET["sure"];
	if (!$sure)
	{
		stderr($lang_forums['std_delete_topic'], $lang_forums['std_delete_topic_note'] .
		"<a class=altlink href=?action=deletetopic&topicid=$topicid&sure=1>".$lang_forums['std_here_if_sure'],false);
	}

	$postcount = get_row_count("posts","WHERE topicid=".sqlesc($topicid));

	sql_query("DELETE FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
	sql_query("DELETE FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);
	sql_query("DELETE FROM readposts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);
	@sql_query("UPDATE forums SET topiccount=topiccount-1, postcount=postcount-$postcount WHERE id=".sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
	$Cache->delete_value('forum_'.$forumid.'_post_'.$today_date.'_count');
	$forum_last_replied_topic_row = $Cache->get_value('forum_'.$forumid.'_last_replied_topic_content');
	if ($forum_last_replied_topic_row && $forum_last_replied_topic_row['id'] == $topicid)
		$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
	write_forum_log("Topic:$topicid was deleted by $CURUSER[username].",$permi);
	//===remove karma
	KPS("-",$starttopic_bonus,$userid);
	//===end

	header("Location: " . get_protocol_prefix() . "$BASEURL/forums.php?action=viewforum&forumid=$forumid");
	die;
}

//-------- Action: Delete post

elseif ($action == "deletepost") {
	$postid = 0+$_GET["postid"];
	$sure = 0+$_GET["sure"];

	$ismod = is_forum_moderator($postid, 'post');
	if ((get_user_class() < $postmanage_class && !$ismod) || !is_valid_id($postid))
		permissiondenied();

	//------- Get topic id
	$res = sql_query("SELECT topicid, userid FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_array($res) or stderr($lang_forums['std_error'], $lang_forums['std_post_not_found']);
	$topicid = $arr['topicid'];
	$userid = $arr['userid'];

	//------- Get the id of the last post before the one we're deleting
	$res = sql_query("SELECT id FROM posts WHERE topicid=$topicid AND id < $postid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 0) // This is the first post of a topic
		stderr($lang_forums['std_error'], $lang_forums['std_cannot_delete_post'] .
	"<a class=altlink href=?action=deletetopic&topicid=$topicid&sure=1>".$lang_forums['std_delete_topic_instead'],false);
	else
	{
		$arr = mysql_fetch_row($res);
		$redirtopost = "&page=p$arr[0]#pid$arr[0]";
	}

	//------- Make sure we know what we do :-)
	if (!$sure)
	{
		stderr($lang_forums['std_delete_post'], $lang_forums['std_delete_post_note'] .
		"<a class=altlink href=?action=deletepost&postid=$postid&sure=1>".$lang_forums['std_here_if_sure'],false);
	}

	//------- Delete post
	sql_query("DELETE FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);
	$Cache->delete_value('user_'.$userid.'_post_count');
	$Cache->delete_value('topic_'.$topicid.'_post_count');
	// update forum
	$forumid = get_single_value("topics","forumid","WHERE id=".sqlesc($topicid));
	if (!$forumid)
		die();
	else{
		sql_query("UPDATE forums SET postcount=postcount-1 WHERE id=".sqlesc($forumid));
	}
	$forum_row = get_forum_row($forumid);
	if($forum_row["minclassread"]>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";
	$forum_last_replied_topic_row = $Cache->get_value('forum_'.$forumid.'_last_replied_topic_content');
	if ($forum_last_replied_topic_row && $forum_last_replied_topic_row['lastpost'] == $postid)
		$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
	write_forum_log("Post:$postid in Topic:$topicid was deleted by $CURUSER[username].",$permi);
	//------- Update topic
	update_topic_last_post($topicid);

	//===remove karma
	KPS("-",$makepost_bonus,$userid);

	header("Location: " . get_protocol_prefix() . "$BASEURL/forums.php?action=viewtopic&topicid=$topicid$redirtopost");
	die;
}

//-------- Action: Set locked on/off

elseif ($action == "setlocked") {
	$topicid = 0 + $_POST["topicid"];
	$ismod = is_forum_moderator($topicid,'topic');
	if (!$topicid || (get_user_class() < $postmanage_class && !$ismod))
		permissiondenied();
	
	$forum_id = get_forum_id($topicid, "topic");
	if($forum_id==-1)
		permissiondenied();
	$forum_row = get_forum_row($forum_id);
	if($forum_row['minclassread']>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";

	$locked = $_POST["locked"];
	 if ($locked != 'yes' && $locked != 'no') {
    die;
  }
	$act = ($locked=='yes'?"locked":"unlocked");
	$locked = sqlesc($locked);
	sql_query("UPDATE topics SET locked=$locked WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
	if(mysql_affected_rows())
		write_forum_log("Topic:$topicid was $act by $CURUSER[username].",$permi);
	header("Location: $_POST[returnto]");
	die;
}
//-------Action:Set highlight
elseif ($action == 'hltopic') {
	$topicid = 0 + $_GET["topicid"];
	$ismod = is_forum_moderator($topicid,'topic');
	if (!$topicid || (get_user_class() < $postmanage_class && !$ismod))
		permissiondenied();
	$color = $_POST["color"];
	$forum_id = get_forum_id($topicid, "topic");
	$forum_row = get_forum_row($forum_id);
	if($forum_row['minclassread']>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";
	if ($color==0 || get_hl_color($color))
		sql_query("UPDATE topics SET hlcolor=".sqlesc($color)." WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
	if(mysql_affected_rows()){
		if($color!=0)
			write_forum_log("Topic:$topicid was highlighted by $CURUSER[username].",$permi);
		else
			write_forum_log("Topic:$topicid was unhighlighted by $CURUSER[username].",$permi);
	}
	$forumid = get_single_value("topics","forumid","WHERE id=".sqlesc($topicid));
	$forum_last_replied_topic_row = $Cache->get_value('forum_'.$forumid.'_last_replied_topic_content');
	if ($forum_last_replied_topic_row && $forum_last_replied_topic_row['id'] == $topicid)
		$Cache->delete_value('forum_'.$forumid.'_last_replied_topic_content');
	header("Location: $_POST[returnto]");
	die;
}

//-------- Action: Set sticky on/off

elseif ($action == "setsticky" || $action == 'setselected') {
  if ($action == 'setsticky') {
    $key = 'sticky';
  }
  elseif ($action == 'setselected') {
    $key = 'selected';
  }
  else {
    die;
  }

  $topicid = 0 + $_REQUEST["topicid"];
  $ismod = is_forum_moderator($topicid,'topic');
  if (!$topicid || (get_user_class() < $postmanage_class && !$ismod))
    permissiondenied();
    
	$forumid = get_forum_id($topicid, "topic");
	if($forumid==-1)
		permissiondenied();
	$forum_row = get_forum_row($forumid);
	if($forum_row['minclassread']>=$confiforumlog_class)
		$permi = "high";
	else
		$permi = "normal";
	
  $value = $_REQUEST["value"];
	$act = ($value=='yes'?'':"un").$key.($key=='sticky'?"ed":'');

  if ($value != 'yes' && $value != 'no') {
    die;
  }
  $value = sqlesc($value);
   	
  sql_query("UPDATE topics SET $key=$value WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
	if(mysql_affected_rows())
		write_forum_log("Topic:$topicid was $act by $CURUSER[username].",$permi);
  header("Location: $_REQUEST[returnto]");
  die;
}

//-------- Action: View forum

elseif ($action == "viewforum") {
	$forumid = 0+$_GET["forumid"];
	int_check($forumid,true);
	$userid = 0+$CURUSER["id"];
	//------ Get forum name, moderators
	$row = get_forum_row($forumid);
	if (!$row){
		write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is trying to visit forum that doesn't exist", 'mod');
		stderr($lang_forums['std_forum_error'],$lang_forums['std_forum_not_found']);
	}
	if (get_user_class() < $row["minclassread"])
		permissiondenied();
	
	$forumname = $row['name'];
	$forummoderators = get_forum_moderators($forumid,false);
	$search = mysql_real_escape_string(trim($_GET["search"]));
	if ($search){
		$wherea = " AND subject LIKE '%$search%'";
		$addparam .= "&search=".rawurlencode($search);
	}
	else {
		$wherea = "";
		$addparam = "";
	}
	$filter = $_REQUEST['filter'];
	if ($filter == 'selected') {
	  $wherea .= " AND selected = 'yes'";
	  $addparam .= '&filter=selected';
	}
	
	$num = get_row_count("topics","WHERE forumid=".sqlesc($forumid).$wherea);

	list($pagertop, $pagerbottom, $limit) = pager($topicsperpage, $num, "?"."action=viewforum&forumid=".$forumid . $addparam."&", ['link' => 'bottom']);
	if ($_GET["sort"]){
		switch ($_GET["sort"]){
			case 'firstpostasc': 
			{
				$orderby = "firstpost ASC";
				break;
			}
			case 'firstpostdesc': 
			{
				$orderby = "firstpost DESC";
				break;
			}
			case 'lastpostasc':
			{
				$orderby = "lastpost ASC";
				break;
			}
			case 'lastpostdesc':
			{
				$orderby = "lastpost DESC";
				break;
			}
			default:
			{
				$orderby = "lastpost DESC";
			}
		}
	}
	else
	{
		$orderby = "lastpost DESC";
	}
	//------ Get topics data
	$topicsres = sql_query("SELECT * FROM topics WHERE forumid=".sqlesc($forumid).$wherea." ORDER BY sticky DESC,".$orderby." ".$limit) or sqlerr(__FILE__, __LINE__);
	$numtopics = mysql_num_rows($topicsres);
	stdhead($lang_forums['head_forum']." ".$forumname);
	print("<h1 id=\"page-title\"><a class=\"faqlink\" href=\"forums.php\">".$SITENAME."&nbsp;".$lang_forums['text_forums'] ."</a> --> <a class=\"faqlink\" href=\"".htmlspecialchars("forums.php?action=viewforum&forumid=".$forumid)."\">".$forumname."</a></h1>\n");

	echo '<div class="minor-list list-seperator minor-nav"><ul>';
	echo '<li>' . '<form action="?" method="GET"><input type="hidden" name="action" value="viewforum" /><input type="hidden" name="forumid" value="'.$forumid.'" /><input type="search" placeholder="' . $lang_forums['text_search'] . '" value="' . $_GET["search"] . '" name="search" /><input type="submit" class="btn" value="' . $lang_forums['text_go'] . '" /></form>' . '</li>';
	if ($filter == 'selected') {
	  echo '<li class="selected">' . $lang_forums['show_selected'] . '</li>';
	}
	else {
	  echo '<li>' . '<a href="' . htmlspecialchars('?action=viewforum&filter=selected&forumid=' . $forumid) . '">' . $lang_forums['show_selected'] . '</a></li>';
	}
	echo '</ul></div>';
	
	print("<br />");
	$maypost = get_user_class() >= $row["minclasswrite"] && get_user_class() >= $row["minclasscreate"] && $CURUSER["forumpost"] == 'yes';

	print('<div id="forum-header"><div>');
	print($forummoderators ? "<img class=\"forum_mod\" src=\"pic/trans.gif\" alt=\"Moderator\" title=\"".$lang_forums['col_moderator']."\">".$forummoderators : "");

	echo '</div><div id="reply-post">';
	if ($maypost) {
	  print('<a href="'.htmlspecialchars("?action=newtopic&forumid=".$forumid)."\"><img class=\"f_new\" src=\"pic/trans.gif\" alt=\"New Topic\" title=\"".$lang_forums['title_new_topic']."\" /></a>");
	}
	else {
	  echo $lang_forums['text_unpermitted_starting_new_topics'];
	}
	print("</div></div>\n");
	if ($numtopics > 0)
	{
		print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" style=\"width:95%\"><thead>");

		print("<tr><th style=\"width:1%;\"></th><th style=\"width:99%\">".$lang_forums['col_topic']."</th>");
		if ($_REQUEST["sort"] == 'firstpostdesc') {
		  $sort = 'firstpostasc';
		  $title =  $lang_forums['title_order_topic_asc'];
		  $class = 'headerSort headerSortUp';
		}
		else {
		  $sort = 'firstpostdesc';
		  $title = $lang_forums['title_order_topic_desc'];
		  $class = 'headerSort';
		}
		if ($_REQUEST["sort"] == 'firstpostasc') {
		  $class = 'headerSort headerSortDown';
		}
		echo "<th class=\"$class\" title=\"$title\"><a href=\"".htmlspecialchars("?action=viewforum&forumid=".$forumid.$addparam."&sort=$sort") . "\">".$lang_forums['col_author']."</a></th>";

		echo "<th>".$lang_forums['col_replies']."/".$lang_forums['col_views']."</th>";
				
		if ($_REQUEST["sort"] == 'lastpostdesc') {
		  $sort = 'lastpostasc';
		  $title =  $lang_forums['title_order_post_asc'];
		  $class = 'headerSort headerSortUp';
		}
		else {
		  $sort = 'lastpostdesc';
		  $title = $lang_forums['title_order_post_desc'];
		  $class = 'headerSort';
		}
		if ($_REQUEST["sort"] == 'lastpostasc') {
		  $class = 'headerSort headerSortDown';
		}
		echo "<th class=\"$class\" title=\"$title\"><a href=\"".htmlspecialchars("?action=viewforum&forumid=".$forumid.$addparam."&sort=$sort")."\">".$lang_forums['col_last_post']."</a></th>\n";

		print("</tr></thead><tbody>\n");
		$counter = 0;

		while ($topicarr = mysql_fetch_assoc($topicsres)) {
			$topicid = $topicarr["id"];
			$topic_userid = $topicarr["userid"];
			$topic_views = $topicarr["views"];
			$views = number_format($topic_views);
			$locked = $topicarr["locked"] == "yes";
			$sticky = $topicarr["sticky"] == "yes";
			$selected = $topicarr["selected"] == "yes";
			$hlcolor = $topicarr["hlcolor"];

			//---- Get reply count
			if (!$posts = $Cache->get_value('topic_'.$topicid.'_post_count')){
				$posts = get_row_count("posts","WHERE topicid=".sqlesc($topicid));
				$Cache->cache_value('topic_'.$topicid.'_post_count', $posts, 3600);
			}

			$replies = max(0, $posts - 1);

			$tpages = floor($posts / $postsperpage);

			if ($tpages * $postsperpage != $posts)
			++$tpages;

			if ($tpages > 1)
			{
				$topicpages = " [<img class=\"multipage\" src=\"pic/trans.gif\" alt=\"multi-page\" /> ";
				$dotted = 0;
				$dotspace = 4;
				$dotend = $tpages - $dotspace;
				for ($i = 1; $i <= $tpages; ++$i){
					if ($i > $dotspace && $i <= $dotend) {
						if (!$dotted)
						$topicpages .= " ... ";
						$dotted = 1;
						continue;
					}
				$topicpages .= " <a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$topicid."&page=".($i-1))."\">$i</a>";
				}

				$topicpages .= " ]";
			}
			else
			$topicpages = "";

			//---- Get userID and date of last post

			$arr = get_post_row($topicarr['lastpost']);
			$lppostid = 0 + $arr["id"];
			$lpuserid = 0 + $arr["userid"];
			$lpusername = get_username($lpuserid);
			$lpadded = gettime($arr["added"],true,false);
			$onmouseover = "";
			if ($enabletooltip_tweak == 'yes'){
					$lastposttime = $lang_forums['text_at_time'].$arr["added"];
				$lptext = format_comment(mb_substr($arr['body'],0,100,"UTF-8") . (mb_strlen($arr['body'],"UTF-8") > 100 ? " ......" : "" ),true,false,false,true,600,false,false);
				$lastpost_tooltip[$counter]['id'] = "lastpost_" . $counter;
				$lastpost_tooltip[$counter]['content'] = $lang_forums['text_last_posted_by'].$lpusername.$lastposttime."<br />".$lptext;
				$onmouseover = "onmouseover=\"domTT_activate(this, event, 'content', document.getElementById('" . $lastpost_tooltip[$counter]['id'] . "'), 'trail', false,'lifetime', 5000,'styleClass','niceTitle','fadeMax', 87,'maxWidth', 400);\"";
			}

			$arr = get_post_row($topicarr['firstpost']);
			$fpuserid = 0 + $arr["userid"];
			$fpauthor = get_username($arr["userid"]);

			$subject = '';
			if ($sticky) {
			  $subject .= "<img class=\"sticky\" src=\"pic/trans.gif\" alt=\"Sticky\" title=\"".$lang_forums['title_sticky']."\" /> ";
			}
			$subject .= "<a href=\"".htmlspecialchars("?action=viewtopic&forumid=".$forumid."&topicid=".$topicid)."\" ".$onmouseover.">" .highlight_topic(highlight($search,htmlspecialchars($topicarr["subject"])), $hlcolor) . "</a>";
			$subject .= ' <span class="minor-list horizon-compact properties"><ul>';
			if ($selected) {
			  $subject .= '<li>' . $lang_forums['title_selected'] . '</li>';
			}
			$subject .= '</ul></span>';
			$subject .= $topicpages;
			$lastpostread = get_last_read_post_id($topicid);

			if ($lastpostread >= $lppostid)
				$img = get_topic_image($locked ? "locked" : "read");
			else{
				$img = get_topic_image($locked ? "lockednew" : "unread");
				if ($lastpostread != $CURUSER['last_catchup'])
					$subject .= "&nbsp;&nbsp;<a href=\"".htmlspecialchars("?action=viewtopic&forumid=".$forumid."&topicid=".$topicid."&page=p".$lastpostread."#pid".$lastpostread)."\" title=\"".$lang_forums['title_jump_to_unread']."\"><font class=\"small new\"><b>".$lang_forums['text_new']."</b></font></a>";
			}

			
			$topictime = substr($arr['added'],0,10);
			if (strtotime($arr['added']) +  86400 > TIMENOW)
				$topictime = "<font class=\"new small\">".$topictime."</font>";
			else
				$topictime = "<font color=\"gray\" class=\"small\">".$topictime."</font>";

			print("<tr>" . "<td style=\"padding:0;\">".$img .
			"</td><td style=\"text-align:left;\">\n" .
			$subject."</td><td class=\"rowfollow\" align=\"center\">".get_username($fpuserid)."<br />".$topictime."</td><td class=\"rowfollow\" align=\"center\">".$replies." / <font color=\"gray\">".$views."</font></td>\n" .
			"<td class=\"rowfollow nowrap\">".$lpadded."<br />".$lpusername."</td>\n");

			print("</tr>\n");
			$counter++;

		} // while

		print("</tbody></table>");
		print($pagerbottom);
		if ($enabletooltip_tweak == 'yes')
			create_tooltip_container($lastpost_tooltip, 400);
	} // if
	else
		print("<p>".$lang_forums['text_no_topics_found']."</p>");
	stdfoot();
	die;
}

//-------- Action: View unread posts

elseif ($action == "viewunread") {
	$userid = $CURUSER['id'];

	$beforepostid = 0+$_GET['beforepostid'];
	$maxresults = 25;
	$res = sql_query("SELECT id, forumid, subject, lastpost, hlcolor FROM topics WHERE lastpost > ".$CURUSER['last_catchup'].($beforepostid ? " AND lastpost < ".sqlesc($beforepostid) : "")." ORDER BY lastpost DESC LIMIT 100") or sqlerr(__FILE__, __LINE__);

	stdhead($lang_forums['head_view_unread']);
	print("<h1 align=\"center\"><a class=\"faqlink\" href=\"forums.php\">".$SITENAME."&nbsp;".$lang_forums['text_forums']."</a>-->".$lang_forums['text_topics_with_unread_posts']."</h1>");

	$n = 0;
	$uc = get_user_class();

	while ($arr = mysql_fetch_assoc($res))
	{
		$topiclastpost = $arr['lastpost'];
		$topicid = $arr['id'];

		//---- Check if post is read
		$lastpostread = get_last_read_post_id($topicid);

		if ($lastpostread >= $topiclastpost)
			continue;

		$forumid = $arr['forumid'];
		//---- Check access & get forum name
		$a = get_forum_row($forumid);
		if ($uc < $a['minclassread'])
			continue;
		++$n;
		if ($n > $maxresults)
			break;

		$forumname = $a['name'];
		if ($n == 1)
		{
			print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
			print("<tr><td class=\"colhead\" align=\"left\">".$lang_forums['col_topic']."</td><td class=\"colhead\" align=\"left\">".$lang_forums['col_forum']."</td></tr>\n");
		}
		print("<tr><td class=\"rowfollow\" align=\"left\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\" style='padding-right: 10px'>" .
		get_topic_image("unread")."</td><td class=\"embedded\">" .
		"<a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$topicid.($lastpostread > 0 && $lastpostread != $CURUSER['last_catchup'] ? "&page=p".$lastpostread."#pid".$lastpostread : ""))."\">" . highlight_topic(htmlspecialchars($arr["subject"]), $arr["hlcolor"]).
		"</a></td></tr></table></td><td class=\"rowfollow\" align=\"left\"><a href=\"".htmlspecialchars("?action=viewforum&forumid=".$forumid)."\"><b>".$forumname."</b></a></td></tr>\n");
	}
	if ($n > 0)
	{
		print("</table>\n");
		print("<table border=\"0\" class=\"main\" cellspacing=\"0\" cellpadding=\"5\" width=\"1%\"><tr><td class=\"embedded\"><form method=\"get\" action=\"?\"><input type=\"hidden\" name=\"catchup\" value=\"1\" /><input type=\"submit\" value=\"".$lang_forums['text_catch_up']."\" class=\"btn\" /></form></td>");
		if ($n > $maxresults){
			print("<td class=\"embedded\"><form method=\"get\" action=\"?\"><input type=\"hidden\" name=\"action\" value=\"viewunread\" /><input type=\"hidden\" name=\"beforepostid\" value=\"".$topiclastpost."\" /><input type=\"submit\" value=\"".$lang_forums['submit_show_more']."\" class=\"btn\" /></form></td>");
		}
		print("</tr></table>");
	}
	else
		print("<p>".$lang_forums['text_nothing_found']."</p>");
	stdfoot();
	die;
}

elseif ($action == "search") {
	stdhead($lang_forums['head_forum_search']);
	unset($error);
	$error = true;
	$found = "";
	$keywords = htmlspecialchars(trim($_GET["keywords"]));
	if ($keywords != "")
	{
		$extraSql 	= " LIKE '%".mysql_real_escape_string($keywords)."%'";

		$res = sql_query("SELECT COUNT(posts.id) FROM posts LEFT JOIN topics ON posts.topicid = topics.id LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ".sqlesc(get_user_class())." AND ((topics.subject $extraSql AND posts.id=topics.firstpost) OR posts.body $extraSql)") or sqlerr(__FILE__, __LINE__);
		$arr = mysql_fetch_row($res);
		$hits = 0 + $arr[0];
		if ($hits){
			$error = false;
			$found = "[<b><font class=\"striking\"> ".$lang_forums['text_found'].$hits.$lang_forums['text_num_posts']." </font></b>]";
		}
	}

	echo '<div style="text-align:center;"><form action="?" method="GET"><input type="hidden" name="action" value="search" /><input type="search" placeholder="' . $lang_forums['text_search'] . '" name="keywords" value="' . $keywords . '" /><input type="submit" class="btn" value="' . $lang_forums['text_go'] . '" /></form></div>';

	if (!$error) {
		$perpage = $topicsperpage;
		list($pagertop, $pagerbottom, $limit) = pager($perpage, $hits, "forums.php?action=search&keywords=".rawurlencode($keywords)."&");
		$res = sql_query("SELECT posts.id, posts.topicid, posts.userid, posts.added, topics.subject, topics.hlcolor, forums.id AS forumid, forums.name AS forumname FROM posts LEFT JOIN topics ON posts.topicid = topics.id LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ".sqlesc(get_user_class())." AND ((topics.subject $extraSql AND posts.id=topics.firstpost) OR posts.body $extraSql) ORDER BY posts.id DESC $limit") or sqlerr(__FILE__, __LINE__);

		print($pagertop);
		print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" style=\"width:95%\"><thead>\n");
		print("<tr><th align=\"center\">".$lang_forums['col_post']."</th><th align=\"center\" width=\"70%\">".$lang_forums['col_topic']."</th><th align=\"left\">".$lang_forums['col_forum']."</th><th align=\"left\">".$lang_forums['col_posted_by']."</th></tr></thead><tbody>\n");

		while ($post = mysql_fetch_array($res))
		{
			print("<tr><td class=\"rowfollow\" align=\"center\" width=\"1%\">".$post[id]."</td><td class=\"rowfollow\" align=\"left\"><a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$post[topicid]."&highlight=".rawurlencode($keywords)."&page=p".$post[id]."#pid".$post[id])."\">" . highlight_topic(highlight($keywords,htmlspecialchars($post['subject'])), $post['hlcolor']) . "</a></td><td class=\"rowfollow nowrap\" align=\"left\"><a href=\"".htmlspecialchars("?action=viewforum&forumid=".$post['forumid'])."\"><b>" . htmlspecialchars($post["forumname"]) . "</b></a></td><td class=\"rowfollow nowrap\" align=\"left\">" . gettime($post['added'],true,false) . "&nbsp;|&nbsp;". get_username($post['userid']) ."</td></tr>\n");
		}

		print("</tbody></table>\n");
		print($pagerbottom);
	}
	echo '<script>$("table").tablesorter()</script>';
stdfoot();
die;
}
else {
if ($_GET["catchup"] == 1){
	catch_up();
}

//-------- Handle unknown action
/* elseif ($action != "") { */
/* 	stderr($lang_forums['std_forum_error'], $lang_forums['std_unknown_action']); */
/* } */

//-------- Default action: View forums

//-------- Get forums
if ($CURUSER) {
	$USERUPDATESET[] = "forum_access = ".sqlesc(date("Y-m-d H:i:s"));
}

stdhead($lang_forums['head_forums']);

print("<h1 align=\"center\">".$SITENAME."&nbsp;".$lang_forums['text_forums']."</h1>");
echo '<div class="minor-list list-seperator minor-nav"><ul><li>';
#      <a href="?action=search">'.$lang_forums['text_search'] . '</a>';
echo '<form action="?" method="GET"><input type="hidden" name="action" value="search" /><input type="search" placeholder="' . $lang_forums['text_search'] . '" name="keywords" /><input type="submit" class="btn" value="' . $lang_forums['text_go'] . '" /></form>';
 echo '</li><li><a href="?action=viewunread">' . $lang_forums['text_view_unread'] . '</a></li><li><a href="?catchup=1">'.$lang_forums['text_catch_up'].'</a></li><li><a href="userhistory.php?action=viewposts">' . $lang_forums['text_my_posts'] . '</a></li><li><a href="userhistory.php?action=viewquotedposts">' . $lang_forums['text_quoted_posts'] . '</a>'.(get_user_class() >= $forummanage_class ? '</li><li><a href="forummanage.php">'.$lang_forums['text_forum_manager'].'</a>':'').'</li></ul></div>';
print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\n");

if (!$overforums = $Cache->get_value('overforums_list')){
	$overforums = array();
	$res = sql_query("SELECT * FROM overforums ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);
	while ($row = mysql_fetch_array($res))
		$overforums[] = $row;
	$Cache->cache_value('overforums_list', $overforums, 86400);
}
$count=0;
if ($Advertisement->enable_ad())
	$interoverforumsad=$Advertisement->get_ad('interoverforums');

foreach ($overforums as $a) {
	if (get_user_class() < $a["minclassview"])
		continue;
	if ($count>=1)
	if ($Advertisement->enable_ad()){
		if ($interoverforumsad[$count-1])
			echo "<tr><td colspan=\"5\" align=\"center\" id=\"ad_interoverforums_".($count-1)."\">".$interoverforumsad[$count-1]."</td></tr>";
	}
	$forid = $a["id"];
	$overforumname = $a["name"];

	print("<tr><td align=\"left\" class=\"colhead\" width=\"99%\">".htmlspecialchars($overforumname)."</td><td align=\"center\" class=\"colhead\">".$lang_forums['col_topics']."</td>" .
	"<td align=\"center\" class=\"colhead\">".$lang_forums['col_posts']."</td>" .
	"<td align=\"left\" class=\"colhead\">".$lang_forums['col_last_post']."</td><td class=\"colhead\" align=\"left\">".$lang_forums['col_moderator']."</td></tr>\n");

	$forums = get_forum_row();
	foreach ($forums as $forums_arr)
	{
		if ($forums_arr['forid'] != $forid)
			continue;
		if (get_user_class() < $forums_arr["minclassread"])
			continue;

		$forumid = $forums_arr["id"];
		$forumname = htmlspecialchars($forums_arr["name"]);
		$forumdescription = htmlspecialchars($forums_arr["description"]);

		$forummoderators = get_forum_moderators($forums_arr['id'],false);
		if (!$forummoderators)
			$forummoderators = "<a href=\"contactstaff.php\"><i>".$lang_forums['text_apply_now']."</i></a>";

		$topiccount = number_format($forums_arr["topiccount"]);
		$postcount = number_format($forums_arr["postcount"]);

		// Find last post ID
		//Returns the ID of the last post of a forum
		if (!$arr = $Cache->get_value('forum_'.$forumid.'_last_replied_topic_content')){
			$res = sql_query("SELECT * FROM topics WHERE forumid=".sqlesc($forumid)." ORDER BY lastpost DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
			$arr = mysql_fetch_array($res);
			$Cache->cache_value('forum_'.$forumid.'_last_replied_topic_content', $arr, 900);
		}

		if ($arr)
		{
			$lastpostid = $arr['lastpost'];
			// Get last post info
			$post_arr = get_post_row($lastpostid);
			$lastposterid = $post_arr["userid"];
			$lastpostdate = gettime($post_arr["added"],true,false);
			$lasttopicid = $arr['id'];
			$hlcolor = $arr['hlcolor'];
			$lasttopicdissubject = $lasttopicsubject = $arr['subject'];
			$max_length_of_topic_subject = 35;
			$count_dispname = mb_strlen($lasttopicdissubject,"UTF-8");
			if ($count_dispname > $max_length_of_topic_subject)
				$lasttopicdissubject = mb_substr($lasttopicdissubject, 0, $max_length_of_topic_subject-2,"UTF-8") . "..";
			$lasttopic = highlight_topic(htmlspecialchars($lasttopicdissubject), $hlcolor);

			$lastpost = "<a href=\"".htmlspecialchars("?action=viewtopic&topicid=".$lasttopicid."&page=last#last")."\" title=\"".htmlspecialchars($lasttopicsubject)."\">".$lasttopic."</a><br />". $lastpostdate."&nbsp;|&nbsp;".get_username($lastposterid);

			$lastreadpost = get_last_read_post_id($lasttopicid);

			if ($lastreadpost >= $lastpostid)
				$img = get_topic_image("read");
			else
				$img = get_topic_image("unread");
		}
		else
		{
			$lastpost = "N/A";
			$img = get_topic_image("read");
		}
		$posttodaycount = $Cache->get_value('forum_'.$forumid.'_post_'.$today_date.'_count');
		if ($posttodaycount == ""){
			$posttodaycount = get_row_count('posts', "LEFT JOIN topics ON posts.topicid = topics.id WHERE posts.added > ".sqlesc(date("Y-m-d"))." AND topics.forumid=".sqlesc($forumid));
			$Cache->cache_value('forum_'.$forumid.'_post_'.$today_date.'_count', $posttodaycount, 1800);
		}
		if ($posttodaycount > 0)
			$posttoday = "&nbsp;&nbsp;(".$lang_forums['text_today']."<b><font class=\"new\">".$posttodaycount."</font></b>)";
		else $posttoday = "";
		print("<tr><td class=\"rowfollow\" align=\"left\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\" style='padding-right: 10px'>".$img."</td><td class=\"embedded\"><a href=\"".htmlspecialchars("?action=viewforum&forumid=".$forumid)."\"><font class=\"big\"><b>".$forumname."</b></font></a>" .$posttoday.
		"<br />".$forumdescription."</td></tr></table></td><td class=\"rowfollow\" align=\"center\" width=\"1%\">".$topiccount."</td><td class=\"rowfollow\" align=\"center\" width=\"1%\">".$postcount."</td>" .
		"<td class=\"rowfollow nowrap\" align=\"left\">".$lastpost."</td><td class=\"rowfollow\" align=\"left\">".$forummoderators."</td></tr>\n");
	}
	$count++;
}
// End Table Mod
print("</table>");
if ($showforumstats_main == "yes"){
   forum_stats();
   /* print('<div style="text-align:center;">'); */
   /* print("<h2><a href=\"?action=search\">".$lang_forums['text_search']."</a> </h2>"); */
   /* print('<div">(新手有疑问请先搜索论坛，不要重复发帖提问)</div>'); */
   /* showSearch(); */
   /* print("</div>"); */
}


stdfoot();

}



