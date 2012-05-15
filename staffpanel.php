<?php
ob_start();
require_once("include/bittorrent.php");
include($rootpath . get_langfile_path());
dbconn();
loggedinorreturn();
stdhead("Administration");
print("<h1 class=\"center\">Administration</h1>");
$panels = $lang_staffpanel;

echo '<dl class="table longt">';
$c = 0;
foreach ($panels as $name => $panel) {
  if (checkPrivilege(['ManagePanels', $name])) {
    dl_item($panel['name'], '<a href="//' . $BASEURL . '/' . $name . '.php' . '">' . $panel['desc'] . '</a>', true);
    ++$c;
  }
}

if ($c == 0) {
  echo '</dl><h2 class="center">这谁家熊孩子啊，怎么到处乱跑呢</h2>';
}
else {
  echo '</dl>';
}

stdfoot();

