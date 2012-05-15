<?php
require('include/bittorrent.php');
dbconn();
header('HTTP/1.1 403 Forbidden');
header('Refresh: 5; url=//' . $BASEURL . '/index.php');
stderr('这谁家熊孩子啊', '别到处乱跑了，赶紧回家吃饭去吧<br /><br /><a href="//' . $BASEURL . '/index.php">5秒后自动回到首页</a>', false);
