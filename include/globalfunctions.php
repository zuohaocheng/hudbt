<?php
if(!defined('IN_TRACKER'))
die('Hacking attempt!');

function get_global_sp_state() {
	global $Cache;
	static $global_promotion_state;
	if (!$global_promotion_state){
		if (!$global_promotion_state = $Cache->get_value('global_promotion_state')){
			$res = mysql_query("SELECT * FROM torrents_state");
			$row = mysql_fetch_assoc($res);
			$global_promotion_state = $row["global_sp_state"];
			$Cache->cache_value('global_promotion_state', $global_promotion_state, 57226);
		}
	}
	return $global_promotion_state;
}

function get_pr_state($promotion, $added = '', $promotionTimeType = 0, $promotionUntil = '') {
  //PRCONFIG
  static $sp_global_table = array(array(1,2,3,4,5,6,7),
             array(2,2,4,4,2,4,2),
             array(3,4,3,4,6,6,3),
             array(4,4,4,4,4,4,4),
             array(5,2,6,4,6,6,7),
             array(6,4,6,4,6,6,6));

  $gstate = get_global_sp_state();
  if (!$gstate) {
    $gstate = 1;
  }
  $state = $sp_global_table[$gstate-1][$promotion-1];

  if ($state == 1 || $gstate != 1 || $promotionTimeType == 1) {
    $expire = NULL;
  }
  else if ( $promotionTimeType == 2) {
    $expire = strtotime( $promotionUntil);
  } 
  else {
    global $expire_limits;
    $expire_day = $expire_limits[$state];
    if ($expire_day != 0) {
      $expire = strtotime($added) + $expire_limits[$state] * 86400;
      if ($expire < TIMENOW && $added != '') {
  $state = 1;
  $expire = NULL;
      }
    }
    else {
      $expire = NULL;
    }
  }

  return array($state, $expire);
}

function get_maxslots($downloaded, $ratio) {
  if ($downloaded > 10) {
    if ($ratio < 0.5) $max = 1;
    elseif ($ratio < 0.65) $max = 2;
    elseif ($ratio < 0.8) $max = 3;
    elseif ($ratio < 0.95) $max = 4;
    else $max = 0;
  }
  return $max;
}

// IP Validation
function validip($ip)
{
	if (!ip2long($ip)) //IPv6
		return true;
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		// reserved IANA IPv4 addresses
		// http://www.iana.org/assignments/ipv4-address-space
		$reserved_ips = array (
		array('192.0.2.0','192.0.2.255'),
		array('192.168.0.0','192.168.255.255'),
		array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

function getip() {
  if (php_sapi_name() == 'cli') {
    return '::1';
  }
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}
	}

	return $ip;
}

function sql_query($query)
{
	global $query_name;
	$query_name[] = $query;
	return mysql_query($query);
}

function sqlesc($value) {
  $value = "'" . mysql_real_escape_string($value) . "'";
  return $value;
}

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function hash_where($name, $hash) {
  $hash = stripslashes($hash);
	$shhash = preg_replace('/ *$/s', "", $hash);
	return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

// Added by BruceWOlf 2010-12-15
function hash_where2($name, $hash) {
	$shhash = preg_replace('/ *$/s', "", $hash);
	$shhash = '0x'.bin2hex(stripslashes($shhash));
	$hash = '0x'.bin2hex(stripslashes($hash));
	return "($name = " . $hash . " OR $name = " . $shhash . ")";
}


