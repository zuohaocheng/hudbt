$(function() {
    $('#funvote form').submit(function(e) {
	e.preventDefault();
	$.post(this.action + '&format=json' , $(this).serialize(), function(res) {
	    if (res.success) {
		$("#funvote-total").text(res.total);
		$("#funvote-fun").text(res.fun);
		$("#funvote-form").hide();
	    }
	});
    });
});
