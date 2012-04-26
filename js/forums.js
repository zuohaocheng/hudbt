$(function() {
    var args = argsFromUri(location.href);
    if (args.action === 'viewforum') {
	$('.headerSort').click(function(e) {
	    e.preventDefault();
	    var href = $(this).find('a').attr('href');
	    window.location.href = href;
	});
    }

    $('#forum-movetopic').submit(function(e) {
	e.preventDefault();
	var form = $(this);
	var targetF = form.find('[name="forumid"] :selected');
	if (targetF.length === 0) {
	    jqui_dialog('失败', '无目标板块', 3000);
	}
	else {
	    jqui_confirm('移动主题', '确认将本主题移动到' + targetF.text() + '么?', function() {
		var data = form.serializeArray();
		data.push({
		    name : 'format',
		    value : 'json'
		});
		$.post(form.attr('action'), data, function(result) {
		    if (result.success) {
			jqui_dialog('移动成功', '成功移动主题', 3000);
		    }
		});
		return true;
	    });
	}
    });
});