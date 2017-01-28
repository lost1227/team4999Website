function ShowFilters(){
	$("#hamburgermenu").slideUp(100);
	$("#Filters").show();
	if($('#TeamSearch').is(':visible')) {
		$("#Filters").css('top','130px');
		$("#container").css('top','130px');
	} else {
		$("#Filters").css('top','80px');
		$("#container").css('top','80px');
	}
}

$("#filterli").click(function() {
	ShowFilters();
});

	
$("#DriveSystemSelect").on("change",function() {
	$("#DriveSystemCheck").prop('checked',false);
	$("#Filters input").trigger("change");
});

$("#Filters input").on("change",function() {
	var filterIDs = $("#filters input").map(function() { return this.id; }).get(); // get ids of each filter element
	for (var i = 0; i < filterIDs.length; i++) {
		if(document.getElementById(filterIDs[i]).checked) {
			filter.enabled = true;
			break;
		} else {
			filter.enabled = false;
		}
	}
	if(this.checked) {
		//alert(this.id);
		if(this.id == "DriveSystemCheck") {
			filter.Drive_System = $("#DriveSystemSelect").val();
		} else {
			filter[this.id.replace(/ /g,"_")] = 1;
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