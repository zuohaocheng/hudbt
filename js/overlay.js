(function() {
    var dt = new Date();
    if (dt.getTime() > 1335715200000) {
	return;
    }
    var stime = $.jStorage.get('timeOverlay', -1);
    if (stime !== -1) {
	var today = dt.getDay();
	if (stime == today) {
	    return;
	}
    }
    $.jStorage.set('timeOverlay', dt.getDay());
    $('body').prepend('<div style="display: block; " id="curtain" class="curtain"></div><div style="display: block;" id="lightbox" class="lightbox"><img src="pic/overlay.jpg" usemap="#ad-overlay-map"><map name="ad-overlay-map"><area shape="circle" coords ="119,95,63" href="#" title="Close" alt="Close" /><area shape="circle" coords ="525,542,63" href="/forums.php?action=viewtopic&amp;forumid=21&amp;topicid=14431" title="Details" alt="Details" /></map></div>');
})();