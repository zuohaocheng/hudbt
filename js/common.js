function postvalid(form) {
    $('#qr').disabled = true;
    return true;
}

function dropmenu(obj){
    $('#' + obj.id + 'list').slideToggle();
}

function confirm_delete(id, note, addon) {
    if(confirm(note)) {
	self.location.href='?action=del'+(addon ? '&'+addon : '')+'&id='+id;
    }
}

// smileit.js

$(function() {
    $('.smileit').click(function(e) {
	e.preventDefault();
	var $this = $(this);
	SmileIT('[em' + $this.attr('smile') + ']', $this.attr('form'));
    });
});

function SmileIT(smile,form,text){
    doInsert(smile, '', false, $(document.forms[form]).find('textarea')[0]);
}

var is_ie = $.browser.msie;
function doInsert(ibTag, ibClsTag, isSingle, obj_ta) {
    var isClose = false;
    obj_ta = obj_ta || document[hb.bbcode.form][hb.bbcode.text];
    if (obj_ta.selectionStart || obj_ta.selectionStart == '0') {
	var startPos = obj_ta.selectionStart;
	var endPos = obj_ta.selectionEnd;
	var val = obj_ta.value;
	obj_ta.value = val.substring(0, startPos) + ibTag + val.substring(startPos, endPos) + ibClsTag + obj_ta.value.substring(endPos, obj_ta.value.length);
	obj_ta.selectionStart = startPos + ibTag.length;
	obj_ta.selectionEnd = ibTag.length + endPos;
    }
    else if (is_ie && obj_ta.isTextEdit) {
	obj_ta.focus();
	var sel = document.selection;
	var rng = sel.createRange();
	rng.colapse;
	if ((sel.type == "Text" || sel.type == "None") && rng != null) {
	    if(ibClsTag != "" && rng.text.length > 0) {
		ibTag += rng.text + ibClsTag;
	    }
	    else if (isSingle) {
		isClose = true;
	    }
	    rng.text = ibTag;
	}
    }
    else {
	if(isSingle) isClose = true;
	obj_ta.value += ibTag;
    }
    obj_ta.focus();
    return isClose;
}

// java_klappe.js

function klappe(id)
{
    var klappText = document.getElementById('k' + id);
    var klappBild = document.getElementById('pic' + id);

    if (klappText.style.display == 'none') {
	klappText.style.display = 'block';
	// klappBild.src = 'pic/blank.gif';
    }
    else {
	klappText.style.display = 'none';
	// klappBild.src = 'pic/blank.gif';
    }
}

var klappe_news = (function() {
    var locks = {};
    var unlock = function (id) {
	locks[id] = false;
    };

    return function (id, noPic) {
	if (locks[id]) {
	    return;
	}
	else {
	    locks[id] = true;
	}

	var $klappText = $('#k' + id);
	if (noPic) {
	    $klappText.slideToggle('normal', function() {
		unlock(id);
	    });
	}
	else {
	    var klappBild = document.getElementById('pic' + id);
	    if ($klappText.css('display') === 'none') {
		$klappText.slideDown('normal', function() {
		    unlock(id);
		});
		klappBild.className = 'minus';
	    }
	    else {
		$klappText.slideUp('normal', function() {
		    unlock(id);
		});
		klappBild.className = 'plus';
	    }
	}
    }
})();

function klappe_ext(id)
{
    var klappText = document.getElementById('k' + id);
    var klappBild = document.getElementById('pic' + id);
    var klappPoster = document.getElementById('poster' + id);
    if (klappText.style.display == 'none') {
	klappText.style.display = 'block';
	klappPoster.style.display = 'block';
	klappBild.className = 'minus';
    }
    else {
	klappText.style.display = 'none';
	klappPoster.style.display = 'none';
	klappBild.className = 'plus';
    }
}

// disableother.js

function disableother(select,target)
{
    if (document.getElementById(select).value == 0)
	document.getElementById(target).disabled = false;
    else {
	document.getElementById(target).disabled = true;
	document.getElementById(select).disabled = false;
    }
}

function disableother2(oricat,newcat)
{
    if (document.getElementById("movecheck").checked == true){
	document.getElementById(oricat).disabled = true;
	document.getElementById(newcat).disabled = false;
    }
    else {
	document.getElementById(oricat).disabled = false;
	document.getElementById(newcat).disabled = true;
    }
}

// ctrlenter.js
var submitted = false;
function ctrlenter(event,formname,submitname){
    if (submitted == false){
	var keynum;
	if (event.keyCode){
	    keynum = event.keyCode;
	}
	else if (event.which){
	    keynum = event.which;
	}
	if (event.ctrlKey && keynum == 13){
	    submitted = true;
	    document.getElementById(formname).submit();
	}
    }
}

// bookmark.js
function bookmark(torrentid) {
    $.getJSON('bookmark.php', {torrentid : torrentid}, function(result) {
	var status = result.status;
	bmicon(status, torrentid);
    });
}

function bmicon(status,torrentid) {
    if (status=="added") {
	document.getElementById("bookmark"+torrentid).innerHTML="<img class=\"bookmark\" src=\"pic/trans.gif\" alt=\"Bookmarked\" />";
    }
    else if (status=="deleted") {
	document.getElementById("bookmark"+torrentid).innerHTML="<img class=\"delbookmark\" src=\"pic/trans.gif\" src=\"pic/trans.gif\" alt=\"Unbookmarked\" />";
    }
}

// check.js
var checkflag = "false";
function check(field,checkall_name,uncheckall_name) {
    if (checkflag == "false") {
	for (i = 0; i < field.length; i++) {
	    field[i].checked = true;}
	checkflag = "true";
	return uncheckall_name; }
    else {
	for (i = 0; i < field.length; i++) {
	    field[i].checked = false; }
	checkflag = "false";
	return checkall_name; }
}

// in functions.php
function get_ext_info_ajax(blockid,url,cache,type)
{
    if (document.getElementById(blockid).innerHTML==""){
	var infoblock=ajax.gets('getextinfoajax.php?url='+url+'&cache='+cache+'&type='+type);
	document.getElementById(blockid).innerHTML=infoblock;
    }
    return true;
}

//scroll commons
var scrollToPosition = (function() {
    var $document = $(document);
    return function(top) {
    	var goTop=setInterval(scrollMove,10);  
	var buf = -1000;
	var buf_pos = -1000;
	
	function scrollMove(){
	    var pos = $document.scrollTop();
	    if (pos != buf_pos && buf_pos >= 0) {
		clearInterval(goTop);  
	    }

	    var diff = pos - top;
	    $document.scrollTop(diff / 1.2 + top);
	    buf_pos = $document.scrollTop();
	    if(Math.abs(diff) < 1 || Math.abs(buf - pos) <1) {
		clearInterval(goTop);  
	    }
	    buf = pos;
        }
    };
})();

$(function() {
    var $top = $('#top');
    if ($top.length === 0) {
	return;
    }
    var top = $top.offset().top;
    var $document = $(document);
    $('a[href="#top"]').click(function(e) {
	e.preventDefault();
	scrollToPosition(top);
    });
});

//curtain_imageresizer.js
$(function() {
    if (navigator.appName=="Netscape") {
	$('body').css('overflow-y', 'scroll');
    }
    var ie6 = $.browser.msie && $.browser.version < 7;
    var lightbox = $('#lightbox');
    var curtain = $('#curtain');

    lightbox.click(function () {
	lightbox.hide();
	curtain.fadeOut();
    });

    $('img.scalable').click(function() {
	var url = $(this).attr('full') || this.src;
	if (!ie6){
	    lightbox.html("<img src=\"" + url + "\" />").fadeIn();
	    curtain.fadeIn();
	}
	else{
	    window.open(url);
	}
    });
});

function findPosition( oElement ) {
    if( typeof( oElement.offsetParent ) != 'undefined' ) {
	for( var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent ) {
	    posX += oElement.offsetLeft;
	    posY += oElement.offsetTop;
	}
	return [ posX, posY ];
    } else {
	return [ oElement.x, oElement.y ];
    }
}

$(function() {    //Back to top
    var backtotop = $('#back-to-top');
    var $document = $(document);
    backtotop.click(function(e) {
	e.preventDefault();
	scrollToPosition(0);
    });

    window.onscroll=function() {
	$document.scrollTop() > 200 ? backtotop.css('display', "") : backtotop.css('display', 'none');
    };
});

var argsFromUri = function(uri) {
    var args = new Object();
    var query = uri.split('?');
    if (query.length !== 2) {
	return {};
    }
    query = query[1].split('#')[0];
    $.each(query.split('&'), function(idx, obj) {
	var t = obj.split('=');
	args[t[0]] = decodeURIComponent(t[1]);
    });
    return args;
};

// Used in index.php & fun.php
$(function() {
    $('#funcomment dt .username').each(function() {
	var user = $(this);
	user.after($('<a />', {
	    href : '#',
	    text : '回复',
	    'class' : 'funcomment-reply'
	}).click(function(e) {
	    e.preventDefault();
	    var target = $('#fun_text');

	    var id = user.find('.user-id').text();
	    var val = target.val().replace(/^@\[user=[0-9]+\] */ig, '');
	    target.val('@[user=' + id + '] ' + val).focus();
	}));
    });
});

var TimeLimit = function(check, target) {
    
    
};

var timeLimit = function(check, time, timeTool) {
    var inited = false;
    var str2date = function(string) {
	var default_date = new Date();
	
	date_part = string.split(' ')[0].split('-');
	time_part = string.split(' ')[1].split(':');
	
	default_date.setFullYear(date_part[0]);
	default_date.setMonth(date_part[1] - 1);
	default_date.setDate(date_part[2]);
	default_date.setHours(time_part[0]);
	default_date.setMinutes(time_part[1]);
	default_date.setSeconds(time_part[2]);
	
	return default_date;
    }
    var timeBox = time.find('input');

    return function() {
	if (!check()) {
	    time.slideUp();
	    timeTool.fadeOut();
	}
	else {
	    if (!inited) {
		inited = true;
		timeTool.html('<label><select id="time_select_day"><option value="0">0</option><option value="1" selected="selected">1</option><option value="2">2</option><option value="3">3</option><option value="5">5</option><option value="7">7</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option><option value="60">60</option><option value="90">90</option><option value="180">180</option><option value="365">365</option></select>天</label><label><select id="time_select_hour"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="8">8</option><option value="10">10</option><option value="12">12</option><option value="15">15</option><option value="18">18</option><option value="20">20</option></select>小时</label><label><select id="time_select_minute"><option value="0">0</option><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option><option value="45">45</option></select>分钟</label>').append($('<input />', {
		    type : 'button',
		    value : '延长促销时间'
		}).click(function() {
		    var new_date = new Date();

		    var day = $('#time_select_day').val();
		    var hour = $('#time_select_hour').val();
		    var minute = $('#time_select_minute').val();

		    // 换算成毫秒
		    var time_period = (day * 24 * 3600 + hour * 3600 + minute * 60) * 1000;
		    var default_unixtime = str2date(timeBox.val());
		    new_date.setTime(default_unixtime.valueOf() + time_period);

		    timeBox.val(new_date.getFullYear() 
				+ '-' + (new_date.getMonth() + 1) 
				+ '-' + new_date.getDate() 
				+ ' ' + new_date.getHours() 
				+ ':' + new_date.getMinutes() 
				+ ':' + new_date.getSeconds());

		    return false;
		}));
	    }
	    
	    time.slideDown();
	    timeTool.fadeIn();
	}
    }
};

var editPr = function() {
    $pr_type = $('#sel_spstate');
    if ($pr_type.length) {
	$pr_time_type = $('#promotion_time_type').attr("disabled", "disabled");
	$pr_time = $('#promotionuntil');
	var validatePrTimeType = function() {
	    if ($pr_type.val() === '1') {
		$pr_time_type.attr("disabled", "disabled");
	    }
	    else {
		$pr_time_type.removeAttr("disabled");
	    }
	    validatePrTime();
	};

	var validatePrTime = timeLimit(function() {
	    return ($pr_time_type.val() === '2');
	}, $('#pr-expire'), $('#expand-pr'));

	validatePrTimeType();

	$pr_type.change(validatePrTimeType);
	$pr_time_type.change(validatePrTime);
    }
};

//edit position status Added by Eggsorer
var editPos = function() {
    $pos_state = $('#sel_posstate');
    $pos_time = $('#posstateuntil');

    var validatePosTime = timeLimit(function() {
	return ($pos_state.val() === 'sticky'||$pos_state.val() === 'random');
    }, $('#pos-expire'), $("#expand-pos"));
    validatePosTime();

    $pos_state.change(validatePosTime);
};

var createOptions = function(options, defaultOpt) {
    return $.map(options, function(v, k) {
	if (v.length === 0) {
	    return '';
	}

	var out = '<option value="' + k + '"';
	if (k === defaultOpt) {
	    out += ' selected="selected"';
	}
	out += '>' + v + '</option>';
	return out;
    }).join('');
};

var jqui_dialog = function(title, html, timeout, callback) {
    var dialog = $('<div />', {
	title : title,
	html : html
    });
    var close = function() {
	dialog.dialog('close');
    };
    dialog.dialog({
	modal : true,
	autoOpen : true,
	buttons : {
	    OK : close
	},
	'close' : function() {
	    dialog.remove();
	    if (callback) {
		callback();
	    }
	}
    });
    if (timeout) {
	setTimeout(close, timeout);
    }
};

var jqui_confirm = function(title, html, onOK) {
    var dialog = $('<div />', {
	title : title,
	html : html
    });
    var close = function() {
	dialog.dialog('close');
    };
    dialog.dialog({
	modal : true,
	autoOpen : true,
	buttons : {
	    OK : function() {
		if (onOK()) {
		    close();
		}
	    },
	    Cancel : close
	},
	'close' : function() {
	    dialog.remove();
	}
    });
};

var jqui_form = function(form, title, callback, buttons, width) {
    form.submit(function(e) {
	e.preventDefault();
	onOK();
    });
    var dialog = $('<div />', {
	title : title
    }).append('<div id="dialog-hint"></div>').append(form);

    var onOK = function() {
	var valid = true;
	form.find('.required, input[required]').each(function() {
	    var $this = $(this);
	    if ($this.val().trim().length === 0) {
		$this.addClass('invalid');
		valid = false;
	    }
	});
	if (!valid) {
	    $('#dialog-hint').text('存在无效字段');
	    return;
	}
	$.post(form.attr('action'), form.serialize(), function(result) {
	    if (callback) {
		if (callback(result, dialog)) {
		    dialog.dialog('close');
		}
	    }
	    else {
		dialog.dialog('close');
	    }
	}, 'json');
    };

    var defaultButtons = {
	OK : onOK,
	Cancel : function() {
	    dialog.dialog('close');
	}
    };
    if (buttons) {
	for (var key in defaultButtons) {
	    buttons[key] = defaultButtons[key];
	}
    }
    else {
	buttons = defaultButtons;
    }
    
    dialog.dialog({
	modal : true,
	autoOpen : true,
	buttons : buttons,
	width : width,
	'close' : function() {
	    dialog.remove();
	}
    });
};

var editTorrent = (function() {
    var prDict = ['', '普通', '免费', '2X', '2X免费', '50%', '2X 50%', '30%'];
    var untilDict = ['使用全局设置', '永久', '直到'];
    var posDict = {
	normal : '普通',
	sticky : '置顶',
	random : '随机'
   }; 

    return function(id, callback) {
	var cake = hb.constant.url.cake;
	$.getJSON('//' + cake + '/torrents/view/' + id + '.json', function(result) {
	    var putTarget = '//' + cake + '/torrents/edit/' + id + '.json?%2Fcake%2Ftorrents%2Fedit%2F' + id + '=';
	    var torrent = result.Torrent;
	    var time;
	    var pos_time;
	    if (torrent.promotion_time_type === '2') {
		time = torrent.promotion_until;
	    }
	    else {
		var new_date = new Date();
		time = new_date.getFullYear() 
		    + '-' + (new_date.getMonth() + 1) 
		    + '-' + new_date.getDate() 
		    + ' ' + new_date.getHours() 
		    + ':' + new_date.getMinutes() 
		    + ':' + new_date.getSeconds();
	    }
	 //position 
	    if (torrent.pos_state === 'sticky') {
		pos_time = torrent.pos_state_until;
	    }
	    else {
		var pos_new_date = new Date();
		pos_time = pos_new_date.getFullYear() 
		    + '-' + (pos_new_date.getMonth() + 1) 
		    + '-' + pos_new_date.getDate() 
		    + ' ' + pos_new_date.getHours() 
		    + ':' + pos_new_date.getMinutes() 
		    + ':' + pos_new_date.getSeconds();
	    }
	    var html = '<div id="dialog-hint"></div><input type="hidden" name="_method" value="PUT" /><input type="hidden" name="data[Torrent][id]" value="' + id + '" id="TorrentId"><ul><li><label>促销种子<select id="sel_spstate" name="data[Torrent][sp_state]" style="width: 100px;">' + createOptions(prDict, parseInt(torrent.sp_state)) + '</select></label> <select id="promotion_time_type" name="data[Torrent][promotion_time_type]" style="width: 100px;" disabled="disabled">' + createOptions(untilDict, parseInt(torrent.promotion_time_type)) + '</select></li><li><label id="pr-expire">截止日期<input type="text" name="data[Torrent][promotion_until]" id="promotionuntil" style="width: 120px;" value="' + time + '"></label></li><li id="expand-pr" style="display: none; "></li><li><label>种子位置<select name="data[Torrent][pos_state]" style="width: 100px;" id="sel_posstate">' + createOptions(posDict, torrent.pos_state) +'</select></label></li><li><label id="pos-expire">截止日期<input type="text" name="data[Torrent][pos_state_until]" id="posstateuntil" style="width: 120px;" value="' + pos_time + '"></label></li><li id="expand-pos" style="display: none; "></li>';
	    html += '<li><input type="hidden" name="data[Torrent][oday]" value="no"><label><input type="checkbox" id="sel_oday" name="data[Torrent][oday]" value="yes"';
	    if (torrent.oday === 'yes') {
		html += ' checked="checked"';
	    }
	    html += '>0day资源</label></li></ul>';
	    var form = $('<form></form>', {
		html : html,
		'class' : 'minor-list',
		action : putTarget,
		method : 'post'
	    });
	    var title = '设定优惠'
	    jqui_form(form, title, function(result) {
		if (result.success) {
		    if (callback) {
			callback();
		    }
		    return true;
		}
		else {
		    $('#dialog-hint').text(result.message);
		    return false;
		}
	    }, {
		'打开完整编辑' : function() {
		    location.href = '//' + hb.constant.url.base + '/edit.php?id=' + id;
		},
	    }, '500');
	    editPr();
	    editPos();
	});
    }
})();

var deleteTorrent = (function() {
    return (function(id, callback) {
	var cake = hb.constant.url.cake;
	var deleteTarget = '//' + cake + '/torrents/delete/' + id + '.json';
	var reasons = ['断种', '重复', '劣质', '违规', '其它'];
 	var html = '<input type="hidden" name="_method" value="DELETE" /><select id="reason-type" name="data[reasonType]">' + createOptions(reasons, 0) + '</select><input style="display: none;" type="text" id="reason-detail" name="data[reasonDetail]" title="详细理由" />';
	var form = $('<form></form>', {
	    html : html,
	    'class' : 'minor-list',
	    action : deleteTarget,
	    method : 'post'
	});
	form.find('#reason-type').change(function() {
	    var reasonDetail=$('#reason-detail');
	    var val=parseInt(this.value);
	    if(val===0) {
		reasonDetail.fadeOut().removeClass('required');
	    }
	    else {
		if(val===1||val===2) {
		    reasonDetail.attr('placeholder','可选').removeClass('required').fadeIn();
		}
		else {
		reasonDetail.attr('placeholder','必填').addClass('required').removeClass('invalid').fadeIn();
		}
	    }
	});
	jqui_form(form, '删除种子', function(result) {
	    if (result.success) {
		jqui_dialog('成功', result.message, 3000, callback);
		return true;
	    }
	    else {
		$('#dialog-hint').text(result.message);
		return false;
	    }	    
	});
    });
})();

$('form').submit(function() {
    $(this).find(':submit').attr('disabled', 'disabled');
});

$.getCSS = function(a) {
    var link = $('<link rel="stylesheet" type="text/css" />').attr('href', a);
    $("head").append(link);
    return link;
};
