<?php
function wechat_text_response($content, $req) {
  global $Cache, $CURUSER;
  if (preg_match('/^pm (.*)$/i', $content, $matches)) {
    # Send pm to id=22

    if (isset($GLOBALS['CURUSER'])) {
      $sender = $CURUSER['id'];
    }
    else {
      $sender = 0;
    }

    # 10 msgs per hour for anonymous user
    if (!$sender) {
      $key = 'wechat_pm_quota_' . date('y-m-d-H');
      $quota = $Cache->get_value($key);
      if ($quota === false) {
	$quota = 9;
      }
      else {
	$quota -= 1;
      }
      $Cache->cache_value($key, $quota, 7200);
    }

    if ($sender || $quota > 0) {
      send_pm($sender, 22, '微信平台', $matches[1]);
      $response = '发送成功';
    }
    else {
      $response = '限额用完啦~请下个小时再来骚扰蝴蝶娘吧~';
    }
  }
  else if ($content == '热门') {
    $response = array_map(function($r) {
	return new NewsResponseItem($r['name'], $r['small_descr'], $r['poster'], $r['url']);
      }, $Cache->get_value('wechat_hot_ext', 1800, function() {
	  $r = [];
	  foreach (sql_query('select id, name, small_descr from torrents where (unix_timestamp(now()) - unix_timestamp(added)) < 7*86400 order by exp((unix_timestamp(added) - unix_timestamp(now())) / 86400/2) * log(times_completed + leechers + seeders * 2) desc limit 5') as $torrent) {
	    $poster = torrent_get_poster($torrent['id']);
	    if (!preg_match('/^http/i', $poster)) {
	      $poster = 'http://www.kmgtp.org/' . $poster;
	    }
	    $r[] = ['name' => $torrent['name'],
		    'small_descr' => $torrent['small_descr'],
		    'poster' => $poster,
		    'url' => 'https://www.kmgtp.org/details.php?id=' . $torrent['id']
		    ];
	  }
	  return $r;
	}));
  }
  else if (preg_match('/(?:流量|魔力)/i', $content)) {
    if (isset($CURUSER)) {
      $response = '上传: ' . mksize($CURUSER['uploaded']) . ', 下载: ' . mksize($CURUSER['downloaded']) . ', 魔力值: ' . $CURUSER['seedbonus'];

    }
    else {
      $response = '你还没有绑定账号呢，发送"bind 用户名"来绑定吧';
    }
  }
  /* else if (preg_match('/^搜索 (.*)$/i', $content, $matches)) { */
  
  /* } */
  else if (preg_match('/(?:bind|绑定) ([^ ]+)/i', $content, $matches)) {
    if (isset($CURUSER)) {
      $response = '你已经绑定了账号';
    }
    else {
      $userid = get_user_id_from_name(trim($matches[1]));
      if (!$userid && is_numeric($matches[1])) {
	$userid = 0 + $matches[1];
      }
      if (isset($userid)) {
	$r = sql_query('SELECT id, passhash, wechat FROM users WHERE id = ?', [$userid])->fetch();
      }
      else {
	$r = false;
      }
      
      if (!$r) {
	$response = '无效的用户id';
      }
      else if (!is_null($r['wechat'])) {
	$response = '已经绑定微信账号，请到 https://www.kmgtp.org/usercp.php?action=personal 解除绑定后再试';
      }
      else {
	$sec = mksecret();
	$oid = $req['fromusername'];
	$hash = md5($sec . $r['passhash'] . $oid . $sec);
	sql_query('INSERT INTO user_properties (user_id, wechat_bind_id, wechat_bind, wechat_bind_time) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE wechat_bind = VALUES(wechat_bind), wechat_bind_id = VALUES(wechat_bind_id), wechat_bind_time = NOW()', [$userid, $oid, $hash]);

	$url = 'https://www.kmgtp.org/oauth.php?hash=' . $hash;
	$response = '请点击网址确认 ' . $url;
      }
    }
  }
  else if ($content == 'unbind' || $content == '取消绑定') {
    $oid = $req['fromusername'];
    
    if (sql_query('UPDATE users SET wechat = NULL WHERE wechat = ?', [$oid])->rowCount()) {
      $response = '解除绑定成功';
      $oid = $req['fromusername'];
      $Cache->delete_value('wechat_userid_' . $oid);
    }
    else {
      $response = '你没有绑定蝴蝶账号耶~';
    }
  }
  else {
    $regexps = $Cache->get_value('wechat_autoreply', 86400 * 7, function() {
	return sql_query('SELECT `regexp`, `content` FROM wechat_replies')->fetchAll();
      });
    $oid = $req['fromusername'];
    $key = 'wechat_autoreply_other_' . $oid;
    $matched = false;

    foreach ($regexps as $pair) {
      if (preg_match($pair['regexp'], $content)) {
	$response = $pair['content'];
	$Cache->delete_value($key);
	$matched = true;
	break;
      }
    }

    if (!$matched) {
      if (!$Cache->get_value($key)) {
	$response = '咦? 蝴蝶娘不知道你在说什么呢~';
	$Cache->cache_value($key, true, 86400);
      }
      else {
	$response = null;
      }
    }
  }

  return $response;
}