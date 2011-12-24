$(function() {
    var table = $('#torrents');
    var target = $('#torrents>tbody');
    var targetH = target.height();
    var surfix = '&format=json';
    var lang = hb.constant.lang;
    var isManager = (parseInt(hb.config.user['class']) >= hb.constant.torrentmanage_class);
    var $document = $(document);
    var $window = $(window);
    var ie = $.browser.msie;
    var ie8 = ie && $.browser.version < 9;

    var args = argsFromUri(window.location.search);

    var $sortHeaders = table.find('thead th:not(.unsortable)');
    var sortHeader = function(isJs) {
	if (isJs) {
	    $sortHeaders.unbind('click');
	    table.tablesorter();
	    return;
	}
	var sortcol = args.sort;
	var sortcoltype = args.type;
	delete args.sort;
	delete args.type;

	$sortHeaders.each(function() {
	    var $this = $(this);
	    var col = $this.attr('value');
	    var sorttype;
	    var sortclass = 'headerSort';
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

	    var coltitle;
	    if (sorttype === 'asc') {
		coltitle = '升序排序';
	    }
	    else {
		coltitle = '降序排序';
	    }

	    args.sort = col;
	    args.type = sorttype;
	    var sortUri = '?' + $.param(args);
	    delete args.sort;
	    delete args.type;
	    $this.attr({
		'class': sortclass,
		title : coltitle
	    }).unbind('click').click(function() {
		getFromUriWithHistory(sortUri);
	    });
	});
    };
    table.find('thead th:not(.unsortable)').each(function() {
	var $this = $(this);
	$this.html($this.find('a').html());
    });
    sortHeader(!hb.nextpage);

    //Go to content
    var goToContent = (function() {
	var $top = $('#content-marker');
	return function() {
	    var top = $top.offset().top;
	    scrollToPosition(top);
	};
    })();


    //Spin loader
    var loader = (function() {
	var $loader = $('#loader');
	var loaderInt;
	var step = 0;
	var lock = false;
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
    })();

    //Auto filling width
    //CAUTION: This method have critical problems in performance under ie
    var setTitleWidth;
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
		var $this = $(this);
		var wThis = $this.width();

		var $prs = $this.find('ul.prs');
		var pr0 = $prs[0];
		var pr1 = $prs[1];
		var img = $this.find('img.sticky')[0];

		var wTitleD = 0;
		var wDescD = 0;
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
		modifyCss(wThis, $this.find('h3'), wDescD);
	    });
	    //console.profileEnd();
	};
	setTitleWidth($('td.torrent div.limit-width.minor-list'));
    }

    //Handling quick delete
    var quickDelete;
    if (isManager) {
	quickDelete = function(a) {
	    a.click(function(e) {
		var $this = $(this);
		e.preventDefault();
		var tr = $this.parent().parent().parent().parent().parent();
		if (confirm('确认删除本种子?')) {
		    $.post($this.attr('href') + '&sure=1&format=json', function(res) {
			if (res.success) {
			    tr.remove();
			}
		    }, 'json');
		}
	    });
	};
	quickDelete(target.find('.staff-quick-delete'));
    }
    else {
	quickDelete = function() {};
    }

    //Convert json to html
    var user_out = function(user) {
	var userClass = user['class'].canonical;
	var userCss = userClass.replace(/\s/g, '') + '_Name username';
	var href = 'userdetails.php?id=' + user.id;
	var out = '<span class="nowrap"><a href="' + href + '" class="' + userCss + '">' + user.username + '</a>';

	if (user.donor) {
	    out += '<img src="pic/trans.gif" alt="Donor" class="star"/>';
	}
	out += '</span>';
	return out;
    };

    var addNumber = function(str, hrefZero, href, zeroClass) {
	var out = '<td><a';
	var comments_num = parseInt(str);
	if (comments_num === 0) {
	    href = hrefZero;
	}

	if (href) {
	    out += ' href="' + href + '"'
	}

	if (comments_num !== 0) {
	    out +=' class="important"'
	}
	else if (zeroClass) {
	    out +=' class="' + zeroClass + '"'			
	}
	out += '>' + str + '</a></td>';

	return out;
    }

    var get_func = function(res) {
	//	    var start = ((new Date()).getTime());
	var catDict = hb['constant'].cat_class;
	var targetsAppearance = [];
	var tableLength = target.find('tr').length - 1;
	$.each(res.torrents, function(idx, torrent) {
	    var id = torrent.id;
	    var tr = '<tr>';

	    var catid = torrent.catid;
	    var catProp = catDict[catid];
	    var cat = '<td class="nowrap category-icon">';
	    if (catProp) {
		var href = '?cat=' + catid;
		cat += '<a href="' + href + '"><img src="pic/cattrans.gif" alt="' + catProp.name + '" title="' + catProp.name + '" class="' + catProp.class_name + '" />';
	    }
	    cat += '</td>';
	    tr += cat;

	    var title_td = '<td class="torrent">';
	    var title_desc = '<div class="limit-width minor-list">';

	    var bookmarkClass = torrent.bookmarked ? 'bookmark' : 'delbookmark';

	    var textMainTitle = torrent.name;
	    var textSubTitle = torrent.desc;

	    if (args.swaph && textSubTitle !== '') {
		var buf = textMainTitle;
		textMainTitle = textSubTitle;
		textSubTitle = buf;
	    }

	    href = 'details.php?id=' + id + '&hit=1';
	    var mainTitle = '';
	    if (torrent.sticky) {
		mainTitle += '<img class="sticky" src="pic/trans.gif" alt="Sticky" title="' + lang.text_sticky + '">';
	    }
	    mainTitle += '<h2 class="transparentbg"><a href="' + href + '" title="' + textMainTitle + '">' + textMainTitle + '</a></h2>';
	    var desc;
	    if (textSubTitle === '') {
		desc = '<h3 class="placeholder"></h3>';
	    }
	    else {
		desc = '<h3 title="' + textSubTitle + '">' + textSubTitle + '</h3>';
	    }

	    var div_main = '<div class="torrent-title">' + mainTitle;
	    var div_desc = '<div class="torrent-title">' + desc;

	    var mainTitleDecorators = '<ul class="prs">';

	    if (torrent['new']) {
		mainTitleDecorators += '<li>(<span class="new">' + lang.text_new_uppercase + '</span>)</li>';
	    }

	    var picktype = torrent.picktype;
	    if (picktype) {
		var ptype = picktype;
		var hptype = '<li><span>[<span class="' + ptype + '">' + lang['text_' + ptype] + '</span>]</span></li>'
		mainTitleDecorators += hptype;
	    }

	    var pr = torrent.pr;
	    if (pr) {
		var state = pr.state;
		var prDict = hb.constant.pr[state - 1];
		var prLabel = '<li><img class="' + prDict.name + '" alt="' + lang[prDict.lang] + '" src="pic/trans.gif" /></li>'
		mainTitleDecorators += prLabel;

		var expire = pr.expire;
		var hexpire = '<ul class="prs"><li><span>[<span class="';
		var $time = $('<span></span>', {text : lang.text_will_end_in});
		if (expire) {
		    hexpire += 'pr-limit" title="' + expire.raw + '">' + expire.canonical;
		}
		else {
		    hexpire += 'pr-eternal">' + lang.text_forever;
		}
		hexpire += '</span>]</span></li></ul>';
		desc += hexpire;
	    }
	    div_desc += '</div>';

	    if (torrent.oday) {
		var oday = '<li><img src="pic/ico_0day.gif" alt="' + lang.text_oday + '" title="' + lang.text_oday + '"/></li>';
		mainTitleDecorators += oday;
	    }

	    if (torrent.banned) {
		var banned = '<li><span>(<span class="striking">' + lang.text_banned + '</span>)</span></li>'
		mainTitleDecorators += banned;
	    }
	    div_main += mainTitleDecorators + '</ul></div>';
	    title_desc += div_main + div_desc;
	    var title = '<div>' + title_desc + '</div>';

	    var torrent_utitly = '<div class="torrent-utilty-icons minor-list-vertical"><ul><li><a href="download.php?id=' + id + '&hit=1"><img class="download" src="pic/trans.gif" alt="download" title="' + lang.title_download_torrent + '" /></a></li><li><a id="bookmark' + id + '" href="javascript: bookmark(' + id + ');"><img class="' + bookmarkClass + '" alt="bookmark" src="pic/trans.gif" title="' + lang.title_bookmark_torrent + '" /></a></li></ul></div>';

	    title += torrent_utitly;
	    title_td += title + '</div></td>';
	    tr += title_td;

	    tr += addNumber(torrent.comments.count, 'comment.php?action=add&pid=' + id + '&type=torrent', 'details.php?id=' + id + '&hit=1&cmtpage=1#startcomments');

	    var time = '<td>' + torrent.added.canonical + '</td>';
	    tr += time;

	    var size = '<td>' + torrent.size.canonical + '</td>';
	    tr += size;

	    var seeders = addNumber(torrent.seeders, '', 'details.php?id=' + id + '&hit=1&dllist=1#seeders', 'no-seeders');
	    tr += seeders;

	    var leechers = addNumber(torrent.leechers, '', 'details.php?id=' + id + '&hit=1&dllist=1#leechers');
	    tr += leechers;

	    var completed = addNumber(torrent.times_completed, '', 'viewsnatches.php?id=' + id);
	    tr += completed;

	    var towner = '<td>';
	    var owner = torrent.owner;
	    if (owner.anonymous) {
		towner += lang.text_anonymous;
	    }
	    
	    var user = owner.user;
	    if (user) {
		if (owner.anonymous) {
		    towner += '<br />(';
		}
		towner += user_out(user);
		if (owner.anonymous) {
		    towner += ')';
		}
	    }
	    else if (!owner.anonymous) {
		towner += lang.text_orphaned;
	    }
	    towner += '</td>';
	    tr += towner;

	    if (isManager) {
		href = 'fastdelete.php?id=' + id;
		var fastdelete = '<li><a href="' + href + '" class="staff-quick-delete"><img class="staff_delete" alt="D" src="pic/trans.gif" title="' + lang.text_delete + '" /></a></li>';

		href = 'edit.php?id=' + id + '&returnto=' + encodeURIComponent(document.location.pathname + document.location.search);
		var fastedit = '<li><a href="' + href + '"><img class="staff_edit" alt="E" src="pic/trans.gif" title="' + lang.text_edit + '" /></a></li>';

		var edit = '<td><div class="minor-list-vertical"><ul>' + fastdelete + fastedit + '</ul></div></td>';
		tr += edit;
	    }
	    
	    tr += '</tr>';
	    target.append(tr);
	});

	loader(false);
	var newRows = (tableLength == -1) ? target.find('tr') : target.find('tr:gt(' + tableLength + ')');
	if (!ie) {
	    setTitleWidth(newRows.find('div.limit-width.minor-list'));
	}
	if (isManager) {
	    quickDelete(newRows.find('.staff-quick-delete'));
	}

	var cont = res['continue'];
	if (cont) {
	    var uri = cont + surfix;

	    var targetH = target.height();
	    $document.scroll(function() {
		var loc = $document.scrollTop() + $window.height();
		if(loc > targetH && loader(true)) {
		    args = argsFromUri(uri);
		    $.getJSON(uri, get_func);
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
    if (hb.nextpage !== '') {
	$document.scroll(function(){
	    var loc = $document.scrollTop() + $(window).height();
	    if(loc > targetH && loader(true)) {
		var uri = hb.nextpage + surfix;
		args = argsFromUri(uri);
		$.getJSON(uri, function(res) {
		    $('#pagerbottom').remove();
		    get_func(res);
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
	$('#torrents tbody tr').remove();
	table.hide();
	$('#stderr').remove();
	args = argsFromUri(uri);
	$.getJSON(uri + surfix, function(result) {
	    var targ = $('#outer');
	    if (result.torrents.length) {
		table.before(result.pager.top);
		sortHeader(!result['continue']);
		table.show();

		$document.unbind('scroll');
		get_func(result);
	    }
	    else {
		loader(false);
		targ.append('<div id="stderr"><h2>没有结果！</h2><div class="table td frame">没有种子:(</div></div>');
	    }
	    goToContent();
	});
    };

    //Ajax search
    var getFromUriWithHistory = function(uri) {
	var state = {
	    title : document.title,
	    url : uri
	}
	if (!ie) {
	    window.history.pushState(state, document.title, uri);
	}

	getFromUri(uri);
    };

    var searchboxform = $('#form-searchbox');
    searchboxform.submit(function(e) {
	e.preventDefault();
	var query = searchboxform.serialize();
	var uri = 'torrents.php?' + query;
	getFromUriWithHistory(uri);
    });

    $('a[href^="?"]').click(function(e) {
	e.preventDefault();
	var uri = this.href;
	getFromUriWithHistory(uri);
    });

    // var popped = ('state' in window.history);
    // var initialURL = location.href;
    // window.addEventListener('popstate', function(e){
    //     var initialPop = !popped && location.href == initialURL
    //     if (!initialPop) return;
    //     var state = e.state;
    //     if (state) {
    // 	getFromUri(state.url);
    // 	console.log(state);
    //     }
    // }, false);


    //Check items in searchbox
    var mainCheckClicked = false;
    var dictChecks = [];

    var check = function(item) {
	item.attr('checked', 'checked');
    };
    var uncheck = function(item) {
	item.removeAttr('checked');
    };
    var mainChecked = function(mainCheck, catChecks) {
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
    };
    var catClicked = function (isMain, mainCheck, catChecks) {
	if (isMain) {
	    if (!mainCheckClicked) {
		mainCheckClicked = true;
		$.each(dictChecks, function(idx, item) {
		    var main = item.main;
		    var cats = item.cats;
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
    };

    var allCatChecks = $.map(hb.constant.maincats, function(item, idx) {
	var mainCheck = $('#cat' + idx);
	var catChecks = $.map(item, function(cat) {
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

    //Auto complete
    var cache = {};
    var lastXhr;
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
    }).data( "autocomplete" )._renderItem = function( ul, item ) {
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