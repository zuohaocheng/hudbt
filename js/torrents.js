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
    ie = $.browser.msie && $.browser.version < 10,
    jqxhr, abortflag = false,

    History = ('pushState' in window.history),
    errCount = 0,

    args = argsFromUri(window.location.search),
    oripage = parseInt(args.page) || 0,
    disableAutoPaging = false;

    //Auto paging switch
    $('#hotbox>ul').append($('<li title="换了浏览器要重新设置的亲"><label><input type="checkbox" />禁用自动翻页</label></li>').find('input').click(function() {
	disableAutoPaging = this.checked;
	$.jStorage.set('disableAutoPaging', disableAutoPaging);
    }).each(function() {
	if ($.jStorage.get('disableAutoPaging', false)) {
	    disableAutoPaging = true;
	    this.checked = true;
	}
    }).end());

    $(document).ajaxError(function(event, jqxhr, settings) {
	if (abortflag) {
	    return;
	}
	errCount += 1;
	if (errCount > 5) {
	    if (!('onLine' in navigator) || navigator.onLine) {
		jqui_confirm('哎呀','好像有点问题，刷新看看?', function() {
		    location.search = '?'+$.param(args); //reload
		});
	    }
	}
	else {
	    $.ajax(settings);
	}
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
	step = 0;
	return function(show) {
	    if (show) {
		if (jqxhr) {
		    abortflag = true;
		    jqxhr.abort();
		    abortflag = false;
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
		jqxhr = null;
	    }
	    return true;
	};
    })(),

    queue = [],
    modifyCss = function(wThis, target, wDecorations) {
	var ref = wThis - wDecorations - 6;
	if (target.offsetWidth > ref) {
	    queue.push([target, ref]);
	}
    },
    commit = function() {
	$.each(queue, function() {
	    var target = this[0], 
	    ref = this[1];
	    target.style.width = ref + 'px';
	});
	queue = [];
    },

    setTitleWidth = function(targets) {
	var wThis = 0,
	t = function() {
	    targets.each(function() {
		var $this = $(this),
		wThis = wThis || $this.width(), // buffer width

		$prs = $this.find('ul.prs'),
		pr0 = $prs[0],
		img = $this.find('img.sticky')[0],

		wTitleD = 0;

		if (pr0 && pr0.children.length) {
		    wTitleD += pr0.offsetWidth;
		}
		if (img) {
		    wTitleD += img.offsetWidth;
		}

		modifyCss(wThis, $this.find('h2')[0], wTitleD);
	    });
	    commit();
	};
	if (ie) {
	    // This method costs a lot in IE
	    setTimeout(t, 50);
	}
	else {
	    t();
	}
    };
    setTitleWidth($('td.torrent div.limit-width.minor-list'));

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
	setTitleWidth(newRows.find('div.limit-width.minor-list'));

	var cont = res['continue'];
	if (cont && !disableAutoPaging) {
	    var uri = cont + surfix,

	    targetH = target.height();
	    $window.on('scroll.autopage', function() {
		var loc = $document.scrollTop() + $window.height();
		if(loc > targetH && loader(true)) {
		    args = argsFromUri(uri);
		    jqxhr = $.getJSON(uri, {
			counter: target.children().length
		    }, get_func);
		    $window.off('scroll.autopage');
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
	$window.on('scroll.autopage', function(){
	    var loc = $document.scrollTop() + $(window).height();
	    if(loc > targetH && loader(true)) {
		var uri = hb.nextpage + surfix;
		args = argsFromUri(uri);
		jqxhr = $.getJSON(uri, {
		    counter: target.children().length
		}, function(res) {
		    $('#pagerbottom').remove();
		    get_func.call(this, res);
		});
		$window.off('scroll.autopage');
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
	jqxhr = $.getJSON('?' + $.param(uri) + surfix, function(result) {
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

		$window.off('scroll.autopage');
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

    mainChecked = function() {
	this.cats.prop('checked', this.main.checked);
    },
    mainCatClicked = function(self) {
	if (!mainCheckClicked) {
	    mainCheckClicked = true;
	    $.each(dictChecks, mainChecked);
	}
	else {
	    mainChecked.call(self);
	}
    },
    catClicked = function (self) {
	mainCheckClicked = false;
	self.main.checked = (self.cats.filter(':not(:checked)').length === 0);
    };

    $.each(hb.constant.maincats, function(idx) {
	var mainCheck = $('#cat' + idx),
	catChecks = $($.map(this, function(cat) {
	    return '#cat' + cat;
	}).join(', ')),

	i = dictChecks.push({
	    main : mainCheck[0],
	    cats : catChecks
	}) - 1;

	mainCheck.click(function() {
	    mainCatClicked(dictChecks[i]);
	});

	catChecks.click(function() {
	    catClicked(dictChecks[i]);
	});
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
