$(function() {
    $('#kfilelist table').tablesorter();
    $('#saythanks:enabled').click(function() {
	var args = argsFromUri(window.location.search);

	var torrentid = args.id;
	var list=$.post('thanks.php', { id : torrentid});
	document.getElementById("thanksbutton").innerHTML = document.getElementById("thanksadded").innerHTML;
	document.getElementById("nothanks").innerHTML = "";
	document.getElementById("addcuruser").innerHTML = document.getElementById("curuser").innerHTML;
    });

    var showpeer = $('#showpeer');
    var hidepeer = $('#hidepeer');
    var peercount = $('#peercount');
    var peerlist = $('#peerlist');

    var showpeerlist = function(href) {
	$.get(href, function(res) {
	    peerlist.html(res);
	    peerlist.slideDown();
	    peercount.slideUp();
	    showpeer.fadeOut(function() {
		hidepeer.fadeIn();
	    });
	    $('#peerlist table').tablesorter();
	}, 'html');
    };
    showpeer.click(function(e) {
	e.preventDefault();
	showpeerlist(this.href);
    });

    hidepeer.click(function(e) {
	e.preventDefault();
	peerlist.slideUp();
	peercount.slideDown();
	hidepeer.fadeOut(function() {
 	    showpeer.fadeIn();
	});
    });

    if (argsFromUri(window.location.search).dllist) {
	showpeerlist(showpeer.attr('href'));
    }
});