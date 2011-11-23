if (navigator.appName=="Netscape") {
    document.write("<style type='text/css'>body {overflow-y:scroll;}<\/style>");
}
var userAgent = navigator.userAgent.toLowerCase();
var is_ie = (userAgent.indexOf('msie') != -1) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function Scale(image, max_width, max_height) {
    var tempimage = new Image();
    tempimage.src = image.src;
    var tempwidth = tempimage.width;
    var tempheight = tempimage.height;
    if (tempwidth > max_width) {
	image.height = tempheight = Math.round(((max_width)/tempwidth) * tempheight);
	image.width = tempwidth = max_width;
    }

    if (max_height != 0 && tempheight > max_height)
    {
	image.width = Math.round(((max_height)/tempheight) * tempwidth);
	image.height = max_height;
    }
}

function check_avatar(image, langfolder){
    var tempimage = new Image();
    tempimage.src = image.src;
    var displayheight = image.height;
    var tempwidth = tempimage.width;
    var tempheight = tempimage.height;
    if (tempwidth > 250 || tempheight > 250 || displayheight > 250) {
	image.src='pic/forum_pic/'+langfolder+'/avatartoobig.png';
    }
}

function Preview(image) {
    if (!is_ie || is_ie >= 7){
	$('#lightbox').html("<a onclick=\"Return();\"><img src=\"" + image.src + "\" /></a>").css('display', 'block');
	$('#curtain').css('display', 'block');
    }
    else {
	window.open(image.src);
    }
}

function Previewurl(url) {
    if (!is_ie || is_ie >= 7){
	$('#lightbox').html("<a onclick=\"Return();\"><img src=\"" + url + "\" /></a>").css('display', 'block');
	$('#curtain').css('display', 'block');
    }
    else{
	window.open(url);
    }
}

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

function Return() {
    $('#lightbox').css('display', 'none').html("");
    $('#curtain').css('display', 'none');
}
