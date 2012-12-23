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

    $('#donater-hidden-show').click(function(e) {
	e.preventDefault();
	$('#donater-hidden').slideToggle();
    });

    $('#to_donate a').each(function() {
	$(this).click(function() {
	    var topicid = hb.topic.id;
	    var bonus   = parseInt(hb.config.user.bonus);
	    var to_donate = parseInt($(this).html());
	    if(bonus < to_donate) {
		alert('你的魔力值不足，谢谢你的好心，继续努力吧~');
	    } else if(confirm('确认向楼主捐赠 ' + to_donate +' 魔力值吗？')) {
		var url = '/donateBonus.php';
		var data = {amount: to_donate, topicid : topicid, type: 'topic'};
		$.post(url, data, function(data) {
		    if(data.status == 9) {
			var newDonate = '<div class="donate'+ data.amount +' donate" id="donated_successfully" title="[' + data.amount + ' 魔力值] ' + data.date + '">' + data.donater + '</div><div style="clear:both;"></div>';
			if($('#donater_list div').size() == 0) {
			    $('#donater_list').html(newDonate);
			} else {
			    $('#donater_list :last-child').remove();
			    $('#donater_list').append(newDonate);
			}
			$('#to_donate').html("你已经于 " + data.date + " 对楼主进行过魔力值捐赠，谢谢你！");
		    } else if(data.status == 1) {
			alert('谢谢你，但是你的魔力值不足，继续努力吧。');
		    } else if(data.status == 2) {
			alert('你要捐赠主题不存在。');
		    } else if(data.status == 3) {
			alert('你要捐赠的用户不存在。');
		    } else if(data.status == 4) {
			alert('只允许以下几个数量的捐赠额：8, 16, 32, 64, 128。');
		    } else if(data.status == 5) {
			alert('不能给自己捐赠的哦！');
		    } else if(data.status == 6) {
			alert('你已经捐赠过了，谢谢！');
		    } else {
			alert('貌似系统出问题了，呼管理员！');
		    }
		}, 'json');
	    }
	});
    });
});