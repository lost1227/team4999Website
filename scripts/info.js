
var TBARequest = new XMLHttpRequest();
TBARequest.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		var responsehtml = $.parseHTML(this.responseText);
    $(responsehtml).find("button.accordionbutton").click(function(e) {
      $(e.target).closest("div.accordion").find("div.accordioncontent").first().slideToggle();
      return false;
    });
    $("#TBA").html(responsehtml);
	}
};

function sendRequest() {
	TBARequest.open("GET","TBA.php?team=" + team,true);
	TBARequest.send();
}
document.getElementById('TBAheading').onclick = function() {
	document.location.href = "https://www.thebluealliance.com/team/"+team;
};

$(document).ready(function() {
  $("button.accordionbutton").click(function(e) {
    $(e.target).closest("div.accordion").find("div.accordioncontent").slideToggle();
    return false;
  });
  sendRequest();
});
