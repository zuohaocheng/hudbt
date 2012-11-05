<?php // !defined('DONATE_BONUS') AND exit('INVALID REQUEST.'); ?>
<?php 
/*
 * Added by BruceWolf. 2012.06.25
 */

?>
<div class="post-donation table">
	<div id="to_donate">
<?php
if($userid != $arr['userid']) {
  $sqlCheckDonate = 'SELECT amount, message, action_date FROM donate_bonus WHERE object_id='.$topicid.' AND donater_id='.$userid.' AND `type`="topic"';
	$result  = sql_query($sqlCheckDonate) or sqlerr();
	$donated = mysql_fetch_assoc($result);
	if(!empty($donated)) {
		echo "<p class=\"donate_note\">你已经于 {$donated['action_date']} 对楼主捐赠过 {$donated['amount']} 魔力值，谢谢你！</p>";
	} else {
	?>
			<a class="donate donate-0" title="向楼主捐赠 8 魔力">8</a>
			<a class="donate donate-1" title="向楼主捐赠 16 魔力">16</a>
			<a class="donate donate-2" title="向楼主捐赠 32 魔力">32</a>
			<a class="donate donate-3" title="向楼主捐赠 64 魔力">64</a>
			<a class="donate donate-4" title="绝对支持楼主！">128</a>
	<?php 
	}
}
?>
	</div>
	<div id="donater_list">
<?php 
// $topicid = (int) $_GET['topicid'];
$sqlDonateList = 'SELECT donater, donater_id, amount, message, action_date FROM donate_bonus WHERE object_id='.$topicid.' AND `type`="topic"';
$result = sql_query($sqlDonateList) or sqlerr();

if($Cache->get_value('sign_update_donate_bonus_topic_'.$topicid) == 'no') {
	$donaterRecodes = $Cache->get_value('update_donate_bonus_topic_'.$topicid);
} else {
	// 'sign_update_donate_bonus_topic_{id}' is 'yes' or empty 
	$donaterRecodes  = array();
	while($donateInfo = mysql_fetch_assoc($result)) {
		$donaterRecodes[] = $donateInfo;
		$i++;
	}
	$Cache->cache_value('update_donate_bonus_topic_'.$topicid, $donaterRecodes, 365 * 24 * 3600);
	$Cache->cache_value('sign_update_donate_bonus_topic_'.$topicid, 'no', 365 * 24 * 3600);
}

$doanterCount = count($donaterRecodes);

$classForAmount = [8 => 'donate-0',
		   16 => 'donate-1',
		   32 => 'donate-2',
		   64 => 'donate-3',
		   128 => 'donate-4'];

if($doanterCount) {
	$amount = 0;
	$donateListHTML = '';
	$count = 0;
	foreach ($donaterRecodes as $donateInfo) {
		$amount += $donateInfo['amount'];
		if ($count == 4) {
		  $donateListHTML .= ' <a href="#" id="donater-hidden-show">等' . $doanterCount . '人</a><div id="donater-hidden" style="display: none;">';
		}
		$donateListHTML .= "<a href=\"//{$BASEURL}/userdetails.php?id={$donateInfo['donater_id']}\" class=\"donate {$classForAmount[$donateInfo['amount']]}\" title=\"[{$donateInfo['amount']} 魔力值] {$donateInfo['action_date']}\">{$donateInfo['donater']}</a>";
		++$count;
	}
	if ($count > 4) {
	  $donateListHTML .= '</div>';
	}
	if($userid == $arr['userid']) {
		$amount_after_tax = $amount - ($amount / 8);
		echo "<p class=\"donate_note\">共有 {$doanterCount} 人给你捐赠了 {$amount} 魔力值，你收到的为 {$amount_after_tax} 魔力值[税后]。</p>";
	}
	
	echo $donateListHTML;
} else {
	if($userid == $arr['userid']) {
		echo '<p class="donate_note">暂时还没有人给你捐赠魔力值，随缘的哦~</p>';
	} else {
		echo '<p class="donate_note">做以实际行动支持楼主的第一人吧~</p>';
	}
}
?>
	</div>
</div>
