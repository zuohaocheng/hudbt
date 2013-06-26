<?php

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

require(__DIR__ . '/../include/bittorrent.php');

$pdo->query("SET wait_timeout=7200");

class PmApplication extends Application {
  protected $users = array();

  public function onConnect($client) {
    global $pdo;
      
    try {
      $pdo->query('SELECT 1');
    } catch (PDOException $e) {
      try {
	$config = new DATABASE_CONFIG();
	$default = $config->default;
	$dsn = 'mysql:dbname=' . $default['database'] . ';host=' . $default['host'];
	$pdo = new PDO($dsn, $default['login'], $default['password']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
	die('dbconn: mysql_pconnect: ' . $e->getMessage());
      }
      echo "Reconnected\n";
    }


    if (isset($client->cookie)) {
      try {
	$row = userlogin_core($this->parseCookie($client->cookie));
      } catch (Exception $e) {
	$row = null;
      }
    }
    else {
      $row = null;
    }
    
    if (!is_null($row)) {
      $this->users[$row['id']] = $client;
      $client->userid = $row['id'];
    }
    echo date('H:i:s '), ' connected ';
    if (isset($client->userid)) {
      echo $client->userid;
    }
    echo "\n";
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
      if (isset($ps[1])) {
	$cookie[trim($ps[0])] = rawurldecode($ps[1]);
      }
    }
    return $cookie;
  }
}