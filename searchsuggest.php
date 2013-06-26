<?php
require "include/bittorrent.php";
dbconn();
if (isset($_GET['q']) && $_GET['q'] != '')
{
	$searchstr = trim($_GET['q']);
	
	$suggest_query = sql_query("SELECT keywords AS suggest, COUNT(*) AS count FROM suggest WHERE keywords LIKE " . sqlesc($searchstr . "%")." AND LENGTH(keywords) < 25 GROUP BY keywords ORDER BY count DESC, keywords DESC LIMIT 5");
	$result = array($searchstr, array(), array());
	while($suggest = _mysql_fetch_array($suggest_query)){
		$result[1][] = $suggest['suggest'];
		$result[2][] = $suggest['count']." times";
	}
	header('Content-type: application/json');
	echo json_encode($result);
}

