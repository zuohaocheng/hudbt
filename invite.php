<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
$id = 0 + $_REQUEST["id"];
$type = unesc($_REQUEST["type"]);
if (!$id) {
  $id = $CURUSER['id'];
}
registration_check('invitesystem',true,false);

if (($CURUSER['id'] != $id && get_user_class() < $viewinvite_class) || !is_valid_id($id)) {
  header("HTTP/1.1 403 Forbidden");
  stderr($lang_invite['std_sorry'],$lang_invite['std_permission_denied']);
}
if (get_user_class() < $sendinvite_class) {
  header("HTTP/1.1 403 Forbidden");
  stderr($lang_invite['std_sorry'],$lang_invite['std_only'].get_user_class_name($sendinvite_class,false,true,true).$lang_invite['std_or_above_can_invite'],false);
}

stdhead($lang_invite['head_invites']);
print("<h1 align=center><a href=\"invite.php?id=".$id."\">".get_user_row($id)['username'].$lang_invite['text_invite_system']."</a></h1>");
	$sent = htmlspecialchars($_GET['sent']);
	if ($sent == 1){
		$msg = $lang_invite['text_invite_code_sent'];
		print("<p align=center><font color=red>".$msg."</font></p>");
	}

$res = sql_query("SELECT invites FROM users WHERE id = ?", [$id]);
$inv = _mysql_fetch_assoc($res);

//for one or more. "invite"/"invites"
if ($inv["invites"] != 1){
	$_s = $lang_invite['text_s'];
} else {
	$_s = "";
}
if ($type == 'recover'){
	if (($CURUSER['id'] != $id && get_user_class() < $viewinvite_class) || !is_valid_id($id))
		stderr($lang_invite['std_sorry'],$lang_invite['std_permission_denied']);
		$recinv =$_POST["invitee"];
		$rechash=$_POST["hash"];
		sql_query("DELETE FROM invites WHERE invitee = '".$recinv."'and hash='".$rechash."'");
		if(_mysql_affected_rows()) {
		  update_user($id, 'invites = invites+1');
		  stdmsg($lang_invite['std_recover'], $lang_invite['std_recoversentto'].$recinv.$lang_invite['std_s_invite']);
		}
	}
if ($type == 'new'){
	if ($CURUSER['invites'] <= 0) {
		stdmsg($lang_invite['std_sorry'],$lang_invite['std_no_invites_left'].
		"<a class=altlink href=invite.php?id=$CURUSER[id]>".$lang_invite['here_to_go_back'],false);
		print("</td></tr></table>");
		stdfoot();
		die;
	}
	$invitation_body =  $lang_invite['text_invitation_body'].$CURUSER['username'];
	//$invitation_body_insite = str_replace("<br />","\n",$invitation_body);
	echo 	"<h2 class=\"center\">".$lang_invite['text_invite_someone']."$SITENAME ($inv[invites]".$lang_invite['text_invitation'].$_s.$lang_invite['text_left'] .")</h2>";

	print("<form method=post action=takeinvite.php?id=".htmlspecialchars($id)." id=\"sendInviteRequest\">".
	"<table border=1 width=737 cellpadding=5>".
	"<tr><td class=\"nowrap\" valign=\"top\" align=\"right\">".$lang_invite['text_email_address']."</td><td align=left><input type=\"email\" size=40 name=\"email\"><br />".$lang_invite['text_email_address_note'].($restrictemaildomain == 'yes' ? "<br />".$lang_invite['text_email_restriction_note'].allowedemails() : "")."</td></tr>".
	"<tr><td class=\"nowrap\" valign=\"top\" align=\"right\">".$lang_invite['text_message']."</td><td><textarea name=body rows=8 cols=120>" .$invitation_body.
	"</textarea></td></tr>".
	"<tr><td class=\"center\" colspan=2><input type=\"submit\" value='".$lang_invite['submit_invite']."' onclick=\"document.getElementById('sendInviteRequest').submit(); this.disabled = true\"></td></tr>".
	"</form></table></td></tr></table>");

} else {

  $rel = sql_query("SELECT COUNT(*) FROM users WHERE invited_by = ?", [$id]);
	$arro = _mysql_fetch_row($rel);
	$number = $arro[0];

	$ret = sql_query("SELECT id, username, email, uploaded, downloaded, status, warned, enabled, donor, email FROM users WHERE invited_by = ?", [$id]);
	$num = _mysql_num_rows($ret);

	print("<h2 align=center>".$lang_invite['text_invite_status']." ($number)</h2>");

	echo "<form method=post action=takeconfirm.php?id=".htmlspecialchars($id).">";
	echo "<table class=\"invites\" border=1 width=737 cellpadding=5>";

	if(!$num){
		print("<tbody><tr><td colspan=7 align=center>".$lang_invite['text_no_invites']."</tr>");
	} else {

		print("<thead><tr><th><b>".$lang_invite['text_username']."</b></th><th><b>".$lang_invite['text_email']."</b></th><th><b>".$lang_invite['text_uploaded']."</b></th><th><b>".$lang_invite['text_downloaded']."</b></th><th><b>".$lang_invite['text_ratio']."</b></th><th><b>".$lang_invite['text_status']."</b></th>");

		print("</tr></thead><tbody>");
		for ($i = 0; $i < $num; ++$i)
		{
			$arr = _mysql_fetch_assoc($ret);
			$user = "<td class=rowfollow>" . get_username($arr['id']) . "</td>";

			if ($arr["downloaded"] > 0) {
				$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
				$ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
			} else {
				if ($arr["uploaded"] > 0) {
					$ratio = "Inf.";
				}
				else {
					$ratio = "---";
				}
			}
			if ($arr["status"] == 'confirmed')
			$status = "<a href=userdetails.php?id=$arr[id]><span class=\"confirmed\">".$lang_invite['text_confirmed']."</font></a>";
			else
			$status = "<span class=\"pending\">".$lang_invite['text_pending']."</span>";

			print("<tr>$user<td>$arr[email]</td><td class=rowfollow>" . mksize($arr['uploaded']) . "</td><td class=rowfollow>" . mksize($arr['downloaded']) . "</td><td class=rowfollow>$ratio</td><td class=rowfollow>$status</td></tr>");
		}
	}


	if ($CURUSER['id'] == $id) {
	  print("<tr><td colspan=7 align=center>");
	  if ($CURUSER['invites'] <= 0) {
	    echo '<span class="disabled">' . $lang_invite['text_unable_to_invite'] . '</span>';
	  }
	  else {
	    echo '<a class="index" href="' . htmlspecialchars('invite.php?id=' . $id . '&type=new') . '">' . $lang_invite['sumbit_invite_someone'] . '</a>';
	  }
	  echo '</td></tr>';
	}
	print("</tbody></table>");
print("</form>");


	$number1 = get_row_count('invites', "WHERE inviter = ?", [$id]);
	echo "<h2 class=\"center\">".$lang_invite['text_sent_invites_status']." ($number1)</h2>";
	print("<table border=1 width=737 cellpadding=5>");

	if(!$number1){
		print("<tr align=center><td colspan=6>".$lang_invite['text_no_invitation_sent']."</tr>");
	} else {
	  	$res = sql_query("SELECT id, hash, invitee, time_invited FROM invites WHERE inviter = ?", [$id]);

		print("<thead><tr><th>".$lang_invite['text_email']."</th><th>".$lang_invite['text_send_date']."</th>");
		if ($CURUSER['id'] == $id) {
		  echo "<th>".$lang_invite['text_action']."</th>";
		}
		echo "</tr></thead><tbody>";
		foreach ($res as $arr1) {
			print("<tr><td>$arr1[invitee]</td><td>$arr1[time_invited]</td>");
			if ($CURUSER['id'] == $id) {
			  print("<td><form class='a' method='post' action='invite.php'><input type='hidden' name='type' value='recover'><input type='hidden' name='id' value=$id><input type='hidden' name='invitee' value=".$arr1['invitee']."><input type='hidden' name='hash' value=".$arr1['hash']."><input type='submit' value=\"".$lang_invite['text_recover']."\"></form><form class='a' method='post' action='takeinvite.php?action=resend-mail&id=" . $arr1['id'] . "'><input type='submit' value=\"".$lang_invite['text_resend_mail']."\"></form></td>");
			}
		  print("</tr>");
		}
	}
	print("</tbody></table>");

}
stdfoot();
die;

