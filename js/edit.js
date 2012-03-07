$(function() {
    $team_sel = $("select[name=team_sel]");
    $team_sel.change(function() {
	if ($team_sel.find("option:selected").val() === '1') {
	    $("#sel_oday").attr("checked", true);
	}
	else {
	    $("#sel_oday").attr("checked", false);
	}
	return true;
    });

    $pr_type = $('select[name="sel_spstate"]');
    if ($pr_type.length) {
	$pr_time_type = $('select[name="promotion_time_type"]').attr("disabled", "disabled");
	$pr_time = $('#promotionuntil').attr("disabled", "disabled");

	var validatePrTimeType = function() {
	    if ($pr_type.find("option:selected").val() === '1') {
		$pr_time_type.attr("disabled", "disabled");
	    }
	    else {
		$pr_time_type.removeAttr("disabled");
	    }
	    validatePrTime();
	};

	var validatePrTime = function() {
	    if ($pr_time_type.find("option:selected").val() !== '2') {
		$pr_time.attr("disabled", "disabled");
		$("#expand-pr").slideUp();
	    }
	    else {
		$pr_time.removeAttr("disabled");
		$("#expand-pr").slideDown();
	    }
	};

	validatePrTimeType();

	$pr_type.change(validatePrTimeType);
	$pr_time_type.change(validatePrTime);

	function str2date(string) {
	    var default_date = new Date();
	    
	    date_part = string.split(' ')[0].split('-');
	    time_part = string.split(' ')[1].split(':');
	    
	    default_date.setFullYear(date_part[0]);
	    default_date.setMonth(date_part[1] - 1);
	    default_date.setDate(date_part[2]);
	    default_date.setHours(time_part[0]);
	    default_date.setMinutes(time_part[1]);
	    default_date.setSeconds(time_part[2]);
	    
	    return default_date;
	}

	var timeBox = $pr_time;
	var default_unixtime = str2date(timeBox.attr('value'));

	$('#delay_promotion_time').click(function() {
	    var new_date = new Date();

	    var day = $('#time_select_day').attr('value');
	    var hour = $('#time_select_hour').attr('value');
	    var minute = $('#time_select_minute').attr('value');;

	    // 换算成毫秒
	    var time_period = (day * 24 * 3600 + hour * 3600 + minute * 60) * 1000;
	    
	    new_date.setTime(default_unixtime.valueOf() + time_period);

	    timeBox.attr('value', new_date.getFullYear() 
			 + '-' + (new_date.getMonth() + 1) 
			 + '-' + new_date.getDate() 
			 + ' ' + new_date.getHours() 
			 + ':' + new_date.getMinutes() 
			 + ':' + new_date.getSeconds());

	    return false;
	});
    }
});