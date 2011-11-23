<?php
require_once("include/bittorrent.php");
dbconn(true);
require_once(get_langfile_path("torrents.php", false, 'chs'));
loggedinorreturn();
parked();
stdhead($lang_torrents['head_torrents']);
print("<table width=\"940\" class=\"main\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\">");
?>
<h1 style="font-size: 32px; color: red;">迁移通告</h1>
<p style="font-size: 16px;color: red;">HUDBT 现已启用新平台，新平台的种子浏览页面为  <a href="/torrents.php" style="color: blue">http://www.kmgtp.org/torrents.php</a></p>
<p style="font-size: 16px;color: red;">你也可以浏览  <a href="/old/browse.php" style="color: blue">存档平台</a>[http://www.kmgtp.org/old/browse.php] 的资源，这些资源正在逐步迁移到新平台。<br />如果你有喜爱的资源，也可以自主的迁移过来，在新平台首页公告里有具体的  <a href="/index.php" style="color: blue">资源迁移方法</a>。资源迁移过程需要遇到问题了？<a href="/forums.php?action=viewtopic&forumid=10&topicid=322" style="color: blue">在此跟帖</a>。</p>
<?php 
print("</td></tr></table>");
stdfoot();
