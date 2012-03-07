$(function() {
    var file = $('#torrent');
    file.change(function () {
	var filename = document.getElementById("torrent").value;
	var filename = filename.toString();
	var lowcase = filename.toLowerCase();
	var start = lowcase.lastIndexOf("\\"); //for Google Chrome on windows/mac
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
	    }).dialog({
		modal : true,
		autoOpen :true
	    });
	    setTimeout(function() {
		dialog.dialog('close');
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
	document.getElementById("name").value=noext;
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
	var invalids = validateInputs(form, true);
	if (invalids) {
	    e.preventDefault();
	    form.find('.invalid').removeClass('invalid');
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
});