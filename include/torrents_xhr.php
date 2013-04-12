<?php

header('Content-type: application/json');

ob_start();
if (isset($_REQUEST['counter'])) {
  $counter = 0 + $_REQUEST['counter'];
}
else {
  $counter = 0;
}
$tooltips = torrenttable($rows, "torrents", $swap_headings, false, $counter, false, true);
$out = array('torrents' => ob_get_clean(),
	     'tooltips' => $tooltips);

if ($next_page_href != '') {
  $out['continue'] = $next_page_href;
}

$out['pager'] = array('top' => $pagertop, 'bottom' => $pagerbottom);

print(php_json_encode($out));

