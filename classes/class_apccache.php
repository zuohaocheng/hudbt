<?php
class APECache {
  var $keyPre='ANTSOUL';	
  function pconnect($host, $port){
    if(extension_loaded('apc'))
      return true;
    else die("Fatal error: Class 'Apc' not found in class_cache.php on line 11");
  }
  
  function connect($host, $port){
    if(extension_loaded('apc'))
      return true;
    else die("Fatal error: Class 'Apc' not found in class_cache.php on line 11");
  }
  
  function _feaKey($key) {
    return md5($this->keyPre . $key);
  } 
  
  function delete($Key){
    if (php_sapi_name() == 'cli') {
      global $BASEURL;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://' . $BASEURL . '/clearcache.php?format=json');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'org.kmgtp.cli_api');
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'cachename=' . urlencode($Key));
      $data = json_decode(curl_exec($ch));
      curl_close($ch);
      return $data->success;
    }
    else {
      return apc_delete($this->_feaKey($Key));
    }
  }
  
  function set($Key,$Value,$What,$Duration){
    apc_store($this->_feaKey($Key),$Value,$Duration);
  }
  
  function get($Key){
    return apc_fetch($this->_feaKey($Key));
  }
  
}