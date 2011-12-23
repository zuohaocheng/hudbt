$(function() {
    $('#funvote').find('form').submit(function(e) {
	e.preventDefault();
	$.post(this.action + '&format=json' , $(this).serialize(), function(res) {
	    if (res.success) {
		$("#funvote").hide();
		$("#voteaccept").show();
	    }
	});
    });
});
