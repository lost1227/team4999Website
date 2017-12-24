function get() {
				if (Object.keys(filters.robot).length > 0 || Object.keys(filters.match).length || filters.team != "") {
					request.open("POST","query.php",true);
					request.setRequestHeader("Content-type", "application/json");
					request.send(JSON.stringify(filters));
				} else {
					request.open("GET","query.php",true);
					request.send();
				}
}

var request = new XMLHttpRequest();
$(document).ready(function() {
	request.onreadystatechange = function() {
		if (this.readyState == this.DONE && this.status == 200){
			$("#container").html(this.responseText);
		}
	};
	get();
});
var loop = window.setInterval(function() {get();}, 5000);
