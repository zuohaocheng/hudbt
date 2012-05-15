<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
require_once(get_langfile_path("",true));
loggedinorreturn();

$id = 0 + $_REQUEST['id'];
if (array_key_exists('action', $_REQUEST)) {
  $action=$_REQUEST["action"];
}
else {
  $action = 'view';
}

if (array_key_exists('returnto', $_REQUEST)) {
  $returnto = htmlspecialchars($_REQUEST["returnto"]);
}
else {
  $returnto = 'fun.php';
  if ($id) {
    $returnto .= '?id=' . $id;
  }
}

if ($action == 'comment') {
  checkHTTPMethod('post');
  $funid = 0 + $_REQUEST["funid"];
  $funcomment = $_REQUEST["fun_text"];
  if (strlen(trim($funcomment)) != 0) {
    sql_query("INSERT INTO funcomment (funid, userid, text, added) VALUES(".sqlesc($funid).", ".$CURUSER['id'].", " . sqlesc($funcomment) . ', ' . sqlesc(date("Y-m-d H:i:s")) . ")") or sqlerr(__FILE__,__LINE__);

    $Cache->delete_value('current_fun_content_comment');
    $Cache->delete_value('current_fun_content_comment_delete');
  }

  header('Location: ' . $returnto . '#funcomment');
}
else if ($action == 'delcomment') {
  checkHTTPMethod('post');
  if (checkPrivilege(['Misc', 'fun'])) {
    $funcommentdel = 0 + $_REQUEST['commentid'];
    sql_query("DELETE FROM funcomment WHERE id=".mysql_real_escape_string($funcommentdel));
    $Cache->delete_value('current_fun_content_comment');
    $Cache->delete_value('current_fun_content_comment_delete');

    header('Location: ' . $returnto . '#funcomment');
  }
}
else if ($action == 'delete') {
  int_check($id,true);
  $res = sql_query("SELECT userid FROM fun WHERE id=$id") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
    stderr($lang_fun['std_error'], $lang_fun['std_invalid_id']);
  if (!checkPrivilege(['Misc', 'fun']))
    permissiondenied();
  $sure = 0 + $_REQUEST["sure"];
  if (!$sure || $_SERVER['REQUEST_METHOD'] != 'POST') {
    stderr($lang_fun['std_delete_fun'], "<form action=\"?action=delete&id=$id&returnto=$returnto\" method=\"POST\">" . '<input type="hidden" name="sure" value="1" />' . $lang_fun['text_please_click'] . $lang_fun['text_here_if_sure'] . '</form>',false);
  }
  
  sql_query("DELETE FROM fun WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
  sql_query("DELETE FROM funcomment WHERE funid=".sqlesc($id));
  $Cache->delete_value('current_fun_content');
  $Cache->delete_value('current_fun', true);
  $Cache->delete_value('current_fun_vote_count');
  $Cache->delete_value('current_fun_vote_funny_count');
  $Cache->delete_value('current_fun_content_comment');
  $Cache->delete_value('current_fun_content_comment_delete');

  header("Location: $returnto");
}
else if ($action == 'new') {
  $sql = "SELECT *, IF(ADDTIME(added, '1 0:0:0') < NOW(),true,false) AS neednew FROM fun WHERE status != 'banned' AND status != 'dull' ORDER BY added DESC LIMIT 1";
  $result = sql_query($sql) or sqlerr(__FILE__,__LINE__);
  $row = mysql_fetch_array($result);
  if ($row && !$row['neednew'])
    stderr($lang_fun['std_error'],$lang_fun['std_the_newest_fun_item'].htmlspecialchars($row['title']).$lang_fun['std_posted_on'].$row['added'].$lang_fun['std_need_to_wait']);
  else {
    stdhead($lang_fun['head_new_fun']);
    begin_main_frame();
    $title = $lang_fun['text_submit_new_fun'];
    print("<form id=compose method=post name=\"compose\" action=?action=add>\n");
    begin_compose($title, 'new');
    end_compose();
    end_main_frame();
  }
  stdfoot();
}
else if ($action == 'add') {
  $sql = "SELECT *, IF(ADDTIME(added, '1 0:0:0') < NOW(),true,false) AS neednew FROM fun WHERE status != 'banned' AND status != 'dull' ORDER BY added DESC LIMIT 1";
  $result = sql_query($sql) or sqlerr(__FILE__,__LINE__);
  $row = mysql_fetch_array($result);
  if ($row && !$row['neednew'])
    stderr($lang_fun['std_error'],$lang_fun['std_the_newest_fun_item'].htmlspecialchars($row['title']).$lang_fun['std_posted_on'].$row['added'].$lang_fun['std_need_to_wait']);
  else {
    $body = $_POST['body'];
    if (!$body)
      stderr($lang_fun['std_error'],$lang_fun['std_body_is_empty']);
    $title = htmlspecialchars($_POST['subject']);
    if (!$title)
      stderr($lang_fun['std_error'],$lang_fun['std_title_is_empty']);
    $sql = "INSERT INTO fun (userid, added, body, title, status) VALUES (".sqlesc($CURUSER['id']).",".sqlesc(date("Y-m-d H:i:s")).",".sqlesc($body).",".sqlesc($title).", 'normal')";
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $Cache->delete_value('current_fun_content');
    $Cache->delete_value('current_fun_content_id');
    $Cache->delete_value('current_fun_content_neednew');
    $Cache->delete_value('current_fun_content_owner');
    $Cache->delete_value('current_fun', true);
    $Cache->delete_value('current_fun_vote_count');
    $Cache->delete_value('current_fun_vote_funny_count');
    $Cache->delete_value('current_fun_content_comment');
    $Cache->delete_value('current_fun_content_comment_delete');

    if (mysql_affected_rows() == 1)
      $warning = $lang_fun['std_fun_added_successfully'];
    else
      stderr($lang_fun['std_error'],$lang_fun['std_error_happened']);
    header("Location: ". $returnto);
  }
}
else if ($action == 'view') {
  stdhead($lang_fun['head_fun']);
  echo get_fun($id, 100);
  stdfoot();
}
else if ($action == 'edit') {
  int_check($id,true);
  $res = sql_query("SELECT * FROM fun WHERE id=$id") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr) {
    stderr($lang_fun['std_error'], $lang_fun['std_invalid_id']);
  }

  if ($arr["userid"] != $CURUSER["id"] && get_user_class() < $funmanage_class) {
    permissiondenied();
  }
  
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = $_POST['body'];

    if ($body == "")
      stderr($lang_fun['std_error'],$lang_fun['std_body_is_empty']);

    $title = htmlspecialchars($_POST['subject']);

    if ($title == "")
      stderr($lang_fun['std_error'],$lang_fun['std_title_is_empty']);

    $body = sqlesc($body);
    $title = sqlesc($title);
    sql_query("UPDATE fun SET body=$body, title=$title WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $Cache->delete_value('current_fun_content');
    $Cache->delete_value('current_fun', true);
    header("Location: " . $returnto);
  }
  else {
    stdhead($lang_fun['head_edit_fun']);
    begin_main_frame();
    $title = $lang_fun['text_edit_fun'];
    print("<form id=compose method=post name=\"compose\" action=?action=edit&id=".$id.">\n");
    begin_compose($title, 'edit',$arr['body'], true, $arr['title']);
    end_compose();
    end_main_frame();
  }
  stdfoot();
}
else if ($action == 'ban') {
  if (get_user_class() < $funmanage_class) {
    permissiondenied();
  }
  $id = 0+$_GET["id"];
  int_check($id,true);
  $res = sql_query("SELECT * FROM fun WHERE id=$id") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
    stderr($lang_fun['std_error'], $lang_fun['std_invalid_id']);
  if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      $banreason = htmlspecialchars($_POST['banreason'],ENT_QUOTES);
      $title = htmlspecialchars($arr['title']);
      if ($banreason == "")
	stderr($lang_fun['std_error'],$lang_fun['std_reason_is_empty']);
      sql_query("UPDATE fun SET status='banned' WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);

      $Cache->delete_value('current_fun_content');
      $Cache->delete_value('current_fun', true);
      $Cache->delete_value('current_fun_vote_count');
      $Cache->delete_value('current_fun_vote_funny_count');
      $Cache->delete_value('current_fun_content_comment');
      $Cache->delete_value('current_fun_content_comment_delete');


      $subject = $lang_fun_target[get_user_lang($arr['userid'])]['msg_fun_item_banned'];
      $msg = $lang_fun_target[get_user_lang($arr['userid'])]['msg_your_fun_item'].$title.$lang_fun_target[get_user_lang($arr['userid'])]['msg_is_ban_by'].$CURUSER['username'].$lang_fun_target[get_user_lang($arr['userid'])]['msg_reason'].$banreason;
      sql_query("INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(0, ".sqlesc($subject).", ".$arr['userid'].", '" . date("Y-m-d H:i:s") . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
      $Cache->delete_value('user_'.$arr['userid'].'_unread_message_count');
      $Cache->delete_value('user_'.$arr['userid'].'_inbox_count');
      write_log("Fun item $id ($title) was banned by $CURUSER[username]. Reason: $banreason", 'normal');
      stderr($lang_fun['std_success'], $lang_fun['std_fun_item_banned']);
    }
  else {
    stderr($lang_fun['std_are_you_sure'], $lang_fun['std_only_against_rule']."<br /><form name=ban method=post action=fun.php?action=ban&id=".$id."><input type=hidden name=sure value=1>".$lang_fun['std_reason_required']."<input type=text style=\"width: 200px\" name=banreason><input type=submit value=".$lang_fun['submit_okay']."></form>", false);
  }
}
else if ($action == 'vote') {
  checkHTTPMethod('POST');
  int_check($id,true);
  $res = sql_query("SELECT * FROM fun WHERE id=$id") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr) {
    stderr($lang_fun['std_error'], $lang_fun['std_invalid_id']);
  }
  else {
    $res = sql_query("SELECT * FROM funvotes WHERE funid=$id AND userid = $CURUSER[id]") or sqlerr(__FILE__,__LINE__);
    $checkvote = mysql_fetch_array($res);
    if ($checkvote)
      stderr($lang_fun['std_error'], $lang_fun['std_already_vote']);
    else {
      if ($_POST["yourvote"] == 'dull')
	$vote = 'dull';
      else $vote = 'fun';
      $sql = "INSERT INTO funvotes (funid, userid, added, vote) VALUES (".sqlesc($id).",".$CURUSER['id'].",".sqlesc(date("Y-m-d H:i:s")).",".sqlesc($vote).")";
      sql_query($sql) or sqlerr(__FILE__,__LINE__);
      KPS("+",$funboxvote_bonus,$CURUSER['id']); //voter gets 1.0 bonus per vote
      $totalvote = $Cache->get_value('current_fun_vote_count');
      if ($totalvote == ""){
	$totalvote = get_row_count("funvotes", "WHERE funid = ".sqlesc($row['id']));
      }
      else $totalvote++;
      $Cache->cache_value('current_fun_vote_count', $totalvote, 756);
      $funvote = $Cache->get_value('current_fun_vote_funny_count');
      if ($funvote == ""){
	$funvote = get_row_count("funvotes", "WHERE funid = ".sqlesc($row['id'])." AND vote='fun'");
      }
      elseif($vote == 'fun') {
	$funvote++;
      }
      $Cache->cache_value('current_fun_vote_funny_count', $funvote, 756);
      if ($totalvote) $ratio = $funvote / $totalvote; else $ratio = 1;
      if ($totalvote >= 20){
	if ($ratio > 0.75){
	  sql_query("UPDATE fun SET status = 'veryfunny' WHERE id = ".sqlesc($id));
	  if ($totalvote == 25) //Give fun item poster some bonus and write a message to him
	    funreward($funvote, $totalvote, $arr['title'], $arr['userid'], $funboxreward_bonus * 2);
	  if ($totalvote == 50)
	    funreward($funvote, $totalvote, $arr['title'], $arr['userid'], $funboxreward_bonus * 2);
	  if ($totalvote == 100)
	    funreward($funvote, $totalvote, $arr['title'], $arr['userid'], $funboxreward_bonus * 2);
	  if ($totalvote == 200)
	    funreward($funvote, $totalvote, $arr['title'], $arr['userid'], $funboxreward_bonus * 2);
	}
	elseif ($ratio > 0.5){
	  sql_query("UPDATE fun SET status = 'funny' WHERE id = ".sqlesc($id));
	  if ($totalvote == 25) //Give fun item poster some bonus and write a message to him
	    funreward($funvote, $totalvote, $arr['id'], $arr['userid'], $funboxreward_bonus);
	  if ($totalvote == 50)
	    funreward($funvote, $totalvote, $arr['id'], $arr['userid'], $funboxreward_bonus);
	  if ($totalvote == 100)
	    funreward($funvote, $totalvote, $arr['id'], $arr['userid'], $funboxreward_bonus);
	  if ($totalvote == 200)
	    funreward($funvote, $totalvote, $arr['id'], $arr['userid'], $funboxreward_bonus);
	}
	elseif ($ratio > 0.25){
	  sql_query("UPDATE fun SET status = 'notfunny' WHERE id = ".sqlesc($id));
	}
	else{
	  sql_query("UPDATE fun SET status = 'dull' WHERE id = ".sqlesc($id));
	  //write a message to fun item poster
	  $subject = $lang_fun_target[get_user_lang($arr['userid'])]['msg_fun_item_dull'];
	  $msg = ($totalvote - $funvote).$lang_fun_target[get_user_lang($arr['userid'])]['msg_out_of'].$totalvote.$lang_fun_target[get_user_lang($arr['userid'])]['msg_people_think'].$arr['title'].$lang_fun_target[get_user_lang($arr['userid'])]['msg_is_dull'];
	  $sql = "INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(0, ".sqlesc($subject).",". $arr['userid'].", '" . date("Y-m-d H:i:s") . "', " . sqlesc($msg) . ")";
	  sql_query($sql) or sqlerr(__FILE__, __LINE__);
	  $Cache->delete_value('user_'.$arr['userid'].'_unread_message_count');
	  $Cache->delete_value('user_'.$arr['userid'].'_inbox_count');
	}
      }
    }
  }

  $format = strtolower($_REQUEST['format']);
  if ($format != 'json') {
    $format = 'html';
  }

  if ($format == 'json') {
    header('Content-type: application/json');
    echo php_json_encode(array('success' => true));
  }
  else {
    header("Location: index.php");
  }
}

function funreward($funvote, $totalvote, $title, $posterid, $bonus) {
  global $lang_fun_target, $lang_fun;
  KPS("+",$bonus,$posterid);
  $subject = $lang_fun_target[get_user_lang($posterid)]['msg_fun_item_reward'];
  $msg = $funvote.$lang_fun_target[get_user_lang($posterid)]['msg_out_of'].$totalvote.$lang_fun_target[get_user_lang($posterid)]['msg_people_think'].$title.$lang_fun_target[get_user_lang($posterid)]['msg_is_fun'].$bonus.$lang_fun_target[get_user_lang($posterid)]['msg_bonus_as_reward'];
  $sql = "INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(0, ".sqlesc($subject).",". $posterid. ",'" . date("Y-m-d H:i:s") . "', " . sqlesc($msg) . ")";
  sql_query($sql) or sqlerr(__FILE__, __LINE__);
  $Cache->delete_value('user_'.$posterid.'_unread_message_count');
  $Cache->delete_value('user_'.$posterid.'_inbox_count');
}
