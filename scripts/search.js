//show searchbar on click on menu option
$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#TeamSearch").show(0, function() {
		if($("#TeamSearch").is(':visible')) {
			$("#Filters").css('top','130px');
			if($("#Filters").is(':visible')) {
				$("#container").css('top','130px');
			} else {
				$("#container").css('top',"130px");
			}
		} else {
			filter.enabled = false;
			$("#Filters").css('top','80px');
			if($("#Filters").is(':visible')) {
				$("#container").css('top','80px');
			} else {
				$("#container").css('top',"80px");
			}
	}});
});

//only allow numerical input
$("#TeamSearch > input").keypress( function(e) {
	var chr = String.fromCharCode(e.which);
	if ("1234567890".indexOf(chr) < 0 && e.which != 8) {
		return false;
	}
});

//update the results on input into the search box
$("#TeamSearch > input").on('input', function() {
	if(!$("#TeamSearch > input").val()) {
		filters.team = "";
	} else {
		filters.team = $("#TeamSearch > input").val();
	}
	get();
});

//close search bar on click of x
$("#closesearchbar").click(function() { closeSearchbar(); });

function closeSearchbar(){
	$("#TeamSearch").hide(0);
	if($("#Filters").is(':visible')) {
		$("#Filters").css('top','80px');
		$("#container").css('top','80px');
	}
	$("#TeamSearch > input").val(''); //empty contents of searchbar
	$("#container").css('top','80px');
}
