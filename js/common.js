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

function SmileIT(smile,form,text){
    doInsert(smile, '', false, document.forms[form].elements[text]);
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
    $.each(query[1].split('&'), function(idx, obj) {
	var t = obj.split('=');
	args[t[0]] = decodeURIComponent(t[1]);
    });
    return args;
};

// Used in index.php & fun.php
$(function() {
    $('#funcomment').find('.username').each(function() {
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

	var validatePrTime = (function() {
	    var expand_pr = $("#expand-pr");
	    var expand_pr_inited = false;
	    var timeBox = $pr_time;

	    return function() {
		if ($pr_time_type.val() !== '2') {
		    $('#pr-expire').fadeOut();
		    expand_pr.slideUp();
		}
		else {
		    $('#pr-expire').fadeIn();
		    if (!expand_pr_inited) {
			expand_pr_inited = true;
			expand_pr.html('<label><select id="time_select_day"><option value="0">0</option><option value="1" selected="selected">1</option><option value="2">2</option><option value="3">3</option><option value="5">5</option><option value="7">7</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option><option value="60">60</option><option value="90">90</option><option value="180">180</option><option value="365">365</option></select>天</label><label><select id="time_select_hour"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="8">8</option><option value="10">10</option><option value="12">12</option><option value="15">15</option><option value="18">18</option><option value="20">20</option></select>小时</label><label><select id="time_select_minute"><option value="0">0</option><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option><option value="45">45</option></select>分钟</label>');
			expand_pr.append($('<input />', {
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
		    
		    expand_pr.slideDown();
		}
	    };
	})();

	validatePrTimeType();

	$pr_type.change(validatePrTimeType);
	$pr_time_type.change(validatePrTime);

	function str2date(string) {
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

    }
};