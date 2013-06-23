<?php
require('include/bittorrent.php');
require('include/wechat.php');
dbconn();
loggedinorreturn();
checkPrivilegePanel();

stdhead('微信测试');

$content = $_REQUEST['content'];
$login = isset($_REQUEST['login']);
if (!$login) {
  $CURUSER_b = $CURUSER;
  unset($CURUSER);
}
?>
<form method="get" action="?">
<label>消息: <input type="text" name="content" value="<?=$content ?>" /></label>
<label><input type="checkbox" name="login" value="true"<?=$login?' checked="checked"':''?>/>已绑定</label>
<input type="submit" />
</form>
<pre>
  <?=wechat_text_response($content, [
    'fromusername' => 'test',
    'tousername' => 'hudbter',
    'createtime' => time(),
    'msgid' => 5893001498719160358,
    'msgtype' => 'text',
    'content' => $content,
  ]) ?>
</pre>
<?php
if (!$login) {
  $CURUSER = $CURUSER_b;
}
stdfoot();