$(function() {
    var target = $('#torrents>tbody');
    var targetH = target.height();
    var surfix = '&format=xml';
    var lang = hb.constant.lang;

    var setTitleWidth;
    if ($.browser.msie && $.browser.version < 9) {
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
	$.each(targets, function(idx, val) {
	    var $this = $(val);
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
	};
	setTitleWidth($('td.torrent div.limit-width.minor-list'));
    }

    var user_out = function(user) {
	var userClass = user.find('canonicalClass').text();
	var userCss = userClass.replace(/\s/g, '') + '_Name username';
	var out = $('<span></span>', {
	    'class' : 'nowrap'
	}).append($('<a></a>', {
	    href : 'userdetails.php?id=' + user.attr('id'),
	    text : user.find('username').text(),
	    'class' : userCss
	}));

	if (user.find('donor').text() === 'true') {
	    out.append($('<img></img>', {
		src : "pic/trans.gif",
		alt : 'Donor',
		'class' : 'star'
	    }));
	}
	return out;
    };


    if (hb.nextpage !== '') {
	var $document = $(document);
	var backtotop = $('<a></a>', {
	    href : '#',
	    'class' : 'back-to-top',
	    title : '回到页首',
	    style : 'display: none'
	});
	$('#footer').after(backtotop);
	backtotop.click(function(e) {
	    e.preventDefault();

	    var goTop=setInterval(scrollMove,10);  
	    function scrollMove(){
		var pos = $document.scrollTop();
		$document.scrollTop(pos / 1.15);
		if(pos < 1) {
		    clearInterval(goTop);  
		}
            }
	});

	window.onscroll=function() {
	    $document.scrollTop() > 200 ? backtotop.css('display', "") : backtotop.css('display', 'none');
	}

	$document.scroll(function(){
            var loc = $document.scrollTop() + $(window).height();
            if(loc > targetH) {
		var uri = hb.nextpage + surfix;

		var get_func = function(res_x) {
		    $("#pagerbottom").after($('<div></div>', {
			'class' : 'pages'
		    })).hide();

		    var res = $(res_x);

		    var catDict = hb['constant'].cat_class;
		    var targetsAppearance = [];
		    res.find('torrent').each(function() {
			var torrent = $(this);
			var id = torrent.attr('id');
			var tr = $('<tr></tr>');

			var catid = torrent.find('catid').text();
			var catProp = catDict[catid];
			var cat = $('<td></td>');
			if (catProp) {
			    cat.addClass("nowrap category-icon").append($('<a></a>', {
				href : '?cat=' + catid
			    }).append($('<img />', {
				src : "pic/cattrans.gif",
				alt : catProp.name,
				title : catProp.name,
				'class' : catProp.class_name
			    })));
			}
			tr.append(cat);

			var title_td = $('<td></td>', {
			    'class' : 'torrent'
			});
			var title_desc = $('<div></div>', {
			    'class' : 'limit-width minor-list'
			});


			var bookmarked = (torrent.find('bookmarked').length !== 0);
			var bookmarkClass = bookmarked ? 'bookmark' : 'delbookmark';

			var textMainTitle = torrent.find('name').text();
			var textSubTitle = torrent.find('desc').text();

			if (hb.config.swaph && textSubTitle !== '') {
			    var buf = textMainTitle;
			    textMainTitle = textSubTitle;
			    textSubTitle = buf;
			}

			var mainTitle = $('<h2></h2>', {
			    'class' : 'transparentbg'
			}).append($('<a></a>', {
			    href : 'details.php?id=' + id + '&hit=1',
			    title : textMainTitle,
			    text : textMainTitle
			}));

			var desc = $('<h3></h3>', {
			    text : textSubTitle,
			    title : textSubTitle
			});
			if (textSubTitle === '') {
			    desc.addClass('placeholder');
			}

			var $div_main = $('<div></div>', {
			    'class' : 'torrent-title'
			}).append(mainTitle);
			var $div_desc = $('<div></div>', {
			    'class' : 'torrent-title'
			}).append(desc);
			title_desc.append($div_main).append($div_desc);
			var title = $('<div></div>');
			title.append(title_desc);
			targetsAppearance.push(title_desc);

			var mainTitleDecorators = $('<ul></ul>', {
			    'class' : 'prs'
			});
			$div_main.append(mainTitleDecorators);

			var picktype = torrent.find('picktype');
			if (picktype.length !== 0) {
			    var ptype = picktype.text()
			    var $ptype = $('<span></span>', {text : '['}).append($('<span></span>', {
				text : lang['text_' + ptype],
				'class' : ptype
			    })).append(']');
			    mainTitleDecorators.append($ptype.wrap('<li></li>'));
			}

			var pr = torrent.find('pr');
			if (pr.length !== 0) {
			    var state = pr.attr('state');
			    var prDict = hb.constant.pr[state - 1];
			    var prLabel = $('<img></img>', {
				'class' : 'pro_' + prDict.name,
				alt : lang[prDict.lang],
				src : 'pic/trans.gif'
			    });
			    mainTitleDecorators.append(prLabel.wrap('<li></li>'));

			    var expire = pr.find('expire');
			    var $expire = $('<span></span>', {text : '['});
			    var $time = $('<span></span>', {text : lang.text_will_end_in});
			    if (expire.length !== 0) {
				$time.attr('title', expire.find('raw').text());
				$time.append(decodeURIComponent(expire.find('canonical').text()));
				$time.addClass('pr-limit');
			    }
			    else {
				$time.append(lang.text_forever);
				$time.addClass('pr-eternal');
			    }
			    $expire.append($time).append(']');
			    desc.after($('<ul></ul>', {
				'class' : 'prs'
			    }).append($expire.wrap('<li></li>')));
			}

			if (torrent.find('oday').length !== 0) {
			    var oday = $('<img></img>', {
				src : 'pic/ico_0day.gif',
				alt : lang.text_oday,
				title : lang.text_oday
			    });
			    mainTitleDecorators.append(oday.wrap('<li></li>'));
			}

			if (torrent.find('banned').length !== 0) {
			    var oday = $('<span></span>', {
				text : '('
			    }).append($('<span></span>', {
				text : lang.text_banned,
				'class' : 'striking'
			    })).append(')');
			    mainTitleDecorators.append(oday.wrap('<li></li>'));
			}


			title.append($('<div></div>', {
			    'class' : 'torrent-utilty-icons minor-list-vertical'
			}).append($('<ul></ul>').append($('<li></li>').append($('<a></a>', {
			    href : 'download.php?id=' + id + '&hit=1',
			}).append($('<img></img>', {
			    src : "pic/trans.gif",
			    alt : 'download',
			    title : 'title_download_torrent',
			    'class' : 'download'
			})))).append($('<li></li>').append($('<a></a>', {
			    id : 'bookmark' + id,
			    href : 'javascript: bookmark(' + id + ');'
			}).append($('<img></img>', {
			    src : "pic/trans.gif",
			    'class' : bookmarkClass
			}))))));
			title_td.append(title);
			tr.append(title_td);

			var addNumber = function(str, hrefZero, href) {
			    var comment = $('<td></td>', {'class' : 'rowfollow'});
			    var comments_num = parseInt(str);
			    var href;
			    if (comments_num === 0) {
				href = hrefZero;
			    }

			    var $a = $('<a></a>', {
				text : str
			    });
			    if (href !== undefined && href !== '') {
				$a.attr('href', href);
			    }

			    comment.append($a);

			    if (comments_num !== 0) {
				$a.addClass('important');
			    }

			    return comment;
			}

			tr.append(addNumber(torrent.find('comments').text(), 'comment.php?action=add&pid=' + id + '&type=torrent', 'details.php?id=' + id + '&hit=1&cmtpage=1#startcomments'));

			var time = $('<td></td>', {'class' : 'rowfollow'});
			time.text(torrent.find('added').text());
			tr.append(time);

			var size = $('<td></td>', {'class' : 'rowfollow'});
			size.html(decodeURIComponent(torrent.find('size canonical').text()));
			tr.append(size);

			var seeders = addNumber(torrent.find('seeders').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#seeders');
			if (torrent.find('seeders').text() === '0') {
			    seeders.find('a').addClass('no-seeders');
			}
			tr.append(seeders);

			var leechers = addNumber(torrent.find('leechers').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#leechers');
			tr.append(leechers);

			var completed = addNumber(torrent.find('times_completed').text(), '', 'viewsnatches.php?id=' + id);
			tr.append(completed);

			var towner = $('<td></td>', {'class' : 'rowfollow'});
			var owner = torrent.find('owner');
			if (owner.attr('anonymous') === 'true') {
			    towner.append(lang.text_anonymous);
			}
			
			var user = owner.find('user');
			if (user.length !== 0) {
			    if (owner.attr('anonymous') === 'true') {
				towner.append('<br />(');
			    }
			    towner.append(user_out(user));
			    if (owner.attr('anonymous') === 'true') {
				towner.append(')');
			    }
			}
			tr.append(towner);

			if (parseInt(hb.config.user['class']) >= hb.constant.torrentmanage_class) {
			    var edit = $('<td></td>', {'class' : 'rowfollow'});
			    edit.append($('<div></div>', {
				'class' : 'minor-list-vertical'
			    }).append($('<ul></ul>').append($('<li></li>').append($('<a></a>', {
				href : 'fastdelete.php?id=' + id
			    }).append($('<img></img>', {
				alt : 'D',
				title : lang.text_delete,
				src : 'pic/trans.gif',
				'class' : 'staff_delete'
			    })))).append($('<li></li>').append($('<a></a>', {
				href : 'edit.php?id=' + id + '&returnto=' + encodeURIComponent(document.location.pathname + document.location.search)
			    }).append($('<img></img>', {
				alt : 'E',
				title : lang.text_edit,
				src : 'pic/trans.gif',
				'class' : 'staff_edit'
			    }))))));
			    tr.append(edit);
			}
			
			target.append(tr);
		    });

		    setTitleWidth(targetsAppearance);

		    var cont = res.find('continue');
		    if (cont.length !== 0) {
			var uri = cont.text() + surfix;

			var targetH = target.height();
			$document.scroll(function() {
			    var loc = $document.scrollTop() + $(window).height();
			    if(loc > targetH) {
				$.get(uri, get_func);
				$document.unbind('scroll');
			    }
			});
		    }
		}

		$.get(uri, get_func);
		$document.unbind('scroll');
    	    }
	});
    }
});