
$("#searchli").click(function() {
	$("#search").slideToggle();
	if($("#search").is(':visible')) {
		window.clearInterval(loop);
		filter = true;
	} else {
		loop = window.setInterval(get(),5000);
		filter = false;
	}
};

$("#search > input").on('input', function() {
	filterData = 'team=' + $('#search > input').val();
	get();
};