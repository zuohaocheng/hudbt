<?php
function bonusTextFromAction($do, $title='') {
  global $lang_mybonus;
  global $CURUSER;
  if ($title == '') {
    $title = $CURUSER['title'];
  }

  if ($do == "upload")
    $msg = $lang_mybonus['text_success_upload'];
  elseif ($do == "invite")
    $msg = $lang_mybonus['text_success_invites'];
  elseif ($do == "vip")
    $msg =  $lang_mybonus['text_success_vip']."<b>".get_user_class_name(UC_VIP,false,false,true)."</b>".$lang_mybonus['text_success_vip_two'];
  elseif ($do == "vipfalse")
    $msg =  $lang_mybonus['text_no_permission'];
  elseif ($do == "title") {
    $msg = $lang_mybonus['text_success_custom_title1'] . $title . $lang_mybonus['text_success_custom_title2'];
  }
  elseif ($do == "transfer")
    $msg =  $lang_mybonus['text_success_gift'];
  elseif ($do == "noad")
    $msg =  $lang_mybonus['text_success_no_ad'];
  elseif ($do == "charity")
    $msg =  $lang_mybonus['text_success_charity'];
  if ($do == "color")
    $msg = $lang_mybonus['text_success_color'];
  else
    $msg = '';
  return $msg;
}

function bonusarray($option) {
  global $onegbupload_bonus,$fivegbupload_bonus,$tengbupload_bonus,$oneinvite_bonus,$customtitle_bonus,$vipstatus_bonus, $basictax_bonus, $taxpercentage_bonus, $bonusnoadpoint_advertisement, $bonusnoadtime_advertisement;
  global $lang_mybonus;
  $bonus = array();
  switch ($option)
    {
    case 1: {//1.0 GB Uploaded
      $bonus['points'] = $onegbupload_bonus;
      $bonus['art'] = 'traffic';
      $bonus['menge'] = 1073741824;
      $bonus['name'] = $lang_mybonus['text_uploaded_one'];
      $bonus['description'] = $lang_mybonus['text_uploaded_note'];
      break;
    }
    case 2: {//5.0 GB Uploaded
      $bonus['points'] = $fivegbupload_bonus;
      $bonus['art'] = 'traffic';
      $bonus['menge'] = 5368709120;
      $bonus['name'] = $lang_mybonus['text_uploaded_two'];
      $bonus['description'] = $lang_mybonus['text_uploaded_note'];
      break;
    }
    case 3: {//10.0 GB Uploaded
      $bonus['points'] = $tengbupload_bonus;
      $bonus['art'] = 'traffic';
      $bonus['menge'] = 10737418240;
      $bonus['name'] = $lang_mybonus['text_uploaded_three'];
      $bonus['description'] = $lang_mybonus['text_uploaded_note'];
      break;
    }
    case 4: {//Invite
      $bonus['points'] = $oneinvite_bonus;
      $bonus['art'] = 'invite';
      $bonus['menge'] = 1;
      $bonus['name'] = $lang_mybonus['text_buy_invite'];
      $bonus['description'] = $lang_mybonus['text_buy_invite_note'];
      break;
    }
    case 5: {//Custom Title
      $bonus['points'] = $customtitle_bonus;
      $bonus['art'] = 'title';
      $bonus['menge'] = 0;
      $bonus['name'] = $lang_mybonus['text_custom_title'];
      $bonus['description'] = $lang_mybonus['text_custom_title_note'];
      break;
    }
    case 6: {//VIP Status
      $bonus['points'] = $vipstatus_bonus;
      $bonus['art'] = 'class';
      $bonus['menge'] = 0;
      $bonus['name'] = $lang_mybonus['text_vip_status'];
      $bonus['description'] = $lang_mybonus['text_vip_status_note'];
      break;
    }
    case 7: {//Bonus Gift
      $bonus['points'] = 25;
      $bonus['art'] = 'gift_1';
      $bonus['menge'] = 0;
      $bonus['name'] = $lang_mybonus['text_bonus_gift'];
      $bonus['description'] = $lang_mybonus['text_bonus_gift_note'];
      if ($basictax_bonus || $taxpercentage_bonus){
	$onehundredaftertax = 100 - $taxpercentage_bonus - $basictax_bonus;
	$bonus['description'] .= "<br /><br />".$lang_mybonus['text_system_charges_receiver']."<b>".($basictax_bonus ? $basictax_bonus.$lang_mybonus['text_tax_bonus_point'].add_s($basictax_bonus).($taxpercentage_bonus ? $lang_mybonus['text_tax_plus'] : "") : "").($taxpercentage_bonus ? $taxpercentage_bonus.$lang_mybonus['text_percent_of_transfered_amount'] : "")."</b>".$lang_mybonus['text_as_tax'].$onehundredaftertax.$lang_mybonus['text_tax_example_note'];
      }
      break;
    }
    case 8: {
      $bonus['points'] = $bonusnoadpoint_advertisement;
      $bonus['art'] = 'noad';
      $bonus['menge'] = $bonusnoadtime_advertisement * 86400;
      $bonus['name'] = $bonusnoadtime_advertisement.$lang_mybonus['text_no_advertisements'];
      $bonus['description'] = $lang_mybonus['text_no_advertisements_note'];
      break;
    }
    case 9: {
      $bonus['points'] = 1000;
      $bonus['art'] = 'gift_2';
      $bonus['menge'] = 0;
      $bonus['name'] = $lang_mybonus['text_charity_giving'];
      $bonus['description'] = $lang_mybonus['text_charity_giving_note'];
      break;
    }
    case 10: {
    	$bonus['points'] = 8000;
    	$bonus['art'] = 'color';
      $bonus['menge'] = 0;
      $bonus['name'] = $lang_mybonus['text_change_color'];
      $bonus['description'] = $lang_mybonus['text_change_color_note'];
      break;
    	}
    default: break;
    }
  return $bonus;
}
?>