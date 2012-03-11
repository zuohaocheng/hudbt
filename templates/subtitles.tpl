<h2 id="page-title" class="transparentbg">{$lang['text_upload_subtitles']}</h2>
<h3 class="page-titles">{$lang['text_uploaded_size']} {mksize($size)}</h3>
<h3 class="page-titles">{$lang['text_rules']}</h3>
<div>
  <ol>
    {foreach $lang['text_rule'] as $hint}
    <li>{$hint}</li>
    {/foreach}
  </ol>
</div>
{if $detail_torrent_id}
<h3 class="page-titles">{$lang['text_uploading_subtitles_for_torrent']} <strong>{$torrent_name}</strong></h3>
{/if}

<form enctype="multipart/form-data" method="post" action="?">
  <input type="hidden" name="action" value="upload" />
  <div class="edit-hint">{$lang['text_red_star_required']}</div>
  <dl class="table" style="width:550px;">
    <dt><span class="required">{$lang['row_file']}</span></dt>
    <dd class="first-child">
      <input type="file" name="file" />
      {if $maxsubsize_main > 0}
      <br />
      ({$lang['text_maximum_file_size']} {mksize($maxsubsize_main)})
      {/if}
    </dd>
    <dt><span class="required">{$lang['row_torrent_id']}</span></dt>
    <dd>
      {if !$detail_torrent_id}
      <input type="text" name="torrent_id" style="width:300px"><br />{$lang['text_torrent_id_note']}
      {else}
      <input type="text" name="torrent_id" value="{$detail_torrent_id}" style="width:300px" /><br />{$lang['text_torrent_id_note']}
      {/if}
    </dd>
    <dt>{$lang['row_title']}</dt>
    <dd><input type="text" name="title" style="width:300px" /><br />{$lang['text_title_note']}</dd>
    <dt><span class="required">{$lang['row_language']}</span></dt>
    <dd>
      <select name="sel_lang">
	<option value="0">{$lang['select_choose_one']}</option>
	{html_options values=$lang_ids output=$lang_names}
      </select>
    </dd>
    {if (get_user_class() >= $beanonymous_class)}
    <dt>{$lang['row_show_uploader']}</dt>
    <dd>
      <label><input type="checkbox" name="uplver" value="yes" />{$lang['hide_uploader_note']}</label>
    </dd>
    {/if}
    <dd class="toolbox"><input type="submit" class="btn" value="{$lang['submit_upload_file']}" /> <input type="reset" class="btn" value="{$lang['submit_reset']}" />
  </dd>
  </dl>

</form>

<div id="search-subtitles">
  <form method="get" action="?">
    <input type="search" style="width:200px" name="search" />
    <select name="lang_id">
      <option value="0">{$lang['select_all_languages']}</option>
      {html_options values=$lang_ids output=$lang_names}
    </select>
    <input type="submit" class="btn" value="{$lang['submit_search']}" />
  </form>
  {a_to_z_index($letter)}
</div>

{if count($rows)}
{$pagertop}
<table style="width:100%;" cellspacing="0" cellpadding="5" class="no-vertical-line">
  <thead class="center">
    <tr>
      <th>{$lang['col_lang']}</th>
      <th>{$lang['col_title']}</th>
      <th>{$lang['col_torrent']}</th>
      <th><img class="time" src="pic/trans.gif" alt="time" title="{$lang['title_date_added']}" /></th>
      <th><img class="size" src="pic/trans.gif" alt="size" title="{$lang['title_size']}" /></th>
      <th>{$lang['col_hits']}</th>
      <th>{$lang['col_upped_by']}</th>
      <th>{$lang['col_report']}</th>
    </tr>
  </thead>
  <tbody>
    {foreach $rows as $arr}
    <tr>
      <td style="text-align:center;" valign="middle"><img border="0" src="pic/flag/{$arr["flagpic"]}" alt="{$arr["lang_name"]}" title="{$arr["lang_name"]}"/></td>
      <td><a href="downloadsubs.php?torrentid={$arr['torrent_id']}&amp;subid={$arr['id']}" title="{htmlspecialchars($arr["title"])}" class="index subtitle-name">{htmlspecialchars($arr["title"])}</a>
      {if $arr['candelete']}
      <a href="?delete={$arr['id']}"><span class="small">{$lang['text_delete']}</span></a>
      {/if}
      </td>
    <td><a href="details.php?id={$arr['torrent_id']}" title="{$arr['torrent_name']}" class="subtitle-name">{htmlspecialchars($arr['torrent_name'])}</a></td>
    <td style="text-align:center;">{gettime($arr["added"],false,false)}</td>
    <td style="text-align:center;">{mksize_loose($arr['size'])}</td>
    <td style="text-align:center;">{number_format($arr['hits'])}</td>
    <td style="text-align:center;">
      {if $arr["anonymous"] == 'yes'}
      {$lang['text_anonymous']}
      {if get_user_class() >= $viewanonymous_class}
      <br />{get_username($arr['uppedby'],false,true,true,false,true)}
      {/if}
      {else}
      {get_username($arr['uppedby'])}
      {/if}
    </td>
    <td style="text-align:center;"><a href="report.php?subtitle={$arr['id']}"><img class="f_report" src="pic/trans.gif" alt="Report" title="{$lang['title_report_subtitle']}" /></a></td>
  </tr>
  {/foreach}
</tbody>
</table>
{$pagerbottom}
{else}
{stdmsg($lang['text_sorry'],$lang['text_nothing_here'])}
{/if}