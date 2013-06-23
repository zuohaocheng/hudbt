<?php
require('include/bittorrent.php');
require('include/wechat.php');
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
      $this->responseText('欢迎关注蝴蝶娘，喵~人家现在只认识两个字呢，"热门"和"魔力值"，呜呜呜');
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
      $response = wechat_text_response($content, $this->getRequest());
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