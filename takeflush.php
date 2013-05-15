<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
function bark($msg)
{
   stdhead();
   stdmsg($lang_takeflush['std_failed'], $msg);
   stdfoot();
   exit;
}

$id = 0 + $_GET['id'];
int_check($id,true);

if (get_user_class() >= UC_MODERATOR || $CURUSER['id'] == $id)
{  
   $deadtime = deadtime();
   $effected = sql_query("DELETE FROM peers WHERE userid=?",[$id])->rowCount();
   $Cache->delete_value('user_'.$CURUSER["id"].'_active_seed_count');
   $Cache->delete_value('user_'.$CURUSER["id"].'_active_leech_count');
   
   stderr($lang_takeflush['std_success'], "$effected ".$lang_takeflush['std_ghost_torrents_cleaned']);
}
else
{
   bark($lang_takeflush['std_cannot_flush_others']);
}
