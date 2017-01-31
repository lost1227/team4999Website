$(".deletePix").on("change",function() {
	var imageId = this.value;
	if(this.checked) {
		document.getElementById(imageId).style.filter = "grayscale(100%)";
		document.getElementById(imageId).style.border = "5px solid red";
	} else {
		document.getElementById(imageId).style.filter = "";
		document.getElementById(imageId).style.border = "inherit";
	}
});

window.onunload = function() {
	history.replaceState({},"Scouting Website","/");
};