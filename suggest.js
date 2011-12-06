$(function() {
    var pos = -1;
    var count = 0;
    var novos = [];
    var suggcont = document.getElementById("suggcontainer");
    var suggdiv = document.getElementById("suggestions");
    var searchinput = $('#searchinput');
    var results = [];

    function noenter(key) {
	if (novos.length && key == 13) {
	    choiceclick(results[pos].word);
	    return false;
	} 
	else {
	    return true;
	}
    }

    document.onclick = function () { closechoices(); }

    function suggest(key,query) 
    {
	if (key == 38) 
	{
	    goPrev();
	} 
	else if (key == 40) 
	{
	    goNext();
	} 
	else if (key != 13) 
	{
	    if (query.length >= 2) {
		query = query.toLowerCase();
		if (query == 'th' || query == 'the' || query == 'the ') {
		    update([]);
		} else {
		    $.getJSON('suggest.php?q='+query,update);
		}
	    } else {
		update([]);
	    }
	}
    }

    function update(result) {
	results = result;
	count = result.length;
	if (count > 0) {
	    suggcont.style.display = "block";
	    suggdiv.innerHTML = '';

	    novos = $.map(result, function(item, i) {
		var novo = document.createElement("li");
		suggdiv.appendChild(novo);
		
		novo.id = 'suggestion' + i;

		novo.onmouseover = function() { select(this,i); }
		novo.onmouseout = function() { unselect(this,i); }
		novo.onclick = function() { choiceclick(item.word); }
		novo.value = item.word;
		
		var stime = item.count + ' Time';
		if (item.count > 1) {
		    stime += 's';
		}
		novo.innerHTML = item.word + "<div style='position:absolute;top:0;right:0;'>" + stime + "</div>";	
		return novo;
	    });
	}
	else {
	    suggcont.style.display = "none";
	    novos = [];
	}
    }

    function select(obj,mouse) {
	obj.className = 'selected';
	if (mouse) {
	    pos = mouse;
	    unselectAllOther(pos);
	}
    }

    function unselect(obj,mouse) {
	obj.className = '';
	if (mouse) {
	    pos = -1;
	}
    }

    function goNext() {
	if (pos < count && count > 0) {
	    if (novos[pos]) {
		unselect(novos[pos]);
	    }
	    pos++;
	    if (novos[pos]) {
		select(novos[pos]);
	    } else {
		pos = -1;
	    }
	}
    }

    function goPrev() {
	if (count > 0) {
	    if (novos[pos]) {
		unselect(novos[pos]);
		pos--;
		if (novos[pos]) {
		    select(novos[pos]);
		} 
		else {
		    pos = -1;
		}
	    } 
	    else {
		pos = count -1;
		select(novos[pos]);
	    }
	}
    }

    function choiceclick(obj) {
	searchinput.val(obj);
	count = 0;
	pos = -1;
	suggcont.style.display = "none";
	searchinput.focus();
    }

    function closechoices() {
	if (suggcont.style.display == "block") {
	    count = 0;
	    pos = -1;
	    suggcont.style.display = "none";
	}
    }

    function unselectAllOther(id) {
	$.each(novos, function(i, item) {
	    if (i != id) {
		item.className='';
	    }
	});
    }

    searchinput.dblclick(function(event) {
	suggest(event.keyCode,this.value);
    });
    searchinput.keyup(function(event) {
	suggest(event.keyCode,this.value);
    });
    searchinput.keypress(function(event) {
	return noenter(event.keyCode);
    });
});