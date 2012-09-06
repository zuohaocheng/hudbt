<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());

function insert_tag($name, $description, $syntax, $example, $remarks='') {
	global $lang_tags;
	$result = format_comment($example);
	print("<h2>$name</h2>\n");
	echo '<dl class="table td">';
	dl_item($lang_tags['text_syntax'], '<code>' . $syntax . '</code>', true);
	dl_item($lang_tags['text_example'], '<code>' . $example . '</code>', true);
	dl_item($lang_tags['text_result'], $result, true);
	if ($description) {
	  dl_item($lang_tags['text_description'], $description, true);
	}
	/* if ($remarks != "") */
	/* 	print("<tr><td>".$lang_tags['text_remarks']."</td><td>$remarks\n"); */
	echo '</dl>';
}

stdhead($lang_tags['head_tags']);
$test = $_POST["test"];
?>
<style type="text/css" media="screen">
kbd {
    font-family: STFangsong, FangSong, FangSong_GB2312, monospace;
    font-size: 120%;
}

dl {
    clear:both;
}
</style>
<h1><?php echo $lang_tags['text_tags'] ?></h1>
<p><?php echo $lang_tags['text_bb_tags_note'] ?></p>

<form id="test-form" method="post" action="?">
<textarea id="test" name="test" cols="60" rows="3"><?php print($test ? htmlspecialchars($test) : "")?></textarea>
<input type="submit" style='height: 23px; margin-left: 5px' value=<?php echo $lang_tags['submit_test_this_code'] ?>>
</form>
<?php

  echo '<div id="test-result">';
if ($test != "") {
  print("<fieldset>" . format_comment($test) . "</fieldset>");
}
echo '</div>';

foreach($lang_tags['tags'] as $tag) {
  insert_tag($tag['name'], $tag['desc'], $tag['syntax'], $tag['example']);
}

stdfoot();

