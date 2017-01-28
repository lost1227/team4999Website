$(".deletePix").on("change",function() {
	var imageId = this.value;
	if(this.checked) {
		document.getElementById(imageId).style.filter = "grayscale(100%)";
	} else {
		document.getElementById(imageId).style.filter = "";
	}
});