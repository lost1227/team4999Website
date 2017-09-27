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
	history.replaceState({},"Scouting Website","/scouting/");
};

var disableSubmit = {
	addValue : function(name,value) {
		this[name] = value;
		var disable = false;
		for (index in this) {
			if (!(index == 'addValue' || index == 'removeValue')) {
				if(!this[index]) {
					disable = true;
				}
			}
		}
		document.getElementById("submit").disabled = disable;
	},
	removeValue : function(name) {
		delete this[name];
		var disable = false;
		for (index in this) {
			if (!(index == 'addValue' || index == 'removeValue')) {
				if(!this[index]) {
					disable = true;
				}
			}
		}
		document.getElementById("submit").disabled = disable;
	}
}

document.getElementById('uploadImage').onchange = function() {
	var upload = document.getElementById('uploadImage');
	var count = upload.files.length;
	var enable = true;
	var totalsize = 0;
	for(var i = 0; i < count; i++) {
		 totalsize += upload.files[i].size;
	}
	if(totalsize < 249000000) {
		document.getElementById("invalidFile").style.display = "none";
		disableSubmit.removeValue("file");
	} else {
		document.getElementById("invalidFile").style.display = "inline-block";
		disableSubmit.addValue("file",false);
	}
};
