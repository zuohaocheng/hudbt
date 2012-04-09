$(function() {
    if (!('config' in hb) || !('pager' in hb.config)) {
	return;
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
	var currentpage = hb.config.pager.current;
	var maxpage = hb.config.pager.max;
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

    $(window).keydown(changepage);

    $('.pager-more').click(function(e) {
	e.preventDefault();
	var page = hb.config.pager;
	var onOK = function() {
	    var target = parseInt($('#pager-go-to').val()) -1;
	    if (target >= 0 && target <= page.max) {
		gotothepage(target);
	    }
	    else {
		$('#pager-go-to').addClass('invalid');
	    }
	};
	var dialog = $('<div></div>', {
	    title : '跳至页码'
	}).append($('<form></form>', {
	    html : '<label>第<input type="text" id="pager-go-to" style="width:2em;" value="' + (page.current + 1) + '" />页，共' + page.max + '页</label>'
	}).submit(function(e) {
	    e.preventDefault();
	    onOK();
	}));

	dialog.dialog({
	    buttons : {
		OK : onOK,
		Cancel : function() {
		    dialog.dialog('close');
		}
	    },
	    modal : false,
	    autoOpen : true
	});
	$('#pager-go-to').keydown(function(e) {
	    if (e.keyCode === 13) {
		onOK();
	    }
	});
    });
});