<?php

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

require(__DIR__ . '/../include/bittorrent.php');

class PmApplication extends Application {
  protected $users = array();

  public function onConnect($client) {
    $cookie = $client->cookie;
    try {
      $row = userlogin_core($this->parseCookie($cookie));
    } catch (Exception $e) {
      $row = null;
    }
    if (!is_null($row)) {
      $this->users[$row['id']] = $client;
      $client->userid = $row['id'];
    }
  }
  
  public function onDisconnect($client) {
    if (isset($client->userid)) {
      unset($this->users[$client->userid]);
    }
  }
  
  public function onData($data, $client) {
    $d = json_decode($data, true);
    
    if (isset($d['connect'])) {
      $client->send(json_encode(['connected'=> true]));
    }
  }

  public function onNewMessage($userid) {
    if (isset($this->users[$userid])) {
      echo date('H:i:s '), $userid, "\n";
      $this->users[$userid]->send(json_encode(['new' => true]));
    }
  }

  protected function parseCookie($cookieString) {
    $pieces = explode(';', $cookieString);
    $cookie = [];
    foreach ($pieces as $p) {
      $ps = explode('=', $p);
      $cookie[trim($ps[0])] = rawurldecode($ps[1]);
    }
    return $cookie;
  }
}