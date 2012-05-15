function getusertorrentlistajax(userid, type, blockid) {
    var target= $('#' + blockid);
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

function enabledel(msg){
    document.deluser.submit.disabled=document.deluser.submit.checked;
    alert (msg);
}

function disabledel(){
    document.deluser.submit.disabled=!document.deluser.submit.checked;
}

$(function() {
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
