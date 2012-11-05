$(function() {
    var lang = hb.constant.lang;
    var base = hb.constant.url.base;
    var title = $('title');
    var origTitle = title.text();
    var checkInt = 10;
    var alertMsg = $('#alert-message');
    var dialogOpen = false;

    var localStorageReallyWorks = false;
    if("localStorage" in window){
        try {
            window.localStorage.setItem('_tmptest', 'tmpval');
            localStorageReallyWorks = true;
            window.localStorage.removeItem('_tmptest');
        } catch(BogusQuotaExceededErrorOnIos5) {
            // Thanks be to iOS5 Private Browsing mode which throws
            // QUOTA_EXCEEDED_ERRROR DOM Exception 22.
        }
    }

    var markread = function(id, callback) {
	$.post('//' + base + '/messages.php?format=json&action=moveordel', {
	    'messages[]' : id,
	    'markread' : true
	}, callback);
    };

    var bindClick = function(alertMsg, result) {
	if (location.pathname.split('/').pop() !== 'messages.php') {
	    var click = function(result) {
		if (!dialogOpen) {
		    var text = '<table cellpadding="2" class="no-vertical-line" style="width:100%"><thead><th>主题</th><th>发信人</th><th class="unsortable"></th><th class="unsortable"></th><th class="unsortable">预览 (点击显示全部)</th></thead><tbody></tbody></table>'; 
		    var dialog = $('<div></div>', {
			title : '站内信 (<a href="//' + base + '/messages.php">打开传统界面</a>)',
			html : text
		    });
		    var tbody = dialog.find('tbody');
		    $.each(result, function() {
			var msg = this;
			var t = '<tr sender="' + msg.sender.id + '" msgid="' + msg.id + '"><td><a href="//' + base + '/messages.php?action=viewmessage&amp;id=' + msg.id + '" title="' + msg.added + '" class="alert-message-subject">' + msg.subject + '</a></td><td>';
			if (msg.sender.id !== 0) {
			    var userClass = msg.sender['class'].canonical;
			    var userCss = userClass.replace(/\s/g, '') + '_Name username';
			    t += '<a href="//' + base + '/userdetails.php?id=' + msg.sender.id + '" class="' + userCss + '">';
			}
			t += msg.sender.username;
			if (msg.sender.id !== 0) {
			    t += '</a>';
			}
			t += '</td><td>';
			t += '<a href="#" class="alert-message-read">已读</a>';
			t += '</td><td>';
			if (msg.sender.id !== 0 && msg.sender.id != hb.config.user.id) {
			    var replyUri = '//' + base + '/sendmessage.php?receiver=' + msg.sender.id + '&amp;replyto=' + msg.id;
			    t += '<a href="' + replyUri + '" class="alert-message-reply">' + '回复' + '</a>';
			}
			t += '</td><td style="position:relative;zoom:1" title="点击显示全部"><div class="alert-message-body">' + msg.msg + '</div></td></tr>';
			var $t = $(t);
			$t.find('.alert-message-reply').click(function(e) {
			    e.preventDefault();
			    jqui_form($('<form action="//' + base + '/takemessage.php?format=json&receiver=' + msg.sender.id + '&subject=Re%3A' + encodeURIComponent(msg.subject) + '&origmsg=' + msg.id + '" style="height:200px"><textarea name="body" style="width:100%;height:100%" autofocus="autofocus" required="required">' + msg.msg_bbcode + "\n\n-------- [user=" + msg.sender.id + '] [i] Wrote at ' + msg.added + ":[/i] --------\n\n</textarea></form>"), 'Re:' + msg.subject + ' (<a href="' + replyUri + '">打开完整编辑</a>)', function(result, dialog) {
				if (!result.success) {
				    dialog.find('#dialog-hint').text(result.message);
				    return false;
				}
				else {
				    markread(msg.id, function(result) {
					if (result.success) {
					    $t.remove();
					}
				    });
				    return true;
				}
			    }, null, 500);
			});
			
			$t.find('.alert-message-read').click(function(e) {
			    e.preventDefault();
			    markread(msg.id, function(result) {
				if (result.success) {
				    $t.remove();
				}
				if (tbody.find('tr').length === 0) {
				    dialogOpen = false;
				    dialog.remove();
				}
			    });
			})

			var showAll = false;
			$t.find('.alert-message-body').click(function() {
			    if (showAll) {
				return;
			    }
			    showAll = true;
			    $(this).parent().prepend($('<div style="border:dashed silver 1px;position:absolute;top:0;background-color:white;z-index:999;padding:0.5em;border-radius:3px;display:none;">' + msg.msg + '</div>').show().click(function() {
				$(this).remove();
				showAll = false;
			    }));
			    markread(msg.id, function(result) {
				if (result.success) {
				    $t.find('.alert-message-read').remove();
				}
			    });
			});
			tbody.append($t);
		    });

		    dialogOpen = true;
		    dialog.dialog({
			autoOpen : true,
			modal : true,
			width : '70%',
			'close' : function() {
			    dialogOpen = false;
			    dialog.remove();
			}
		    }).find('table').tablesorter();
		}
	    };
	    alertMsg.unbind('click').click(function(e) {
		e.preventDefault();
		if (typeof(result) === 'undefined') {
		    getResult(click);
		}
		else {
		    click(result);
		}
	    });
	}
    };

    var processResult = function (result) {
	if (result.length !== 0) {
	    title.text('(' + result.length + ') ' + origTitle);
	    if (alertMsg.length === 0) {
		alertMsg = $('<li></li>', {
		    style : 'background-color: red',
		    id : 'alert-message'
		}).append($('<a></a>', {
		    href : '//' + base + '/messages.php'
		}));
		$('#alert').append(alertMsg);
	    }
	    
	    alertMsg.find('a').text(lang.text_you_have + result.length + lang.text_new_message + lang.text_click_here_to_read);
	    bindClick(alertMsg, result);
	}
	else {
	    alertMsg.remove();
	    title.text(origTitle);
	}
    };

    var getResult = function(callback) {
	if (localStorageReallyWorks) {
	    var lastGet = localStorage.getItem('pmGetTime');
	    if (lastGet && ((new Date()).getTime() - parseInt(lastGet)) < checkInt * 1000 ) {
		setTimeout(checkMsg, checkInt * 1000);
		callback($.parseJSON(localStorage.getItem('pmResult')));
		return;
	    }
	}

	$.getJSON('//' + base + '/messages.php?format=json&unread=yes', {'time' : (new Date()).getTime()}, function (result) {
	    callback(result);
	    if (localStorageReallyWorks) {
		localStorage.setItem('pmGetTime', (new Date().getTime()));
		localStorage.setItem('pmResult', $.toJSON(result));
	    }
	    setTimeout(checkMsg, checkInt * 1000);
	});
    };

    var checkMsg = function() {
	getResult(processResult);
    };
    bindClick($('#alert-message'));
    setTimeout(checkMsg, checkInt * 1000);
});