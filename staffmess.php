<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
checkPrivilegePanel();
stdhead("Mass PM", false);
?>
<div style="text-align:center">
<h1>Mass PM to all Staff members and users:</h1>
<form method="post" action="takestaffmess.php">
<?php

if ($_GET["returnto"] || $_SERVER["HTTP_REFERER"]) {
?>
<input type="hidden" name="returnto" value="<?php echo htmlspecialchars($_GET["returnto"]) ? htmlspecialchars($_GET["returnto"]) : htmlspecialchars($_SERVER["HTTP_REFERER"])?>">
<?php
}
?>
<table cellspacing="0" cellpadding="5" style="margin:0 auto;text-align:left;width:50%;">
<?php
if ($_GET["sent"] == 1) {
?>
<tr><td colspan="2"><span class="striking">The message has ben sent.</span></tr></td>
<?php
}
?>
<tr>
<td><div class="minor-list"><h3>Send to:</h3><ul>
<li><label><input type="checkbox" name="clases[]" value="0" />Peasant</label></li>
<li><label><input type="checkbox" name="clases[]" value="1" />User</label></li>
<li><label><input type="checkbox" name="clases[]" value="2" />Power User</label></li>
<li><label><input type="checkbox" name="clases[]" value="3" />Elite User</label></li>
<li><label><input type="checkbox" name="clases[]" value="4" />Crazy User</label></li>
<li><label><input type="checkbox" name="clases[]" value="5" />Insane User</label></li>
<li><label><input type="checkbox" name="clases[]" value="6" />Veteran User</label></li>
<li><label><input type="checkbox" name="clases[]" value="7" />Extreme User</label></li>
<li><label><input type="checkbox" name="clases[]" value="8" />Ultimate User</label></li>
<li><label><input type="checkbox" name="clases[]" value="9" />Nexus Master</label></li>
<li><label><input type="checkbox" name="clases[]" value="10" />VIP</label></li>
<li><label><input type="checkbox" name="clases[]" value="11" />Retiree</label></li>
<li><label><input type="checkbox" name="clases[]" value="12" />Uploader</label></li>
<li><label><input type="checkbox" name="clases[]" value="13" />Moderator</label></li>
<li><label><input type="checkbox" name="clases[]" value="14" />Administrator</label></li>
<li><label><input type="checkbox" name="clases[]" value="15" />SysOp</label></li>
<li><label><input type="checkbox" name="clases[]" value="16" />Staff Leader</label></li>
</ul></div></td>
</tr>
<tr><td>Subject <input type="text" name="subject" size="75"></tr></td>
<tr><td><textarea name="msg" cols="80" rows="15"><?php echo $body?></textarea></td></tr>
<tr>
<td colspan="1"><div align="center"><b>Sender:&nbsp;&nbsp;</b>
<?php echo $CURUSER['username']?>
<input name="sender" type="radio" value="self" checked="checked" />
&nbsp; System
<input name="sender" type="radio" value="system">
</div></td></tr>
<tr><td colspan="1" align=center><input type=submit value="Send!" class=btn></td></tr>
</table>
<input type="hidden" name="receiver" value="<?php echo $receiver?>">
</form>

NOTE: Do not user BB codes. (NO HTML)
 </div>

<?php
stdfoot();
