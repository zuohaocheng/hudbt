      </div>
      <a href="#" id="back-to-top" title="回到页首" style="display:none;"></a>
    </div>
    <div id="footer">
      {if $footerad}
      <div style="margin-top: 10px; text-align:center;" id="ad_footer">{$footerad[0]}</div>
      {/if}
      {nocache}
      <div style="margin-top: 10px; margin-bottom: 30px; text-align: center;" id="tech-stats">
	(c) <a href="{get_protocol_prefix()}{$BASEURL}" target="_self">{$SITENAME}</a>{$icplicense_main}{if date("Y") != $yearfounded}{$yearfounded}-{/if}{$smarty.now|date_format:"%Y"} {$VERSION}<br /><br />
	[page created in <b> {$totaltime} </b> sec with <b>{count($queries)}</b> db queries, <b>{$Cache->getCacheReadTimes()}</b> reads and <b>{$Cache->getCacheWriteTimes()} </b> writes of memcached and <b>{mksize(memory_get_usage())}</b> ram]
      </div>
      {if $details}
      <div id="sql_debug">
	SQL query list: 
	<ul>
	  {foreach $queries as $query}
	  <li>{$query}</li>
	  {/foreach}
	</ul>
	Memcached key read:
	<ul>
	  {foreach $Cache->getKeyHits('read') as $keyName => $hits}
	  <li>{$keyName} : {$hits}</li>
	  {/foreach}
	</ul>
	Memcached key written:
	<ul>
	  {foreach $Cache->getKeyHits('write') as $keyName => $hits}
	  <li>{$keyName} : {$hits}</li>
	  {/foreach}
	</ul>
	{/if}
	{/nocache}
	<div style="display: none;" id="lightbox" class="lightbox"></div><div style="display: none;" id="curtain" class="curtain"></div>
	{$key_shortcut}
      </div>
      {$analyticscode_tweak}
      {$cnzz}
    </div>
  </body>
</html>

