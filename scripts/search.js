
$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#container").css('top','130px');
	$("#TeamSearch").show(0, function() {
		if($("#TeamSearch").is(':visible')) {
			window.clearInterval(loop);
		} else {
			filter.enabled = false;
			loop = window.setInterval(get(),5000);
	}});
});

$("#TeamSearch > input").on('input', function() {
	if(!($("#TeamSearch > input").val())
		clearFilters();
	else
		filter.enabled = true;
		filter.Team = $("#TeamSearch > input").val();
	get();
});

$("#closesearchbar").click(function(){
	$("#TeamSearch").hide(0);
	$("#TeamSearch > input").val(''); //empty contents of searchbar
	$("#container").css('top','80px');
	clearFilters();
});