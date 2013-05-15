<?php
require "include/bittorrent.php";
dbconn();
failedloginscheck ("Recover",true);
cur_user_check ('index.php') ;

if ($securelogin == "op-yes" || $securelogin == 'yes') {
  $link = 'https://';
}
else {
  $link = 'http://';
}

$take_recover = !isset($_GET['sitelanguage']);
$langid = 0 + $_GET['sitelanguage'];
if ($langid)
{
	$lang_folder = validlang($langid);
	if(get_langfolder_cookie() != $lang_folder)
	{
		set_langfolder_cookie($lang_folder);
		header("Location: " . $_SERVER['PHP_SELF']);
	}
}
require_once(get_langfile_path("", false, $CURLANGDIR));

function bark($msg) {
	global $lang_recover;
	stdhead();
	stdmsg($lang_recover['std_recover_failed'], $msg);
	stdfoot();
	exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($iv == "yes")
	check_code ($_POST['imagehash'], $_POST['imagestring'],"recover.php",true);
	$email = unesc(htmlspecialchars(trim($_POST["email"])));
	$email = safe_email($email);
	if (!$email)
	failedlogins($lang_recover['std_missing_email_address'],true);
	if (!check_email($email))
	failedlogins($lang_recover['std_invalid_email_address'],true);
	$res = sql_query("SELECT * FROM users WHERE email=" . sqlesc($email) . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
	$arr = _mysql_fetch_assoc($res);
	if (!$arr) failedlogins($lang_recover['std_email_not_in_database'],true);
	if ($arr['status'] == "pending") failedlogins($lang_recover['std_user_account_unconfirmed'],true);

	$sec = mksecret();

	update_user($arr['id'], 'editsecret=?', [$sec]);
	if (!_mysql_affected_rows())
	stderr($lang_recover['std_error'], $lang_recover['std_database_error']);

	$hash = md5($sec . $email . $arr["passhash"] . $sec);
	$ip = getip() ;
	$title = $SITENAME.$lang_recover['mail_title'];
	$link .= "$BASEURL/recover.php?id=" . $arr["id"] . "&secret=$hash";
	$body = <<<EOD
{$lang_recover['mail_one']}($email){$lang_recover['mail_two']}$ip{$lang_recover['mail_three']}
<b><a href="$link" onclick="window.open('$link');return false"> {$lang_recover['mail_this_link']} </a></b><br />
$link
{$lang_recover['mail_four']}
EOD;

	sent_mail($arr["email"],$SITENAME,$SITEEMAIL,change_email_encode(get_langfolder_cookie(), $title),change_email_encode(get_langfolder_cookie(),$body),"confirmation",true,false,'',get_email_encode(get_langfolder_cookie()));

}
elseif($_SERVER["REQUEST_METHOD"] == "GET" && $take_recover && isset($_GET["id"]) && isset($_GET["secret"]))
{
	$id = 0 + $_GET["id"];
	$md5 = $_GET["secret"];

	if (!$id)
	httperr();

	$res = sql_query("SELECT username, email, passhash, editsecret FROM users WHERE id = ?", [$id]) or sqlerr(__FILE__, __LINE__);
	$arr = _mysql_fetch_array($res) or httperr();
	$email = $arr["email"];

	$sec = hash_pad($arr["editsecret"]);
	if (preg_match('/^ *$/s', $sec))
	httperr();
	if ($md5 != md5($sec . $email . $arr["passhash"] . $sec))
	httperr();

	// generate new password;
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	$newpassword = "";
	for ($i = 0; $i < 10; $i++)
	$newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];

	$sec = mksecret();

	$newpasshash = md5($sec . $newpassword . $sec);

	update_user($id, "secret=?, editsecret='', passhash=?", [$sec, $newpasshash]);

	if (!_mysql_affected_rows())
	stderr($lang_recover['std_error'], $lang_recover['std_unable_updating_user_data']);
	$title = $SITENAME.$lang_recover['mail_two_title'];
	$link .= "$BASEURL/usercp.php?action=security";
	$body = <<<EOD
{$lang_recover['mail_two_one']}{$arr["username"]}
{$lang_recover['mail_two_two']}$newpassword
{$lang_recover['mail_two_three']}
<b><a href="$link" onclick="window.open('$link');return false">{$lang_recover['mail_here']}</a></b>
$link
{$lang_recover['mail_two_four']}

EOD;

	sent_mail($email,$SITENAME,$SITEEMAIL,change_email_encode(get_langfolder_cookie(), $title),change_email_encode(get_langfolder_cookie(),$body),"details",true,false,'',get_email_encode(get_langfolder_cookie()));

}
else
{
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

	stdhead();
	lang_choice_before_login();
	?>

	<h1><?php echo $lang_recover['text_recover_user'] ?></h1>
	<p><?php echo $lang_recover['text_use_form_below'] ?></p>
 	<p><?php echo $lang_recover['text_reply_to_confirmation_email'] ?></p>
  	<p><b><?php echo $lang_recover['text_note'] ?><?php echo $maxloginattempts;?></b><?php echo $lang_recover['text_ban_ip'] ?></p>
	<p><?php echo $lang_recover['text_you_have'] ?><b><?php echo remaining ();?></b><?php echo $lang_recover['text_remaining_tries'] ?></p>
	<form method="post" action="recover.php">
	<table border="1" cellspacing="0" cellpadding="10">
	<tr><td class="rowhead"><?php echo $lang_recover['row_registered_email'] ?></td>
	<td class="rowfollow"><input type="text" style="width: 150px" name="email" /></td></tr>
	<?php
	show_image_code ();
	?>
	<tr><td class="toolbox" colspan="2" align="center"><input type="submit" value="<?php echo $lang_recover['submit_recover_it'] ?>" class="btn" /></td></tr>
	</table></form>
	<?php
	stdfoot();
}
