//(function() {
    var b_open = 0;
    var i_open = 0;
    var u_open = 0;
    var color_open = 0;
    var list_open = 0;
    var quote_open = 0;
    var html_open = 0;

    var myAgent = navigator.userAgent.toLowerCase();
    var myVersion = parseInt(navigator.appVersion);

    var is_ie = ((myAgent.indexOf("msie") != -1) && (myAgent.indexOf("opera") == -1));
    var is_nav = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
		  && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
		  && (myAgent.indexOf('webtv') ==-1) && (myAgent.indexOf('hotjava')==-1));

    var is_win = ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
    var is_mac = (myAgent.indexOf("mac")!=-1);
    var bbtags = new Array();
    function cstat() {
	var c = stacksize(bbtags);
	if ( (c < 1) || (c == null) ) {c = 0;}
	if ( ! bbtags[0] ) {
	    c = 0;
	}
	document[hb.bbcode.form].tagcount.value = "Close last, Open "+c;
    }
    function stacksize(thearray) {
	for (i = 0; i < thearray.length; i++ ) {
	    if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) {return i;}
	}
	return thearray.length;
    }
    function pushstack(thearray, newval) {
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
    }
    function popstackd(thearray) {
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	return theval;
    }
    function popstack(thearray) {
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
    }
    function closeall() {
	if (bbtags[0]) {
	    while (bbtags[0]) {
		tagRemove = popstack(bbtags)
		if ( (tagRemove != 'color') ) {
		    doInsert("[/"+tagRemove+"]", "", false);
		    document[hb.bbcode.form][tagRemove].value = tagRemove;
		    eval(tagRemove + "_open = 0");
		} else {
		    doInsert("[/"+tagRemove+"]", "", false);
		}
		cstat();
		return;
	    }
	}
	document[hb.bbcode.form].tagcount.value = "Close last, Open 0";
	bbtags = new Array();
	document[hb.bbcode.form][hb.bbcode.text].focus();
    }
    function add_code(NewCode) {
	document[hb.bbcode.form][hb.bbcode.text].value += NewCode;
	document[hb.bbcode.form][hb.bbcode.text].focus();
    }
    function alterfont(theval, thetag) {
	if (theval == 0) return;
	if(doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true)) pushstack(bbtags, thetag);
	document[hb.bbcode.form].color.selectedIndex = 0;
	cstat();
    }

    function tag_url(PromptURL, PromptTitle, PromptError) {
	var FoundErrors = '';
	var enterURL = prompt(PromptURL, "http://");
	var enterTITLE = prompt(PromptTitle, "");
	if (!enterURL || enterURL=="") {FoundErrors += " " + PromptURL + ",";}
	if (!enterTITLE) {FoundErrors += " " + PromptTitle;}
	if (FoundErrors) {alert(PromptError+FoundErrors);return;}
	doInsert("[url="+enterURL+"]"+enterTITLE+"[/url]", "", false);
    }

    function tag_list(PromptEnterItem, PromptError) {
	var FoundErrors = '';
	var enterTITLE = prompt(PromptEnterItem, "");
	if (!enterTITLE) {FoundErrors += " " + PromptEnterItem;}
	if (FoundErrors) {alert(PromptError+FoundErrors);return;}
	doInsert("[*]"+enterTITLE+"", "", false);
    }

    function tag_image(PromptImageURL, PromptError) {
	var FoundErrors = '';
	var enterURL = prompt(PromptImageURL, "http://");
	if (!enterURL || enterURL=="http://") {
	    alert(PromptError+PromptImageURL);
	    return;
	}
	doInsert("[img]"+enterURL+"[/img]", "", false);
    }

    function tag_extimage(content) {
	doInsert(content, "", false);
    }

    function tag_email(PromptEmail, PromptError) {
	var emailAddress = prompt(PromptEmail, "");
	if (!emailAddress) {
	    alert(PromptError+PromptEmail);
	    return;
	}
	doInsert("[email]"+emailAddress+"[/email]", "", false);
    }

    function doInsert(ibTag, ibClsTag, isSingle) {
	var isClose = false;
	var obj_ta = document[hb.bbcode.form][hb.bbcode.text];
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

    function winop() {
	windop = window.open("moresmilies.php?form=" + hb.bbcode.form + "&text=" + hb.bbcode.text ,"mywin","height=500,width=500,resizable=no,scrollbars=yes");
    }

    function simpletag(thetag)
    {
	var tagOpen = eval(thetag + "_open");
	if (tagOpen == 0) {
	    if(doInsert("[" + thetag + "]", "[/" + thetag + "]", true))
	    {
		eval(thetag + "_open = 1");
		document[hb.bbcode.form][thetag].value = '*';
		pushstack(bbtags, thetag);
		cstat();
	    }
	}
	else {
	    lastindex = 0;
	    for (i = 0; i < bbtags.length; i++ ) {
		if ( bbtags[i] == thetag ) {
		    lastindex = i;
		}
	    }

	    while (bbtags[lastindex]) {
		tagRemove = popstack(bbtags);
		doInsert("[/" + tagRemove + "]", "", false)
		if ((tagRemove != 'COLOR') ){
		    document[hb.bbcode.form][tagRemove].value = tagRemove.toUpperCase();
		    eval(tagRemove + "_open = 0");
		}
	    }
	    cstat();
	}
    }
//});