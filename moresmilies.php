<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
echo '<div class="minor-list"><ul>';
$count = 0;
for($i=1; $i<192; $i++) {
     print("<li><a href=\"javascript: SmileIT('[em$i]','".$_GET["form"]."','".$_GET["text"]."')\"><img src=\"pic/smilies/$i.gif\" alt=\"\" ></a></li>");
     $count++;
}

echo '</ul></div>';

?>