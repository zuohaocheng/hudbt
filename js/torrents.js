$(function() {
    "use strict";
    var table = $('#torrents'),
    target = $('#torrents>tbody'),
    tooltip = $('#tooltipPool'),
    targetH = target.height(),
    surfix = '&format=xhr',
    isManager = (parseInt(hb.config.user['class']) >= hb.constant.torrentmanage_class),
    $document = $(document),
    $window = $(window),
    ie = $.browser.msie,
    ie8 = ie && $.browser.version < 9,

    History = !ie,

    args = argsFromUri(window.location.search),
    oripage = parseInt(args.page) || 0,
    disableAutoPaging = false,

    //Auto paging switch
    auto_paging_switch = $('<input />', {
	type : 'checkbox',
	id : 'disable-autopaging',
	title : '换了浏览器要重新设置的亲'
    }).click(function() {
	disableAutoPaging = (auto_paging_switch.attr('checked') === 'checked');
	$.jStorage.set('disableAutoPaging', disableAutoPaging);
    });
    if ($.jStorage.get('disableAutoPaging', false)) {
	disableAutoPaging = true;
	auto_paging_switch.attr('checked', 'checked');
    }
    $('#hotbox>ul').append($('<li></li>').append(auto_paging_switch).append($('<label></label>', {
	text : '禁用自动翻页',
	'for' : 'disable-autopaging',
	title : '刷新后生效'
    })));

    $(document).ajaxError(function() {
	jqui_dialog('失败','网络连接有问题', 5000, function() {
	    location.reload();
	});
    });

    var $sortHeaders = table.find('thead th:not(.unsortable)'),
    sortHeader = function(isJs) {
	if (isJs) {
	    $sortHeaders.unbind('click');
	    table.tablesorter();
	    return;
	}
	var sortcol,
	sortcoltype;
	if ($.isArray(args)) {
	    var deletes = [];
	    $.each(args, function(idx, obj) {
		if (obj.name === 'sort') {
		    sortcol = obj.value;
		    deletes.push(idx);
		}
		else if (obj.name === 'type') {
		    sortcoltype = obj.value
		    deletes.push(idx);
		}
	    });
	    $.each(deletes.sort(), function(idx, obj) {
		args.splice(obj, 1);
	    });
	}
	else {
	    sortcol = args.sort;
	    sortcoltype = args.type;
	    delete args.sort;
	    delete args.type;
	}

	$sortHeaders.each(function() {
	    var $this = $(this),
	    col = $this.attr('value'),
	    sorttype,
	    sortclass = 'headerSort',
	    coltitle;
	    if (sortcol === col) {
		if (sortcoltype === 'desc') {
		    sorttype = 'asc';
		    sortclass += ' headerSortUp';
		}
		else {
		    sorttype = 'desc';
		    sortclass += ' headerSortDown';
		}
	    }
	    else {
		if (col === '1' || col ==='4') {
		    sorttype = 'asc';
		    if (col === '4' && typeof(sortcol) === 'undefined') {
			sortclass += ' headerSortUp';
		    }
		}
		else {
		    sorttype = 'desc';
		}
	    }

	    if (sorttype === 'asc') {
		coltitle = '升序排序';
	    }
	    else {
		coltitle = '降序排序';
	    }

	    $this.attr({
		'class': sortclass,
		title : coltitle
	    }).unbind('click').click(function() {
		if ($.isArray(args)) {
		    var sort = false,
		    type = false;
		    $.each(args, function(idx, obj) {
			if (obj.name === 'sort') {
			    obj.value = col;
			    sort = true;
			}
			else if (obj.name === 'type') {
			    obj.value = sorttype;
			    type = true;
			}
		    });
		    if (!sort) {
			args.push({name : 'sort', value :col});
		    }
		    if (!type) {
			args.push({name : 'type', value : sorttype});
		    }
		}
		else {
		    args.sort = col;
		    args.type = sorttype;
		}
		getFromUriWithHistory(args);
	    });
	});
    };
    table.find('thead th:not(.unsortable)').each(function() {
	var $this = $(this);
	$this.html($this.find('a').html());
    });
    sortHeader(!hb.nextpage);

//    Go to content
    var goToContent = (function() {
    	var $top = $('#content-marker');
    	return function() {
    	    var top = $top.offset().top;
    	    scrollToPosition(top);
    	};
    })(),


    //Spin loader
    loader = (function() {
	var $loader = $('#loader'),
	loaderInt,
	step = 0,
	lock = false;
	return function(show) {
	    if (show) {
		if(lock) {
		    return false;
		}
		else {
		    lock = true;
		}

		$loader.show();
		$loader.attr('class', 'loader' + step);
		loaderInt = setInterval(function() {
		    step += 1;
		    if (step == 12) {
			step = 0;
		    }
		    $loader.attr('class', 'loader' + step);
		}, 100);
	    }
	    else {
		$loader.hide();
		clearInterval(loaderInt);
		lock = false;
	    }
	    return true;
	};
    })(),

    //Auto filling width
    //CAUTION: This method have critical problems in performance under ie
    setTitleWidth;
    if (ie) {
    	setTitleWidth = function(targets) {
    	    return false;
    	}
    }
    else {
	var modifyCss = function(wThis, target, wDecorations) {
	    var ref = wThis - wDecorations - 6;
	    if (target[0].offsetWidth > ref) {
		target.css('width', ref + 'px');
	    }
	}

	setTitleWidth = function(targets) {
	    //console.profile();
	    targets.each(function() {
		var $this = $(this),
		wThis = $this.width(),

		$prs = $this.find('ul.prs'),
		pr0 = $prs[0],
		pr1 = $prs[1],
		img = $this.find('img.sticky')[0],

		wTitleD = 0,
		wDescD = 0;
		if (pr0) {
		    wTitleD += pr0.offsetWidth;
		}
		if (img) {
		    wTitleD += img.offsetWidth;
		}
		if (pr1) {
		    wDescD += pr1.offsetWidth;
		}

		modifyCss(wThis, $this.find('h2'), wTitleD);
	    });
	    //console.profileEnd();
	};
	setTitleWidth($('td.torrent div.limit-width.minor-list'));
    }

    //Handling quick delete
    if (isManager) {
	target.on('click', '.staff-quick-delete', function(e) {
	    var id = argsFromUri(this.href).id;
	    if (id) {
		e.preventDefault();
		deleteTorrent(id);
	    }
	}).on('click', '.staff-quick-edit', function (e) {
	    var id = argsFromUri(this.href).id;
	    if (id) {
		e.preventDefault();
		editTorrent(id);
	    }
	});
    }

    var get_func = function(res) {
	//	    var start = ((new Date()).getTime());
	var catDict = hb['constant'].cat_class,
	targetsAppearance = [],
	tableLength = target.find('tr').length - 1,
	returnto = document.location.pathname + document.location.search;
	if (isManager) {
	    var page = argsFromUri(this.url).page,
	    pagesurfix = '';
	    if (page) {
		pagesurfix = 'page=' + page;

		if (document.location.search) {
		    returnto += '&' + pagesurfix;
		}
		else {
		    returnto += '?' + pagesurfix;
		}
	    }
	}
	returnto = encodeURIComponent(returnto);
	
	target.append(res.torrents);
	tooltip.append(res.tooltips);

	loader(false);
	var newRows = (tableLength == -1) ? target.find('tr') : target.find('tr:gt(' + tableLength + ')');
	if (!ie) {
	    setTitleWidth(newRows.find('div.limit-width.minor-list'));
	}

	var cont = res['continue'];
	if (cont && !disableAutoPaging) {
	    var uri = cont + surfix,

	    targetH = target.height();
	    $document.scroll(function() {
		var loc = $document.scrollTop() + $window.height();
		if(loc > targetH && loader(true)) {
		    args = argsFromUri(uri);
		    $.getJSON(uri, {
			counter: target.children().length
		    }, get_func);
		    $document.unbind('scroll');
		}
	    });
	}
	else {
	    $('#pagertop').remove();
	    sortHeader(true);
	}
	//	    var end = ((new Date()).getTime());
	//	    console.log(end - start);
    };

    //Auto scroll
    if (hb.nextpage !== '' && !disableAutoPaging) {
	$document.scroll(function(){
	    var loc = $document.scrollTop() + $(window).height();
	    if(loc > targetH && loader(true)) {
		var uri = hb.nextpage + surfix;
		args = argsFromUri(uri);
		$.getJSON(uri, {
		    counter: target.children().length
		}, function(res) {
		    $('#pagerbottom').remove();
		    get_func.call(this, res);
		});
		$document.unbind('scroll');
    	    }
	});
    }

    var getFromUri = function(uri) {
	if (!loader(true)) {
	    return false;
	}
	$('.pages').remove();
	target.children().remove();
	tooltip.children().remove();
	table.hide();
	$('#stderr').remove();
	$.getJSON('?' + $.param(uri) + surfix, function(result) {
	    var targ = $('#outer');
	    if (result.torrents.length) {
		table.before(result.pager.top);
		sortHeader(!result['continue']);
		table.show();
		table.after(result.pager.bottom);
		$('.pages a[href^="?"]').click(function(e) {
		    e.preventDefault();
		    var uri = argsFromUri(this.href);
		    getFromUriWithHistory(uri);
		});

		$document.unbind('scroll');
		get_func.call(this, result);
	    }
	    else {
		loader(false);
		targ.append('<div id="stderr"><h2>没有结果！</h2><div class="table td frame">没有种子:(</div></div>');
	    }
	    goToContent();
	    oripage = parseInt(argsFromUri(document.location.search).page) || 0;
	});
    },

    //Ajax search
    getFromUriWithHistory = function(argu) {
	var uri = '?' + $.param(argu),
	state = {
	    title : document.title,
	    url : uri
	}
	if (History) {
	    window.history.pushState(state, document.title, uri);
	}

	args = argu;
	getFromUri(args);
    },

    searchboxform = $('#form-searchbox').submit(function(e) {
	e.preventDefault();
	var query = searchboxform.serializeArray();
	getFromUriWithHistory(query);
    });

    $('a[href^="?"]').click(function(e) {
	e.preventDefault();
	var uri = argsFromUri(this.href);
	getFromUriWithHistory(uri);
    });

    if (History) {
	var initialURL = window.location.href,
	state = {
	    title : document.title,
	    url : initialURL
	}
	window.addEventListener('popstate', function(e){
            var initialPop = !(('state' in e) && e.state) && location.href == initialURL;
            if (initialPop) return;
	    var t;
	    if (hb.config.torrents_query !== '') {
		t = '?' + hb.config.torrents_query;
	    }
	    else {
		t = '';
	    }
	    var backPop = (t === location.search);
	    if (backPop) return;
    	    getFromUri(argsFromUri(window.location.href));
	}, false);
	window.history.replaceState(state, document.title, initialURL);

	(function() {
	    var rowHeight = target.find('tr').height() || 42,
	    offsetT = target.offset().top,
	    page = argsFromUri(document.location.search).page || 0,
	    onscroll = function() {
		var loc = $document.scrollTop() - offsetT,
		row = loc / rowHeight + 5,
		npage = Math.floor(row / 50) + oripage;
		if (npage != page && npage >= oripage) {
		    args = argsFromUri(document.location.search);
		    page = npage;
		    args.page = page;
		    var uri = '?' + $.param(args),
		    state = {
			title : document.title,
			url : uri
		    }
		    window.history.replaceState(state, document.title, uri);
		}
	    };

	    $window.scroll(onscroll);
	})();
    }

    //Check items in searchbox
    var mainCheckClicked = false,
    dictChecks = [],

    check = function(item) {
	item.attr('checked', 'checked');
    },
    uncheck = function(item) {
	item.removeAttr('checked');
    },
    mainChecked = function(mainCheck, catChecks) {
	var setCheck;
	if (mainCheck.attr('checked')) {
	    setCheck = check;
	}
	else {
	    setCheck = uncheck;
	}

	$.each(catChecks, function(idx, item) {
	    setCheck(item);
	});
    },
    catClicked = function (isMain, mainCheck, catChecks) {
	if (isMain) {
	    if (!mainCheckClicked) {
		mainCheckClicked = true;
		$.each(dictChecks, function(idx, item) {
		    var main = item.main,
		    cats = item.cats;
		    mainChecked(main, cats);
		});
	    }
	    else {
		mainChecked(mainCheck, catChecks);
	    }
	}
	else {
	    mainCheckClicked = false;

	    var alltrue = true;
	    $.each(catChecks, function(idx, item) {
		alltrue = item.attr('checked') && alltrue;
	    });
	    
	    if (alltrue) {
		check(mainCheck);
	    }
	    else {
		uncheck(mainCheck);
	    }
	}
    },

    allCatChecks = $.map(hb.constant.maincats, function(item, idx) {
	var mainCheck = $('#cat' + idx),
	catChecks = $.map(item, function(cat) {
	    return $('#cat' + cat);
	});

	mainCheck.click(function() {
	    catClicked(true, mainCheck, catChecks);
	});

	$.each(catChecks, function(idx, item) {
	    item.click(function() {
		catClicked(false, mainCheck, catChecks);
	    });
	});

	dictChecks.push({
	    main :mainCheck,
	    cats : catChecks
	});
	return catChecks;
    });

    //Use exact in imdb & username
    $('[name="search_area"]').change(function() {
	if (parseInt(this.value) >= 3) {  //Username or imdb
	    $('[name="search_mode"]').val('3');
	}
	else {
	    $('[name="search_mode"]').val('0');
	}
    });

    //Auto complete
    var cache = {},
    lastXhr;
    $( "#searchinput" ).autocomplete({
	minLength: 2,
	source: function( request, response ) {
	    var term = request.term;
	    if ( term in cache ) {
		response( cache[ term ] );
		return;
	    }

	    lastXhr = $.getJSON( "suggest.php", request, function( data, status, xhr ) {
		cache[ term ] = data;
		if ( xhr === lastXhr ) {
		    response( data );
		}
	    });
	}
    }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
	var stime = item.count + ' Time';
	if (item.count > 1) {
	    stime += 's';
	}

	return $( "<li></li>" )
	    .data( "item.autocomplete", item )
	    .append( "<a>" + item.label + '<span class="suggestion-count">' + stime + "</span></a>" )
	    .appendTo( ul );
    };


});

//Select all
var form='searchbox';
function SetChecked(chkName,ctrlName,checkall_name,uncheckall_name,start,count) {
    var dml=document.forms[form];
    var len = dml.elements.length;
    var begin;
    var end;
    var check_state;
    if (start == -1){
	begin = 0;
	end = len;
    }
    else{
	begin = start;
	end = start + count;
    }
    var check_state;
    var button = document.getElementById(ctrlName)

    if(button.value == checkall_name) {
	button.value = uncheckall_name;
	check_state=1;
    }
    else {
	button.value = checkall_name;
	check_state=0;
    }

    for( i=begin ; i<end ; i++) {
	if (dml.elements[i].name.indexOf(chkName) != -1) {
	    dml.elements[i].checked=check_state;
	}

    }
}
