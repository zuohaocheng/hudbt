<script type="text/javascript">hb.bbcode={json_encode($config)};</script>
<div id="bbcode-toolbar"></div>
{if $attach}
<iframe src="attachment.php" width="100%" height="24" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
{/if}
<div style="margin:1.5em 1.5em 0 0; width:20%; float:right; text-align:center;">
  <div class="minor-list smiles" style="margin-bottom: 1.5em;">
    <ul>
      {foreach $smilies as $smily}
      <li>{getSmileIt($config['form'], $config['text'], $smily)}</li>
      {/foreach}
    </ul>
  </div>
  <a id="showmoresmilies" href="#">{$lang['text_more_smilies']}</a>
</div>
<div id="moresmilies" style="display:none;" title="{$lang_functions['text_more_smilies']}"></div>
<textarea class="bbcode" cols="100" style="width: 70%;" name="{$config['text']}" id="{$config['text']}" rows="20" onkeydown="ctrlenter(event,'compose','qr')">{nocache}{$content}{nocache}</textarea>
{get_load_uri('js', 'bbcode.js')}
