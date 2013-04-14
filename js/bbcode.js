$(function() {
    "use strict";
    var opentags = {
	b : 0,
	i : 0,
	u : 0,
	color : 0,
	list : 0,
	quote : 0,
	html : 0
    },
    bbtags = [],
    cstat = function() {
	var c = stacksize(bbtags);
	if ( (c < 1) || (c == null) ) {c = 0;}
	if ( ! bbtags[0] ) {
	    c = 0;
	}
    },
    stacksize = function(thearray) {
	for (i = 0; i < thearray.length; i++ ) {
	    if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) {return i;}
	}
	return thearray.length;
    },
    pushstack = function(thearray, newval) {
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
    },
    popstackd = function(thearray) {
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	return theval;
    },
    popstack = function(thearray) {
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
    },

    add_code = function(NewCode) {
	document[hb.bbcode.form][hb.bbcode.text].value += NewCode;
	document[hb.bbcode.form][hb.bbcode.text].focus();
    },
    alterfont = function(theval, thetag) {
	if (theval == 0) return;
	if(doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true)) pushstack(bbtags, thetag);
	cstat();
    },

    url_inited = false,
    dialog_type = '',
    url_form = function(t) {
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
	    titlefield = $('#tag-url-title'),
	    allFields = $( [] ).add( urlfield ).add( titlefield ),
	    
	    updateTips = function( t ) {
		var tips = $('#validateTips');
		tips.text( t )
		    .addClass( "ui-state-highlight" );
		setTimeout(function() {
		    tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	    },

	    isUrl = function(s) {
		var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
		    return regexp.test(s);
	    },

	    checkLength = function( o, n ) {
		if (!isUrl(o.val())) {
		    o.addClass( "ui-state-error" );
		    updateTips( n + "无效。" );
		    return false;
		} else {
		    return true;
		}
	    },

	    onok = function() {
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
    },

    tag_url = function() {
	url_form('url');
    },

    tag_list = function(PromptEnterItem, PromptError) {
	var FoundErrors = '';
	var enterTITLE = prompt(PromptEnterItem, "");
	if (!enterTITLE) {FoundErrors += " " + PromptEnterItem;}
	if (FoundErrors) {alert(PromptError+FoundErrors);return;}
	doInsert("[*]"+enterTITLE+"", "", false);
    },

    tag_image = function() {
	url_form('image');
    },

    tag_email = function(PromptEmail, PromptError) {
	var emailAddress = prompt(PromptEmail, "");
	if (!emailAddress) {
	    alert(PromptError+PromptEmail);
	    return;
	}
	doInsert("[email]"+emailAddress+"[/email]", "", false);
    },

    inited = false;
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

    var simpletag = function(thetag) {
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
    },

    lang = hb.constant.lang,
    buttons = [$('<span/>').append(
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
	    var input_list = $('<ul></ul>');
	    var last_input;
	    var linput = function() {
		var ret = $('<input />', {
		    style : 'width: 90%'
		});
		input_list.append($('<li></li>').append(ret));
		ret.keypress(function(event) {
		    if (event.keyCode === 13) {
			if (this === last_input && ret.val() !== '') {
			    linput();
			}
			else {
			    ret.parent().next().find('input').focus();
			}
		    }
		}).blur(function() {
		    if (this === last_input && ret.val() !== '') {
			linput();
		    }
		});
		last_input = ret[0];
		setTimeout(function() {
		    ret.focus();
		}, 10);
	    };
	    linput();

	    var styles = {
		ul : {
		    circle : '空心圆',
		    none : '无标记',
		    disc : '实心圆',
		    square : '实心方块'
		},
		ol : {
		    decimal : '数字',
		    'lower-roman' : 'i, ii, iii',
		    'upper-roman' : 'I, II, III',
		    'lower-greek' : 'αβγ',
		    'lower-latin' : 'abc',
		    'upper-latin' : 'ABC',
		    'cjk-ideographic' : '一二三',
		    hiragana : 'あいう',
		    katakana : 'アイウ',
		    'hiragana-iroha' : 'いろは',
		    'katakana-iroha' : 'イロハ'
		}
	    };

	    var list_type_change = function() {
		list_mark_type.html(createOptions(styles[this.value]));
		list_mark_type_change.call(list_mark_type[0]);
	    },
	    list_mark_type_change = function() {
		input_list.css('list-style-type', this.value);
	    },
	    list_mark_type = $('<select></select>').change(list_mark_type_change),
	    list_type = $('<select></select>', {
		html : '<option value="ul" selected="selected">无序列表</option><option value="ol">有序列表</option>'
	    }).change(list_type_change);
	    list_type_change.call(list_type[0]);

	    var dialog = $('<div></div>', {
		title : '编辑列表，按Tab或Enter可以直接产生新项目'
	    }).append(list_type).append(list_mark_type).append(input_list),
	    close = function() {
		dialog.dialog('close');
	    };
	    dialog.dialog({
		width : '35em',
		modal : true,
		autoOpen : true,
		buttons : {
		    OK : function() {
			var str = '[' + list_type.val() + '=' + list_mark_type.val() + "]\n";
			input_list.find('input').each(function() {
			    var val = this.value.trim();
			    if (val !== '') {
				str += '[li]' + val + "[/li]\n";
			    }
			});
			str += '[/' + list_type.val() + "]\n";
			doInsert(str, '', false);
			close();
		    },
		    Cancel : close
		},
		'close' : function() {
		    dialog.remove();
		}
	    });
//	    tag_list(lang.js_prompt_enter_item, lang.js_prompt_error);
	}),
	$('<input />', {
	    'class' : 'codebuttons',
	    type : 'button',
	    name : 'quote',
	    value : 'QUOTE'
	}).click(function() {
	    simpletag('quote');
	})).find('input').button().end().buttonset()],

    selections = function(header, opts, callback, css) {
	var currentLi,
	openMenu = function(e) {
	    menuitems.show().position({
		my: "left top",
		at: "left bottom",
		of: menu
            });
	    var counter = 2,
	    handler = function() {
		counter -= 1;
		if (counter === 0) {
	    	    menuitems.hide();
		    $(document).unbind('click', handler);
		}
            };
            $( document ).click(handler);
	    e.preventDefault();
	},
	btn = $('<button>' + header + '</button>').button().click(function(e) {
	    if (currentLi === undefined) {
		openMenu(e);
	    }
	    else {
		e.preventDefault();
		currentLi.click();
	    }
	}),
	menuitems = $('<ul class="bbcode-menu">' + $.map(opts, function(item, idx) {
	    var attrs = item.attrs || '';
	    if (css) {
		attrs += ' style="' + css + ':' + item.val + '"';
	    }
	    return '<li val="' + item.val + '"><a href="#"' + attrs + '>' + item.title + '</a></li>';
	}).join('') + '</ul>').hide().menu().on('click', 'li', function(e) {
	    e.preventDefault();
	    currentLi = this;
	    btn.button('option', 'label', this.innerText);
	    if (callback) {
		callback.call(this);
	    }
	}),
	menu = $('<button>' + header + '</button>').button({
	    text: false,
	    icons: {
		primary: "ui-icon-triangle-1-s"
            }
	}).click(openMenu),
	btnset = $('<span/>').append(btn).append(menu).buttonset(),
	wrapper = $('<span/>').append(btnset).append(menuitems);
	return wrapper;
    },

    colorOpts = [{"title":"Black","val":"Black"},{"title":"Sienna","val":"Sienna"},{"title":"Dark Olive Green","val":"DarkOliveGreen"},{"title":"Dark Green","val":"DarkGreen"},{"title":"Dark Slate Blue","val":"DarkSlateBlue"},{"title":"Navy","val":"Navy"},{"title":"Indigo","val":"Indigo"},{"title":"Dark Slate Gray","val":"DarkSlateGray"},{"title":"Dark Red","val":"DarkRed"},{"title":"Dark Orange","val":"DarkOrange"},{"title":"Olive","val":"Olive"},{"title":"Green","val":"Green"},{"title":"Teal","val":"Teal"},{"title":"Blue","val":"Blue"},{"title":"Slate Gray","val":"SlateGray"},{"title":"Dim Gray","val":"DimGray"},{"title":"Red","val":"Red"},{"title":"Sandy Brown","val":"SandyBrown"},{"title":"Yellow Green","val":"YellowGreen"},{"title":"Sea Green","val":"SeaGreen"},{"title":"Medium Turquoise","val":"MediumTurquoise"},{"title":"Royal Blue","val":"RoyalBlue"},{"title":"Purple","val":"Purple"},{"title":"Gray","val":"Gray"},{"title":"Magenta","val":"Magenta"},{"title":"Orange","val":"Orange"},{"title":"Yellow","val":"Yellow"},{"title":"Lime","val":"Lime"},{"title":"Cyan","val":"Cyan"},{"title":"Deep Sky Blue","val":"DeepSkyBlue"},{"title":"Dark Orchid","val":"DarkOrchid"},{"title":"Silver","val":"Silver"},{"title":"Pink","val":"Pink"},{"title":"Wheat","val":"Wheat"},{"title":"Lemon Chiffon","val":"LemonChiffon"},{"title":"Pale Green","val":"PaleGreen"},{"title":"Pale Turquoise","val":"PaleTurquoise"},{"title":"Light Blue","val":"LightBlue"},{"title":"Plum","val":"Plum"},{"title":"White","val":"White"}],
    fontOpts = [{"title":"Arial","val":"Arial"},{"title":"Arial Black","val":"Arial Black"},{"title":"Arial Narrow","val":"Arial Narrow"},{"title":"Book Antiqua","val":"Book Antiqua"},{"title":"Century Gothic","val":"Century Gothic"},{"title":"Comic Sans MS","val":"Comic Sans MS"},{"title":"Courier New","val":"Courier New"},{"title":"Fixedsys","val":"Fixedsys"},{"title":"Garamond","val":"Garamond"},{"title":"Georgia","val":"Georgia"},{"title":"Impact","val":"Impact"},{"title":"Lucida Console","val":"Lucida Console"},{"title":"Lucida Sans Unicode","val":"Lucida Sans Unicode"},{"title":"Microsoft Sans Serif","val":"Microsoft Sans Serif"},{"title":"Palatino Linotype","val":"Palatino Linotype"},{"title":"System","val":"System"},{"title":"Tahoma","val":"Tahoma"},{"title":"Times New Roman","val":"Times New Roman"},{"title":"Trebuchet MS","val":"Trebuchet MS"},{"title":"Verdana","val":"Verdana"}],
    sizeOpts = [{"title":"1","val":"1", "attrs":"style=\"font-size:x-small\""},{"title":"2","val":"2", "attrs":"style=\"font-size:small\""},{"title":"3","val":"3", "attrs":"style=\"font-size:medium\""},{"title":"4","val":"4", "attrs":"style=\"font-size:large\""},{"title":"5","val":"5", "attrs":"style=\"font-size:x-large\""},{"title":"6","val":"6", "attrs":"style=\"font-size:xx-large\""},{"title":"7","val":"7", "attrs":"style=\"font-size:48px\""}];

    buttons.push(selections(lang.select_color, colorOpts, function() {
	alterfont(this.getAttribute('val'), 'color');
    }, 'color'));
    buttons.push(selections(lang.select_font, fontOpts, function() {
    	alterfont(this.getAttribute('val'), 'font');
    }, 'font-family'));    
    buttons.push(selections(lang.select_size, sizeOpts, function() {
    	alterfont(this.getAttribute('val'), 'size');
    })); 
  

    var toolbar = $('#bbcode-toolbar');
    $.each(buttons, function(idx,itm) {
    	toolbar.append(itm);
    })
});

// preview.js
$(function() {
    var $tar = $('#commit-btn'),
    $previewouter = $('#previewouter'),
    $editorouter = $('#editorouter'),
    previewing = false,

    $preview = $('<input />', {
	type : 'button',
	'class' : 'btn2',
	id : 'previewbutton',
	value : hb.constant.lang.submit_preview
    }).click(function() {
	if (!previewing) {
	    $.post('preview.php', {body : $('#body').val()}, function(res) {
		$preview.val(hb.constant.lang.submit_edit);
		$previewouter.html(res).slideDown();
		$editorouter.slideUp();
		previewing = true;
	    }, 'html');
	}
	else {
	    $preview.val(hb.constant.lang.submit_preview);
	    $previewouter.html('').slideUp();
	    $editorouter.slideDown();
	    previewing = false;
	}
    }).appendTo($tar);
    $tar.find('input').button().parent().buttonset();
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

