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
		header("Location: " . $_SERVER['PHP_SELF']);
	}
}
require_once(get_langfile_path("", false, $CURLANGDIR));
failedloginscheck ();

unset($returnto);
if (!empty($_GET["returnto"])) {
  $returnto = $_GET["returnto"];
}
else {
  $returnto = "//$BASEURL/index.php";
}

cur_user_check ($returnto) ;
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
stdhead($lang_login['head_login']);

lang_choice_before_login();

if (!empty($_GET["returnto"])) {
	if (!$_GET["nowarn"]) {
		print("<h1>" . $lang_login['h1_not_logged_in']. "</h1>\n");
		print("<p><b>" . $lang_login['p_error']. "</b> " . $lang_login['p_after_logged_in']. "</p>\n");
	}
}
else {
  unset($returnto);
}
?>
<form method="post" action="takelogin.php">
<h2 class="page-titles"><a href="https://sp-pt.hust.edu.cn:444/Shibboleth.sso/DS?target=http://sp-pt.hust.edu.cn:81">联盟用户在此登录</a></h2>
<h3 class="page-titles">（本校用户可使用南六楼网络中心申请的锐捷账号通过联盟认证登录）</h3>
<div class="hints center"><ul><li><?php echo $lang_login['p_need_cookies_enables']?></li>
<li>[<b><?php echo $maxloginattempts;?></b>] <?php echo $lang_login['p_fail_ban']?></li>
<li><?php echo $lang_login['p_you_have']?> <b><?php echo remaining ();?></b> <?php echo $lang_login['p_remaining_tries']?></li></ul></div>
<?php
echo '<div id="sns" class="minor-list text center"><ul>';
foreach ($sns as $s) {
  echo '<li><a href="' . $s['url'] . '">' . $s['text'] . '</a></li>';
}
echo '</ul></div>';
?>
<table border="0" cellpadding="5" style="margin:0 auto;">
<tr><td class="rowhead"><?php echo $lang_login['rowhead_username']?></td><td class="rowfollow" align="left"><input type="text" name="username" style="width: 180px; border: 1px solid gray" /></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['rowhead_password']?></td><td class="rowfollow" align="left"><input type="password" name="password" style="width: 180px; border: 1px solid gray"/></td></tr>
<?php
show_image_code ();
if ($securelogin == "yes") 
	$sec = "checked=\"checked\" disabled=\"disabled\"";
elseif ($securelogin == "no")
	$sec = "disabled=\"disabled\"";
elseif ($securelogin == "op")
	$sec = "";
elseif ($securelogin == "op-yes")
	$sec = 'checked="checked"';


if ($securetracker == "yes") 
	$sectra = "checked=\"checked\" disabled=\"disabled\"";
elseif ($securetracker == "no")
	$sectra = "disabled=\"disabled\"";
elseif ($securetracker == "op")
	$sectra = '';
elseif ($securetracker == "op-yes")
	$sectra = 'checked="checked"';
?>
<tr><td class="toolbox" colspan="2" align="left"><?php echo $lang_login['text_advanced_options']?></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_auto_logout']?></td><td class="rowfollow" align="left"><input class="checkbox" type="checkbox" name="logout" value="yes" /><?php echo $lang_login['checkbox_auto_logout']?></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_restrict_ip']?></td><td class="rowfollow" align="left"><input class="checkbox" type="checkbox" name="securelogin" value="yes" /><?php echo $lang_login['checkbox_restrict_ip']?></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_ssl']?></td><td class="rowfollow" align="left"><label title="<?php echo $lang_login['hint_http_ssl'] ?>"><input class="checkbox" type="checkbox" name="ssl" value="yes" <?php echo $sec?> /><?php echo $lang_login['checkbox_ssl']?></label><br /><label><input class="checkbox" type="checkbox" name="trackerssl" value="yes" <?php echo $sectra?> /><?php echo $lang_login['checkbox_ssl_tracker']?></label></td></tr>
<tr><td class="toolbox" colspan="2" align="right"><input type="submit" value="<?php echo $lang_login['button_login']?>" class="btn" /> <input type="reset" value="<?php echo $lang_login['button_reset']?>" class="btn" /></td></tr>
</table>
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>
</form>
<div class="hints center"><ul>
<li><?php echo $lang_login['p_no_account_signup']?></li>
<?php
if ($smtptype != 'none'){
?>
<li><?php echo $lang_login['p_forget_pass_recover']?></li>
<li><?php echo $lang_login['p_resend_confirm']?></li>
<?php
}
?>
</ul></div>
<?php
if ($showhelpbox_main != 'no') {?>
<table width="700" class="main" border="0" cellspacing="0" cellpadding="0"><tr><td class="embedded">
<h2><?php echo $lang_login['text_helpbox'] ?><font class="small"> - <?php echo $lang_login['text_helpbox_note'] ?><font id= "waittime" color="red"></font></h2>
<?php
print("<table width='100%' border='1' cellspacing='0' cellpadding='1'><tr><td class=\"text\">\n");
print("<iframe src='" . get_protocol_prefix() . $BASEURL . "/shoutbox.php?type=helpbox' width='650' height='180' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe><br /><br />\n");
print("<form action='" . get_protocol_prefix() . $BASEURL . "/shoutbox.php' id='helpbox' method='get' target='sbox' name='shbox'>\n");
print($lang_login['text_message']."<input type='text' id=\"hbtext\" name='shbox_text' autocomplete='off' style='width: 500px; border: 1px solid gray' ><input type='submit' id='hbsubmit' class='btn' name='shout' value=\"".$lang_login['sumbit_shout']."\" /><input type='reset' class='btn' value=".$lang_login['submit_clear']." /> <input type='hidden' name='sent' value='yes'><input type='hidden' name='type' value='helpbox' />\n");
print("<div id=sbword style=\"display: none\">".$lang_login['sumbit_shout']."</div>");
print(smile_row("shbox","shbox_text"));
print("</td></tr></table></form></td></tr></table>");
}


stdfoot();
