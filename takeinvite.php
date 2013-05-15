<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
registration_check('invitesystem', true, false);

function bark($msg) {
  global $Cache, $lock_key, $lang_takeinvite;
  if (isset($lock_key)) {
    $Cache->delete_value($lock_key);
  }
  stdhead();
  stdmsg($lang_takeinvite['head_invitation_failed'], $msg);
  stdfoot();
  exit;
}

function send_confirm_mail($row) {
  global $CURUSER, $lang_takeinvite, $BASEURL, $SITENAME, $SITEEMAIL, $invite_timeout;
  $body = $row['body'];
  $hash = $row['hash'];
  $email = $row['invitee'];

  $title = $SITENAME.$lang_takeinvite['mail_tilte'];
  
  $message = <<<EOD
	{$lang_takeinvite['mail_one']}{$CURUSER['username']}{$lang_takeinvite['mail_two']}
	<b><a href="javascript:void(null)" onclick="window.open('http://$BASEURL/signup.php?type=invite&invitenumber=$hash')">{$lang_takeinvite['mail_here']}</a></b><br />
	http://$BASEURL/signup.php?type=invite&invitenumber=$hash
	<br />{$lang_takeinvite['mail_three']}$invite_timeout{$lang_takeinvite['mail_four']}{$CURUSER['username']}{$lang_takeinvite['mail_five']}<br />
	$body
	<br /><br />{$lang_takeinvite['mail_six']}
EOD;


try {
  $successed = sent_mail_core($email,$SITENAME,$SITEEMAIL,change_email_encode(get_langfolder_cookie(), $title),change_email_encode(get_langfolder_cookie(),$message),"invitesignup",false,false,'',get_email_encode(get_langfolder_cookie()));
//this email is sent only when someone give out an invitation
} catch (Exception $e) {
    bark('通过 SMTP 服务器发送邮件失败，请稍后<form class="a" method="post" action="takeinvite.php?action=resend-mail&id=' . $row['id'] . '"><input class="a" type="submit" value="重新尝试发送" /></form>。');
  }
  header("Refresh: 0; url=invite.php?id=".htmlspecialchars($id)."&sent=1");
}

if ($_REQUEST['action'] == 'resend-mail') {
  $id = 0 + $_REQUEST['id'];
  $row = sql_query('SELECT id, hash, invitee, body FROM invites WHERE id=? AND inviter = ?', [$id, $CURUSER['id']])->fetch();
  if ($row) {
    send_confirm_mail($row);
  }
  else {
    bark('怎么回事?');
  }
  die;
}

if ($CURUSER['invites'] <= 0 && get_user_class() < $sendinvite_class) {
  header("HTTP/1.1 403 Forbidden");
  stderr($lang_takeinvite['std_error'],$lang_takeinvite['std_invite_denied']);
}

$lock_key = 'lock_takeinvite_' . $CURUSER['id'] . '_' . trim($_POST["email"]);
if ($Cache->get_value($lock_key)) {
  header("HTTP/1.1 409 Conflict");
  stderr($lang_takeinvite['std_error'],$lang_takeinvite['std_err_session'], false);
}
else {
  $Cache->cache_value($lock_key, true, 30); 
}

$id = $CURUSER['id'];

$email = unesc(htmlspecialchars(trim($_POST["email"])));
$email = safe_email($email);
if (!$email)
    bark($lang_takeinvite['std_must_enter_email']);
if (!check_email($email))
	bark($lang_takeinvite['std_invalid_email_address']);
if(EmailBanned($email))
    bark($lang_takeinvite['std_email_address_banned']);

if(!EmailAllowed($email))
    bark($lang_takeinvite['std_wrong_email_address_domains'].allowedemails());

$body = str_replace("<br />", "<br />", nl2br(trim(strip_tags($_POST["body"]))));
if(!$body)
	bark($lang_takeinvite['std_must_enter_personal_message']);


// check if email addy is already in use
$a = (@_mysql_fetch_row(@sql_query("select count(*) from users where email=".sqlesc($email)))) or die(_mysql_error());
if ($a[0] != 0)
  bark($lang_takeinvite['std_email_address'].htmlspecialchars($email).$lang_takeinvite['std_is_in_use']);
$b = (@_mysql_fetch_row(@sql_query("select count(*) from invites where invitee=".sqlesc($email)))) or die(_mysql_error());
if ($b[0] != 0)
  bark($lang_takeinvite['std_invitation_already_sent_to'].htmlspecialchars($email).$lang_takeinvite['std_await_user_registeration']);

$hash  = md5(mt_rand(1,10000).$CURUSER['username'].TIMENOW.$CURUSER['passhash']);


sql_query("INSERT INTO invites (inviter, invitee, hash, time_invited, body) VALUES (?, ?, ?, ?, ?)", [$id, $email, $hash, date("Y-m-d H:i:s"), $body]);
update_user($id, 'invites = invites - 1');

$Cache->delete_value($lock_key);

send_confirm_mail(['id' => _mysql_insert_id(), 'hash' => $hash, 'body' => $body, 'invitee' => $email]);


  
    

