<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path('sendmessage.php', false, 'chs'));
loggedinorreturn();
parked();

	$receiver = $_GET["receiver"];
	int_check($receiver,true);

	$replyto = $_GET["replyto"];
	if ($replyto && !is_valid_id($replyto))
		stderr($lang_sendmessage['std_error'],$lang_sendmessage['std_permission_denied']);

	
	$res = sql_query("SELECT * FROM users WHERE id=$receiver") or die(mysql_error());
	$user = mysql_fetch_assoc($res);
	if (!$user)
		stderr($lang_sendmessage['std_error'],$lang_sendmessage['std_no_user_id']);
	$subject = "请求迁移：我想下载你发在旧平台的种子";
	
	$cgbt_tid = $_GET['cgbt_tid'];
	$body = "我想下载你发在旧平台的种子。原来的种子地址：[url=/old/details.php?tid={$cgbt_tid}]这里[/url]\n\n点击\"迁移种子\"后面的链接就可以快速迁移了，不需要重新下载、上传种子。\n\n谢谢！ [em1] ";
	
	if ($replyto)
	{
		$res = sql_query("SELECT * FROM messages WHERE id=$replyto") or sqlerr();
		$msga = mysql_fetch_assoc($res);
		if ($msga["receiver"] != $CURUSER["id"])
			stderr($lang_sendmessage['std_error'],$lang_sendmessage['std_permission_denied']);
		$res = sql_query("SELECT username FROM users WHERE id=" . $msga["sender"]) or sqlerr();
		$usra = mysql_fetch_assoc($res);
		$body .= $msga[msg]."\n\n-------- [url=userdetails.php?id=".$CURUSER["id"]."]".$CURUSER["username"]."[/url][i] Wrote at ".date("Y-m-d H:i:s").":[/i] --------\n";
		$subject = $msga['subject'];
		if (preg_match('/^Re:\s/', $subject))
			$subject = preg_replace('/^Re:\s(.*)$/', 'Re(2): \\1', $subject);
		elseif (preg_match('/^Re\([0-9]*\):\s/', $msga['subject']))
		{
			$replycount=(int)preg_replace('/^Re\(([0-9]*)\):\s/', '\\1', $subject);
			$replycount++;
			$subject=preg_replace('/^Re\(([0-9]*)\):\s(.*)$/', 'Re('.$replycount.'): \\2', $subject);
		}
		else $subject = "Re: " . $msga['subject'];
		$subject = htmlspecialchars($subject);
	}
	stdhead($lang_sendmessage['head_send_message'], false);
	begin_main_frame();
	print("<form id=compose name=\"compose\" method=post action=takemessage.php>");
	print("<input type=hidden name=receiver value=".$receiver.">");
	if ($_GET["returnto"] || $_SERVER["HTTP_REFERER"])
		print("<input type=hidden name=returnto value=\"".(htmlspecialchars($_GET["returnto"]) ? htmlspecialchars($_GET["returnto"]) : htmlspecialchars($_SERVER["HTTP_REFERER"]))."\">");
	$title = $lang_sendmessage['text_message_to'].get_username($receiver);
	begin_compose($title, ($replyto ? "reply" : "new"), $body, true, $subject);
	print("<tr><td class=toolbox colspan=2 align=center>");
	if ($replyto) {
		print("<input type=checkbox name='delete' value='yes' ".($CURUSER['deletepms'] == 'yes' ? " checked" : "").">".$lang_sendmessage['checkbox_delete_message_replying_to']."<input type=hidden name=origmsg value=".$replyto.">");
	}

	print("<input type=checkbox name='save' value='yes' ". ($CURUSER['savepms'] == 'yes' ? " checked" : "").">".$lang_sendmessage['checkbox_save_message_to_sendbox']);
	print("</td></tr>");
	end_compose();
	end_main_frame();
	stdfoot();
?>
