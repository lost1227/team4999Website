
$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#search").show(0, function() {
		if($("#search").is(':visible')) {
			window.clearInterval(loop);
			filter = true;
		} else {
			filter = false;
			loop = window.setInterval(get(),5000);
	}});
});

$("#search > input").on('input', function() {
	filterData = 'team=' + $('#search > input').val();
	if($("#search > input").val() === '')
		filterData = '';
	get();
});

$("#closesearchbar").click(function(){
	$("#search").slideUp();
	$("#search > input").val(''); //empty contents of searchbar
});