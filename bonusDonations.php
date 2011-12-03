<?php
require("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();

stdhead($lang_donations['head_bonusDonations']);


function donationsForKey($key, $userid) {
  if ($key == 'donater_id') {
    $queryid = 'receiver_id';
    $nextpre = '?';
  }
  else if ($key == 'receiver_id') {
    $queryid = 'donater_id';
    $nextpre = '?receiver=1';
  }
  else {
    die();
  }

  $itemsperpage = 100;

  $where = 'WHERE ' . $key . ' = ' . $userid;
  $sql = 'SELECT COUNT(*), SUM(amount) FROM donate_bonus ' . $where;

  $res = sql_query($sql) or die(mysql_error());
  $count = 0;
  $sum = 0;
  while($row = mysql_fetch_array($res)) {
    $count += $row[0];
    $sum += $row[1];
  }

  list($pagertop, $pagerbottom, $limit) = pager($itemsperpage, $count, $nextpre);

  $query = 'SELECT donate_bonus.' . $queryid . ', donate_bonus.amount, donate_bonus.action_date, donate_bonus.object_id, torrents.name FROM donate_bonus LEFT JOIN torrents ON donate_bonus.object_id = torrents.id ' . $where . ' ORDER BY action_date DESC ' . $limit;

  $res = sql_query($query) or die(mysql_error());
  
  return array($pagertop, $pagerbottom, $res, $queryid, $sum);
}

function donationsTableFromResult($res, $queryid) {
  global $lang_donations;
  $out = '<table id="donations" cellpadding="5" style="width:750px;"><thead><tr><th>' . $lang_donations['col_time'] . '</th><th>' . $lang_donations['col_torrent'] . '</th><th>' . $lang_donations['col_username' ] . '</th><th>' . $lang_donations['col_amount'] . '</th></tr></thead><tbody>';

  while ($row = mysql_fetch_assoc($res)) {
    $out .= '<tr><td>' . $row['action_date'] . '</td><td><a href="details.php?id=' . $row['object_id'] . '" title="' . $row['name'] . '">' . $row['name'] . '</a></td><td>' . get_username($row[$queryid]) . '</td><td>' . $row['amount'] . '</td></tr>';
  }
  
  $out .= '</tbody></table>';
  return $out;
}

function donationsHtmlFromResult($result) {
  global $lang_donations;
  list($pagertop, $pagerbottom, $res, $queryid, $sum) = $result;
  $out = '<h3>' . $lang_donations['text_sum_' . $queryid] . $sum . '</h3>';
  $out .= $pagertop;
  $out .= donationsTableFromResult($res, $queryid);
  $out .= $pagerbottom;
  return $out;
}


print('<h2>' . $lang_donations['head_bonusDonations'] . '</h2>');
if ($_GET['receiver']) {
  $key = 'receiver_id';
}
else {
  $key = 'donater_id';
}

print(donationsHtmlFromResult(donationsForKey($key, 26058)));#$CURUSER['id'])));



/* if ($count == 0) { */
/*   print($lang_donations['text_not_available']; */
/* } */
/* else */ {

?>
<script type="text/javascript" src="js/jquery.tablesorter/jquery.tablesorter.js"></script>
<link rel="stylesheet" href="js/jquery.tablesorter/jquery.tablesorter.css" type="text/css" media="screen" />
<script type="text/javascript">
$(function() {
    $('#donations').tablesorter();
});
</script>

<?php
}

stdfoot();

?>