//viewpeerlist.js
function viewpeerlist(torrentid) {
    var list=ajax.gets('viewpeerlist.php?id='+torrentid);
    document.getElementById("showpeer").style.display = 'none';
    document.getElementById("hidepeer").style.display = 'block';
    document.getElementById("peercount").style.display = 'none';
    document.getElementById("peerlist").innerHTML=list;
}
function hidepeerlist() {
    document.getElementById("hidepeer").style.display = 'none';
    document.getElementById("peerlist").innerHTML="";
    document.getElementById("showpeer").style.display = 'block';
    document.getElementById("peercount").style.display = 'block';
}

$(function() {
    $('#kfilelist table').tablesorter();
    $('#saythanks:enabled').click(function() {
	var argsFromUri = function(uri) {
	    var args = new Object();
	    var query = uri.split('?');
	    if (query.length !== 2) {
		return [];
	    }
	    $.each(query[1].split('&'), function(idx, obj) {
		var t = obj.split('=');
		args[t[0]] = t[1];
	    });
	    return args;
	};
	var args = argsFromUri(window.location.search);

	var torrentid = args.id;
	var list=$.post('thanks.php', { id : torrentid});
	document.getElementById("thanksbutton").innerHTML = document.getElementById("thanksadded").innerHTML;
	document.getElementById("nothanks").innerHTML = "";
	document.getElementById("addcuruser").innerHTML = document.getElementById("curuser").innerHTML;
    });
});