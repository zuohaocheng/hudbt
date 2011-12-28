$(function() {
    $('#test-form').submit(function (e) {
	e.preventDefault();
	$.post('preview.php', {body : $('#test').val()} ,function(res) {
	    $('#test-result').html('<fieldset>' + res + '</fieldset>');
	}, 'html');
    });
});