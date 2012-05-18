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

    editPr();
    editPos();

    $('#reason-type').change(function() {
	var reasonDetail=$('#reason-detail');
	var val=parseInt(this.value);
	if(val===0) {
	    reasonDetail.fadeOut().removeClass('required');
	}
	else {
	    if(val===1||val===2) {
		reasonDetail.attr('placeholder','可选').removeClass('required').fadeIn();
	    }
	    else {
		reasonDetail.attr('placeholder','必填').addClass('required').removeClass('invalid').fadeIn();
	    }
	}
    });
    $('#reason-detail').hide();
});
