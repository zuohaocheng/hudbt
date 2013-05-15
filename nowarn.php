<?php
require_once("include/bittorrent.php");
function bark($msg) {
stdhead();
stdmsg("Update Has Failed !", $msg);
stdfoot();
exit;
}
dbconn();
loggedinorreturn();

if(isset($_POST["nowarned"])&&($_POST["nowarned"]=="nowarned")){
//if (get_user_class() >= UC_SYSOP) {
if (get_user_class() < UC_MODERATOR)
stderr("Sorry", "Access denied.");

if (empty($_POST["usernw"]) && empty($_POST["desact"]) && empty($_POST["delete"]))
   bark("You Must Select A User To Edit.");

if (!empty($_POST["usernw"])) {
  #$msg = sqlesc("Your Warning Has Been Removed By: " . $CURUSER['username'] . ".");
  #$added = sqlesc(date("Y-m-d H:i:s"));
  #$userid = implode(", ", $_POST[usernw]);
  # send_pm(0, $userid, $msg, $msg);

  $modcomment = date("Y-m-d") . " - Warning Removed By " . $CURUSER['username'] . ".\n";

  foreach ($_POST['usernw'] as $user) {
    update_user($user, 'modcomment = CONCAT(?, modcomment), warned="no", warneduntil="0000-00-00 00:00:00"', [$modcomment]);
  }
}

if (!empty($_POST["desact"])) {
  foreach ($_POST['desact'] as $user) {
    update_user($user, "enabled='no'");
  }
}}
header("Refresh: 0; url=warned.php");

