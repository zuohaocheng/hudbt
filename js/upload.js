$(function() {
    var file = $('#torrent').change(function () {
	var filename = document.getElementById("torrent").value,
	lowcase = filename.toLowerCase(),
	start = lowcase.lastIndexOf("\\"); //for Google Chrome on windows/mac

	if (lowcase.length === 0) {
	    return;
	}
	
	if (start == -1){
	    start = lowcase.lastIndexOf("\/"); // for Google Chrome on linux
	    if (start == -1)
		start == 0;
	    else start = start + 1;
	}
	else start = start + 1;
	var end = lowcase.lastIndexOf(".torrent");
	if (end === -1) {
	    file.val('');
	    var dialog = $('<div></div>', {
		title : '警告',
		text : '请上传torrent文件。'
	    })
	    dialog.dialog({
		modal : true,
		autoOpen :true
	    });
	    setTimeout(function() {
		dialog.dialog('close');
		dialog.remove();
	    }, 3000);
	    return;
	}
	var noext = filename.substring(start,end);
	noext = noext.replace(/H\.264/ig,"H_264");
	noext = noext.replace(/5\.1/g,"5_1");
	noext = noext.replace(/2\.1/g,"2_1");
	noext = noext.replace(/\./g," ");
	noext = noext.replace(/H_264/g,"H.264");
	noext = noext.replace(/5_1/g,"5.1");
	noext = noext.replace(/2_1/g,"2.1");
	
	var name = document.getElementById("name");
	if (!$.trim(name.value)) {
	    name.value=noext;
	}
    });

    var validateInputs = function(target, validateSelect) {
	var result = [];
	var dts = target.find('.required').next();
	dts.find(':input[name!=""][value=""]').each(function() {
	    if (this.value === '') {
		result.push(this);
	    }
	});
	
	if (validateSelect) {
	    dts.find('select:not(.no-validate)').each(function() {
		if (parseInt(this.value) === 0) {
		    result.push(this);
		}
	    });
	}
	if (result.length) {
	    return result;
	}
	else {
	    return false;
	}
    };

    var form = $('#compose');
    form.submit(function(e) {
	form.find('.invalid').removeClass('invalid');
	var invalids = validateInputs(form, true);
	if (invalids) {
	    e.preventDefault();
	    $.each(invalids, function() {
		var t = this;
		while (t && t.tagName.toLowerCase() !== 'dd') {
		    t = t.parentElement;
		}
		if (t) {
		    var dt = t.previousElementSibling;
		    dt.classList.add('invalid');
		}
	    });
	}
    });

    var compose = $('#compose');
    var validating = (function() {
	var val = false;
	return function(newVal) {
	    if (typeof(newVal) === 'undefined') {
		return val;
	    }
	    else {
		val = newVal;
		if (val) {
		    compose.find(':submit').attr('disabled', 'disabled');
		}
		else {
		    compose.find(':submit').removeAttr('disabled');
		}
	    }
	}
    })();
    var form = $('#tcategories');
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

		    lastXhr = $.getJSON( "/cake/tcategories/search/", request, function( data, status, xhr ) {
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

		var catName = this.value;
		if (catName !== '') {
		    validating(true);
		    $.getJSON("/cake/tcategories/search/" + catName, function(result) {
			var validation = true;
			if (result.length === 1) {
			    var resultId = result[0].Tcategory.id;
			    form.find(':input[type="hidden"]').each(function() {
				if (resultId == this.value && this !== in_id[0]) {
				    $(this).parent().addClass('invalid-ref');
				    validation = false;
				}
			    });

			    $inputs.find('.remove-tcategory').fadeIn();
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
			validating(false);
		    });
		}
		else {
		    $inputs.removeClass('invalid');
		    in_id.attr('value', '');
		    if (form.find('ul li:last')[0] !== $inputs[0]) {
			$inputs.remove();
		    }
		}
	    });
	});
	return inputs;
    };

    var removeTcategory = function(e) {
	e.preventDefault();
	$(this).parent().remove();
    };

    tcategory(form.find('.tcategory'));

    var lastTcategoryEvent = function() {
	if (validating()) {
	    setTimeout(lastTcategoryEvent, 100);
	    return;
	}

	if (form.find('ul li:last input[type="hidden"]').val().length !== 0) {
	    addTcategory();
	    lastTcategory();
	}
    };
    var lastTcategory = function() {
	form.find('ul :text').unbind('blur', lastTcategoryEvent);
	form.find('ul li:last :text').blur(lastTcategoryEvent);
    };

    var addTcategory = function(e) {
	if (e) {
	    e.preventDefault();
	}

	form.find('ul').append(tcategory($('<li></li>', {
	    'class' : 'tcategory'
	}).append($('<input />', {
	    type : 'text',
	    placeholder : 'Tcategory'
	})).append($('<input />', {
	    type : 'hidden',
	    name : 'data[Tcategory][Tcategory][]'
	})).append($('<a></a>', {
	    'class' : 'remove-tcategory',
	    href : '#',
	    text : '-',
	    style : 'display:none;'
	}).click(removeTcategory))));
	lastTcategory();
    };

    addTcategory();

    compose.submit(function(e) {
	if ($('.invalid').length !== 0) {
	    e.preventDefault();
	    var dialog = $('<div></div>', {
		title : '警告',
		text : '存在无效字段'
	    });
	    dialog.dialog({
		modal : true,
		autoOpen : true,
	    });
	    setTimeout(function() {
		dialog.dialog('close');
		dialog.remove();
	    }, 3000);
	}
	document.getElementById('browsecat').disabled = false;
    });

    $('#offer').change(function() {
	var name = document.getElementById('name'), 
	cat = document.getElementById('browsecat'),
	descr = document.getElementById('descr'),
	sel = (this.value == 0);
	if (sel) {
	    name.value = '';
	    cat.value = '';
	    descr.value = '';
	    file.trigger('change');
	}
	else {
	    var offer = hb.offers[this.value];
	    name.value = offer.name;
	    cat.value = offer.category;
	    descr.value = offer.descr;
	}

	name.readOnly = !sel;
	cat.disabled = !sel;
    }).trigger('change');
});