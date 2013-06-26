<?php
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');
define('TIMENOWSTART',microtime(1));
include_once($rootpath . 'include/config.php');
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('TIMENOW', time());
$USERUPDATESET = array();
$query_name=array();

$NEXUSPHP = true;

define ("UC_PEASANT", 0);
define ("UC_USER", 1);
define ("UC_POWER_USER", 2);
define ("UC_ELITE_USER", 3);
define ("UC_CRAZY_USER", 4);
define ("UC_INSANE_USER", 5);
define ("UC_VETERAN_USER", 6);
define ("UC_EXTREME_USER", 7);
define ("UC_ULTIMATE_USER", 8);
define ("UC_NEXUS_MASTER", 9);
define ("UC_VIP", 10);
define ("UC_RETIREE",11);
define ("UC_UPLOADER",12);
define ("UC_FORUM_MODERATOR", UC_UPLOADER); # An alias for uploaders
define ("UC_MODERATOR",13);
define ("UC_ADMINISTRATOR",14);
define ("UC_SYSOP",15);
define ("UC_STAFFLEADER",16);
ignore_user_abort(1);
@set_time_limit(60);

function get_langfolder_list() {
	//do not access db for speed up, or for flexibility
	return array("en", "chs", "cht", "ko", "ja");
}

if (defined('ANNOUNCE') && extension_loaded('memcache')) {
  // NOTE: CakePHP can't be used in announce because of performance
  // Use Cake if memcache is not loaded, because class_cache.php needs it
  require('cake/app/Config/database.php');
  try {
    $config = new DATABASE_CONFIG();
    $default = $config->default;
    $dsn = 'mysql:dbname=' . $default['database'] . ';host=' . $default['host'];
    $pdo = new PDO($dsn, $default['login'], $default['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die('dbconn: mysql_pconnect: ' . $e->getMessage());
  }
  //Require the fake caching class of cake
  require_once($rootpath . 'classes/class_cakecache.php'); 
}
else {
  if (!defined('HB_CAKE')) {
    require_once('./cake/app/webroot/index.php');
  }
  App::uses('ConnectionManager', 'Model');
  $datasource = ConnectionManager::getDataSource('default');
  $pdo = $datasource->getConnection();
}
//Require the caching class
require_once($rootpath . 'classes/class_cache.php'); 
$Cache = new NPCache(); //Load the caching class
$Cache->setLanguageFolderArray(get_langfolder_list());


// Set this will cause named parameters can't be use
//$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$lastStmt;

function _mysql_query($q, $args=[]) {
  global $pdo, $lastStmt;
  try {
    if ($args) {
      $lastStmt = $pdo->prepare($q);
      $lastStmt->execute($args);
    }
    else {
      $lastStmt = $pdo->query($q);
    }
  }
  catch (PDOException $e) {
    if ($e->errorInfo[1] != 2006 && // 2006: MySQL server has gone away
	$e->errorInfo[1] != 1062) { // 1062: Duplicate entry
      ob_start();
      echo date('d M Y H:i:s'), "\n";
      var_dump($q);
      var_dump($args);
      var_dump($e);
      var_dump($_SERVER);
      debug_print_backtrace();
      $msg = ob_get_clean() . "\n\n---------------------------------\n\n";
      
      $h = fopen('log/sqlerr.log', 'a');
      if ($h) {
	fwrite($h, $msg);
	fclose($h);
      }
    }

    if (function_exists('sqlerr') && (php_sapi_name() !== 'cli')) {
      sqlerr(__FILE__, __LINE__, true, $q, $args, $e);
    }
    else if (function_exists('err')) {
      err('Tracker error');
    }
  }
  return $lastStmt;
}

function sql_fetchAll($q, $args) {
  $stmt = sql_query($q, $args);
  return $stmt->fetchAll();
}

function _mysql_insert_id() {
  global $pdo;
  return $pdo->lastInsertId();
}

function _mysql_error() {
  global $pdo;
  return implode(', ', $pdo->errorInfo());
}

function _mysql_affected_rows() {
  global $lastStmt;
  return $lastStmt->rowCount();
}

function _mysql_num_rows($stmt) {
  return $stmt->rowCount();
}

function _mysql_fetch_row($stmt) {
  return $stmt->fetch(PDO::FETCH_NUM);
}

function _mysql_fetch_assoc($stmt) {
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function _mysql_fetch_array($stmt) {
  return $stmt->fetch();
}
