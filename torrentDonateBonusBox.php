<?php !defined('DONATE_BONUS') AND exit('INVALID REQUEST.'); ?>
<?php 
/*
 * Added by BruceWolf. 2011.10.15
 */

?>
<style>
#donating {
	text-align: right;
	font-weight: bold;
}
#to_donate a {
	width: 42px;
}
.donate {
	display: block;
	float: left;
	height: 22px;
	margin: 6px;
	padding: 0 6px;
	line-height: 22px;
	text-align: center;
	border: 1px solid #AAA;
}
.donate64 {background-color: #FFFF99;}
.donate128 {background-color: #CCFF99;}
.donate256 {background-color: #99FFFF;}
.donate512 {background-color: #99CCFF;}
.donate1024 {background-color: #FF9999;}
.donate_note {margin: 0;}
</style>
<tr>
	<td rowspan=2 id="donating">捐赠魔力值<br /><a href='bonusDonations.php' style="font-weight:normal;">[查看我的捐赠历史]</a></td>
	<td id="to_donate">
<?php
$torrent_id = (int) $_GET['id'];
if($CURUSER['id'] == $row['owner']) {
	echo '<p class="donate_note">谢谢你发布种子，'.$CURUSER['username'].'。</p>';
} else {
	$sqlCheckDonate = 'SELECT amount, message, action_date FROM donate_bonus WHERE object_id='.$torrent_id.' AND donater_id='.$CURUSER['id'].' AND `type`="torrent"';
	$result  = sql_query($sqlCheckDonate);
	$donated = mysql_fetch_assoc($result);
	if(!empty($donated)) {
		echo "<p class=\"donate_note\">你已经于 {$donated['action_date']} 对种子发布者捐赠过 {$donated['amount']} 魔力值，谢谢你！</p>";
	} else {
	?>
			<a href="#" class="donate donate64" title="向楼主捐赠 64 魔力">64</a>
			<a href="#" class="donate donate128" title="向楼主捐赠 128 魔力">128</a>
			<a href="#" class="donate donate256" title="向楼主捐赠 256 魔力">256</a>
			<a href="#" class="donate donate512" title="向楼主捐赠  512 魔力">512</a>
			<a href="#" class="donate donate1024" title="You know~ I'm rich!">1024</a>
	<?php 
	}
}
?>
	</td>
</tr>
<tr><td id="donater_list">
<?php 
$torrent_id = (int) $_GET['id'];
$sqlDonateList = 'SELECT donater, donater_id, amount, message, action_date FROM donate_bonus WHERE object_id='.$torrent_id.' AND `type`="torrent"';
$result = sql_query($sqlDonateList);

if($Cache->get_value('sign_update_donate_bonus_torrent_'.$_GET['id']) == 'no') {
	$donaterRecodes = $Cache->get_value('update_donate_bonus_torrent_'.$_GET['id']);
} else {
	// 'sign_update_donate_bonus_torrent_{id}' is 'yes' or empty 
	$donaterRecodes  = array();
	while($donateInfo = mysql_fetch_assoc($result)) {
		$donaterRecodes[] = $donateInfo;
		$i++;
	}
	$Cache->cache_value('update_donate_bonus_torrent_'.$_GET['id'], $donaterRecodes, 365 * 24 * 3600); // 数据缓存一年，除非有更新状态或者
	$Cache->cache_value('sign_update_donate_bonus_torrent_'.$_GET['id'], 'no', 365 * 24 * 3600);       // 数据缓存一年，除非该种子有新的捐赠或者全局重建缓存
}

$doanterCount = count($donaterRecodes);

if($doanterCount) {
	$amount = 0;
	$donateListHTML = '';
	foreach($donaterRecodes as $donateInfo) {
		$amount += $donateInfo['amount'];
		$donateListHTML .= "<a id=\"donater_{$donateInfo['donater_id']}\"  class=\"donate{$donateInfo['amount']} donate\" title=\"{$donateInfo['message']} \n[{$donateInfo['amount']} 魔力值] {$donateInfo['action_date']}\" href='userdetails.php?id=" . $donateInfo['donater_id'] . "'>{$donateInfo['donater']}</a>";
	}
	
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

</td></tr>
<script type="text/javascript">
$('#to_donate a').each(function() {
    $(this).click(function(e) {
	e.preventDefault();

	var torrent_id = <?php echo intval($_GET['id']);?>;
	var bonus      = <?php echo $CURUSER['seedbonus']; ?>;
	var to_donate = $(this).html();
	if(bonus < to_donate) {
	    alert('你的魔力值不足，谢谢你的好心，继续努力吧~');
	} else if(confirm('确认向种子发布者捐赠 ' + to_donate +' 魔力值吗？')) {
	    var url = 'donateBonus.php';
	    var data = {amount: to_donate, torrent_id : torrent_id, type: 'torrent'};
	    $.getJSON(url, data, function(data) {
		if(data.status == 9) {
		    var newDonate = '<div class="donate'+ data.amount +' donate" id="donated_successfully" title="' + data.message + '\n[' + data.amount + ' 魔力值] ' + data.date + '">' + data.donater + '</div>';
		    $('#donater_list').append(newDonate);

		    $('#to_donate').html("你已经于 " + data.date + " 对种子发布者进行过魔力值捐赠，谢谢你！");
		} else if(data.status == 1) {
		    alert('谢谢你，但是你的魔力值不足，继续努力吧。');
		} else if(data.status == 2) {
		    alert('你要捐赠种子不存在。');
		} else if(data.status == 3) {
		    alert('你要捐赠的用户不存在。');
		} else if(data.status == 4) {
		    alert('只允许以下几个数量的捐赠数：64, 128, 256, 512, 1024。');
		} else if(data.status == 5) {
		    alert('不能给自己捐赠的哦！');
		} else if(data.status == 6) {
		    alert('你已经捐赠过了，谢谢！');
		} else {
		    alert('貌似系统出问题了，呼管理员！');
		}
	    });
	}
    });
});
    <?php if($doanterCount): ?>
    $('#donater_list div').each(function() {
	$(this).click(function() {
	    var html_id = $(this).attr('id');
	    var id = html_id.split('_')[1];
	    window.open('/userdetails.php?id=' + id);
	});
    });
<?php endif;?>
</script>