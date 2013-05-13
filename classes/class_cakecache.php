<?php
//Caching class (Based on file From ProjectGazelle)
class Cache { // fake a Cache class for getting configs
  protected static $prefix = '';
  protected static $memcache = null;
  protected static $flag = false;
  protected static $duration = 3600;
  
  static function config($plan, $settings) {
    if (isset($settings['prefix'])) {
      self::$prefix = $settings['prefix'];
    }

    if (isset($settings['servers'])) {
      if (!self::$memcache) {
	self::$memcache = new Memcache();
      }

      foreach ($settings['servers'] as $server) {
	list($host, $port) = self::_parseServerString($server);
	self::$memcache->addServer($host, $port, $settings['persistent']);
      }
    }

    if ($settings['compress']) {
      self::$flag = MEMCACHE_COMPRESSED;
    }
  }

  static protected function _parseServerString($server) {
    if ($server[0] === 'u') {
      return array($server, 0);
    }
    if (substr($server, 0, 1) === '[') {
      $position = strpos($server, ']:');
      if ($position !== false) {
	$position++;
      }
    } else {
      $position = strpos($server, ':');
    }
    $port = 11211;
    $host = $server;
    if ($position !== false) {
      $host = substr($server, 0, $position);
      $port = substr($server, $position + 1);
    }
    return array($host, $port);
  }


  static function isInitialized() {
    return true;
  }

  static function set($k, $v) {
    $k = self::$prefix . $k;
    self::$duration = $v;
  }

  static function write($k, $v) {
    $k = self::$prefix . $k;
    return self::$memcache->set($k, $v, self::$flag, self::$duration);
  }

  static function read($k) {
    $k = self::$prefix . $k;
    return self::$memcache->get($k);
  }

  static function delete($k) {
    $k = self::$prefix . $k;
    return self::$memcache->delete($k);
  }
}

require('cake/app/Config/cache.php');
