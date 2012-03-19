<?php
class Memcache{   
    private $lifetime = 3600;
    private $path = 'cache';
	
    function set($name,$value,$time=0){
	    if($time) $this->lifetime = $time;
        $filename = $this->path.'/'.md5($name).'.php';
        @unlink($filename);
        $valuecache['cache'] = $value;
		$valuecache['cachetimeuntil']=time()+$this->lifetime;
        $array = "<?php\n\$filecache['".$name."']=".var_export($valuecache, true).";\n?>";
        $strlen = file_put_contents($filename, $array);
        @chmod($filename, 0777);
        return $strlen;
    } 

    function get($name){
			$filename = $this->path.'/'.md5($name).'.php';
            include_once $filename;
			if($filecache[$name]['cachetimeuntil']>time())
            return $filecache[$name]['cache'];
			else
			return false;
   } 
	
    function delete($name){
        $filename = $this->path.'/'.md5($name).'.php';
        @unlink($filename);
	} 
 	
    function connect(){
        return true;
    }

    function pconnect() {
      return true;
    }
}
