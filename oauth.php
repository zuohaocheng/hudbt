<?php
include('include/bittorrent.php');
dbconn();
loggedinorreturn();

if (!isset($_REQUEST['hash'])) {
  stderr('错误', '无效的ID');
}
else {
  $oid = get_single_value('user_properties', 'wechat_bind_id', 'WHERE user_id = ? AND wechat_bind = ? AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wechat_bind_time) < 86400', [$CURUSER['id'], $_REQUEST['hash']]);
  if ($oid) {
    sql_query('UPDATE users SET wechat = ? WHERE id = ?', [$oid, $CURUSER['id']]);
    sql_query('UPDATE user_properties SET wechat_bind = NULL, wechat_bind_time = NULL, wechat_bind_id = NULL WHERE user_id = ?', [$CURUSER['id']]);
    
    $Cache->delete_value('wechat_userid_' . $oid);
  }
  else {
    stderr('错误', '无效的ID');
  }
}

stdhead();
echo '<script type="text/javascript">window.opener=null;setTimeout(function() {window.open("","_self","");window.close()}, 5000)</script>';
stdmsg('绑定成功', '5秒后关闭本页');
stdfoot();
