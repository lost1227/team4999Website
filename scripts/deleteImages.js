$("img.deletePix").click(function() {
	var imageId = this.value;
	if(this.checked) {
		$(imageId).css("filter","greyscale(100%)");
	} else {
		$(imageId).css("filter","");
	}
});