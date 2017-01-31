var teamRequest = new XMLHttpRequest();
teamRequest.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		switch(this.responseText) {
			case "TRUE":
				document.getElementsByName("Team")[0].style.border = "";
				document.getElementsByName("Team")[0].style.backgroundColor = "";
				document.getElementById("teamExists").style.display = "none";
				document.getElementById("submit").disabled = false;
				break;
			case "FALSE":
				document.getElementsByName("Team")[0].style.border = "2px solid red";
				document.getElementsByName("Team")[0].style.backgroundColor = "rgba(255,0,0,.15)";
				document.getElementById("teamExists").style.display = "inline-block";
				document.getElementById("teamExists").setAttribute("href","/edit.php?team=" + this.teamNumber);
				document.getElementById("submit").disabled = true;
				break;
			case "LOGIN_ERROR":
				document.location.href = "/login.php?redirect=edit.php";
				break;
			case "NO_TEAM":
				console.log("INVALID TEAM REQUESTED");
				break;
			default:
				console.log("INVALID RESPONSE: " + this.responseText);
				break;
		}
	}
};
document.getElementsByName("Team")[0].onkeypress = function(e) {
	var chr = String.fromCharCode(e.which);
	if ("1234567890".indexOf(chr) < 0 && e.which != 8) {
		return false;
	}
}
document.getElementsByName("Team")[0].onblur = function() {
	teamRequest.teamNumber = document.getElementsByName("Team")[0].value;
	teamRequest.open("GET","/checkteam.php?team=" + teamRequest.teamNumber, true);
	teamRequest.send();
};