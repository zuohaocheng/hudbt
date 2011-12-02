<?php
require("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();

stdhead($lang_donations['head_bonusDonations']);

$itemsperpage = 100;

$where = 'WHERE donater_id = ' . $CURUSER['id'];
$sql = 'SELECT COUNT(*), SUM(amount) FROM donate_bonus ' . $where;
$res = sql_query($sql) or die(mysql_error());
$count = 0;
$sum = 0;
while($row = mysql_fetch_array($res)) {
  $count += $row[0];
  $sum += $row[1];
}


print('<h2>' . $lang_donations['head_bonusDonations'] . '</h2>');
print('<h3>' . $lang_donations['text_sum'] . $sum . '</h3>');

/* if ($count == 0) { */
/*   print($lang_donations['text_not_available']; */
/* } */
/* else */ {
  list($pagertop, $pagerbottom, $limit) = pager($itemsperpage, $count, '?');

  $query = 'SELECT donate_bonus.receiver_id, donate_bonus.amount, donate_bonus.action_date, donate_bonus.object_id, torrents.name FROM donate_bonus LEFT JOIN torrents ON donate_bonus.object_id = torrents.id ' . $where . ' ORDER BY action_date DESC ' . $limit;
  print($pagertop);

  $res = sql_query($query) or die(mysql_error());
  print('<table id="donations" cellpadding="5" style="width:750px;"><thead><tr><th>' . $lang_donations['col_time'] . '</th><th>' . $lang_donations['col_torrent'] . '</th><th>' . $lang_donations['col_username'] . '</th><th>' . $lang_donations['col_amount'] . '</th></tr></thead><tbody>');

  while ($row = mysql_fetch_assoc($res)) {
    print('<tr><td class="colfollow">' . $row['action_date'] . '</td><td class="colfollow"><a href="details.php?id=' . $row['object_id'] . '" title="' . $row['name'] . '">' . $row['name'] . '</a></td><td class="colfollow">' . get_username($row['receiver_id']) . '</td><td class="colfollow">' . $row['amount'] . '</td></tr>');
  }
  
  print('</tbody></table>');

  print($pagerbottom);
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