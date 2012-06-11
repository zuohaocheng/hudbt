<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Refs extends HTML_BBCodeParser_Filter {
  function _preparse() {
    $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];
    $this->_preparsed = $this->_text;
    foreach (['torrent', 'user', 'topic', 'post','name'] as $item) {
      if($item=='name'){
      	$this->_preparsed =
	preg_replace_callback(
			      "!".$oe.$item."=(\w+)".$ce."!Ui",
			      array($this, $item . 'Callback'),
			      $this->_preparsed);
      	}
      else{
      $this->_preparsed =
	preg_replace_callback(
			      "!".$oe.$item."=([0-9]+)".$ce."!Ui",
			      array($this, $item . 'Callback'),
			      $this->_preparsed);
			    }
    }
  }

  function torrentCallback($keys) {
    $key = $keys[1];
    $query = 'SELECT name FROM torrents WHERE id=' . (0+$key) . ' LIMIT 1';
    $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_row($r);
    if ($a) {
      global $BASEURL;
      return '<a href="//' . $BASEURL . '/details.php?id=' . $key . '">' . htmlspecialchars($a[0]) . '</a>';
    }
    else {
      return '(无此种子)';
    }
  }

  function userCallback($keys) {
    return get_username(0 + $keys[1]);
  }
  
 
  function nameCallback($keys) {
    if(get_user_id_from_name($keys[1],0)=="NULL")
  		return '(无此帐户)';
    else
    return get_username(get_user_id_from_name($keys[1],0));
  }
  function topicCallback($keys) {
    $key = $keys[1];
    $query = 'SELECT subject FROM topics WHERE id=' . (0+$key) . ' LIMIT 1';
    $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_row($r);
    if ($a) {
      global $BASEURL;
      return '<a href="//' . $BASEURL . '/forums.php?action=viewtopic&amp;topicid=' . $key . '">' . $a[0] . '</a>';
    }
    else {
      return '(无此帖子)';
    }
  }

  function postCallback($keys) {
    $key = $keys[1];
    $query = 'SELECT topics.subject, topics.id FROM posts LEFT JOIN topics ON posts.topicid = topics.id WHERE posts.id=' . (0+$key) . ' LIMIT 1';
    $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_row($r);
    if ($a) {
      $topicname = $a[0];
      $topicid = $a[1];
      $query = 'SELECT COUNT(1) FROM posts WHERE id<' . (0 + $key) . ' AND topicid=' . $topicid . ';';
      $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
      $a = mysql_fetch_row($r);
      
      global $BASEURL;
      return '<a href="//' . $BASEURL . '/forums.php?action=viewtopic&amp;topicid=' . $topicid . '&amp;page=p' . $key . '#pid' . $key . '">' . htmlspecialchars($topicname) . '#' . (1+$a[0]) . '楼</a>';
    }
    else {
      return '(无此帖子)';
    }
  }
}

