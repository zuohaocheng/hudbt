<?php

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

class PmServerApplication extends Application {
  protected $pm;
  
  function __construct($pm) {
    $this->pm = $pm;
  }
  
  public function onData($data, $client) {
    $this->pm->onNewMessage($data->buffer);
  }

}