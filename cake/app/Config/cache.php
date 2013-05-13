<?php

// Setup a 'default' cache configuration for use in the application.
//Cache::config('default', array('engine' => 'File'));
Cache::config('default', array(
 		'engine' => 'Memcache', //[required]
 		'duration'=> 3600, //[optional]
 		'probability'=> 100, //[optional]
  		'prefix' => 'hb_', //[optional]  prefix every cache file with this string
  		'servers' => array(
  			'127.0.0.1:11211' // localhost, default port 11211
  		), //[optional]
  		'persistent' => true, // [optional] set this to false for non-persistent connections
  		'compress' => false, // [optional] compress data in Memcache (slower, but uses less memory)
 	));