<?php
require "include/bittorrent.php";
dbconn();

loggedinorreturn();
checkPrivilegePanel();

stdhead();

if (isset($_POST['connect'])) {
  if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})){3}$/', $_POST['ip'])) {
    $out = exec('./ssh ' . $_POST['ip']);
  }
  else {
    $out = 'Invalid IP addr';
  }
}

?>

<form method="post">
  <input type="hidden" name="connect" value="1" />
  <label>IP: <input type="text" name="ip" value="<?php echo getip(); ?>" /></label>
  <input type="submit" value="Submit" />
</form>

<?php
if (isset($out)) {
?><div><?php echo $out ?></div><?php
}

stdfoot();