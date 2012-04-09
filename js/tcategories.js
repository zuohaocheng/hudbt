$(function() {
    var redirect = $('#redirect');
    var chkRedirect = redirect.find(':checkbox');
    var categoryName = $('#TcategoryName');
    var cake = '//' + hb.constant.url.cake + '/';

    chkRedirect.click(function() {
	if (chkRedirect.attr('checked')) {
	    redirect.find('.tcategory').fadeIn();
	    $('#parents').slideUp();
	}
	else {
	    var tcategory = redirect.find('.tcategory').fadeOut();
	    tcategory.find(':input').val('');
	    $('#parents').slideDown();
	}
    });


    var validating = false;
    var tcategory = function(inputs) {
	inputs.each(function() {
	    var $inputs = $(this);
	    var input = $inputs.find(':text');
	    var in_id = $inputs.find(':input[type="hidden"]');
	    //Auto complete
	    var cache = {};
	    var lastXhr;
	    input.autocomplete({
		source: function( request, response ) {
		    var term = request.term;
		    if ( term in cache ) {
			response( cache[ term ] );
			return;
		    }

		    lastXhr = $.getJSON( cake + "tcategories/search/", request, function( data, status, xhr ) {
			data = $.map(data, function(item) {
			    return item.Tcategory.name;
			});

			cache[ term ] = data;
			if ( xhr === lastXhr ) {
			    response( data );
			}
		    });
		}
	    });

	    input.blur(function() {
		$inputs.removeClass('invalid-ref');
		categoryName.removeClass('invalid-ref');

		var catName = this.value;
		if (catName !== '') {
		    if ($inputs.parent().parent().attr('id') === 'parents' && catName === $('#TcategoryName').val()) {
			input.focus();
			$inputs.addClass('invalid');
			categoryName.addClass('invalid-ref');
		    }

		    validating = true;
		    $.getJSON(cake + "tcategories/search/exact:1/" + catName, function(result) {
			var validation = true;
			if (result.length === 1) {
			    if ($inputs.parent().attr('id') === 'redirect') {
				chkRedirect.attr('checked', 'checked');
			    }
			    else if ($inputs.parent().parent().attr('id') === 'parents') {
				var resultId = result[0].Tcategory.id;
				if (resultId == $('#TcategoryId').val()) {
				    validation = false;
				    categoryName.addClass('invalid-ref');
				}
				else {
				    $('#parents :input[type="hidden"]').each(function() {
					if (resultId == this.value && this !== in_id[0]) {
					    $(this).parent().addClass('invalid-ref');
					    validation = false;
					}
				    });
				}

				$inputs.find('.remove-parent').fadeIn();
			    }
			}
			else {
			    validation = false;
			}

			if (validation) {
			    input.attr('value', result[0].Tcategory.name);
			    $inputs.removeClass('invalid');
			    in_id.attr('value', result[0].Tcategory.id);
			}
			else {
			    input.focus();
			    $inputs.addClass('invalid');
			}
			validating = false;
		    });
		}
		else {
		    $inputs.removeClass('invalid');
		    in_id.attr('value', '');
		    if ($inputs.parent().attr('id') === 'redirect') {
			chkRedirect.removeAttr('checked');
		    }
		    else if ($inputs.parent().parent().attr('id') === 'parents' && $('#parents ul li:last')[0] !== $inputs[0]) {
			$inputs.remove();
		    }
		}
	    });
	});
	return inputs;
    };

    tcategory($('.tcategory'));

    categoryName.blur(function() {
	var act = function(valid) {
	    if (valid) {
		categoryName.removeClass('invalid');
	    }
	    else {
		categoryName.addClass('invalid').focus();
	    }
	};
	var catName = categoryName.val();
	if (catName.length === 0) {
	    act(false);
	}
	else {
	    validating = true;
	    $.getJSON(cake + "tcategories/search/exact:true/" + categoryName.val(), function(result) {
		act(result.length === 0 || (hb.tcategory && result[0].Tcategory.id === hb.tcategory.id));
		validating = false;
	    });
	}
    });

    var form = $('#TcategoryEditForm');
    form.submit(function(e) {
	e.preventDefault();
	var params = form.serializeArray();
	var warning = function() {
	    if (validating) {
		setTimeout(warning, 200);
		return;
	    }

	    if (form.find('.tcategory.invalid').length !== 0) {
		e.preventDefault();
		var dialog = $('<div></div>', {
		    title : '警告',
		    text : '存在无效字段'
		}).dialog({
		    modal : true,
		    autoOpen : true,
		});
		setTimeout(function() {
		    dialog.dialog('close');
		}, 3000);
	    }
	    else {
		$.post(form.attr('action'), params, function(result) {
		    $('#outer').html(result);
		    var state = {
			title : document.title,
			url : cake + 'tcategories'
		    }
		    window.history.pushState(state, document.title, cake + 'tcategories');
		});
	    }
	};

	warning();
    });


    var lastParentEvent = function() {
	if (validating) {
	    setTimeout(lastParentEvent, 100);
	    return;
	}

	if ($('#parents ul li:last input[type="hidden"]').val().length !== 0) {
	    addParent();
	    lastParent();
	}
    };
    var lastParent = function() {
	$('#parents ul :text').unbind('blur', lastParentEvent);
	$('#parents ul li:last :text').blur(lastParentEvent);
    };
    lastParent();

    var addParent = function(e) {
	if (e) {
	    e.preventDefault();
	}

	$('#parents ul').append(tcategory($('<li></li>', {
	    'class' : 'tcategory'
	}).append($('<input />', {
	    type : 'text',
	    placeholder : 'New parent'
	})).append($('<input />', {
	    type : 'hidden',
	    name : 'data[Parent][Parent][]'
	})).append($('<a></a>', {
	    'class' : 'remove-parent',
	    href : '#',
	    text : '-',
	    style : 'display:none;'
	}).click(removeParent))));
	lastParent();
    };

    var removeParent = function(e) {
	e.preventDefault();
	$(this).parent().remove();
    };
    $('.remove-parent').click(removeParent);

});