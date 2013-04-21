<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
function logmenu($selected = "dailylog"){
		global $lang_log;
		global $showfunbox_main;
		begin_main_frame();
		print ("<div id=\"lognav\"><ul id=\"logmenu\" class=\"menu\">");
		print ("<li" . ($selected == "dailylog" ? " class=selected" : "") . "><a href=\"?action=dailylog\">".$lang_log['text_daily_log']."</a></li>");
		print ("</ul></div>");
		end_main_frame();
}

function searchtable($title, $action, $opts = array()){
		global $lang_log;
		
}



$action = isset($_POST['action']) ? htmlspecialchars($_POST['action']) : (isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '');
$allowed_actions = array("dailylog");
if (!$action)
	$action='dailylog';
if (!in_array($action, $allowed_actions))
stderr($lang_log['std_error'], $lang_log['std_invalid_action']);
else {
	switch ($action){
	case "dailylog":
		stdhead($lang_log['head_site_log']);

		$query_raw = trim($_GET["query"]);
		$query = '%' . $query_raw . '%';
		$search = $_GET["search"];

		$addparam = "";
		$wherea = "";
		if (get_user_class() >= $confilog_class){
			switch ($search)
			{
				case "mod": $wherea=" WHERE security_level = 'mod'"; break;
				case "normal": $wherea=" WHERE security_level = 'normal'"; break;
				case "all": break;
			}
			$addparam = ($wherea ? "search=".rawurlencode($search)."&" : "");
		}
		else{
			$wherea=" WHERE security_level = 'normal'";
		}

		if($query){
				$wherea .= ($wherea ? " AND " : " WHERE ")." txt LIKE '?' ";
				$addparam .= "query=".rawurlencode($query_raw)."&";
		}

		logmenu('dailylog');
		$opt = array (all => $lang_log['text_all'], normal => $lang_log['text_normal'], mod => $lang_log['text_mod']);
		searchtable($lang_log['text_search_log'], 'dailylog',$opt);
       
		//die();
		$res = sql_query("SELECT COUNT(*) FROM sitelog".$wherea);
		$row = _mysql_fetch_array($res);
		$count = $row[0];

		$perpage = 50;

		list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "deletelog.php?action=dailylog&".$addparam);

		$res = sql_query("SELECT added, txt FROM sitelog $wherea ORDER BY added DESC $limit", [$query]);
		if (_mysql_num_rows($res) == 0)
		print($lang_log['text_log_empty']);
		else
		{

		//echo $pagertop;

			print("<table width=940 border=1 cellspacing=0 cellpadding=5>\n");
			print("<tr><td class=colhead align=center><img class=\"time\" src=\"pic/trans.gif\" alt=\"time\" title=\"".$lang_log['title_time_added']."\" /></td><td class=colhead align=left>".$lang_log['col_event']."</td></tr>\n");
			while ($arr = _mysql_fetch_assoc($res))
			{
				$color = "";
				
				if (strpos($arr['txt'],'was deleted by')){
					$color = "red";
					print("<tr><td class=\"rowfollow nowrap\" align=center>".gettime($arr['added'],true,false)."</td><td class=rowfollow align=left><font color='".$color."'>".htmlspecialchars($arr['txt'])."</font></td></tr>\n");
				}
			}
			print("</table>");
	
			echo $pagerbottom;
		}

		print($lang_log['time_zone_note']);

		stdfoot();
		die;
		break;
	}
}


