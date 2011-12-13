$(function() {
    var posting = false;
    var submits = $('#outer :submit:enabled');

    $('#outer form').each(function() {
	var form = $(this);
	var method = form.attr('method').toLocaleLowerCase();
	form.submit(function(e) {
	    e.preventDefault();
	    if (posting) {
		return;
	    }
	    posting = true;
	    submits.attr('disabled', 'disabled');

	    var query = form.serializeArray();
	    $.post('takebonusexchange.php?format=json', query, function(result) {
		posting = false;
		submits.removeAttr('disabled');
		$target = $('#mybonus-result-text');
		if (result.success) {
		    $target.html(result.text);
		    $('#bonus, .bonus').text(result.bonus);
		    $('#uploaded').text(result.uploaded);
		    $('#invites').text(result.invites);
		}
		else {
		    $target.html(result.text + '<br />' + result.desc);
		}
		$target.attr('title', result.title);
		$target.html(result.text);
		$target.dialog({modal : true});
		setTimeout(function(){$target.dialog("close")},5000);
	    }, 'json');
	});
    });

    var txtCustom = document.getElementById("giftcustom");
    $('#giftselect').change(function() {
	if (this.value == '0'){
	    txtCustom.disabled = false;
	    txtCustom.focus();
	}
	else {
	    txtCustom.disabled = true;
	    txtCustom.value = '';
	}
    });
});