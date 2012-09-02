$(function() {
    var posting = false;
    var submits = $('#outer :submit:enabled');

    var dialog = $('<div></div>', {
	title : '确认',
	text : '你确定要交换该项魔力值么?'
    }).dialog({
	modal : true,
	autoOpen : false
    });

    $('#outer form').submit(function(e) {
	e.preventDefault();
	if (posting) {
	    return;
	}
	var form = $(this);
	var price = form.find('.bonus-price').text();
	var text;
	if (price) {
	    text = '你确定要花费' + price  + '魔力值交换'  + form.find('.bonus-title').text() + '么?';
	}
	else {
	    var text = '你确定要'  + form.find('.bonus-title').text() + '么?';
	}
	dialog.text(text).dialog('option', 'buttons', {
	    OK : function() {
		posting = true;
		submits.attr('disabled', 'disabled');

		var query = form.serializeArray();
		$.post('takebonusexchange.php?format=json', query, function(result) {
		    posting = false;
		    submits.removeAttr('disabled');
		    $target = $('#mybonus-result-text');
		    if (result.success) {
			$('#bonus, .bonus').text(result.bonus);
			$('#uploaded').text(result.uploaded);
			$('#invites').text(result.invites);
		    }
		    $target.html(result.text);
		    $target.dialog({
			title : result.title,
			modal : true
		    });
		    setTimeout(function(){$target.dialog("close")},5000);
		}, 'json');
		dialog.dialog("close");
	    },
	    Cancel: function() {
		dialog.dialog("close");
	    }
	}).dialog('open');
    });

    var giftSelect = $('#giftselect');
    var txtCustom = $("#giftcustom");
    var giftSubmit = $('#bonus-7 :submit');
    giftSelect.change(function() {
	if (this.value == '0'){
	    txtCustom.show().removeAttr('disabled').keyup(validateGift);
	    txtCustom.focus();
	}
	else {
	    txtCustom.hide().attr('disabled', 'disabled').unbind('keyup');
	    txtCustom.val('');
	}
	validateGift();
    });
    var validateGift = function() {
	var amount = giftSelect.val();
	if (amount == 0) {
	    amount = txtCustom.val();
	}
	amount = parseInt(amount);
	if (amount > parseInt(hb.config.user.bonus)) {
	    giftSubmit.attr('disabled', 'disabled').val('再去多赚点吧');
	}
	else if (amount < 25) {
	    giftSubmit.attr('disabled', 'disabled').val('这么一点能拿得出手?');
	}
	else if(amount > 10000) {
	    giftSubmit.attr('disabled', 'disabled').val('不要太大方了嘛');
	}
	else if (!$('#gift-username').val()) {
	    giftSubmit.attr('disabled', 'disabled').val('要送给哪位亲呢?');
	}
	else {
	    giftSubmit.removeAttr('disabled').val('赠送');
	}
    };
    $('#gift-username').keyup(validateGift);
    validateGift();

    var karmaSubmit = $('#bonus-9 :submit');
    var karmaSelect = $('#charityselect');
    var validateKarma = function() {
	var amount = parseInt(karmaSelect.val());
	if (amount > parseInt(hb.config.user.bonus)) {
	    karmaSubmit.attr('disabled', 'disabled').val('先养活自己吧亲');
	}
	else {
	    karmaSubmit.removeAttr('disabled').val('赠送');
	}
    };
    karmaSelect.change(validateKarma);
    validateKarma();

    (function() {
	var color = $('.color')[0];
	if (color.type !== 'color') { // type=color supported
	    console.log(color.value);
	    color.value = color.value.replace(/#/, '');
	    console.log(color.value);
	    jscolor.bind();
	}
    })();
});