$(function() {
    $('#kfilelist table').tablesorter();
    $('#saythanks:enabled').click(function() {
	var torrentid = hb.torrent.id;
	var list=$.post('thanks.php', { id : torrentid});
	document.getElementById("thanksbutton").innerHTML = document.getElementById("thanksadded").innerHTML;
	document.getElementById("nothanks").innerHTML = "";
	document.getElementById("addcuruser").innerHTML = document.getElementById("curuser").innerHTML;
    });

    var showpeer = $('#showpeer');
    var hidepeer = $('#hidepeer');
    var peercount = $('#peercount');
    var peerlist = $('#peerlist');

    var showpeerlist = function(href, scrollToTable) {
	$.get(href, function(res) {
	    peerlist.html(res);
	    peerlist.slideDown();
	    peercount.slideUp();
	    showpeer.fadeOut(function() {
		hidepeer.fadeIn();
	    });
	    $('#peerlist table').tablesorter();
	    if (scrollToTable && window.location.hash) {
    		var scrollTar = $(window.location.hash);
		if (scrollTar.length) {
    		    var top = scrollTar.offset().top - 50;
    		    scrollToPosition(top);
		}
	    }
	}, 'html');
    };
    showpeer.click(function(e) {
	e.preventDefault();
	showpeerlist(this.href);
    });

    hidepeer.click(function(e) {
	e.preventDefault();
	peerlist.slideUp();
	peercount.slideDown();
	hidepeer.fadeOut(function() {
 	    showpeer.fadeIn();
	});
    });

    if (argsFromUri(window.location.search).dllist) {
	showpeerlist(showpeer.attr('href'), true);
    }
});

//TorrentDonate
$(function() {
    $('#to_donate a').click(function(e) {
	e.preventDefault();
	var torrent_id = hb.torrent.id;
	var bonus      = parseInt(hb.config.user.bonus);
	var to_donate = parseInt($(this).html());
	if(bonus < to_donate) {
	    alert('你的魔力值不足，谢谢你的好心，继续努力吧~');
	} else if(confirm('确认向种子发布者捐赠 ' + to_donate +' 魔力值吗？')) {
	    var url = 'donateBonus.php';
	    var data = {amount: to_donate, torrent_id : torrent_id, type: 'torrent'};
	    $.post(url, data, function(data) {
		if(data.status == 9) {
		    var newDonate = '<div class="donate'+ data.amount +' donate" id="donated_successfully" title="' + data.message + '\n[' + data.amount + ' 魔力值] ' + data.date + '">' + data.donater + '</div>';
		    $('#donater_list').append(newDonate);

		    $('#to_donate').html("你已经于 " + data.date + " 对种子发布者进行过魔力值捐赠，谢谢你！");
		} else if(data.status == 1) {
		    alert('谢谢你，但是你的魔力值不足，继续努力吧。');
		} else if(data.status == 2) {
		    alert('你要捐赠种子不存在。');
		} else if(data.status == 3) {
		    alert('你要捐赠的用户不存在。');
		} else if(data.status == 4) {
		    alert('只允许以下几个数量的捐赠数：64, 128, 256, 512, 1024。');
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

// Tcategories
$(function() {
    var form = $('#tcategories');
    var cake = '//' + hb.constant.url.cake + '/';

    var validating = false;
    var tcategory = function(inputs) {
	inputs.each(function() {
	    var $inputs = $(this);
	    var input = $inputs.find(':text');
	    var in_id = $inputs.find(':input[type="hidden"]');
	    //Auto complete
	    var cache = {};
	    var lastXhr;

	    var input_validate = function() {
		$inputs.removeClass('invalid-ref');

		var catName = this.value;
		if (catName !== '') {
		    validating = true;
		    $.getJSON(cake + "tcategories/search/exact:1/" + catName, function(result) {
			var validation = true;
			if (result.length === 1) {
			    var resultId = result[0].Tcategory.id;
			    form.find(':input[type="hidden"]').each(function() {
				if (resultId == this.value && this !== in_id[0]) {
				    $(this).parent().addClass('invalid-ref');
				    validation = false;
				}
			    });

			    $inputs.find('.remove-tcategory').fadeIn();
			}
			else {
			    validation = false;
			}

			if (validation) {
			    input.attr('value', result[0].Tcategory.name);
			    $inputs.removeClass('invalid');
			    in_id.attr('value', result[0].Tcategory.id);
			    lastTcategoryEvent();
			}
			else {
			    input.focus();
			    $inputs.addClass('invalid');
			}
			validating = false;
		    });
		}
		else {
		    $inputs.removeClass('invalid');
		    in_id.attr('value', '');
		    if (form.find('ul li:last')[0] !== $inputs[0]) {
			$inputs.remove();
		    }
		}
	    };
	    input.blur(function() {
		var t = this;
		setTimeout(function() {
		    input_validate.call(t);
		}, 100);
	    });
	    input.autocomplete({
		source: function( request, response ) {
		    var term = request.term;
		    if ( term in cache ) {
			response( cache[ term ] );
			return;
		    }

		    lastXhr = $.getJSON(cake + "tcategories/search/", request, function( data, status, xhr ) {
			data = $.map(data, function(item) {
			    return item.Tcategory.name;
			});

			cache[ term ] = data;
			if ( xhr === lastXhr ) {
			    response( data );
			}
		    });
		},
	    });
	});

	inputs.find('.remove-tcategory').click(removeTcategory);
	inputs.find('.edit-tcategory').click(function(e) {
	    e.preventDefault();
	    var aSpan = $(this).parent();
	    var li = aSpan.parent();
	    aSpan.remove();
	    
	    li.find('.tcategory-edit').fadeIn();

	    form.find(':submit').fadeIn();
	    $('#hidden-tcategories').slideDown();
	});

	return inputs;
    };

    var removeTcategory = function(e) {
	e.preventDefault();
	$(this).parent().parent().remove();
    };

    tcategory(form.find('.tcategory'));

    form.submit(function(e) {
	e.preventDefault();
	var params = form.serializeArray();
	var warning = function() {
	    if (validating) {
		setTimeout(warning, 200);
		return;
	    }

	    if (form.find('.tcategory.invalid').length !== 0) {
		e.preventDefault();
		var dialog = $('<div></div>', {
		    title : '警告',
		    text : '存在无效字段'
		}).dialog({
		    modal : true,
		    autoOpen : true,
		});
		setTimeout(function() {
		    dialog.dialog('close');
		}, 3000);
	    }
	    else {
		$.post(form.attr('action'), params, function(result) {
		    if (result.success) {
			form.find(':submit').hide();
			form.find('#add-tcategory').show();
			var shows ='';
			var hiddens = '';
			$.each(result.tcategories, function() {
			    var o = generateLi(this);
			    if (this.hidden) {
				hiddens += o;
			    }
			    else {
				shows += o;
			    }
			});
			
			if (hiddens !== '') {
			    shows += '<div style="display: none" id="hidden-tcategories">隐藏分类: ' + hiddens + '</div>';
			    addShowHiddens();
			}

			tcategory(form.find('ul').html(shows).find('.tcategory'));
		    }
		    else {
			var dialog = $('<div></div>', {
			    title : '警告',
			    text : result.message
			}).dialog({
			    modal : true,
			    autoOpen : true,
			});
			setTimeout(function() {
			    dialog.dialog('close');
			}, 3000);
		    }
		}, 'json');
	    }
	};

	warning();
    });


    var lastTcategoryEvent = function() {
	if (validating) {
	    setTimeout(lastTcategoryEvent, 100);
	    return;
	}

	if (form.find('ul li:last input[type="hidden"]').val().length !== 0) {
	    addTcategory();
	    lastTcategory();
	}
    };
    var lastTcategory = function() {
	form.find('ul :text').unbind('blur', lastTcategoryEvent);
	form.find('ul li:last :text').blur(lastTcategoryEvent);
    };

    var addTcategory = function(e) {
	if (e) {
	    e.preventDefault();
	}

	form.find('ul').append(tcategory($('<li></li>', {
	    'class' : 'tcategory'
	}).append($('<input />', {
	    type : 'text',
	    placeholder : 'Tcategory'
	})).append($('<input />', {
	    type : 'hidden',
	    name : 'data[Tcategory][Tcategory][]'
	})).append($('<a></a>', {
	    'class' : 'remove-tcategory',
	    href : '#',
	    text : '-',
	    style : 'display:none;'
	}).click(removeTcategory))));
	lastTcategory();
    };

    var addShowHiddens = (function() {
	var added = false;
	return function() {
	    if (added) {
		return;
	    }

	    added = true;
	    $('#tcategories-title').append('<br />').append($('<a></a>', {
		href : '#',
		text : '[显示隐藏分类]',
		'class' : 'sublink'
	    }).click(function(e) {
		e.preventDefault();
		$('#hidden-tcategories').slideToggle();
	    }));
	}
    })();

    var clickAdd = function(e) {
	addTcategory(e);
	$(this).hide();
	form.find(':submit').fadeIn();
	$('#hidden-tcategories').slideDown();
    };
    form.find('#add-tcategory').click(clickAdd);
    if (form.find('.tcategory').length === 0) {
	clickAdd();
    }
    else if ($('#hidden-tcategories').length !== 0) {
	addShowHiddens();
    }

    var generateLi = function(tcategory) {
	return '<li class="tcategory"><span class="tcategory-show"><a href="//' + cake + '/tcategories/view/' + tcategory.id + '">' + tcategory.showName + '</a><a href="#" class="edit-tcategory">±</a></span><span class="tcategory-edit" style="display: none;"><input type="text" placeholder="Tcategory" value="' + tcategory.showName + '" /><input type="hidden" name="data[Tcategory][Tcategory][]" value="'  + tcategory.id + '"/ ><a href="#" class="remove-tcategory">-</a></span></li>'
    };
});

$(function() {
     $('#set-pr').click(function(e) {
	e.preventDefault();
	 editTorrent(hb.torrent.id);
     });

    $('#torrent-delete').click(function(e) {
	e.preventDefault();
	deleteTorrent(hb.torrent.id, function() {
	    location.href = "//" + hb.constant.url.base + '/torrents.php';
	});
    });
});
