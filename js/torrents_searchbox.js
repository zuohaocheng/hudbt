//Persist searchbox status
(function() {
    var searchbox = $('#ksearchboxmain');
    var toggleSearchbox = function() {
	klappe_news('searchboxmain');
	klappe_news('searchbox-simple', true);
    }

    $('#searchbox-header a').click(function(e) {
	e.preventDefault();
	$.jStorage.set('hideSearchbox', (searchbox.css('display') === 'none'));
	toggleSearchbox();
    });

    if ($.jStorage.get('hideSearchbox', false)) {
	searchbox.show();
	$('#ksearchbox-simple').hide();
	document.getElementById('picsearchboxmain').className = 'minus';
    }
})();

