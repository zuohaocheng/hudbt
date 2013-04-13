<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
echo '<div class="minor-list"><ul>';
$count = 0;
for($i=1; $i<192; $i++) {
     print("<li><a href=\"#\" class=\"smileit\" smile=\"$i\" form=\"".$_GET["form"]."\"><img src=\"pic/smilies/$i.gif\" alt=\"\" ></a></li>");
     $count++;
}

echo '</ul></div>';
