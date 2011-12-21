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