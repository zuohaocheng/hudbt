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
});