$(function() {
    var click = function(e) {
	e.preventDefault();
	$.getJSON(this.href, {format : 'json'}, function(res) {
	    document.title = res.title;
	    $('#page-title').html(res.h1);
	    $('#contents').html(res.content);
	    $('#navbar li').each(function(idx, obj) {
		if (idx === res.navbar[2]) {
		    var span = $('<span></span>', {
			text : res.navbar[0][idx],
			'class' : 'selected'
		    });
		    $(this).html('').append(span);
		}
		else {
		    var a = $('<a></a>', {
			text : res.navbar[0][idx], 
			href : '?action=' + res.navbar[1][idx] + '&id=' + hb.config.user.id
		    }).click(click);
		    $(this).html('').append(a);
		}
	    });
	});
    };
    $('#navbar a').click(click);
});