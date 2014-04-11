<?php
require "include/bittorrent.php";
if ($_SERVER["REQUEST_METHOD"] != "POST")
	stderr("Error", "Permission denied!");
dbconn();
loggedinorreturn();                                                    
checkPrivilegePanel('staffmess');

$sender_id = ($_POST['sender'] == 'system' ? 0 : (int)$CURUSER['id']);
$msg = trim($_POST['msg']);
if (!$msg)
	stderr("Error","Don't leave any fields blank.");
$updateset = $_POST['clases'];
if (is_array($updateset)) {
	foreach ($updateset as $class) {
		if (!is_valid_id($class) && $class != 0)
			stderr("Error","Invalid Class");
	}
}else{
  stderr("Error","Invalid Class");
}

$subject = trim($_POST['subject']);
sql_query("INSERT INTO messages (sender, receiver, added, subject, msg) SELECT ?, id, NOW(), ?, ? FROM users WHERE class IN (".implode(",", $updateset).")",
	  [$sender_id, $subject, $msg]);

header("Refresh: 0; url=staffmess.php?sent=1");

