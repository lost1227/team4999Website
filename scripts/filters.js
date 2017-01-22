$("#filterli").click(function() {
	$("#hamburgermenu").slideUp(100);
	if($('#TeamSearch').is(':visible')) {
		$("#Filters").css('top','130px');
		$("#container").css('top',(130 + $('#Filters').height()) + 'px');
	} else {
		$("#Filters").css('top','80px');
		$("#container").css('top',(80 + $('#Filters').height()) + 'px');
	}
	$("#Filters").show();
});

$("#Filters > input").on("change",function() {
	var filterIDs = $("#mydiv span[id]").map(function() { return this.id; }).get(); // get ids of each filter element
	for (var i = 0; i < filterIDs.length; i++) {
		if($("#"+filterIDs[i]).checked) {
			window.clearInterval(loop);
			filter.enabled = true;
		} else {
			filter.enabled = false;
		}
	}
	if(!filter.enabled) {
		loop = window.setInterval(get(),5000);
	}
	if(this.checked) {
		//alert(this.id);
		if(this.id == "DriveSystemCheck") {
			filter.Drive_System = $("#DriveSystemSelect").val();
		} else {
			filter[this.id.replace(/ /g,"_")] == 1;
		}
	} else {
		if(this.id == "DriveSystemCheck") {
			delete filter.Drive_System;
		} else {
			delete filter[this.id.replace(/ /g,"_")];
		}
	}
	get();
});