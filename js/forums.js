$(function() {
    var args = argsFromUri(location.href);
    if (args.action === 'viewforum') {
	$('.headerSort').click(function(e) {
	    e.preventDefault();
	    var href = $(this).find('a').attr('href');
	    window.location.href = href;
	});
    }
});