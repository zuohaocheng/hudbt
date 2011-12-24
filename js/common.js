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

var myAgent = navigator.userAgent.toLowerCase();
var is_ie = ((myAgent.indexOf("msie") != -1) && (myAgent.indexOf("opera") == -1));
var is_win = ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
var myVersion = parseInt(navigator.appVersion);
function doInsert(ibTag, ibClsTag, isSingle, obj_ta) {
    var isClose = false;
    obj_ta = obj_ta || document[hb.bbcode.form][hb.bbcode.text];
    if ( (myVersion >= 4) && is_ie && is_win)
    {
	if(obj_ta.isTextEdit)
	{
	    obj_ta.focus();
	    var sel = document.selection;
	    var rng = sel.createRange();
	    rng.colapse;
	    if((sel.type == "Text" || sel.type == "None") && rng != null)
	    {
		if(ibClsTag != "" && rng.text.length > 0)
		    ibTag += rng.text + ibClsTag;
		else if(isSingle) isClose = true;
		rng.text = ibTag;
	    }
	}
	else
	{
	    if(isSingle) isClose = true;
	    obj_ta.value += ibTag;
	}
    }
    else if (obj_ta.selectionStart || obj_ta.selectionStart == '0')
    {
	var startPos = obj_ta.selectionStart;
	var endPos = obj_ta.selectionEnd;
	obj_ta.value = obj_ta.value.substring(0, startPos) + ibTag + obj_ta.value.substring(endPos, obj_ta.value.length);
	obj_ta.selectionEnd = startPos + ibTag.length;
	if(isSingle) isClose = true;
    }
    else
    {
	if(isSingle) isClose = true;
	obj_ta.value += ibTag;
    }
    obj_ta.focus();
    // obj_ta.value = obj_ta.value.replace(/ /, " ");
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
function gotothepage(page){
    var url=window.location.href;
    var end=url.lastIndexOf("page");
    url = url.replace(/#[0-9]+/g,"");
    if (end == -1){
	if (url.lastIndexOf("?") == -1)
	    window.location.href=url+"?page="+page;
	else
	    window.location.href=url+"&page="+page;
    }
    else{
	url = url.replace(/page=.+/g,"");
	window.location.href=url+"page="+page;
    }
}
function changepage(event){
    var gotopage;
    var keynum;
    var altkey;
    if (navigator.userAgent.toLowerCase().indexOf('presto') != -1)
	altkey = event.shiftKey;
    else altkey = event.altKey;
    if (event.keyCode){
	keynum = event.keyCode;
    }
    else if (event.which){
	keynum = event.which;
    }
    if(altkey && keynum==33){
	if(currentpage<=0) return;
	gotopage=currentpage-1;
	gotothepage(gotopage);
    }
    else if (altkey && keynum == 34){
	if(currentpage>=maxpage) return;
	gotopage=currentpage+1;
	gotothepage(gotopage);
    }
}
if(window.document.addEventListener){
    window.addEventListener("keydown",changepage,false);
}
else{
    window.attachEvent("onkeydown",changepage,false);
}

// bookmark.js
function bookmark(torrentid)
{
    var result=ajax.gets('bookmark.php?torrentid='+torrentid);
    bmicon(result,torrentid);
}
function bmicon(status,torrentid)
{
    if (status=="added")
	document.getElementById("bookmark"+torrentid).innerHTML="<img class=\"bookmark\" src=\"pic/trans.gif\" alt=\"Bookmarked\" />";
    else if (status=="deleted")
	document.getElementById("bookmark"+torrentid).innerHTML="<img class=\"delbookmark\" src=\"pic/trans.gif\" src=\"pic/trans.gif\" alt=\"Unbookmarked\" />";
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

// in upload.php
function getname()
{
    var filename = document.getElementById("torrent").value;
    var filename = filename.toString();
    var lowcase = filename.toLowerCase();
    var start = lowcase.lastIndexOf("\\"); //for Google Chrome on windows
    if (start == -1){
	start = lowcase.lastIndexOf("\/"); // for Google Chrome on linux
	if (start == -1)
	    start == 0;
	else start = start + 1;
    }
    else start = start + 1;
    var end = lowcase.lastIndexOf("torrent");
    var noext = filename.substring(start,end-1);
    noext = noext.replace(/H\.264/ig,"H_264");
    noext = noext.replace(/5\.1/g,"5_1");
    noext = noext.replace(/2\.1/g,"2_1");
    noext = noext.replace(/\./g," ");
    noext = noext.replace(/H_264/g,"H.264");
    noext = noext.replace(/5_1/g,"5.1");
    noext = noext.replace(/2_1/g,"2.1");
    document.getElementById("name").value=noext;
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

	function scrollMove(){
	    var pos = $document.scrollTop();
	    var diff = pos - top;
	    $document.scrollTop(diff / 1.15 + top);
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
    var ie6 = $.browser.msie && $.browser.version < 9;
    var lightbox = $('#lightbox');
    var curtain = $('#curtain');

    function Previewurl(url) {
	if (!ie6){
	    lightbox.html("<img src=\"" + url + "\" />").fadeIn();
	    curtain.fadeIn();
	}
	else{
	    window.open(url);
	}
    }

    lightbox.click(function () {
	lightbox.hide();
	curtain.fadeOut();
    });

    $('img.scalable').click(function() {
    	Previewurl(this.src);
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
	return [];
    }
    $.each(query[1].split('&'), function(idx, obj) {
	var t = obj.split('=');
	args[t[0]] = decodeURIComponent(t[1]);
    });
    return args;
};
