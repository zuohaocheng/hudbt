<?php
require "include/bittorrent.php";
dbconn();
if (php_sapi_name() == 'cli') {
  if (isset($argv[1])) {
    $action = $argv[1];
  }
  else {
    echo 'No action!', "\n";
    die;
  }
}
else {
  loggedinorreturn();
  checkPrivilegePanel();

  $action = (isset($_POST['status']) ? $_POST['status'] : 'main');
}

if ($action == 'Free') {
	sql_query("UPDATE torrents_state SET global_sp_state = 2");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set free..');
}
elseif ($action == '2XUP') {
	sql_query("UPDATE torrents_state SET global_sp_state = 3");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set 2x up..');
}
elseif ($action == '2XFree') {
	sql_query("UPDATE torrents_state SET global_sp_state = 4");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set 2x up and free..');
}
elseif ($action == '50%Down') {
	sql_query("UPDATE torrents_state SET global_sp_state = 5");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set half down..');
}
elseif ($action == '2XUp&50%Down') {
	sql_query("UPDATE torrents_state SET global_sp_state = 6");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set half down..');
}
elseif ($action == 'Normal') {
	sql_query("UPDATE torrents_state SET global_sp_state = 1");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set normal..');
}
elseif ($action == 'main') {
	//stderr('Select action','Click <a class=altlink href=freeleech.php?action=setallfree>here</a> to set all torrents free.. <br /> Click <a class=altlink href=freeleech.php?action=setall2up>here</a> to set all torrents 2x up..<br /> Click <a class=altlink href=freeleech.php?action=setall2up_free>here</a> to set all torrents 2x up and free.. <br />Click <a class=altlink href=freeleech.php?action=setallhalf_down>here</a> to set all torrents half down..<br />Click <a class=altlink href=freeleech.php?action=setall2up_half_down>here</a> to set all torrents 2x up and half down..<br />Click <a class=altlink href=freeleech.php?action=setallnormal>here</a> to set all torrents normal..', false);
stdhead();
?>
<div class="minor-list-vertical center"><ul>
  <li><form method="post" ><input type="hidden" value="Free" name="status" /><input type="submit" value="Free" /></form></li>
  <li><form method="post" ><input type="hidden" value="2XUP" name="status" /><input type="submit" value="2x Up" /></form></li>
  <li><form method="post" ><input type="hidden" value="2XFree" name="status"/><input type="submit" value="2x &amp; Free" /></form></li>
  <li><form method="post" ><input type="hidden" value="50%Down" name="status" /><input type="submit" value="50%" /></form></li>
  <li><form method="post" ><input type="hidden" value="2XUp&amp;50%Down" name="status"/><input type="submit" value="2x &amp; 50%" /></form></li>
  <li><form method="post" ><input type="hidden" value="Normal" name="status"/><input type="submit" value="Normal" /></form></li>
</ul></div>
<?php
stdfoot();
}

