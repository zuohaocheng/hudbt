<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Attachments extends HTML_BBCodeParser_Filter {
  function _preparse() {
    $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];
    $this->_preparsed =
      preg_replace_callback(
		   "!".$oe."attach".$ce."([0-9a-f]{32})".$oe."/attach".$ce."!Ui",
		   array($this, 'attachmentCallback'),
		   $this->_text);
  }

  public function attachmentFromId($dlkey) {
    global $Cache, $httpdirectory_attachment;
    global $lang_functions;
    $enableimage = true;

    if (!$row = $Cache->get_value('attachment_'.$dlkey.'_content')) {
      $res = sql_query("SELECT * FROM attachments WHERE dlkey=".sqlesc($dlkey)." LIMIT 1") or sqlerr(__FILE__,__LINE__);
      $row = mysql_fetch_array($res);
      $Cache->cache_value('attachment_'.$dlkey.'_content', $row, 86400);
    }

    if (!$row) {
      return '[s]'.$lang_functions['text_attachment_key'] . $dlkey . $lang_functions['text_not_found'] . '[/s]';
    }

    $id = $row['id'];
    if ($row['isimage'] == 1) {
	if ($enableimage){
	  $fullurl = '';
	  if ($row['thumb'] == 1){
	    $url = $httpdirectory_attachment."/".$row['location'].".thumb.jpg";
	    $fullurl = ' full="' . $httpdirectory_attachment."/".$row['location'] . '"';
	  }
	  else{
	    $url = $httpdirectory_attachment."/".$row['location'];
	  }

	  $return = '[img' . $fullurl . ']' . $url . '[/img]';
	}
	else $return = "";
      }
    else {
      switch($row['filetype']) {
      case 'application/x-bittorrent': {
	$icon = "<img alt=\"torrent\" src=\"pic/attachicons/torrent.gif\" />";
	break;
      }
      case 'application/zip':{
	$icon = "<img alt=\"zip\" src=\"pic/attachicons/archive.gif\" />";
	break;
      }
      case 'application/rar':{
	$icon = "<img alt=\"rar\" src=\"pic/attachicons/archive.gif\" />";
	break;
      }
      case 'application/x-7z-compressed':{
	$icon = "<img alt=\"7z\" src=\"pic/attachicons/archive.gif\" />";
	break;
      }
      case 'application/x-gzip':{
	$icon = "<img alt=\"gzip\" src=\"pic/attachicons/archive.gif\" />";
	break;
      }
      case 'audio/mpeg':{
      }
      case 'audio/ogg':{
	$icon = "<img alt=\"audio\" src=\"pic/attachicons/audio.gif\" />";
	break;
      }
      case 'video/x-flv':{
	$icon = "<img alt=\"flv\" src=\"pic/attachicons/flv.gif\" />";
	break;
      }
      default: {
	$icon = "<img alt=\"other\" src=\"pic/attachicons/common.gif\" />";
	break;
      }
      }
      $return = "<div class=\"attach\">".$icon."&nbsp;&nbsp;<a href=\"".htmlspecialchars("getattachment.php?id=".$id."&dlkey=".$dlkey)."\" target=\"_blank\" id=\"attach".$id."\" onmouseover=\"domTT_activate(this, event, 'content', '".htmlspecialchars("<strong>".$lang_functions['text_downloads']."</strong>: ".number_format($row['downloads'])."<br />".gettime($row['added']))."', 'styleClass', 'attach', 'x', findPosition(this)[0], 'y', findPosition(this)[1]-58);\">".htmlspecialchars($row['filename'])."</a>&nbsp;&nbsp;<font class=\"size\">(".mksize($row['filesize']).")</font></div>";
      }
    return $return;
  }
  
  public function attachmentCallback($m) {
    return $this->attachmentFromId($m[1]);
  }
}

?>