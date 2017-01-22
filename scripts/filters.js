$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	if($('#TeamSearch').is(':visible')) {
		$("#Filters").css('top','130px');
		$("#container").css('top',(130 + $('#Filters').height()) + 'px');
	} else {
		("#Filters").css('top','80px');
		$("#container").css('top',(80 + $('#Filters').height()) + 'px');
	}
}