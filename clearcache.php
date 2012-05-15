<?php
require "include/bittorrent.php";
dbconn();
$ip = getip();
if (($ip != '::1' && $ip != '127.0.0.1') || $_SERVER['HTTP_USER_AGENT'] != 'org.kmgtp.cli_api') {
  loggedinorreturn();
  checkPrivilegePanel();
}

$done = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $cachename = $_POST["cachename"];
  if ($cachename == "")
    stderr("Error", "You must fill in cache name.");
  if ($_POST['multilang'] == 'yes')
    $Cache->delete_value($cachename, true);
  else 
    $Cache->delete_value($cachename);
  $done = true;
}

if ($_REQUEST['format'] == 'json' && $done) {
  header('Content-type: application/json');
  echo json_encode(['success' => true]);
  die;
}

stdhead("Clear cache");
?>
<h1>Clear cache</h1>
<?php
if ($done) {
  print ("<div class=striking>Cache cleared</div>");
}
?>
<form method="post" action="?">
<dl class="table" style="width:40%">
<dt>Cache name</dt><dd><input type="text" name="cachename" size="40" required="required" /></dd>
<dt>Multi languages</dt><dd><label><input type="checkbox" name="multilang" />Yes</label></dd>
<dd><input type="submit" value="Okay" class="btn" /></dd>
</dl>
</form>
<?php
stdfoot();