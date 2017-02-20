var teamURL = 'https://www.thebluealliance.com/team/' + team;
String.prototype.toProperCase = function () {
    return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
};
function processResponse(rawdata) {
	var data = JSON.parse(rawdata);
	document.getElementById('TBA').innerHTML = "";
	for (index in data) {
		if(data[index]){
			switch(index) {
				case "key":
					break;
				case "website":
					document.getElementById('TBA').innerHTML += '<p>' + index.replace(/_/gi," ").toProperCase() + ': <a href="' + data[index] + '">' + data[index] + "</a></p>";
					break;
				default:
					document.getElementById('TBA').innerHTML += "<p>" + index.replace(/_/gi," ").toProperCase() + ": " + data[index] + "</p>";
					break;
			}
		}
	}
}

var TBARequest = new XMLHttpRequest();
TBARequest.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		processResponse(this.responseText);
	} else if (this.readyState == this.DONE && this.status == 404){
		document.getElementById('TBA').innerHTML = "<p>No info for this team</p>";
		teamURL = "#";
	}
};

function sendRequest(data) {
	TBARequest.open("GET","https://www.thebluealliance.com/api/v2/team/frc" + team,true);
	TBARequest.setRequestHeader("X-TBA-App-Id",data);
	TBARequest.send(data);
}
sendRequest("frc4999:scouting-app:v01");
var loop = window.setInterval(function() {sendRequest("frc4999:scouting-app:v01");}, 20000);
document.getElementById('TBAheading').onclick = function() {
	document.location.href = teamURL;
};