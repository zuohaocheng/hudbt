$(function() {
    "use strict";

    var tabModified = false,
    accordionModified = false,
    confirmOnClose = function() {
	if (tabModified || accordionModified) {
	    $(window).on('beforeunload', function() {
		return 'You have unsaved modifications!'
	    });
	}
	else {
	    $(window).off('beforeunload');
	}
    },
    tabs = $( "#tabs" ).tabs({
	activate: function(e, ui) {
	    ui.newTab.find('.tab-remove').show();
	    ui.oldTab.find('.tab-remove').hide();

	    orgItemSeq = getItemSeq()
	}
    }),
    tabSorter = tabs.find( ".ui-tabs-nav" ).sortable({
	axis: "x",
	items: "li:not(.unsortable)",
	stop: function(e, ui) {
	    tabs.trigger('tabModified', [getTabSeq().toString() !== orgTabSeq.toString()]);
	}
    }),
    getTabSeq = function() {
	return $('#tabs > ul a[tab]').map(function() {
	    return parseInt(this.getAttribute('tab'))
	}).toArray();
    },
    orgTabSeq = getTabSeq(),
    tabAdd = '<li class="ui-state-default ui-corner-top unsortable" title="Add"><a href="faqactions.php?action=addsection"><span class="ui-icon ui-icon-plus"></span></a></li>',
    tabRevert = $('<li class="ui-state-default ui-corner-top unsortable" title="Revert" style="display:none"><a href="#"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></a></li>').click(function(e) {
	e.preventDefault();
	tabSorter.sortable( "cancel" );
	tabs.trigger('tabModified', [false]);
    }),
    tabSave = $('<li class="ui-state-default ui-corner-top unsortable" title="Save" style="display:none"><a href="#"><span class="ui-icon ui-icon-check"></span></a></li>').click(function(e) {
	e.preventDefault();
	var data = {};
	$('#tabs > ul a[tab]').each(function(idx) {
	    data['order[' + this.getAttribute('tab') + ']'] = idx + 1;
	});
	$.post('faqactions.php?action=reorder&format=json', data, function(result) {
	    tabs.trigger('tabModified', [false]);
	    location.reload();
	});
    });

    tabSorter.find('> li').unbind('keydown').find('a[tab]').editable('faqactions.php?action=editsect&format=json', {
	event: 'dblclick',
	name: 'title', // field in form
	submitdata : function(value, settings) {
	    return {id: this.getAttribute('tab')};
	}
    });

    tabs.find('.tab-remove').click(function(e) {
	e.preventDefault();
	var tab = $(this).parent().find('[tab]'),
	id = tab.attr('tab');
	if ($('#tabs-' + id).find('.item').length === 0) {
	    jqui_confirm('Delete', 'Confirm delete?', function() {
		$.post('faqactions.php?action=delete&confirm=yes&id=' + id, function(result) {
		    location.reload();
		});
	    });
	}
	else {
	    jqui_dialog('Can\'t delete', 'Not empty tab', 5000);
	}
    }).first().show();

    tabs.on('tabModified', function(e, modified) {
	tabModified = modified;
	if (modified) {
	    tabSave.show();
	    tabRevert.show();
	}
	else {
	    tabSave.hide();
	    tabRevert.hide();
	}
	confirmOnClose();
    });

    tabs.find('>ul').append(tabAdd).append(tabSave).append(tabRevert);


    var accordions = $( ".accordion" ).accordion({
	heightStyle: "content",
	header: "> div > h3"
    }).sortable({
	axis: "y",
	handle: "h3",
	items: 'div:not(.unsortable)',
	placeholder: "ui-state-highlight",
	stop: function( event, ui ) {
	    // IE doesn't register the blur when sorting
	    // so trigger focusout handlers to remove .ui-state-focus
	    ui.item.children( "h3" ).triggerHandler( "focusout" );

	    $(this).trigger('accordionModified', [getItemSeq().toString() !== orgItemSeq.toString()]);
	}
    }).append('<div class="item-toolbar unsortable"><button href="#" title="Add">1</button><button href="#" class="save" title="Save">2</button><button href="#" class="revert" title="Revert">3</button></div>').on('accordionModified', (function() {
	var tabChange = function(e, ui) {
	    e.preventDefault();
	    jqui_confirm('Unsaved modifications', 'Discard edits?', function() {
		accordions.eq(tabs.tabs( "option", "active")).sortable( "cancel" ).trigger('accordionModified', [false]);
		tabs.tabs( "option", "active", ui.newTab.index() );
		return true;
	    });
	};

	return function(e, modified) {
	    var $this = $(this);
	    accordionModified = modified;
	    if (modified) {
		$this.find('.item-toolbar .save').button('option', 'disabled', false);
		$this.find('.item-toolbar .revert').button('option', 'disabled', false);
		tabs.on( "tabsbeforeactivate", tabChange);
	    }
	    else {
		$this.find('.item-toolbar .save').button('option', 'disabled', true);
		$this.find('.item-toolbar .revert').button('option', 'disabled', true);
		tabs.off( "tabsbeforeactivate");
	    }
	    confirmOnClose();
	};
    })()),
    toolbar = accordions.find('.item-toolbar'),
    items = accordions.find('div[item]'),
    getItemSeq = function() {
	return accordions.eq(tabs.tabs( "option", "active")).find(' > div[item]').map(function() {
	    return parseInt(this.getAttribute('item'))
	}).toArray();
    },
    orgItemSeq = getItemSeq();
    accordions.find('h3').unbind('keydown');

    toolbar.find('button:first').button({
	icons: {primary: "ui-icon-plus"},
	text: false
    }).click(function(e) {
	e.preventDefault();
	var tab = $('.ui-tabs-active [tab]');
	location.href = 'faqactions.php?action=additem&inid=' + tab.attr('tab') + '&langid=' + tab.attr('langid');
    }).next().button({
	icons: {primary: "ui-icon-check"}, 
	text: false,
	disabled: true
    }).click(function(e) {
	e.preventDefault();
	var data = {};
	accordions.eq(tabs.tabs( "option", "active")).find(' > div[item]').each(function(idx) {
	    data['order[' + this.getAttribute('item') + ']'] = idx + 1;
	    $.post('faqactions.php?action=reorder&format=json', data, function(result) {
		tabs.trigger('accordionModified', [false]);
		location.reload();
	    });
	});
	console.log(data);
    }).next().button({
	icons: {primary: "ui-icon-arrowreturnthick-1-w"}, 
	text: false,
	disabled: true
    }).click(function(e) {
	e.preventDefault();
	accordions.eq(tabs.tabs( "option", "active")).sortable( "cancel" ).trigger('accordionModified', [false]);
    });
    toolbar.buttonset();

    items.find('.title').editable('faqactions.php?action=edititem&format=json', {
	event: 'dblclick',
	name: 'question', // field in form
	submitdata : function(value, settings) {
	    return {id: this.parentNode.parentNode.getAttribute('item')};
	}
    });var t;
    items.find('.faq-status').editable('faqactions.php?action=edititem&format=json', {
	event: 'dblclick',
	type: 'select',
	data: {0: 'Hidden', 1: 'Normal', 2: 'Updated', 3:'New'},
	onblur : 'submit',
	name: 'flag', // field in form
	submitdata : function(value, settings) {
	    return {id: this.parentNode.parentNode.getAttribute('item')};
	},
	callback: function(value, settings) {
	    var v = parseInt(value),
	    $this = $(this);
	    this.innerHTML = settings.data[v];
	    $this.removeClass("faq-hidden faq_updated faq_new faq-status-img");
	    switch (v) {
	    case 0:
		$this.addClass('faq-hidden');
		break;
	    case 2:
		$this.addClass('faq_updated faq-status-img');
		break;
	    case 3:
		$this.addClass('faq_new faq-status-img');
		break;
	    }
	}
    }).on({
	edit: function() {
	    $(this).addClass('editing');
	    t = this.innerText;
	},
	endEdit: function() {
	    $(this).removeClass('editing');
	},
	submit: function(e) {
	    if (({0: 'Hidden', 1: 'Normal', 2: 'Updated', 3:'New'})[$(this).find('select').val()] === t) {
		e.preventDefault();
	    }
	}
    });
    items.find('.text').editable('faqactions.php?action=edititem&format=json', {
	event: 'dblclick',
	type: 'textarea',
	submit: 'OK',
	cancel: 'Cancel',
	onblur : 'ignore',
	name: 'answer', // field in form
	submitdata : function(value, settings) {
	    return {id: this.parentNode.getAttribute('item')};
	}
    });
    items.find('.faq-item-remove').click(function(e) {
	e.preventDefault();
	var id = this.parentNode.parentNode.getAttribute('item');
	jqui_confirm('Delete', 'Confirm delete?', function() {
	    $.post('faqactions.php?action=delete&confirm=yes&id=' + id, function(result) {
		location.reload();
	    });
	});
    });
});