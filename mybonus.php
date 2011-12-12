<?php
require_once('include/bittorrent.php');
dbconn();
require_once(get_langfile_path());
require(get_langfile_path("",true));
loggedinorreturn();
parked();
require_once('include/bonus.php');

if ($bonus_tweak == "disable" || $bonus_tweak == "disablesave")
	stderr($lang_mybonus['std_sorry'],$lang_mybonus['std_karma_system_disabled'].($bonus_tweak == "disablesave" ? "<b>".$lang_mybonus['std_points_active']."</b>" : ""),false);

$action = htmlspecialchars($_GET['action']);
$do = htmlspecialchars($_GET['do']);

if (isset($do)) {
  $msg = bonusTextFromAction($do);
}
else {
  $msg = '';
}

$bonus = number_format($CURUSER['seedbonus'], 1);
if (!$action) {
  	stdhead($CURUSER['username'] . $lang_mybonus['head_karma_page']);
?>
<h1 id="page-title;"><?php echo $SITENAME.$lang_mybonus['text_karma_system']; ?></h1>
<h3 class="page-titles transparentbg"><?php echo $lang_mybonus['text_exchange_your_karma']?><span class="bonus"><?php echo $bonus?></span><?php echo $lang_mybonus['text_for_goodies'] ?></h3>
<h3 class="page-titles"><?php echo $lang_mybonus['text_no_buttons_note'] ?></h3>

<div id="mybonus-result-text" <?php echo ($msg =='' ? 'style="display:none;"' : ''); ?>><?php echo $msg; ?></div>
<table align="center" width="940" border="1" cellspacing="0" cellpadding="3"><thead>
<?php
print("<tr><th align=\"center\">".$lang_mybonus['col_option']."</th>".
"<th align=\"left\">".$lang_mybonus['col_description']."</th>".
"<th align=\"center\">".$lang_mybonus['col_points']."</th>".
"<th align=\"center\">".$lang_mybonus['col_trade']."</th>".
"</tr></thead><tbody>");
for ($i=1; $i <=9; $i++)
{
	$bonusarray = bonusarray($i);
	if (($i == 7 && $bonusgift_bonus == 'no') || ($i == 8 && ($enablead_advertisement == 'no' || $bonusnoad_advertisement == 'no')))
		continue;
	print("<tr>");
	print("<form action=\"takebonusexchange.php\" method=\"post\">");
	print("<td class=\"rowhead_center\"><input type=\"hidden\" name=\"option\" value=\"".$i."\" /><b>".$i."</b></td>");
	if ($i==5){ //for Custom Title!
	$otheroption_title = "<input type=\"text\" name=\"title\" style=\"width: 200px\" maxlength=\"30\" />";
	print("<td class=\"rowfollow\" align='left'><h3>".$bonusarray['name']."</h3>".$bonusarray['description']."<br /><br />".$lang_mybonus['text_enter_titile'].$otheroption_title.$lang_mybonus['text_click_exchange']."</td><td class=\"rowfollow\" align='center'>".number_format($bonusarray['points'])."</td>");
	}
	elseif ($i==7){  //for Give A Karma Gift
			$otheroption = "<table width=\"100%\"><tr><td class=\"embedded\"><b>".$lang_mybonus['text_username']."</b><input type=\"text\" name=\"username\" style=\"width: 200px\" maxlength=\"24\" /></td><td class=\"embedded\"><b>".$lang_mybonus['text_to_be_given']."</b><select name=\"bonusgift\" id=\"giftselect\" onchange=\"customgift();\"> <option value=\"25\"> 25</option><option value=\"50\"> 50</option><option value=\"100\"> 100</option> <option value=\"200\"> 200</option> <option value=\"300\"> 300</option> <option value=\"400\"> 400</option><option value=\"500\"> 500</option><option value=\"1000\" selected=\"selected\"> 1,000</option><option value=\"5000\"> 5,000</option><option value=\"10000\"> 10,000</option><option value=\"0\">".$lang_mybonus['text_custom']."</option></select><input type=\"text\" name=\"bonusgift\" id=\"giftcustom\" style='width: 80px' disabled=\"disabled\" />".$lang_mybonus['text_karma_points']."</td></tr><tr><td class=\"embedded\" colspan=\"2\"><b>".$lang_mybonus['text_message']."</b><input type=\"text\" name=\"message\" style=\"width: 400px\" maxlength=\"100\" /></td></tr></table>";
			print("<td class=\"rowfollow\" align='left'><h3>".$bonusarray['name']."</h3>".$bonusarray['description']."<br /><br />".$lang_mybonus['text_enter_receiver_name']."<br />$otheroption</td><td class=\"rowfollow nowrap\" align='center'>".$lang_mybonus['text_min']."25<br />".$lang_mybonus['text_max']."10,000</td>");
	}
	elseif ($i==9){  //charity giving
			$otheroption = "<table width=\"100%\"><tr><td class=\"embedded\">".$lang_mybonus['text_ratio_below']."<select name=\"ratiocharity\"> <option value=\"0.1\"> 0.1</option><option value=\"0.2\"> 0.2</option><option value=\"0.3\" selected=\"selected\"> 0.3</option> <option value=\"0.4\"> 0.4</option> <option value=\"0.5\"> 0.5</option> <option value=\"0.6\"> 0.6</option><option value=\"0.7\"> 0.7</option><option value=\"0.8\"> 0.8</option></select>".$lang_mybonus['text_and_downloaded_above']." 10 GB</td><td class=\"embedded\"><b>".$lang_mybonus['text_to_be_given']."</b><select name=\"bonuscharity\" id=\"charityselect\" > <option value=\"1000\"> 1,000</option><option value=\"2000\"> 2,000</option><option value=\"3000\" selected=\"selected\"> 3000</option> <option value=\"5000\"> 5,000</option> <option value=\"8000\"> 8,000</option> <option value=\"10000\"> 10,000</option><option value=\"20000\"> 20,000</option><option value=\"50000\"> 50,000</option></select>".$lang_mybonus['text_karma_points']."</td></tr></table>";                                                                
			print("<td class=\"rowfollow\" align='left'><h3>".$bonusarray['name']."</h3>".$bonusarray['description']."<br /><br />".$lang_mybonus['text_select_receiver_ratio']."<br />$otheroption</td><td class=\"rowfollow nowrap\" align='center'>".$lang_mybonus['text_min']."1,000<br />".$lang_mybonus['text_max']."50,000</td>");
	}
	else{  //for VIP or Upload
		print("<td class=\"rowfollow\" align='left'><h3>".$bonusarray['name']."</h3>".$bonusarray['description']."</td><td class=\"rowfollow\" align='center'>".number_format($bonusarray['points'])."</td>");
	}

	if($CURUSER['seedbonus'] >= $bonusarray['points'])
	{
		if ($i==7){
			print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_karma_gift']."\" /></td>");
		}
		elseif ($i==8){
			if ($enablenoad_advertisement == 'yes' && get_user_class() >= $noad_advertisement)
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_class_above_no_ad']."\" disabled=\"disabled\" /></td>");
			elseif (strtotime($CURUSER['noaduntil']) >= TIMENOW)
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_already_disabled']."\" disabled=\"disabled\" /></td>");
			elseif (get_user_class() < $bonusnoad_advertisement)
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".get_user_class_name($bonusnoad_advertisement,false,false,true).$lang_mybonus['text_plus_only']."\" disabled=\"disabled\" /></td>");
			else
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_exchange']."\" /></td>");
		}
		elseif ($i==9){
			print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_charity_giving']."\" /></td>");
		}
		elseif($i==4)
		{
			if(get_user_class() < $buyinvite_class)
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".get_user_class_name($buyinvite_class,false,false,true).$lang_mybonus['text_plus_only']."\" disabled=\"disabled\" /></td>");
			else
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_exchange']."\" /></td>");
		}
		elseif ($i==6)
		{
			if (get_user_class() >= UC_VIP)
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['std_class_above_vip']."\" disabled=\"disabled\" /></td>");
			else
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_exchange']."\" /></td>");
		}
		elseif ($i==5)
			print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_exchange']."\" /></td>");
		else
		{
			if ($CURUSER['downloaded'] > 0){
				if ($CURUSER['uploaded'] > $dlamountlimit_bonus * 1073741824)//Uploaded amount reach limit
					$ratio = $CURUSER['uploaded']/$CURUSER['downloaded'];
				else $ratio = 0;
			}
			else $ratio = $ratiolimit_bonus + 1; //Ratio always above limit
			if ($ratiolimit_bonus > 0 && $ratio > $ratiolimit_bonus){
				print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['text_ratio_too_high']."\" disabled=\"disabled\" /></td>");
			}
			else print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['submit_exchange']."\" /></td>");
		}
	}
	else
	{
		print("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" value=\"".$lang_mybonus['text_more_points_needed']."\" disabled=\"disabled\" /></td>");
	}
	print("</form>");
	print("</tr>");
	
}
print("</tbody></table><br />");
?>

<table width="940" cellpadding="3"><thead>
<tr><th align="center"><font class="big"><?php echo $lang_mybonus['text_what_is_karma'] ?></font></th></tr></thead>
<tbody><tr><td class="text" align="left">
<?php
print("<h3>".$lang_mybonus['text_get_by_seeding']."</h3>");
print("<ul>");
if ($perseeding_bonus > 0)
	print("<li>".$perseeding_bonus.$lang_mybonus['text_point'].add_s($perseeding_bonus).$lang_mybonus['text_for_seeding_torrent'].$maxseeding_bonus.$lang_mybonus['text_torrent'].add_s($maxseeding_bonus).")</li>");
print("<li>".$lang_mybonus['text_bonus_formula_one'].$tzero_bonus.$lang_mybonus['text_bonus_formula_two'].$nzero_bonus.$lang_mybonus['text_bonus_formula_three'].$bzero_bonus.$lang_mybonus['text_bonus_formula_four'].$l_bonus.$lang_mybonus['text_bonus_formula_five']."</li>");
if ($donortimes_bonus)
	print("<li>".$lang_mybonus['text_donors_always_get'].$donortimes_bonus.$lang_mybonus['text_times_of_bonus']."</li>");
print("</ul>");

		$sqrtof2 = sqrt(2);
		$logofpointone = log(0.1);
		$valueone = $logofpointone / $tzero_bonus;
		$pi = 3.141592653589793;
		$valuetwo = $bzero_bonus * ( 2 / $pi);
		$valuethree = $logofpointone / ($nzero_bonus - 1);
		$timenow = strtotime(date("Y-m-d H:i:s"));
		$sectoweek = 7*24*60*60;
		$A = 0;
		$count = 0;
		$torrentres = sql_query("select torrents.id, torrents.added, torrents.size, torrents.seeders from torrents LEFT JOIN peers ON peers.torrent = torrents.id WHERE peers.userid = $CURUSER[id] AND peers.seeder ='yes' GROUP BY torrents.id")  or sqlerr(__FILE__, __LINE__);
		while ($torrent = mysql_fetch_array($torrentres))
		{
			$weeks_alive = ($timenow - strtotime($torrent[added])) / $sectoweek;
			$gb_size = $torrent[size] / 1073741824;
			$temp = (1 - exp($valueone * $weeks_alive)) * $gb_size * (1 + $sqrtof2 * exp($valuethree * ($torrent[seeders] - 1)));
			$A += $temp;
			$count++;
		}
		if ($count > $maxseeding_bonus)
			$count = $maxseeding_bonus;
		$all_bonus = $valuetwo * atan($A / $l_bonus) + ($perseeding_bonus * $count);
		$percent = $all_bonus * 100 / ($bzero_bonus + $perseeding_bonus * $maxseeding_bonus);
	print("<div align=\"center\">".$lang_mybonus['text_you_are_currently_getting'].round($all_bonus,3).$lang_mybonus['text_point'].add_s($all_bonus).$lang_mybonus['text_per_hour']." (A = ".round($A,1).")</div><table align=\"center\" border=\"0\" width=\"400\"><tr><td class=\"loadbarbg\" style='border: none; padding: 0px;'>");

	if ($percent <= 30) $loadpic = "loadbarred";
	elseif ($percent <= 60) $loadpic = "loadbaryellow";
	else $loadpic = "loadbargreen";
	$width = $percent * 4;
	print("<img class=\"".$loadpic."\" src=\"pic/trans.gif\" style=\"width: ".$width."px;\" alt=\"".$percent."%\" /></td></tr></table>");

print("<h3>".$lang_mybonus['text_other_things_get_bonus']."</h3>");
print("<ul>");
if ($uploadtorrent_bonus > 0)
	print("<li>".$lang_mybonus['text_upload_torrent'].$uploadtorrent_bonus.$lang_mybonus['text_point'].add_s($uploadtorrent_bonus)."</li>");
if ($uploadsubtitle_bonus > 0)
	print("<li>".$lang_mybonus['text_upload_subtitle'].$uploadsubtitle_bonus.$lang_mybonus['text_point'].add_s($uploadsubtitle_bonus)."</li>");
if ($starttopic_bonus > 0)
	print("<li>".$lang_mybonus['text_start_topic'].$starttopic_bonus.$lang_mybonus['text_point'].add_s($starttopic_bonus)."</li>");
if ($makepost_bonus > 0)
	print("<li>".$lang_mybonus['text_make_post'].$makepost_bonus.$lang_mybonus['text_point'].add_s($makepost_bonus)."</li>");
if ($addcomment_bonus > 0)
	print("<li>".$lang_mybonus['text_add_comment'].$addcomment_bonus.$lang_mybonus['text_point'].add_s($addcomment_bonus)."</li>");
if ($pollvote_bonus > 0)
	print("<li>".$lang_mybonus['text_poll_vote'].$pollvote_bonus.$lang_mybonus['text_point'].add_s($pollvote_bonus)."</li>");
if ($offervote_bonus > 0)
	print("<li>".$lang_mybonus['text_offer_vote'].$offervote_bonus.$lang_mybonus['text_point'].add_s($offervote_bonus)."</li>");
if ($funboxvote_bonus > 0)
	print("<li>".$lang_mybonus['text_funbox_vote'].$funboxvote_bonus.$lang_mybonus['text_point'].add_s($funboxvote_bonus)."</li>");
if ($ratetorrent_bonus > 0)
	print("<li>".$lang_mybonus['text_rate_torrent'].$ratetorrent_bonus.$lang_mybonus['text_point'].add_s($ratetorrent_bonus)."</li>");
if ($saythanks_bonus > 0)
	print("<li>".$lang_mybonus['text_say_thanks'].$saythanks_bonus.$lang_mybonus['text_point'].add_s($saythanks_bonus)."</li>");
if ($receivethanks_bonus > 0)
	print("<li>".$lang_mybonus['text_receive_thanks'].$receivethanks_bonus.$lang_mybonus['text_point'].add_s($receivethanks_bonus)."</li>");
if ($adclickbonus_advertisement > 0)
	print("<li>".$lang_mybonus['text_click_on_ad'].$adclickbonus_advertisement.$lang_mybonus['text_point'].add_s($adclickbonus_advertisement)."</li>");
if ($prolinkpoint_bonus > 0)
	print("<li>".$lang_mybonus['text_promotion_link_clicked'].$prolinkpoint_bonus.$lang_mybonus['text_point'].add_s($prolinkpoint_bonus)."</li>");
if ($funboxreward_bonus > 0)
	print("<li>".$lang_mybonus['text_funbox_reward']."</li>");
print('<li>' . $lang_mybonus['text_donations_reward'] . '</li>');
print($lang_mybonus['text_howto_get_karma_four']);
if ($ratiolimit_bonus > 0)
	print("<li>".$lang_mybonus['text_user_with_ratio_above'].$ratiolimit_bonus.$lang_mybonus['text_and_uploaded_amount_above'].$dlamountlimit_bonus.$lang_mybonus['text_cannot_exchange_uploading']."</li>");
print($lang_mybonus['text_howto_get_karma_five'].$uploadtorrent_bonus.$lang_mybonus['text_point'].add_s($uploadtorrent_bonus).$lang_mybonus['text_howto_get_karma_six']);
?>
</td></tr></tbody></table>
<?php
}

?>
<script type="text/javascript" src="js/mybonus.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" href="styles/jqui/ui-lightness/jquery-ui-1.8.16.custom.css" type="text/css" media="screen" />
<?php
stdfoot();
?>
