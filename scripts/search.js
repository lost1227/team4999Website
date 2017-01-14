
$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#container").css('top','130px');
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
	filterData = 'Team=%' + $('#search > input').val() + "%";
	
	if($("#search > input").val() === '')
		filter = false;
	else
		filter = true;
		
	get();
});

$("#closesearchbar").click(function(){
	$("#search").hide(0);
	$("#search > input").val(''); //empty contents of searchbar
	$("#container").css('top','80px');
	clearFilters();
});