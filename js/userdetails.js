function getusertorrentlistajax(type, blockid) {
    var target= $('#' + blockid),
    userid = hb.user.id;
    if (target.html()==""){
	$.get('getusertorrentlistajax.php?userid='+userid+'&type='+type, function(result) {
	    target.html(result);
	    target.find('table').tablesorter();
	    klappe_news(blockid.substr(1));
	}); 
    }
    else {
	klappe_news(blockid.substr(1));
    }
}

$(function() {
    $('#delenable').click(function() {
	var self = this,
	submit = $('#del-user :submit')[0];
	if (this.checked) {
	    jqui_confirm('人死不能复生', hb.constant.pagelang.js_delete_user_note, function() {
		submit.disabled = false;
		return true;
	    }, function() {
		self.checked = false;
	    });
	}
	else {
	    submit.disabled = true;
	}
    });

    $('#send-bonus').click(function(e) {
	e.preventDefault();
	var form = $('<form></form>', {
	    html : '<input type="hidden" name="option" value="7"><input type="hidden" name="userid" value="'+ hb.user.id +'" /><ul><li><label>赠送魔力值数量: <input type="number" name="bonusgift" min="25" max="10000" value="1000" required /></label></li><li><label>留言: <input type="text" name="message" maxlength="100" /></label></li></ul>',
	    'class' : 'minor-list',
	    action : '//' + hb.constant.url.base + '/takebonusexchange.php?format=json',
	    method : 'post'
	});
	jqui_form(form, '赠送魔力值', function(result, dialog) {
	    if (result.success) {
		$('#bonus, .bonus').text(result.bonus);
		$('#uploaded').text(result.uploaded);
		$('#invites').text(result.invites);
		jqui_dialog(result.title, result.text, 3000);
		return true;
	    }
	    else {
		$('#dialog-hint').html(result.text);
		return false;
	    }
	});
    });
});
