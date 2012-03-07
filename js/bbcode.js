$(function() {
    var opentags = {
	b : 0,
	i : 0,
	u : 0,
	color : 0,
	list : 0,
	quote : 0,
	html : 0
    };
    var bbtags = [];
    function cstat() {
	var c = stacksize(bbtags);
	if ( (c < 1) || (c == null) ) {c = 0;}
	if ( ! bbtags[0] ) {
	    c = 0;
	}
	btnClose.val("Close last, Open "+c);
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
		    opentags[tagRemove] = 0;
		} else {
		    doInsert("[/"+tagRemove+"]", "", false);
		}
		cstat();
		return;
	    }
	}
	btnClose.val("Close last, Open 0");
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
	cstat();
    }

    var url_inited = false;
    var dialog_type = '';
    function url_form(t) {
	dialog_type = t;
	if (!url_inited) {
	    url_inited = true;
	    var dialog = $('<div></div>', {
		title : 'URL',
		'class' : 'minor-list-vertical',
		id : 'url-form'
	    });
	    $('#bbcode-toolbar').append(dialog);
	    dialog.html('<ul><div id="validateTips"></div><li><label for="tag-url">' + lang.js_prompt_enter_url + '</label><input type="url" id="tag-url" class="text ui-widget-content ui-corner-all" /></li><li><label for="tag-url-title">' + lang.js_prompt_enter_title + '</label><input type="text" id="tag-url-title" class="text ui-widget-content ui-corner-all" /></li></ul>');
	    var urlfield = $('#tag-url');
	    var titlefield = $('#tag-url-title');
	    var allFields = $( [] ).add( urlfield ).add( titlefield );
	    
	    function updateTips( t ) {
		var tips = $('#validateTips');
		tips.text( t )
		    .addClass( "ui-state-highlight" );
		setTimeout(function() {
		    tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	    }

	    function isUrl(s) {
		var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
		    return regexp.test(s);
	    }

	    function checkLength( o, n ) {
		if (!isUrl(o.val())) {
		    o.addClass( "ui-state-error" );
		    updateTips( n + "无效。" );
		    return false;
		} else {
		    return true;
		}
	    }

	    var onok = function() {
		allFields.removeClass( "ui-state-error" );
		var valid = true;
		valid = valid && checkLength(urlfield, "URL");
		if (valid) {
		    var url = urlfield.val();
		    var title = $('#tag-url-title').val();
		    if (title.length === 0) {
			title = url;
		    }

		    if (dialog_type === 'url') {
			doInsert("[url="+url+"]"+title+"[/url]", "", false);
		    }
		    else if (dialog_type === 'image') {
			doInsert("[img]"+url+"[/img]", "", false);
		    }
		    dialog.dialog( "close" );
		}
	    };

	    allFields.keypress(function(e) {
		if (e.keyCode === 13) {
		    onok();
		}
	    });

	    dialog.dialog({
		modal: true,
		buttons: {
		    OK: onok,
		    Cancel: function() {
			$( this ).dialog( "close" );
		    }
		},
		close: function() {
		    allFields.val( "" ).removeClass( "ui-state-error" );
		    $('#validateTips').text('');
		}
	    });
	}

	$('#tag-url-title').parent().toggle(dialog_type === 'url');
	$('#tag-url').val('http://').select();
	$('#url-form').dialog('open');
    }

    function tag_url() {
	url_form('url');
    }

    function tag_list(PromptEnterItem, PromptError) {
	var FoundErrors = '';
	var enterTITLE = prompt(PromptEnterItem, "");
	if (!enterTITLE) {FoundErrors += " " + PromptEnterItem;}
	if (FoundErrors) {alert(PromptError+FoundErrors);return;}
	doInsert("[*]"+enterTITLE+"", "", false);
    }

    function tag_image() {
	url_form('image');
    }

    function tag_email(PromptEmail, PromptError) {
	var emailAddress = prompt(PromptEmail, "");
	if (!emailAddress) {
	    alert(PromptError+PromptEmail);
	    return;
	}
	doInsert("[email]"+emailAddress+"[/email]", "", false);
    }

    var inited = false;
    $('#showmoresmilies').click(function (e) {
	e.preventDefault();
	if (inited) {
	    $('#moresmilies').dialog('open');
	}
	else {
	    inited = true;
	    var uri = "moresmilies.php?form=" + hb.bbcode.form + "&text=" + hb.bbcode.text;
	    $.get(uri, function(result) {
		$('#moresmilies').html(result).dialog({
		    width: '50%',
		    position: 'right'
		});
	    }, 'html');
	}
    });

    function simpletag(thetag) {
	var tagOpen = opentags[thetag];
	if (tagOpen == 0) {
	    if(doInsert("[" + thetag + "]", "[/" + thetag + "]", true)) {
		opentags[thetag] = 1;
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
		    opentags[tagRemove] = 0;
		}
	    }
	    cstat();
	}
    }

    var lang = hb.constant.lang;
    var buttons = [
	$('<input />', {
	    'class' : 'codebuttons',
	    style : "font-weight: bold;",
	    type : 'button',
	    name : 'b',
	    value : 'B'
	}).click(function() {
	    simpletag('b');
	}), 
	$('<input />', {
	    'class' : 'codebuttons',
	    style : "font-style: italic;",
	    type : 'button',
	    name : 'i',
	    value : 'I'
	}).click(function() {
	    simpletag('i');
	}),
	$('<input />', {
	    'class' : 'codebuttons',
	    style : "text-decoration: underline;",
	    type : 'button',
	    name : 'u',
	    value : 'U'
	}).click(function() {
	    simpletag('u');
	}),
	$('<input />', {
	    'class' : 'codebuttons',
	    type : 'button',
	    name : 'url',
	    value : 'URL'
	}).click(tag_url),
	$('<input />', {
	    'class' : 'codebuttons',
	    type : 'button',
	    name : 'IMG',
	    value : 'IMG'
	}).click(tag_image),
	$('<input />', {
	    'class' : 'codebuttons',
	    type : 'button',
	    value : 'List'
	}).click(function() {
	    tag_list(lang.js_prompt_enter_item, lang.js_prompt_error);
	}),
	$('<input />', {
	    'class' : 'codebuttons',
	    type : 'button',
	    name : 'quote',
	    value : 'QUOTE'
	}).click(function() {
	    simpletag('quote');
	})];
    var btnClose = $('<input />', {
	'class' : 'codebuttons',
	type : 'button',
	value : '关闭标签'
    }).click(closeall);
    buttons.push(btnClose);

    var selections = function(name, header, opts) {
	var sel = $('<select></select>', {
	    'class' : "med codebuttons no-validate",
	    name : name
	});
	sel.append('<option value="0">' + header + '</option>');
	return sel.append($.map(opts, function(item, idx) {
	    return '<option value="' + item.val + '"' + item.attrs + '>' + item.title + '</option>';
	}).join());
    };

    var colorOpts = [{"title":"Black","val":"Black","attrs":" style=\"background-color: black;\""},{"title":"Sienna","val":"Sienna","attrs":" style=\"background-color: sienna;\""},{"title":"Dark Olive Green","val":"DarkOliveGreen","attrs":" style=\"background-color: darkolivegreen;\""},{"title":"Dark Green","val":"DarkGreen","attrs":" style=\"background-color: darkgreen;\""},{"title":"Dark Slate Blue","val":"DarkSlateBlue","attrs":" style=\"background-color: darkslateblue;\""},{"title":"Navy","val":"Navy","attrs":" style=\"background-color: navy;\""},{"title":"Indigo","val":"Indigo","attrs":" style=\"background-color: indigo;\""},{"title":"Dark Slate Gray","val":"DarkSlateGray","attrs":" style=\"background-color: darkslategray;\""},{"title":"Dark Red","val":"DarkRed","attrs":" style=\"background-color: darkred;\""},{"title":"Dark Orange","val":"DarkOrange","attrs":" style=\"background-color: darkorange;\""},{"title":"Olive","val":"Olive","attrs":" style=\"background-color: olive;\""},{"title":"Green","val":"Green","attrs":" style=\"background-color: green;\""},{"title":"Teal","val":"Teal","attrs":" style=\"background-color: teal;\""},{"title":"Blue","val":"Blue","attrs":" style=\"background-color: blue;\""},{"title":"Slate Gray","val":"SlateGray","attrs":" style=\"background-color: slategray;\""},{"title":"Dim Gray","val":"DimGray","attrs":" style=\"background-color: dimgray;\""},{"title":"Red","val":"Red","attrs":" style=\"background-color: red;\""},{"title":"Sandy Brown","val":"SandyBrown","attrs":" style=\"background-color: sandybrown;\""},{"title":"Yellow Green","val":"YellowGreen","attrs":" style=\"background-color: yellowgreen;\""},{"title":"Sea Green","val":"SeaGreen","attrs":" style=\"background-color: seagreen;\""},{"title":"Medium Turquoise","val":"MediumTurquoise","attrs":" style=\"background-color: mediumturquoise;\""},{"title":"Royal Blue","val":"RoyalBlue","attrs":" style=\"background-color: royalblue;\""},{"title":"Purple","val":"Purple","attrs":" style=\"background-color: purple;\""},{"title":"Gray","val":"Gray","attrs":" style=\"background-color: gray;\""},{"title":"Magenta","val":"Magenta","attrs":" style=\"background-color: magenta;\""},{"title":"Orange","val":"Orange","attrs":" style=\"background-color: orange;\""},{"title":"Yellow","val":"Yellow","attrs":" style=\"background-color: yellow;\""},{"title":"Lime","val":"Lime","attrs":" style=\"background-color: lime;\""},{"title":"Cyan","val":"Cyan","attrs":" style=\"background-color: cyan;\""},{"title":"Deep Sky Blue","val":"DeepSkyBlue","attrs":" style=\"background-color: deepskyblue;\""},{"title":"Dark Orchid","val":"DarkOrchid","attrs":" style=\"background-color: darkorchid;\""},{"title":"Silver","val":"Silver","attrs":" style=\"background-color: silver;\""},{"title":"Pink","val":"Pink","attrs":" style=\"background-color: pink;\""},{"title":"Wheat","val":"Wheat","attrs":" style=\"background-color: wheat;\""},{"title":"Lemon Chiffon","val":"LemonChiffon","attrs":" style=\"background-color: lemonchiffon;\""},{"title":"Pale Green","val":"PaleGreen","attrs":" style=\"background-color: palegreen;\""},{"title":"Pale Turquoise","val":"PaleTurquoise","attrs":" style=\"background-color: paleturquoise;\""},{"title":"Light Blue","val":"LightBlue","attrs":" style=\"background-color: lightblue;\""},{"title":"Plum","val":"Plum","attrs":" style=\"background-color: plum;\""},{"title":"White","val":"White","attrs":" style=\"background-color: white;\""}];
    var fontOpts = [{"title":"Arial","val":"Arial"},{"title":"Arial Black","val":"Arial Black"},{"title":"Arial Narrow","val":"Arial Narrow"},{"title":"Book Antiqua","val":"Book Antiqua"},{"title":"Century Gothic","val":"Century Gothic"},{"title":"Comic Sans MS","val":"Comic Sans MS"},{"title":"Courier New","val":"Courier New"},{"title":"Fixedsys","val":"Fixedsys"},{"title":"Garamond","val":"Garamond"},{"title":"Georgia","val":"Georgia"},{"title":"Impact","val":"Impact"},{"title":"Lucida Console","val":"Lucida Console"},{"title":"Lucida Sans Unicode","val":"Lucida Sans Unicode"},{"title":"Microsoft Sans Serif","val":"Microsoft Sans Serif"},{"title":"Palatino Linotype","val":"Palatino Linotype"},{"title":"System","val":"System"},{"title":"Tahoma","val":"Tahoma"},{"title":"Times New Roman","val":"Times New Roman"},{"title":"Trebuchet MS","val":"Trebuchet MS"},{"title":"Verdana","val":"Verdana"}];
    var sizeOpts = [{"title":"1","val":"1"},{"title":"2","val":"2"},{"title":"3","val":"3"},{"title":"4","val":"4"},{"title":"5","val":"5"},{"title":"6","val":"6"},{"title":"7","val":"7"}];

    buttons.push(selections('color', lang.select_color, colorOpts).change(function() {
	alterfont(this.options[this.selectedIndex].value, 'color');
	this.selectedIndex = 0;
    }));
    buttons.push(selections('font', lang.select_font, fontOpts).change(function() {
	alterfont(this.options[this.selectedIndex].value, 'font');
	this.selectedIndex = 0;
    }));    
    buttons.push(selections('size', lang.select_size, sizeOpts).change(function() {
	alterfont(this.options[this.selectedIndex].value, 'size');
	this.selectedIndex = 0;
    }));    

    var toolbar = $('#bbcode-toolbar');
    $.each(buttons, function(idx,itm) {
    	toolbar.append(itm.wrap('<li></li>'));
    })
});

// preview.js
$(function() {
    var $tar = $('#commit-btn');
    var $previewouter = $('#previewouter');
    var $editorouter = $('#editorouter');
    var previewing = false;

    var $preview = $('<input />', {
	type : 'button',
	'class' : 'btn2',
	id : 'previewbutton',
	value : hb.constant.lang.submit_preview
    }).click(function() {
	if (!previewing) {
	    $.post('preview.php', {body : $('#body').val()}, function(res) {
		$preview.attr('value', hb.constant.lang.submit_edit);
		$previewouter.html(res).slideDown();
		$editorouter.slideUp();
		previewing = true;
	    }, 'html');
	}
	else {
	    $preview.attr('value', hb.constant.lang.submit_preview);
	    $previewouter.html('').slideUp();
	    $editorouter.slideDown();
	    previewing = false;
	}
    }).appendTo($tar);
    $('#commit-btn input').button().parent().buttonset();
});

var allowedtypes = (function() {
    var lock = false;
    var dialog;
    return function (e, obja) {
	var a = $(obja);
	if (!dialog) {
	    dialog = $('<div></div>', {
		title : a.parent().text(),
		text : a.attr('title')
	    }).hide();
	    a.attr('title', '');
	    $('#bbcode-toolbar').after(dialog);
	    dialog.dialog({
		position : ['right', 'top'],
		autoOpen: false,
		close : function() {lock = false;}
	    });
	}

	if (e.type === 'mouseover') {
	    dialog.dialog('open');
	}
	else if (e.type === 'mouseout') {
	    if (!lock) {
		dialog.dialog('close');
	    }
	}
	else if (e.type === 'click') {
	    e.preventDefault();
	    lock = true;
	    dialog.dialog('open');
	}
    };
})();

function tag_extimage(content) {
    doInsert(content, "", false);
}

