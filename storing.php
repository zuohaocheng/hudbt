<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

$action = $_GET['action'];
$torrentid = 0 + $_GET['torrentid'];
$userid = 0 + $_GET['userid'];

if(!in_array($action,["checkin","checkout"]) || $torrentid == 0 || $userid == 0){
	permissiondenied();	
}
if(!permissionAuth("storing",$CURUSER['usergroups'],$CURUSER['class'])){
	permissiondenied();	
}
else{
	if($action=="checkin"){
		$check_storing_res = sql_query("SELECT storing FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
		if(mysql_num_rows($check_storing_res)==0){
			header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
			stderr($lang_storing['head_storing_error'],$lang_storing['text_no_such_torrent']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);					
		}
		
		$check_storing = mysql_fetch_assoc($check_storing_res);
		if($check_storing['storing']===0){
			header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
			stderr($lang_storing['head_storing_error'],$lang_storing['text_not_storing_tagged']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);
		}
		
		$snatched_res = sql_query("SELECT seedtime,to_go FROM snatched WHERE torrentid = $torrentid AND userid = $userid") or sqlerr(__FILE__,__LINE__);
		if(mysql_num_rows($snatched_res)==0){
			header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
			stderr($lang_storing['head_storing_error'],$lang_storing['text_not_even_snatched']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);					
		}

		$snatched = mysql_fetch_assoc($snatched_res);
		if($snatched['to_go']!=0){
			header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
			stderr($lang_storing['head_storing_error'],$lang_storing['text_not_completed']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);	
		}
		elseif($snatched['to_go']==0){
			$exist_res = sql_query("SELECT in_seedtime FROM storing_records WHERE keeper_id = $userid AND torrent_id = $torrentid AND checkout = 0") or sqlerr(__FILE__,__LINE__);
			if(mysql_num_rows($exist_res) > 0){
				header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
				stderr($lang_storing['head_storing_error'],$lang_storing['text_already_check_in']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);					
			}
			else{
				sql_query("INSERT INTO storing_records (keeper_id,torrent_id,in_date,in_seedtime,checkout) VALUES ($userid,$torrentid,NOW(),$snatched[seedtime],0)") or sqlerr(__FILE__,__LINE__);
				if(mysql_affected_rows()){
					header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
				  stdhead();
					stdmsg($lang_storing['head_check_in_success'],$lang_storing['text_check_in_success']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);
					stdfoot();
				}
			}
		}
	}
	elseif($action==checkout){
		$exist_res = sql_query("SELECT in_seedtime FROM storing_records WHERE torrent_id = $torrentid AND keeper_id = $userid AND checkout = 0") or sqlerr(__FILE__,__LINE__);
		if(mysql_num_rows($exist_res)==0){
			header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
			stderr($lang_storing['head_storing_error'],$lang_storing['text_not_check_in']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);					
		}
		else{
			$out_seedtime_res = sql_query("SELECT seedtime FROM snatched WHERE torrentid = $torrentid AND userid = $userid") or sqlerr(__FILE__,__LINE__);
			if(mysql_num_rows($out_seedtime_res)==0){
				header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
				stderr($lang_storing['head_storing_error'],$lang_storing['text_registered_not_snatched']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);					
			}
			$out_seedtime = mysql_fetch_assoc($out_seedtime_res);
			sql_query("UPDATE storing_records SET out_date = NOW(), out_seedtime = $out_seedtime[seedtime], checkout = 1 WHERE torrent_id = $torrentid AND keeper_id = $userid AND checkout = 0 ") or sqlerr(__FILE__,__LINE__);
			if(mysql_affected_rows()){
				header("Refresh: 5; url=//$BASEURL/details.php?id=$torrentid");
				stdhead();
				stdmsg($lang_storing['head_check_out_success'],$lang_storing['txt_check_out_success']."<a href=\"//$BASEURL/details.php?id=$torrentid\">".$lang_storing['text_auto_return']."</a>",false);
				stdfoot();
			}		
		}
	}
}
?>