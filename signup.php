<?php
require_once("include/bittorrent.php");
dbconn();

$langid = 0 + $_GET['sitelanguage'];
if ($langid)
{
	$lang_folder = validlang($langid);
	if(get_langfolder_cookie() != $lang_folder)
	{
		set_langfolder_cookie($lang_folder);
		header("Location: " . $_SERVER['REQUEST_URI']);
	}
}
require_once(get_langfile_path("", false, $CURLANGDIR));
cur_user_check ("//$BASEURL/index.php");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
$type = $_GET['type'];
if ($type == 'invite')
{
	registration_check();
	failedloginscheck ("Invite signup");
	$code = $_GET["invitenumber"];

	/* $nuIP = getip(); */
	/* $dom = @gethostbyaddr($nuIP); */
	/* if ($dom == $nuIP || @gethostbyname($dom) != $nuIP) */
	/* $dom = ""; */
	/* else */
	/* { */
	/* $dom = strtoupper($dom); */
	/* preg_match('/^(.+)\.([A-Z]{2,3})$/', $dom, $tldm); */
	/* $dom = $tldm[2]; */
	/* } */

	$res = sql_query('SELECT inviter, invitee FROM invites WHERE hash =?', [$code]);
	$inv = _mysql_fetch_assoc($res);
	if (!$inv)
		stderr($lang_signup['std_error'], $lang_signup['std_uninvited'], 0);
	$inviter = htmlspecialchars($inv["inviter"]);

	stdhead($lang_signup['head_invite_signup']);
}
else {
	registration_check("normal");
	failedloginscheck ("Signup");
	stdhead($lang_signup['head_signup']);
}


if ($type == 'invite') {
  $extra = ("<input type=hidden name=type value='invite'><input type=hidden name=invitenumber value='".$code."'>");
}
else {
  $extra = '';
}
lang_choice_before_login($extra);
?>

<form method="post" action="takesignup.php">
<?php if ($type == 'invite') print("<input type=\"hidden\" name=\"inviter\" value=\"".$inviter."\"><input type=hidden name=type value='invite' />"); ?>
<table border="1" cellspacing="0" cellpadding="10" style="margin: 0 auto;">
<?php
print("<tr><td class=\"text center\" colspan=2>".$lang_signup['text_cookies_note']."</td></tr>");
?>
<tr><td class="rowhead"><?php echo $lang_signup['row_desired_username'] ?></td><td><input type="text" style="width: 200px" name="wantusername" /><br />
<span class="small"><?php echo $lang_signup['text_allowed_characters'] ?></span></td></tr>
<tr><td class="rowhead"><?php echo $lang_signup['row_pick_a_password'] ?></td><td><input type="password" style="width: 200px" name="wantpassword" /><br />
	<span class="small"><?php echo $lang_signup['text_minimum_six_characters'] ?></span></td></tr>
<tr><td class="rowhead"><?php echo $lang_signup['row_enter_password_again'] ?></td><td><input type="password" style="width: 200px" name="passagain" /></td></tr>
<?php
show_image_code ();
?>
<tr><td class="rowhead"><?php echo $lang_signup['row_email_address'] ?></td><td><input type="email" style="width: 200px" name="email" <?php if (isset($inv)) echo 'value="' . $inv['invitee'] . '"'; ?>/>
<table width=250 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font class=small><?php echo ($restrictemaildomain == 'yes' ? $lang_signup['text_email_note'].allowedemails() : "") ?></td></tr>
</font></td></tr></table>
</td></tr>
<?php $countries = "<option value=\"8\">---- ".$lang_signup['select_none_selected']." ----</option>n";
$ct_r = sql_query("SELECT id,name FROM countries ORDER BY name") or die;
while ($ct_a = _mysql_fetch_array($ct_r))
$countries .= "<option value=$ct_a[id]" . ($ct_a['id'] == 8 ? " selected" : "") . ">$ct_a[name]</option>n";
tr($lang_signup['row_country'], "<select name=country>n$countries</select>", 1); 
//School select
if ($showschool == 'yes'){
$schools = "<option value=35>---- ".$lang_signup['select_none_selected']." ----</option>n";
$sc_r = sql_query("SELECT id,name FROM schools ORDER BY name") or die;
while ($sc_a = _mysql_fetch_array($sc_r))
$schools .= "<option value=$sc_a[id]" . ($sc_a['id'] == 16 ? " selected" : "") . ">$sc_a[name]</option>n";
tr($lang_signup['row_school'], "<select name=school>$schools</select>", 1);
}
?>
<tr><td class="rowhead"><?php echo $lang_signup['row_gender'] ?></td><td>
<label><input type="radio" name="gender" value="Male"><?php echo $lang_signup['radio_male'] ?></label><label><input type="radio" name="gender" value="Female"><?php echo $lang_signup['radio_female'] ?></label></td></tr>
<tr><td class="rowhead"><?php echo $lang_signup['row_verification'] ?></td><td><label><input type="checkbox" name="rulesverify" value="yes"><?php echo $lang_signup['checkbox_read_rules'] ?></label><br />
<label><input type="checkbox" name="faqverify" value="yes"><?php echo $lang_signup['checkbox_read_faq'] ?></label> <br />
<label><input type="checkbox" name="ageverify" value="yes"><?php echo $lang_signup['checkbox_age'] ?></label></td></tr>
  <?php if (isset($code)) { ?>
<input type="hidden" name="hash" value=<?php echo $code?>>
   <?php } ?>
<tr><td class="toolbox center" colspan="2"><div class="striking"><?php echo $lang_signup['text_all_fields_required'] ?></div><input type="submit" value=<?php echo $lang_signup['submit_sign_up'] ?> style='height: 25px'></td></tr>
</table>
</form>
<?php
stdfoot();
