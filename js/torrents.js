$(function() {
    var target = $('#torrents>tbody');
    var targetH = target.height();
    var surfix = '&format=xml';

    var get_func = function(res_x) {
	$("#pagerbottom").after($('<div></div>').addClass('pages')).hide();

	var res = $(res_x);

	res.find('torrent').each(function() {
	    var torrent = $(this);
	    var id = torrent.attr('id');
	    var tr = $('<tr></tr>');

	    var cat = $('<td></td>').addClass("nowrap").css('vertical-align', 'middle').append($('<a></a>', {
		href : '?cat=' + torrent.find('catid').text()
	    }).append($('<img />', {
		src : "pic/cattrans.gif",
		alt : "CDrama",
		title : "CDrama"
	    }).addClass("c_tvseries")));

	    tr.append(cat);

	    var title_td = $('<td></td>').css('align', 'left');
	    var title = $('<div></div>').css('position', 'relative');

	    title.append($('<h2></h2>').append($('<a></a>', {
		href : 'details.php?id=' + id + '&hit=1',
		title : torrent.find('name').text(),
		text : torrent.find('name').text()
	    }))).append($('<h3></h3>', {
		text : torrent.find('desc').text()
	    })).append($('<div></div>').addClass('torrent-utilty-icons minor-list-vertical').append($('<ul></ul>').append($('<li></li>').append($('<a></a>', {
		href : 'download.php?id=' + id + '&hit=1',
	    }).append($('<img></img>', {
		src : "pic/trans.gif"
	    }).addClass('download')))).append($('<li></li>').append($('<a></a>', {
		href : '#'
	    }).append($('<img></img>', {
		src : "pic/trans.gif"
	    }).addClass('delbookmark'))))));
	    title_td.append(title);
	    tr.append(title_td);

	    var addNumber = function(str, hrefZero, href) {
		var comment = $('<td></td>').addClass('rowfollow');
		var comments_num = parseInt(str);
		var href;
		if (comments_num === 0) {
		    href = hrefZero;
		}

		var $a = $('<a></a>', {
		    'href' : href,
		    text : str
		});
		comment.append($a);

		if (comments_num !== 0) {
		    $a.addClass('important');
		}

		return comment;
	    }

	    tr.append(addNumber(torrent.find('comments').text(), 'comment.php?action=add&pid=' + id + '&type=torrent', 'details.php?id=' + id + '&hit=1&cmtpage=1#startcomments'));

	    var time = $('<td></td>').addClass('rowfollow');
	    time.text(torrent.find('added').text());
	    tr.append(time);

	    var size = $('<td></td>').addClass('rowfollow');
//	    size.text(torrent.find('size').text());
	    tr.append(size);

	    var seeders = addNumber(torrent.find('seeders').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#seeders');
	    tr.append(seeders);

	    var leechers = addNumber(torrent.find('leechers').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#leechers');
	    if (torrent.find('leechers').text() === '0') {
		leechers.find('a').addClass('no-seeders');
	    }
	    tr.append(leechers);

	    var completed = addNumber(torrent.find('times_completed').text(), '', 'viewsnatches.php?id=' + id);
	    tr.append(completed);

	    var towner = $('<td></td>').addClass('rowfollow');
	    var owner = torrent.find('owner');
	    if (owner.attr('anonymous') === 'true') {
		towner.append('Anon');
	    }
	    
	    var user_out = function(user) {
		var out = $('<span></span>').addClass('nowrap').append($('<a></a>', {
		    href : 'userdetails.php?id=' + user.attr('id'),
		    text : user.find('username').text()
		}).addClass(user.find('canonicalClass').text() + '_Name'));

		if (user.find('donor').text() === 'true') {
		    out.append($('<img></img>', {
			src : "pic/trans.gif",
			alt : 'Donor'
		    }).addClass('star'));
		}
		return out;
	    };

	    var user = owner.find('user');
	    if (user.length !== 0) {
		if (owner.attr('anonymous') === 'true') {
		    towner.append('<br />');
		}
		towner.append(user_out(user));
	    }
	    tr.append(towner);
	    
	    
	    target.append(tr);
	});



	var cont = res.find('continue');
	if (cont.length !== 0) {
	    var uri = cont.text() + surfix;

	    var targetH = target.height();
	    $(document).scroll(function() {
		var loc = $(document).scrollTop() + $(window).height();
		if(loc > targetH) {
		    $.get(uri, get_func);
		    $(document).unbind('scroll');
		}
	    });
	}
    }

    $(document).scroll(function(){
        var loc = $(document).scrollTop() + $(window).height();
        if(loc > targetH) {
	    var uri = hb.nextpage + surfix;

	    $.get(uri, get_func);
	    $(document).unbind('scroll');
    	}
    });
    
});