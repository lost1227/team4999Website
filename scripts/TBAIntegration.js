function prettify(str) {
	if (typeof str === "string") {
		str.replace(/ /gi,"_");
		str = str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}
	return str;
}
function processResponse(rawdata) {
	var data = JSON.parse(rawdata);
	document.getElementById('TBA').innerHTML = "";
	for (index in data) {
		document.getElementById('TBA').innerHTML += "<p>" + prettify(index) + ": " + prettify(data[index]) + "<p>";
	}
}

var TBARequest = new XMLHttpRequest();
TBARequest.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		processResponse(this.responseText);
	}
};

function sendRequest(data) {
	TBARequest.open("GET","https://www.thebluealliance.com/api/v2/team/frc" + team,true);
	TBARequest.setRequestHeader("X-TBA-App-Id",data);
	TBARequest.send(data);
}
sendRequest("frc4999:scouting-app:v01");
var loop = window.setInterval(function() {sendRequest("frc4999:scouting-app:v01");}, 5000);