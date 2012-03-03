<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  {$meta}
  {get_style_addicode()}
  <title>{nocache}{$title}{/nocache}</title>
  <link rel="shortcut icon" href="//{$BASEURL}/favicon.ico" type="image/x-icon" />
  <link rel="apple-touch-icon" href="//{$BASEURL}/pic/icon.png" />
  <link rel="search" type="application/opensearchdescription+xml" title="{$SITENAME} Torrents" href="//{$BASEURL}/opensearch.php" />
  <script type="text/javascript">
    //<![CDATA[
    {js_hb_config()}
    //]]>
  </script>
  {nocache}
  {get_load_uri('css', '')}
  {get_load_uri('js', '')}
  {/nocache}
  <!--[if lte IE 6]>
      <script type="text/javascript" src="js/ie6utf8.js"></script>
      <![endif]-->
</head>
<body>
  <div id="wrap">
    <div id="header">
      {if $logo_main == ""}
      <div class="logo">{$SITENAME}</div>
      <div class="slogan">{$SLOGAN}</div>
      {else}
      <div><a href="//{$BASEURL}/index.php"><img id="logo-img" src="//{$BASEURL}/{$logo_main}" alt="{$SITENAME}" title="{$SITENAME} - {$SLOGAN}" /></a></div>  
      {/if}
      <div id="donate">
	{if $headerad}
	<span id="ad_header">{$headerad[0]}</span>
	{/if}

	{if $enabledonation}
	<a href="//{$BASEURL}/donate.php"><img src="//{$BASEURL}/{$forum_pic}/donate.gif" alt="Make a donation" style="margin-left: 5px; margin-top: 50px;" /></a>
	{/if}
    </div></div>
    <div id="page">
      {if !$CURUSER} 
      <div id="nav-reg-signup" class="big minor-list list-seperator minor-nav"><ul>{nocache}{no_login_navbar()}{/nocache}</ul></div>
      {else}
      {nocache}
      {menu()}
      {/nocache}
      <div id="info_block" class="table td">
	<div><span class="medium">{$lang['text_the_time_is_now']}{$smarty.now|date_format:"%H:%M"}
	<br />
	{if get_user_class() >= $staffmem_class}
	<a href="//{$BASEURL}/cheaterbox.php"><img class="cheaterbox" alt="cheaterbox" title="{$lang['title_cheaterbox']}" src="//{$BASEURL}/pic/trans.gif" /></a> {$totalcheaters}
	<a href="//{$BASEURL}/reports.php"><img class="reportbox" alt="reportbox" title="{$lang['title_reportbox']}" src="//{$BASEURL}/pic/trans.gif" /></a> {$totalreports}
	<a href="//{$BASEURL}/staffbox.php"><img class="staffbox" alt="staffbox" title="{$lang['title_staffbox']}" src="//{$BASEURL}/pic/trans.gif" /></a> {$totalsm}
	{/if}

	<a href="//{$BASEURL}/messages.php"><img class="{if $unread}inboxnew{else}inbox{/if}" src="//{$BASEURL}/pic/trans.gif" alt="inbox" title={if $unread}{$lang['title_inbox_new_messages']}{else}$lang['title_inbox_no_new_messages']}{/if} /></a>
	{if $messages}{$messages}({$unread}{$lang['text_message_new']})
	{else}
	0
	{/if}

	<a href="//{$BASEURL}/messages.php?action=viewmailbox&amp;box=-1"><img class="sentbox" alt="sentbox" title="{$lang['title_sentbox']}" src="//{$BASEURL}/pic/trans.gif" /></a> {if $outmessages}{$outmessages}{else}0{/if}
	<a href="//{$BASEURL}/friends.php"><img class="buddylist" alt="Buddylist" title="{$lang['title_buddylist']}" src="//{$BASEURL}/pic/trans.gif" /></a>
	<a href="//{$BASEURL}/getrss.php"><img class="rss" alt="RSS" title="{$lang['title_get_rss']}" src="//{$BASEURL}/pic/trans.gif" /></a>
	</span></div>

	<div>
	  <div class="minor-list list-seperator compact">
	    <ul>
	      <li><span class="medium">{$lang['text_welcome_back']}, {get_username($id)}</li>
	      <li><form action="//{$BASEURL}/logout.php" method="POST"><input type="submit" class="a" value="{$lang['text_logout']}" /></form></li>
	      {if get_user_class() >= $UC_MODERATOR}
	      <li><a href="//{$BASEURL}/staffpanel.php">{$lang['text_staff_panel']}</a></li>
	      {/if} 
	      {if get_user_class() >= $UC_SYSOP}
	      <li><a href="//{$BASEURL}/settings.php">{$lang['text_site_settings']}</a></li>
	      {/if}
	      <li><a href="//{$BASEURL}/torrents.php?inclbookmarked=1&amp;allsec=1&amp;incldead=0">{$lang['text_bookmarks']}</a></li>
	      <li><a href="//{$BASEURL}/mybonus.php" title="{$lang['text_use']}"><span class = 'color_bonus'>{$lang['text_bonus']}</span>: <span id="bonus">{number_format($CURUSER['seedbonus'], 1)}</span></a></li>
	      <li><a href="//{$BASEURL}/invite.php?id={$id}" title="{$lang['text_send']}"><span class = "color_invite">{$lang['text_invite']}</span>: <span id="invites">{$CURUSER['invites']}</span></a></li>
	    </ul>
	  </div>

	  <div class="minor-list compact">
	    <ul>
	      <li><span class="color_ratio">{$lang['text_ratio']}</span>{$ratio}</li>
	      <li><span class="color_uploaded">{$lang['text_uploaded']}</span><span id="uploaded">{mksize($CURUSER['uploaded'])}</span></li>
	      <li><span class='color_downloaded'> {$lang['text_downloaded']}</span>{mksize($CURUSER['downloaded'])}</li>
	      <li><span class='color_active'>{$lang['text_active_torrents']}</span> <img class="arrowup" alt="Toerrents seeding" title="{$lang['title_torrents_seeding']}" src="//{$BASEURL}/pic/trans.gif" />{$activeseed}<img class="arrowdown" alt="Torrents leeching" title="{$lang['title_torrents_leeching']}" src="//{$BASEURL}/pic/trans.gif" />{$activeleech}</li>
	      <li><span class='color_connectable'>{$lang['text_connectable']}</span>{$connectable}</li>
	      <li>{maxslots()}</li>
	    </ul>
	  </div>
	</div>
      </div>

      {if $belownavad}
      <div style="margin-bottom: 10px;text-align:center" id="ad_belownav">
	{$belownavad[0]}
      </div>
      {/if}

      {if $alerts}
      <div id="alert" class="minor-list"><ul>
	{section name=idx loop=$alerts}
	{msgalert($alerts[idx]['href'], $alerts[idx]['text'], $alerts[idx]['color'])}
	{/section}
      </ul></div>
      {/if}
      {/if}
      <div id="outer">
