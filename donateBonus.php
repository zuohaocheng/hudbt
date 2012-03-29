<?php
ob_start(); //Do not delete this line
header('Content-type: application/json');
require_once("include/bittorrent.php");
dbconn();

$amount = (int) $_REQUEST['amount'];
$now    = date('Y-m-d H:i:s');

$donater    = $CURUSER['username'];
$donaterId  = $CURUSER['id'];
$objectType = $_REQUEST['type'];

$status = 0;
if(!empty($CURUSER['username'])) {
	if($CURUSER['seedbonus'] < $amount) {
		
		$status = 1; // Not enough bonus
		
	} else if($objectType == 'torrent') {
		
		$objectId = (int) $_REQUEST['torrent_id'];
		
		$result = sql_query('SELECT id, owner, name, anonymous FROM torrents WHERE id='.$objectId);
		$torrentInfo = mysql_fetch_assoc($result);
		
		$receiverId = $torrentInfo['owner'];
		$objectId = $torrentInfo['id'];
		$anonymous = $torrentInfo['anonymous'];
		
		if(empty($objectId)) {
			$status = 2; // No such torrent
		} else if(empty($receiverId)) {
			$status = 3; // No such user
		} else if(!in_array($amount, array(64, 128, 256, 512, 1024))) {
			$status = 4; // Involid amount of donate
		} else if($receiverId == $donaterId) {
			$status = 5; // Donate to someone self
		} else if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
		  $status = 7;
		} else {
			
			$sqlCheckDonate = 'SELECT donater_id FROM donate_bonus WHERE object_id='.$objectId.' AND donater_id='.$donaterId.' AND `type`="torrent"';
			$result  = sql_query($sqlCheckDonate);
			$donated = mysql_fetch_assoc($result);
			
			if(!empty($donated)) {
				$status = 6; // Already donated
			}  else {
				$amount_after_tax = $amount - ($amount / 8); // Tax is required, 1/8
				$message = sqlesc($_REQUEST['message']);
				
				$sqlAddLog = 'INSERT INTO donate_bonus'
				            .' (`donater_id`, `donater`, `receiver_id`, `type`, `object_id`, `amount`, `amount_after_tax`, `message`)'
				            .' VALUES'
				            ." ({$donaterId}, '{$donater}', {$receiverId}, 'torrent', {$objectId}, {$amount}, {$amount_after_tax}, '')";
				sql_query($sqlAddLog);
				
				if(mysql_affected_rows()) {
					$status = 7; // Loged the donate

					// Update the user details of donater  |->
					$sqlReceiverInfo = 'SELECT username FROM users WHERE id='.$receiverId;
					$result = mysql_query($sqlReceiverInfo);
					$receiverInfo = mysql_fetch_assoc($result);
					$receiver = $receiverInfo['username'];
					$dotanerBonusComment = date("Y-m-d") . " - {$amount} Points as donate to {$receiver} on {$objectType} {$objectId}.\n";

					$sqlUpdateDonaterInfo = "UPDATE LOW_PRIORITY users SET seedbonus=seedbonus-{$amount}, bonuscomment=CONCAT('{$dotanerBonusComment}', bonuscomment) WHERE id=".$donaterId;
					sql_query($sqlUpdateDonaterInfo);
					// End update the user details of donater
					
					
					if(mysql_affected_rows()) {
						$status = 8; // Reduced the bonus from donater
						
						$receiverBonusComment = date("Y-m-d") . " + {$amount_after_tax} Points (after tax) as donate from {$donater} on {$objectType} {$objectId}.\n";
						
						$sqlUpdateReceiverInfo = "UPDATE LOW_PRIORITY users SET seedbonus=seedbonus+{$amount_after_tax}, bonuscomment=CONCAT('{$receiverBonusComment}', bonuscomment) WHERE id={$receiverId}"; // Successful;
						sql_query($sqlUpdateReceiverInfo);
						
						if(mysql_affected_rows()) {
							$status = 9; // Successful
				
						}
					}
				}
			}
		}
	} else if($objectType == 'post') {
		$objectId = 0;
	} else {
		$status = 6; // Undefined object type
	}
}

if($status == 9) {
	
	// send private message ||->
	$type = array(
		'torrent' => '种子页面',
		'post'    => '论坛主题页面'
	  );
	$receiverSubject = "{$donater} 在{$type[$objectType]}给你捐赠了 {$amount} 魔力值";
	if($anonymous == 'yes') {
		$donaterSubject  = "你在{$type[$objectType]}向种子发布者捐赠了 {$amount} 魔力值";
	} else {
		$donaterSubject  = "你在{$type[$objectType]}向  {$receiver} 捐赠了 {$amount} 魔力值";
	}
	
	$donaterLink = "[color=Blue][url=http://{$BASEURL}/userdetails.php?id={$donaterId}][b]{$donater}[/b][/url][/color]";
	if($anonymous == 'yes') {
		$receiverLink = "种子发布者（用户已匿名）";
	} else {
		$receiverLink = "[color=Blue][url=http://{$BASEURL}/userdetails.php?id={$receiverId}][b]{$receiver}[/b][/url][/color]";
	}
	
	if($objectType == 'torrent') {
		
		$pageLink = "[url=http://{$BASEURL}/details.php?id={$objectId}][b]{$torrentInfo['name']}[/b][/url]";
		$donaterMessage = <<<MESSAGE
你在种子页面 {$pageLink} 向 {$receiverLink} 捐赠了[b]{$amount}[/b] 魔力值。

谢谢你对发种者的支持!
MESSAGE;
		$receiverMessage = <<<MESSAGE
用户 {$donaterLink} 在种子页面 {$pageLink} 给你捐赠了[b]{$amount}[/b] 魔力值，税后你将获得 [b]{$amount_after_tax}[/b] 魔力值。

谢谢你发布种子，祝愉快~
MESSAGE;

	} else if($objectType == 'post') {
		$donaterMessage = <<<MESSAGE

MESSAGE;
		$receiverMessage = <<<MESSAGE

MESSAGE;
	} 
	
	
	
	// Send PM to donater |->
	/*	$sqlSendPMToDonater = 'INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location)'
	             .' VALUES'
	             ." (0, {$donaterId}, '{$now}', '{$donaterSubject}', '{$donaterMessage}', 'yes', 'no', 1)";

	sql_query($sqlSendPMToDonater);
	$Cache->delete_value('user_'.$donaterId.'_unread_message_count');
	$Cache->delete_value('user_'.$donaterId.'_inbox_count');

	$sqlUpdatePMStatus = 'UPDATE LOW_PRIORITY users SET last_pm = NOW() WHERE id = '.$donaterId;
	sql_query($sqlUpdatePMStatus); */
	// End send PM to donater ||
	
	// Send PM to receiver |->
	$sqlSendPMToReceiver = 'INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location)'
	             .' VALUES'
	             ." (0, {$receiverId}, '{$now}', '{$receiverSubject}', '{$receiverMessage}', 'yes', 'no', 1)";

	sql_query($sqlSendPMToReceiver);
	$Cache->delete_value('user_'.$receiverId.'_unread_message_count');
	$Cache->delete_value('user_'.$receiverId.'_inbox_count');

	$sqlUpdatePMStatus = 'UPDATE LOW_PRIORITY users SET last_pm = NOW() WHERE id = '.$receiverId;
	sql_query($sqlUpdatePMStatus);
	// End send PM to receiver ||
	
	// End send private message. ||
	
	// Update memcache sign
	$Cache->cache_value("sign_update_donate_bonus_{$objectType}_{$objectId}", 'yes', 365 * 24 * 3600);
}
//var_dump($query_name);die(); // debug
$response = array(
  'status' => $status,
  'amount' => $amount,
  'donater' => $donater,
  'message' => '',
//  'message' => $status < 9 ? '' : 'I love the movie~'
  'date' => $now
);
echo php_json_encode($response);