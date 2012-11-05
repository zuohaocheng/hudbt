<?php
require_once('include/bittorrent.php');
dbconn();
checkPrivilegePanel();
stdhead();
echo '<h1>华工IP地址查询</h1>';

if ($_POST['ip']) {
  echo '<div class="center">';
  if (preg_match('/^[0-9.%]+$/', $_POST['ip'])) {
    $sqlwhere = 'WHERE ip LIKE ' . sqlesc($_POST['ip']);
    $c = get_row_count('hustips', $sqlwhere);
    if ($c) {
      list($pagertop, $pagerbottom, $limit) = pager(50, $c, '?');
      echo $pagertop;
      echo '<table class="no-vertical-line center" cellpadding="5"><thead><tr><th>账号</th><th>IP</th></tr></thead><tbody>';
      $sql = 'SELECT account, ip FROM hustips '. $sqlwhere . ' ORDER BY account ASC ' . $limit;
      $res = sql_query($sql) or sqlerr();
      while ($row = mysql_fetch_assoc($res)) {
	echo '<tr><td>' . $row['account'] . '</td><td>' . $row['ip'] . '</tr>';
      }
      echo '</tbody></table>';
      echo $pagerbottom;
    }
    else {
      echo '没找到';
    }
  }
  else {
    echo '无效IP';
  }
  echo '</div>';
}
echo '<div class="center">支持%作为通配符，仅IPv4地址；本数据来源你懂的，请低调</div><form action="?" method="post" class="center"><label>IP: <input name="ip" autofocus="autofocus" required="required" /></form>';
stdfoot();