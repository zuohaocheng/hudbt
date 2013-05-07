<?php

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

class PmApplication extends Application {
  protected $users = array();

  public function onDisconnect($client) {
    unset($this->users[$client->userid]);
    var_dump($users);
  }
  
  public function onData($data, $client) {
    $client->log('123');

    $d = json_decode($data, true);

    
    if (isset($d['userid'])) {
      $this->users[$d['userid']] = $client;
      $client->userid = $d['userid'];
      var_dump($users);
    }
#    $client->send($data);
  }
}