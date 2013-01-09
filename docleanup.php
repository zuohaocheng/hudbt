<?php
ob_start();
require_once("include/bittorrent.php");
dbconn();
if (php_sapi_name() == 'cli') {
  $web = false;
  $forceall = isset($argv[1]) && ($argv[1] == 'force');

  $puts = function($s) {
    echo $s, "\n";
  };
}
else {
  checkPrivilegePanel();
  echo "<html><head><title>Do Clean-up</title></head><body>";
  echo "<p>";
  $forceall = isset($_GET['forceall']) && $_GET['forceall'];
  $puts = function($s) {
    echo $s, '<br/>';
  };
}
$puts("clean-up in progress...please wait");
ob_flush();
flush();
if (!$forceall) {
  $puts("you may force full clean-up by adding the parameter 'forceall=1' to URL, or add 'force' in the command line arguments");
}
echo "</p>";
$tstart = getmicrotime();
require_once("include/cleanup.php");
$puts("<p>".docleanup($forceall, 1)."</p>");
$tend = getmicrotime();
$totaltime = ($tend - $tstart);
printf ("Time consumed:  %f sec", $totaltime);
$puts('');
$puts("Done");
if ($web) {
  echo "</body></html>";
}

