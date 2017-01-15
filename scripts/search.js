
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

$(document).keypress(function(e){
	if(e.keyCode === 27) //if you hit esc, close the searchbar
		closeSearchbar();
});

$("#TeamSearch > input").keypress( function(e) {		
	var chr = String.fromCharCode(e.which);
	if ("1234567890".indexOf(chr) < 0)
		return false;
});

$("#TeamSearch > input").on('input', function() {
	if(!$("#TeamSearch > input").val())
		clearFilters();
	else
		filter.enabled = true;
		filter.Team = "%25" + $("#TeamSearch > input").val() + "%25";
	get();
});

$("#closesearchbar").click(function(){
	closeSearchbar();
});

function closeSearchbar(){
	$("#TeamSearch").hide(0);
	$("#TeamSearch > input").val(''); //empty contents of searchbar
	$("#container").css('top','80px');
	clearFilters();
}
