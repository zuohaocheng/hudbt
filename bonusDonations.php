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

  $count = 0;
  while ($row = mysql_fetch_assoc($res)) {
    $count++;
    if ($row['name'] == '') {
      $torrent = $lang_donations['text_no_torrent'];
    }
    else {
      $torrent = '<a href="details.php?id=' . $row['object_id'] . '" title="' . $row['name'] . '">' . $row['name'] . '</a>';
    }
    $out .= '<tr><td>' . $row['action_date'] . '</td><td>' . $torrent . '</td><td>' . get_username($row[$queryid]) . '</td><td>' . $row['amount'] . '</td></tr>';
  }

  if ($count == 0) {
    return '';
  }
  
  $out .= '</tbody></table>';
  return $out;
}

function donationsHtmlFromResult($result) {
  global $lang_donations;
  list($pagertop, $pagerbottom, $res, $queryid, $sum) = $result;
  $out = '<h3 style="text-align:center;">' . $lang_donations['text_sum_' . $queryid] . $sum . '</h3>';
  $out .= $pagertop;
  $table = donationsTableFromResult($res, $queryid);
  if ($table == '') {
    return $lang_donations['text_no_record'];
  }
  $out .= $table;
  $out .= $pagerbottom;
  return $out;
}

if ($_GET['receiver']) {
  $donate = '<a href="bonusDonations.php">' . $lang_donations['nav_donate'] . '</a>';
  $receive = '<span class="selected">' . $lang_donations['nav_receive'] . '</span>';
}
else {
  $receive = '<a href="bonusDonations.php?receiver=1">' . $lang_donations['nav_receive'] . '</a>';
  $donate = '<span class="selected">' . $lang_donations['nav_donate'] . '</span>';
}

?>
<div class="minor-list list-seperator minor-nav"><ul><li><?php echo $donate ?></li><li><?php echo $receive ?></li></ul></div>
<?php

print('<h2 style="text-align:center;">' . $lang_donations['head_bonusDonations'] . '</h2>');
if ($_GET['receiver']) {
  $key = 'receiver_id';
}
else {
  $key = 'donater_id';
}

print(donationsHtmlFromResult(donationsForKey($key, $CURUSER['id'])));

/* if ($count == 0) { */
/*   print($lang_donations['text_not_available']; */
/* } */
/* else */ {

?>

<?php
}

stdfoot();

?>