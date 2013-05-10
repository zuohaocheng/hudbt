<?php
class BBCodePreparser {
  function __construct($text) {
    $o  = '[';
    $c  = ']';
    $this->text = $text;
    global $possibleUrls;
    $regpre = '!(^|[^="])https?://(?:' . implode('|', $possibleUrls) . ')/';
    foreach (['user' => 'userdetails.php\?id=([0-9]+)[&;=%0-9a-z#]*',
	      'torrent' => 'details.php\?id=([0-9]+)[&;=%0-9a-z#]*',
	      'post' => 'forums.php\?[&;=%a-z0-9]*?page=p([0-9]+)[&;=%0-9a-z#]*',
	      'topic' => 'forums.php\?[&;=%a-z0-9]*?topicid=([0-9]+)[&;=%0-9a-z#]*'] as $k=>$p) {
      $regex = $regpre . $p . '!i';
      $this->text =
	preg_replace_callback($regex, function($keys) use ($k, $o, $c) {
	    return $keys[1] . $o . $k . '=' . $keys[2] . $c;
	  }, $this->text);
    }

    
    $this->text = preg_replace_callback('!\[name=(\w+)\]!i', function($keys) {
	$id = get_user_id_from_name($keys[1]);
	if ($id === false) {
	  $id = 0;
	}
	return '[user=' . $id . ']';
      }, $this->text);

    $t = preg_replace('/\[quote(=[a-z0-9\'"\[\]=]+)?\].*\[\/quote\]/smi', '\1', $this->text);
    preg_match_all('!\[user=([0-9]+)\]!i', $t, $ids, PREG_SET_ORDER);

    $this->ids = array_unique(array_map(function($o) {
	  return 0 + $o[1];
	}, $ids));

    if (count($this->ids) > 10) {
      $this->ids = [];
      $this->text .= "\n\n[hr/]\n不要太丧失了，一次最多@十个噢\n    by 蝴蝶娘";
    }
  }

  function getText() {
    return $this->text;
  }

  function setLink($link) {
    global $CURUSER;
    
    foreach ($this->ids as $id) {
      if (get_user_row($id) && $id != $CURUSER['id']) {
	send_pm($CURUSER['id'], $id, '我在帖子中提到了你', '快去看看吧~' . $link);
      }
    }
  }
}
