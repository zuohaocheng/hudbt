<?php !defined('DONATE_BONUS') AND exit('INVALID REQUEST.'); ?>
<?php 
/*
 * Added by BruceWolf. 2011.10.15
 */

?>
<dt id="donating">捐赠魔力值</dt>
<dd>
	<div id="to_donate">
<?php
$torrent_id = (int) $_GET['id'];
if($CURUSER['id'] == $row['owner']) {
	echo '<p class="donate_note">谢谢你发布种子，'.$CURUSER['username'].'。</p>';
} else {
	$sqlCheckDonate = 'SELECT amount, message, action_date FROM donate_bonus WHERE object_id='.$torrent_id.' AND donater_id='.$CURUSER['id'].' AND `type`="torrent"';
	$result  = sql_query($sqlCheckDonate);
	$donated = _mysql_fetch_assoc($result);
	if(!empty($donated)) {
		echo "<p class=\"donate_note\">你已经于 {$donated['action_date']} 对种子发布者捐赠过 {$donated['amount']} 魔力值，谢谢你！</p>";
	} else {
	?>
			<a class="donate donate-0" title="向楼主捐赠 64 魔力">64</a>
			<a class="donate donate-1" title="向楼主捐赠 128 魔力">128</a>
			<a class="donate donate-2" title="向楼主捐赠 256 魔力">256</a>
			<a class="donate donate-3" title="向楼主捐赠 512 魔力">512</a>
			<a class="donate donate-4" title="You know~ I'm rich!">1024</a>
	<?php 
	}
}
?>
	</div>
<div id="donater_list">
<?php 
$torrent_id = (int) $_GET['id'];
$sqlDonateList = 'SELECT donater, donater_id, amount, message, action_date FROM donate_bonus WHERE object_id='.$torrent_id.' AND `type`="torrent"';
$result = sql_query($sqlDonateList);

if($Cache->get_value('sign_update_donate_bonus_torrent_'.$torrent_id) == 'no') {
	$donaterRecodes = $Cache->get_value('update_donate_bonus_torrent_'.$torrent_id);
} else {
	// 'sign_update_donate_bonus_torrent_{id}' is 'yes' or empty 
	$donaterRecodes  = array();
	while($donateInfo = _mysql_fetch_assoc($result)) {
		$donaterRecodes[] = $donateInfo;
		$i++;
	}
	$Cache->cache_value('update_donate_bonus_torrent_'.$torrent_id, $donaterRecodes, 365 * 24 * 3600);
	$Cache->cache_value('sign_update_donate_bonus_torrent_'.$torrent_id, 'no', 365 * 24 * 3600);
}

$doanterCount = count($donaterRecodes);

$classForAmount = [64 => 'donate-0',
		   128 => 'donate-1',
		   256 => 'donate-2',
		   512 => 'donate-3',
		   1024 => 'donate-4'];

if($doanterCount) {
	$amount = 0;
	$donateListHTML = '';
	foreach($donaterRecodes as $donateInfo) {
		$amount += $donateInfo['amount'];
//		$donateListHTML .= "<div id=\"donater_{$donateInfo['donater_id']}\"  class=\"donate{$donateInfo['amount']} donate\" title=\"{$donateInfo['message']} \n[{$donateInfo['amount']} 魔力值] {$donateInfo['action_date']}\">{$donateInfo['donater']}</div>";
		$donateListHTML .= "<a href=\"//{$BASEURL}/userdetails.php?id={$donateInfo['donater_id']}\"  class=\"{$classForAmount[$donateInfo['amount']]} donate\" title=\"[{$donateInfo['amount']} 魔力值] {$donateInfo['action_date']}\">{$donateInfo['donater']}</a>";
	}
	$donateListHTML .= '<div style="clear:both;"></div>';
	if($CURUSER['id'] == $row['owner']) {
		$amount_after_tax = $amount - ($amount / 8);
		echo "<p class=\"donate_note\">共有 {$doanterCount} 人给你捐赠了 {$amount} 魔力值，你收到的为 {$amount_after_tax} 魔力值[税后]。</p>";
	}
	
	echo $donateListHTML;
} else {
	if($CURUSER['id'] == $row['owner']) {
		echo '<p class="donate_note">暂时还没有人给你捐赠魔力值，随缘的哦~</p>';
	} else {
		echo '<p class="donate_note">支持发种者，你来做第一个捐赠人吧~</p>';
	}
}
?>

</div></dd>
