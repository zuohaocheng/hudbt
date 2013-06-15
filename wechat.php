<?php
/**
 * 微信公众平台 PHP SDK 示例文件
 *
 * @author NetPuter <netputer@gmail.com>
 */

require('include/bittorrent.php');
require('lib/Wechat.php');

  /**
   * 微信公众平台演示类
   */
  class MyWechat extends Wechat {
    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
      $this->responseText('欢迎关注蝴蝶娘，喵~');
    }

    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe() {
      // 「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
      write_file_log('wechat', implode(' ', $this->getRequest()));
      $content = $this->getRequest('content');
      $this->login($this->getRequest('FromUserName'));
      $response = $this->textResponse($content);
      if (is_string($response)) {
	$this->responseText($response);
      }
      else {
	$this->responseNews($response);
      }
    }

    protected function login($oid) {
      global $Cache;
      $id = $Cache->get_value('wechat_userid_' . $oid, 86400, function() use ($oid) {
	  return get_single_value('users', 'id', 'WHERE wechat = ?' ,[$oid]);
	});
      if ($id) {
	$GLOBALS['CURUSER'] = get_user_row($id);
      }
    }
    
    protected function textResponse($content) {
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
	  $response('限额用完啦~请下个小时再来骚扰蝴蝶娘吧~');
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
	  $response = '你还没有绑定账号呢，发送"bind 用户数字id"来绑定吧';
	}
      }
      /* else if (preg_match('/^搜索 (.*)$/i', $content, $matches)) { */
	
      /* } */
      else if (preg_match('/(?:bind|绑定) (\d+)/i', $content, $matches)) {
	$userid = 0 + $matches[1];
	$r = sql_query('SELECT id, passhash FROM users WHERE id = ?', [$userid])->fetch();
	if (!$r) {
	  $response = '无效的用户id';
	}
	else {
	  $sec = mksecret();
	  $oid = $this->getRequest('FromUserName');
	  $hash = md5($sec . $r['passhash'] . $oid . $sec);
	  sql_query('INSERT INTO user_properties (user_id, wechat_bind_id, wechat_bind, wechat_bind_time) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE wechat_bind = VALUES(wechat_bind), wechat_bind_id = VALUES(wechat_bind_id), wechat_bind_time = NOW()', [$userid, $oid, $hash]);

	  $url = 'https://www.kmgtp.org/oauth.php?hash=' . $hash;
	  $response = '请点击网址确认 ' . $url;
	}
      }
      else if ($content == 'unbind' || $content == '取消绑定') {
	$oid = $this->getRequest('FromUserName');
	
	if (sql_query('UPDATE users SET wechat = NULL WHERE wechat = ?', [$oid])->rowCount()) {
	  $response = '解除绑定成功';
	  $oid = $this->getRequest('FromUserName');
	  $Cache->delete_value('wechat_userid_' . $oid);
	}
	else {
	  $response = '你没有绑定蝴蝶账号耶~';
	}
      }
      else if (preg_match('/(?:妹子)/i', $content)) {
	$response = '请出门左转乘坐591路公交车去武大';
      }
      else if (preg_match('/(?:hello|喵|萌|你好)/i', $content)) {
	$response = '喵~';
      }
      else if (preg_match('/(?:大爷|妹|擦|泥煤|妈)/i', $content)) {
	$response = '呵呵';
      }
      else if (preg_match('/(?:呵呵)/i', $content)) {
	$response = '蝴蝶娘去洗澡了';
      }
      else if (preg_match('/(?:么么|mua)/i', $content)) {
	$response = '....pia!';
      }
      else if (preg_match('/(?:免费)/i', $content)) {
	$response = '不要成天想些有的没的，哼';
      }
      else {
	$response = '咦? 蝴蝶娘不知道你在说什么呢~';
      }

      /* header('Content-type:text/plain'); */
      /* var_dump($response); */

      return $response;
    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage() {
#      $items = array(
#        new NewsResponseItem('标题一', '描述一', $this->getRequest('picurl'), $this->getRequest('picurl')),
#        new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),
#      );

#      $this->responseNews($items);
    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation() {
#      $num = 1 / 0;
      // 故意触发错误，用于演示调试功能

#      $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink() {
#      $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown() {
#      $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }

  }

  $wechat = new MyWechat($wechattoken, TRUE);
  $wechat->run();