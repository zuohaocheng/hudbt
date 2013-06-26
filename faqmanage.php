<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

checkPrivilegePanel();

stdhead("FAQ Management");
?>
<h1>FAQ Management</h1>
<style type="text/css" media="screen">
#tabs li a {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
<?php
// make the array that has all the faq in a nice structured
$res = sql_query("SELECT faq.id, faq.link_id, faq.lang_id, lang_name, faq.question, faq.flag, faq.order FROM faq LEFT JOIN language on faq.lang_id = language.id WHERE type='categ' ORDER BY lang_name, `order` ASC");
while ($arr = _mysql_fetch_array($res, MYSQL_BOTH)) {
	$faq_categ[$arr['lang_id']][$arr['link_id']]['title'] = $arr['question'];
	$faq_categ[$arr['lang_id']][$arr['link_id']]['flag'] = $arr['flag'];
	$faq_categ[$arr['lang_id']][$arr['link_id']]['order'] = $arr['order'];
	$faq_categ[$arr['lang_id']][$arr['link_id']]['id'] = $arr['id'];
	$faq_categ[$arr['lang_id']][$arr['link_id']]['lang_name'] = $arr['lang_name'];
}

$res = sql_query("SELECT faq.id, faq.question, faq.answer, faq.lang_id, faq.flag, faq.categ, faq.order FROM faq WHERE type='item' ORDER BY `order` ASC");
while ($arr = _mysql_fetch_array($res)) {
	$faq_categ[$arr['lang_id']][$arr['categ']]['items'][$arr['id']]['question'] = $arr['question'];
	$faq_categ[$arr['lang_id']][$arr['categ']]['items'][$arr['id']]['answer'] = $arr['answer'];
	$faq_categ[$arr['lang_id']][$arr['categ']]['items'][$arr['id']]['flag'] = $arr['flag'];
	$faq_categ[$arr['lang_id']][$arr['categ']]['items'][$arr['id']]['order'] = $arr['order'];
}

if (isset($faq_categ)) {
// gather orphaned items
	foreach ($faq_categ as $lang => $temp2){
		foreach ($temp2 as $id => $temp)
		{
			if (!array_key_exists("title", $temp2[$id]))
			{
				foreach ($temp2[$id]['items'] as $id2 => $temp)
				{
					$faq_orphaned[$lang][$id2]['question'] = $temp2[$id]['items'][$id2]['question'];
					$faq_orphaned[$lang][$id2]['flag'] = $temp2[$id]['items'][$id2]['flag'];
					unset($temp2[$id]);
				}
			}
		}
	}

	// print the faq table
#	print("<form method=\"post\" action=\"faqactions.php?action=reorder\">");
	$lis = [''];
	foreach ($faq_categ as $lang => $temp2)	{
	  foreach ($temp2 as $id => $temp) {
	    $contents = '<div class="accordion">';

			/* $status = ($temp2[$id]['flag'] == "0") ? "<font color=\"red\">Hidden</font>" : "Normal"; */
			/* print("<td align=\"center\" width=\"60px\">". $temp2[$id]['lang_name'] ."</td><td align=\"center\" width=\"60px\">". $status ."</td><td align=\"center\" width=\"60px\"><a href=\"faqactions.php?action=edit&id=". $temp2[$id]['id'] ."\">Edit</a> <a href=\"faqactions.php?action=delete&id=". $temp2[$id]['id'] ."\">Delete</a></td></tr>\n"); */

	    if (array_key_exists("items", $temp)) {
	      foreach ($temp['items'] as $id2 => $temp3) {
		if ($temp3['flag'] == "0") $status = ' faq-hidden">Hidden';
		elseif ($temp3['flag'] == "2") $status = ' faq_updated faq-status-img">Updated';
		elseif ($temp3['flag'] == "3") $status = ' faq_new faq-status-img">New';
		else $status = '">Normal';

		$contents .= '<div class="item" item="' . $id2 . '"><h3><span class="title">' . $temp3['question'] . '</span><span class="faq-status' . $status . '</span><a href="faqactions.php?action=delete&id=' . $id2 . '" class="faq-item-remove action" title="Remove"><span class="ui-icon ui-icon-minus"></span></a></h3>';
		$contents .= '<div class="text">' . $temp3['answer'] . '</div>';
		$contents .= '</div>';
		

		/* print("<td>". $temp2[$id]['items'][$id2]['question'] ."</td><td align=\"center\"></td><td align=\"center\" width=\"60px\">". $status ."</td><td align=\"center\" width=\"60px\"><a href=\"faqactions.php?action=edit&id=". $id2 ."\">Edit</a> <a href=\"faqactions.php?action=delete&id=". $id2 ."\">Delete</a></td></tr>\n"); */
	      }
	    }
	    $contents .= '</div>';

	    /* print("<tr><td colspan=\"6\" align=\"center\"><a href=\"faqactions.php?action=additem&inid=". $id ."&langid=".$lang."\">Add new item</a></td></tr>\n"); */

	    $lis[$temp['id']] = ['title' => $temp['title'],
				 'contents' => $contents,
				 'lang' => $lang,
				 ];

	  }
	}

	$ul = '';
	$divs = '';
	foreach ($lis as $id => $li) {
	  $ul .= '<li><a href="#tabs-' . $id . '" tab="' . $id . '" langid="' . $li['lang'] . '">' . $li['title'] . '</a><a href="faqactions.php?action=delete&id=' . $id . '" class="tab-remove action" title="Remove" style="display:none"><span class="ui-icon ui-icon-minus"></span></a></li>';
	  $divs .= '<div id="tabs-' . $id . '">' . $li['contents'] . '</div>';
	}
	echo '<div id="tabs"><ul>', $ul, '</ul>', $divs, '</div>';
}

// print the orphaned items table
/*if (isset($faq_orphaned)) {
	print("<br />\n<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" width=\"95%\">\n");
	print("<tr><td align=\"center\" colspan=\"3\"><b style=\"color: #FF0000\">Orphaned Items</b></td>\n");
	print("<tr><td class=\"colhead\" align=\"left\">Item Title</td><td class=\"colhead\" align=\"center\">Status</td><td class=\"colhead\" align=\"center\">Actions</td></tr>\n");
	foreach ($faq_orphaned as $lang => $temp2){
		foreach ($temp2 as $id => $temp)
		{
			if ($temp2[$id]['flag'] == "0") $status = "<font color=\"#FF0000\">Hidden</font>";
			elseif ($temp2[$id]['flag'] == "2") $status = "<font color=\"#0000FF\">Updated</font>";
			elseif ($temp2[$id]['flag'] == "3") $status = "<font color=\"#008000\">New</font>";
			else $status = "Normal";
			print("<tr><td>". $temp2[$id]['question'] ."</td><td align=\"center\" width=\"60px\">". $status ."</td><td align=\"center\" width=\"60px\"><a href=\"faqactions.php?action=edit&id=". $id ."\">edit</a> <a href=\"faqactions.php?action=delete&id=". $id ."\">delete</a></td></tr>\n");
		}
	}
	print("</table>\n");
}
*/

#print("<br />\n<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" width=\"95%\">\n<tr><td align=\"center\"><a href=\"faqactions.php?action=addsection\">Add new section</a></td></tr>\n</table>\n");


#print("<p>When the position numbers don't reflect the position in the table, it means the order id is bigger than the total number of sections/items and you should check all the order id's in the table and click \"reorder\"</p>");

echo '<p>双击修改，拖动修改顺序</p>';

stdfoot();

