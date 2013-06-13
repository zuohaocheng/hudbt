<?php
require "include/bittorrent.php";
dbconn();

loggedinorreturn();
parked();

if (isset($_REQUEST['action'])) {
  $action = htmlspecialchars($_REQUEST['action']);
}
else {
  if($_GET['id']) {
    $action='view';
  }
  else {
    $action='list';
  }
}

$allowed_actions = array("list","new","newmessage","view","edit","takeedit","takeadded","res","takeres","addamount","delete","modifyres","message","search");
if (!in_array($action, $allowed_actions)) {
  header('HTTP/1.1 403 Forbidden');
  stderr("错误", "对象不存在");
}
else {
	switch ($action){
	case "list":
	{
	$finished = isset($_GET['finished']) ? $_GET['finished'] : $_POST['finished'];
	$finishedlimit = isset($_GET['finished']) ? "finished=".$_GET['finished']."&" : '';

	$allowed_finished = array("yes","no","all","ing","my");
	switch ($finished){
	case "yes":{$limit ="finish = 'yes'";break;}
	case "no":{$limit ="finish = 'no'";break;}
	case "all":{$limit ="1";break;}
	case "my":{$limit ="1 and userid=".$CURUSER["id"];break;}
	case "ing":{$limit ="(SELECT count(DISTINCT torrentid) FROM resreq  where reqid=requests.id )>=1 and finish = 'no'";break;}
	default:{$limit ="finish = 'no'";break;}
	}
	//if (!in_array($finished, $allowed_finished)){$limit = "finish = 'no'";(get_user_class() >= 13?$limitorder="Totalreq DESC ,":"");}
	//else $limit = ( $finished=="all" ? "1" : ( $finished=="all" ? "1" : "finish ='".$finished."'"));
	
	
	if(isset($_REQUEST['query'])) {
	  $querystr = $_REQUEST['query'];
	  $limit=$limit." and (request like ".sqlesc("%" . $querystr . "%")." or descr like ".sqlesc("%" . $querystr . "%").")";
	}
	
	$rows=sql_query("SELECT  requests.*  FROM requests WHERE ".$limit." ORDER BY id DESC") or sqlerr(__FILE__, __LINE__);
	list($pagertop, $pagerbottom, $limit2) = pager(20, _mysql_num_rows($rows), "?$finishedlimit");
	//if (_mysql_num_rows($rows) == 0) stderr( "没有求种" , "没有符合条件的求种项目，<a href=viewrequests.php?action=new>点击这里增加新求种</a>",0);
	//else 
	{
	stdhead("求种区");

$rows=sql_query("SELECT requests.* ,(SELECT count(DISTINCT torrentid) FROM resreq  where reqid=requests.id ) as Totalreq FROM requests WHERE ".$limit." ORDER BY id DESC $limit2") or sqlerr(__FILE__, __LINE__);	
	print("<h1 align=center>求种区</h1>");
	echo '<div class="minor-list list-seperator minor-nav"><ul><li><a href="?action=new">添加</a></li><li><a href="?finished=all">查看所有</a></li><li><a href="?finished=yes">查看已解决</a></li><li><a href="?finished=no">查看未解决</a></li><li><a href="?finished=ing">查看解决中</a></li><li><a href="?finished=my">查看我发布的</a></li><li><form method="get" action="?"><input type="search" name="query"';
	if (isset($querystr)) {
	  echo ' value="', $querystr, '"';
	}
	echo '/><input type="hidden" name="action" value="list" /><input type="hidden" name="finished" value="all" /><input type="submit" value="搜索"></form></li></ul></div>';
	
	if (_mysql_num_rows($rows) == 0){
	  print('<h3 class="center">森马都没有找到</h3>');
	}
	else {
	  print("<table style=\"width:95%;\" cellspacing=\"0\" cellpadding=\"5\" class=\"no-vertical-line\"><thead>\n");

	print("<tr class=\"center\"><th>名称</th><th>最新出价</th><th>原始出价</th><th>评论数</th><th>应求数</th><th>求种者</th><th>时间</th><th>状态</th></tr></thead><tbody>\n");
	while($row = _mysql_fetch_array( $rows )) {
	print("<tr>
	<td align=left class='rowfollow'><a href=viewrequests.php?action=view&id=".$row["id"]."><b>".$row["request"]."</b></a></td>
	<td align=center class='rowfollow nowrap'><font color=#ff0000><b>".$row['amount']."</b></font></td>
	<td align=center class='rowfollow nowrap'>".$row['ori_amount']."</td>
	<td align=center class='rowfollow nowrap'>".($row['comments'])."</td><td align=center>".($row['Totalreq'])."</td>
	<td align=center class='rowfollow nowrap'>".get_username($row['userid'])."</td>
	<td align=center class='rowfollow nowrap'>".gettime($row['added'],true,false)."</td>
	<td align=center class='rowfollow nowrap'>".($row['finish']=="yes"?"求种成功":($row['userid']== $CURUSER['id']?"求种中":"<a href=viewrequests.php?action=res&id=".$row["id"]." >求种中</a>"))."</td></tr>\n");
	}
	print("</tbody></table>\n");
	print($pagerbottom);
	}

	stdfoot();	
	}
	die;
	break;
	}
	
	case "view":
	{	
	if(is_numeric($_GET["id"])){
	$id = $_GET["id"];
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr( "错误" , "ID不存在");
	else $arr = _mysql_fetch_assoc($res);
	stdhead("求种区");
	print("<h1 align=center id=top>求种-".htmlspecialchars($arr["request"])."</h1>\n");
	print("<table width=940 cellspacing=0 cellpadding=5>\n");
	$res = sql_query("SELECT * FROM resreq WHERE reqid ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
	tr("基本信息", get_username($arr['userid'])."发表于".gettime($arr["added"],true,false)."\n", 1);
	tr("悬赏", "最新竞价为".$arr['amount']."     原始竞价为".$arr["ori_amount"]."\n", 1);
	tr("操作", "<div class=\"minor-list list-seperator\"><ul><li><a href=report.php?reportrequestid=".$id." >举报</a></li>".
	(($arr['userid']== $CURUSER['id'] || get_user_class() >= 13)&&$arr["finish"]=="no" ?"<li><a href=viewrequests.php?action=edit&id=".$id." >编辑</a></li>":"")."\n".
	($arr['userid']== $CURUSER['id']||$arr["finish"]=="yes"?"":"<li><a href=viewrequests.php?action=res&id=".$id." >应求</a></li>\n").
	   (checkPrivilege(['Requests', 'delete'], $id) ?"<li><a href=viewrequests.php?action=delete&id=".$id." ".(_mysql_num_rows($res)?">删除":"title='回收返还80%魔力值'>回收")."</a></li>":"")."</ul></div>\n"
	,1);
	if($arr["finish"]=="no")tr("追加悬赏","<form action=viewrequests.php method=post> <input type=hidden name=action value=addamount><input type=hidden name=reqid value=".$arr["id"]."><input size=6 name=amount value=1000 ><input type=submit value=提交 > 追加悬赏每次将扣减25个魔力值作为手续费</form>",1);
	tr("介绍",format_comment(unesc($arr["descr"])),1);
	$limit = ($arr['finish']=="no"?"":" AND chosen = 'yes' ");
	$ress="";
	if (_mysql_num_rows($res) == 0) $ress="还没有应求";
	else {
	  $canModify = (($arr['userid']== $CURUSER['id'] || get_user_class() >= 13)&&$arr['finish']=="no");
	  $canDelete = false;

	  while($row = _mysql_fetch_array($res)) {
	    $each = _mysql_fetch_assoc(sql_query("SELECT id, name, owner FROM torrents WHERE id = '".$row["torrentid"]."'"));
	    if ($each){
	      $delete = checkPrivilege(['Requests', 'deleteres'], [$arr['finish']=='no', $row['torrentid']]);
	      $canDelete = $canDelete || $delete;
	      if ($canModify || $delete) {
		$ress.="<input type=checkbox name=torrentid[] value=".$row["torrentid"].">";
	      }
	      $ress .= "<a href=details.php?id=".$each["id"]."&hit=1 >".$each["name"]."</a> by ".get_username($each['owner'])."<br/>";
	    }
	  }
		
	  if ($canModify) {
	    $ress.="<input type='submit' name='confirm' value='使用勾选的资源作为所需资源'>\n";
	  }
	  if ($canDelete) {
	    $ress.="<input type='submit' name='delete' value='删除所选应求'>\n";
	  }

	  if ($canModify || $canDelete) {
	    $ress = "<form action=viewrequests.php method=post>\n<input type=hidden name=action value=modifyres > <input type=hidden name=id value=".$id." >\n" . $ress . '</form>';
	  }
	}

	tr("应求",$ress,1);
	print("</table><br/><br/>\n");
	

	
	
	
	$count = get_row_count("comments","WHERE request=".sqlesc($_GET["id"]));
	if ($count)
	{
		print("<br /><br />");
		print("<h1 align=\"center\" id=\"startcomments\">评论</h1>\n");
		list($pagertop, $pagerbottom, $limit) = pager(10, $count, "viewrequests.php?action=view&id=".$_GET["id"]."&", array('lastpagedefault' => 1), "page");

		$subres = sql_query("SELECT * FROM comments WHERE request=".sqlesc($_GET["id"])." ORDER BY id $limit") or sqlerr(__FILE__, __LINE__);
		
		$allrows = array();
		while ($subrow = _mysql_fetch_array($subres)) {
			$allrows[] = $subrow;
		}
		print($pagertop);
		commenttable($allrows, 'request',$_GET["id"]);
		print($pagerbottom);
	}
	
	
	
	print ("<div id=\"forum-reply-post\" class=\"table td\"><h2><a class=\"index\" href=comment.php?action=add&pid=$id&type=request>添加评论</a></h2>
	<form id=\"compose\" name=\"comment\" method=\"post\" action=\"".htmlspecialchars("comment.php?action=add&type=request")."\" onsubmit=\"return postvalid(this);\">
	<input type=\"hidden\" name=\"pid\" value=\"".$id."\" /><br />");
quickreply('comment', 'body', "添加");
print("</form></div>");

	stdfoot();
	
	}
	else stderr("出错了！！！", "ID不存在");
	die;
	break;
	}
	
	case "edit":
	{
	if(!is_numeric($_GET["id"]))stderr("出错了！！！","求种ID必须为数字");
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！");
	$arr = _mysql_fetch_assoc($res);
	if ($arr["finish"]=="yes") stderr("出错了！","该求种已完成！");
	if($arr['userid']== $CURUSER['id'] || get_user_class() >= 13)
		{	
		stdhead("编辑求种");
		print(
		"<form id=compose method=post name=edit action=viewrequests.php >\n
		<input type=hidden name=action  value=takeedit >
		<input type=hidden name=reqid  value=".$_GET["id"]." >
		");
		begin_compose('', 'edit', $arr["descr"], true, $arr["request"]);
		end_compose();
		echo '</form>';

		stdfoot();
		die;
		}
	else stderr("出错了！！！","你没有该权限！！！<a href=viewrequests.php?action=view&id=".$_GET["id"].">点击这里返回</a>",0);
	}
	
	case "new":
	{
	if(get_user_class() >= 2)
		{	
		stdhead("新增求种");
		echo '<h1>新增求种</h1>';
		echo '<h3>规则：</h3><ul><li>求种资源必须为本站允许发布资源，发布规则参照<a href="forums.php?action=viewtopic&amp;topicid=5211">蝴蝶-HUDBT 发种规则</a> <br></li><li>标题必需包括所求种子名字，严禁出现跪求之类的字眼 <br></li><li>求种介绍  <div>　　必须包含：中文名，外文电影必需要有其外文名字，IMDB链接(如果有必须加)  <br>　　可选信息：内容简介，越详细越好，不得模糊求种，例如求某人的XX </div></li><li>所求资源的格式或者清晰度等等有要求的请写清楚，以免出现所求到的资源不是自己想要的，造成不必要的麻烦 <br></li><li>严禁内容只出现RT这些字眼，没有简介的一律回收 <br></li><li>供种人所发种子也必需遵守<a href="forums.php?action=viewtopic&amp;topicid=5211">蝴蝶-HUDBT 发种规则</a>，违者按照相关规则处理 <br></li><li>希望求种者对已求到的种做好保种措施，毕竟这个资源可能都是些冷门资源不太好找，如果求种人不做种了也许就会断种了，还是希望能保种以便有相同需要的人</li><li>求种前请先搜索本站是否有你想要的东西，若已有种子还求种的一律警告一周处理！除非你自己有说明情况那就另当别论</li><li>对违反求种规范的帖子一律删帖处理，再犯相同错误者将警告一周处理</li></ul>';
		print(
		"<form id=compose method=post name=edit action=viewrequests.php >\n<input type=hidden name=action  value=takeadded >\n");
		begin_compose();
		tr("悬赏：","<input name=amount size=11 value=2000>赏金不得低于100魔力值，每次求种将扣去100魔力值作为手续费。<br/>", 1);
		end_compose();
		echo '</form>';
		
		stdfoot();
		die;
		}
	else stderr("出错了！！！","你没有该权限！！！<a href=viewrequests.php>点击这里返回</a>",0);
	}
	
		case "newmessage":
	{
	
		{	
		stdhead("回复");



	//<input type=hidden name=id value=$id ><br />");
//quickreply('reply', 'message', "我要留言");
//print("</form></td></tr></table>");

$ruserid=0+$_GET["userid"];

		
		print(
		"<form id=reply name=reply method=post action=viewrequests.php >\n<input type=hidden name=action value=message ><input type=hidden name=id value=".$_GET["id"]." >\n");
		print("<table width=940 cellspacing=0 cellpadding=3>\n");
		
		print("<tr><td class=rowfollow align=left>");
		if($ruserid){textbbcode("reply","message","[b]回复:".get_plain_username($ruserid)."[/b]\n");
		print("<input id=ruserid type=hidden value=$ruserid />");}
		else
		textbbcode("reply","message");
		print("</td></tr>");
		print("</table><input id=qr type=submit value=添加评论 class=btn /></form><br />\n");
		
		stdfoot();
		die;
		}
	
	}
			case "search":
	{
	
		{	
		stdhead("搜索");


		print("<table border=1 cellspacing=0  cellpadding=5>\n");
		print("<tr><td class=colhead align=left>搜索</td></tr>\n");
		print("<tr><td class=toolbox align=left><form  method=\"post\" action='viewrequests.php'>\n");
		print("<input type=\"text\" name=\"query\" style=\"width:500px\" >\n");
		print("<input type=\"hidden\" name=\"action\" value='list'>");
		print("<input type=submit value='搜索'></form>\n");
		print("</td></tr></table><br />\n");



		stdfoot();
		die;
		}
	
	}
	case "takeadded":
	{
	if(!$_POST["body"])stderr("出错了！","介绍未填！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	if(!$_POST["subject"])stderr("出错了！","名称未填！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	if(!$_POST["amount"])stderr("出错了！","赏金未填！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	if(!is_numeric($_POST["amount"]))stderr("出错了！！！","赏金必须为数字！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	$amount=$_POST["amount"];
	if($amount<100)stderr("出错了！","发布求种赏金不得小于100个魔力值！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	if($amount>10000)stderr("出错了！","发布求种赏金不得大于10000个魔力值！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	$amount += 100;
	if($amount+100>$CURUSER['seedbonus'])stderr("出错了！","你没有那么多魔力值！！！<a href=viewrequests.php?action=new>点击这里返回</a>",0);
	if(get_user_class() >= 2) {
		KPS('-', $amount, $CURUSER['id']);

		sql_query("INSERT requests ( request , descr, ori_descr ,amount , ori_amount , userid ,added ) VALUES ( ".sqlesc($_POST["subject"])." , ".sqlesc($_POST["body"])." , ".sqlesc($_POST["body"])." , ".sqlesc($_POST["amount"])." , ".sqlesc($_POST["amount"])." , ".sqlesc($CURUSER['id'])." , '".date("Y-m-d H:i:s")."' )") or sqlerr(__FILE__, __LINE__);
		
		header('Location: viewrequests.php?id='._mysql_insert_id(), true, 303);

		}
	else stderr("出错了！！！","你没有该权限！！！<a href=viewrequests.php>点击这里返回</a>",0);
	die;
	break;
	}
	
	case "takeedit":
	{
	if(!is_numeric($_POST["reqid"]))stderr("出错了！！！","求种ID必须为数字！<a href=viewrequests.php?action=edit&id=".$_POST["reqid"].">点击这里返回</a>",0);
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_POST["reqid"]."'") or sqlerr(__FILE__, __LINE__);
	if(!$_POST["body"])stderr("出错了！！！","介绍未填！<a href=viewrequests.php?action=edit&id=".$_POST["reqid"].">点击这里返回</a>",0);
	if(!$_POST["subject"])stderr("出错了！！！","名称未填！<a href=viewrequests.php?action=edit&id=".$_POST["reqid"].">点击这里返回</a>",0);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！<a href=viewrequests.php>点击这里返回</a>",0);
	$arr = _mysql_fetch_assoc($res);
	if ($arr["finish"]=="yes") stderr("出错了！","该求种已完成！<a href=viewrequests.php?action=view&id=".$_POST["reqid"].">点击这里返回</a>",0);
	if($arr['userid']== $CURUSER['id'] || get_user_class() >= 13)
		{
		sql_query("UPDATE requests SET descr = ".sqlesc($_POST["body"])." , request = ".sqlesc($_POST["subject"])." WHERE id ='".$_POST["reqid"]."'") or sqlerr(__FILE__, __LINE__);
		header('Location: viewrequests.php?id='.$_POST["reqid"], true, 303);
		}
	else stderr("出错了！！！","你没有该权限！！！<a href=viewrequests.php?action=view&id=".$_POST["reqid"].">点击这里返回</a>",0);
	die;
	break;
	}
	
	case "res":
	{
	stdhead("应求");	
	stdmsg("我要应求", "
	<form action=viewrequests.php method=post>
	<input type=hidden name=action value=takeres />
	<input type=hidden name=reqid value=\"".$_GET["id"]."\" />
	请输入种子的ID:http://$BASEURL/details.php?id=<input type=text name=torrentid size=11/>
	<input type=submit value=提交></form><a href=viewrequests.php?action=view&id=".$_GET["id"].">点击这里返回</a>",0);
	stdfoot();
	die;
	break;
	}
	
	case "takeres":
	{
	if(!is_numeric($_POST["reqid"]))stderr("出错了！！！","不要试图入侵系统！");
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_POST["reqid"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！<a href=viewrequests.php>点击这里返回</a>",0);
	$arr = _mysql_fetch_assoc($res);
	if ($arr["finish"]=="yes") stderr("出错了！","该求种已完成！<a href=viewrequests.php?action=view&id=".$_POST["reqid"].">点击这里返回</a>",0);
	if(!is_numeric($_POST["torrentid"]))stderr("出错了！！！","种子ID必须为数字！<a href=viewrequests.php?action=res&id=".$_POST["reqid"].">点击这里返回</a>",0);
	$res = sql_query("SELECT * FROM torrents WHERE id ='".$_POST["torrentid"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该种子不存在！<a href=viewrequests.php?action=res&id=".$_POST["reqid"].">点击这里返回</a>",0);
	$tor=_mysql_fetch_assoc($res);
	if ($tor['last_seed'] == "0000-00-00 00:00:00") stderr("出错了！！！","该种子尚未正式发布！<a href=viewrequests.php?action=res&id=".$_POST["reqid"].">点击这里返回</a>",0);
	if(get_row_count('resreq', "where reqid ='".$_POST["reqid"]."' and torrentid='".$_POST["torrentid"]."'"))
	stderr("出错了！！！","该应求已经存在！<a href=viewrequests.php?action=res&id=".$_POST["reqid"].">点击这里返回</a>",0);
	sql_query("INSERT resreq (reqid , torrentid) VALUES ( '".$_POST["reqid"]."' , '".$_POST["torrentid"]."')");
	
	
	$subject = ("有人应求你的求种请求,请及时确认该应求");
	$notifs = ("求种名称:[url=viewrequests.php?id=$arr[id]] " . $arr['request'] . "[/url],请及时确认该应求.");
	send_pm(0, $arr['userid'], $subject, $notifs);
		
	header('Location: viewrequests.php?id='.$_POST["reqid"], true, 303);

	die;
	break;
	}
	case "addamount":
	{
	if(!is_numeric($_POST["reqid"]))stderr("出错了！！！","不要试图入侵系统");
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_POST["reqid"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！");	
	$arr = _mysql_fetch_assoc($res);
	if ($arr["finish"]=="yes") stderr("出错了！","该求种已完成！");
	if(!is_numeric($_POST["amount"]))stderr("出错了！","赏金必须为数字！");
	$amount=$_POST["amount"];
	if($amount<100)stderr("出错了！","追加悬赏赏金不得小于100个魔力值！");
	if($amount>5000)stderr("出错了！","追加悬赏赏金不得大于5000个魔力值！");
	$amount += 25;
	if($amount >$CURUSER['seedbonus'])stderr("出错了！","你没有那么多魔力值！");
	KPS('-', $amount, $CURUSER['id']);
	sql_query("UPDATE requests SET amount = amount + ".$_POST["amount"]." WHERE id = ".$_POST["reqid"]);
	header('Location: viewrequests.php?id='.$_POST["reqid"], true, 303);
	die;
	break;
	}

	case "delete":
	{
	if(!is_numeric($_GET["id"]))stderr("出错了！！！","求种ID必须为数字");
	$res = sql_query("SELECT * FROM requests WHERE id ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！");
	$arr = _mysql_fetch_assoc($res);
	if (checkPrivilege(['Requests', 'delete'], $_GET["id"]))
		{
		if(!get_row_count("resreq","WHERE reqid=".sqlesc($_GET["id"]))){
		KPS("+",$arr['amount']*8/10,$arr['userid']);
		}
		sql_query("DELETE FROM requests WHERE id ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
		sql_query("DELETE FROM resreq WHERE reqid ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
		sql_query("DELETE FROM comments WHERE request ='".$_GET["id"]."'") or sqlerr(__FILE__, __LINE__);
		header('Location: viewrequests.php', true, 303);

		}
	else stderr("出错了！！！","你没有该权限！！！");
	die;
	break;
	}
	
	case "modifyres":	{
	if(!is_numeric($_POST["id"]))stderr("出错了！！！","不要试图入侵系统");
	$req = 0 + $_REQUEST['id'];
	$res = sql_query("SELECT * FROM requests WHERE id ='".$req."'") or sqlerr(__FILE__, __LINE__);
	if (_mysql_num_rows($res) == 0) stderr("出错了！","该求种已被删除！");
	$arr = _mysql_fetch_assoc($res);
	if(empty($_POST["torrentid"]))stderr("出错了！","你没有选择符合条件的应求！");
	else $torrentid = $_POST["torrentid"];

	if (isset($_REQUEST['delete'])) {
	  checkHTTPMethod('post');

	  foreach ($torrentid as $torrent) {
	    $canDelete = checkPrivilege(['Requests', 'deleteres'], [$arr['finish']=='no', $torrent]);
	    if ($canDelete) {
	      sql_query('DELETE FROM resreq WHERE reqid = ? AND torrentid = ?', [$req, $torrent]);
	      $owner = get_single_value('torrents', 'owner', 'WHERE id=?', [$torrent]);
	      $self_delete = $owner == $CURUSER['owner'];
	      if (!$self_delete) {
		send_pm($CURUSER['id'], $owner, '应求被删除', '你在求种[url=viewrequest.php?id=' . $req . ']' . $arr['request'] . '[/url]中的应求[torrent=' . $torrent . ']被删除。');
	      }
	    }
	  }
	  header('Location: ?id=' . $req, true, 303);
	  die;
	}
	else if (isset($_REQUEST['confirm'])) {
	  if ($arr['userid']== $CURUSER['id'] || get_user_class() >= 13) {
	      $amount = $arr["amount"]/count($torrentid);
	      sql_query("UPDATE requests SET finish = 'yes' WHERE id = ".$req);
	      sql_query("UPDATE resreq SET chosen = 'yes' WHERE reqid = ".$req." AND ( torrentid = '".join("' OR torrentid = '",$torrentid )."' )")or sqlerr(__FILE__, __LINE__);
	      sql_query("DELETE FROM resreq WHERE reqid ='".$req."' AND chosen = 'no'") or sqlerr(__FILE__, __LINE__);
	      $res=sql_query("SELECT owner FROM torrents WHERE ( id = '".join("' OR id = '",$torrentid )."' ) ")or sqlerr(__FILE__, __LINE__);
	      while($row = _mysql_fetch_array($res)){
		$owner=$row[0];
		$added = sqlesc(date("Y-m-d H:i:s"));
		$subject = ("你的种子被人应求");
		$notifs = ("求种名称:[url=viewrequests.php?id=$arr[id]] " . $arr['request'] . "[/url].你获得: $amount 魔力值");
		send_pm(0, $owner, $subject, $notifs);
		KPS('+', $amount, $owner);
	      }

	      header('Location: ?id=' . $req, true, 303);
	  }
	
	}
	
}
	}

}
die;

