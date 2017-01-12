//test to see if jquery is loading properly

var toggleMenu = function(){
	console.log("You just clicked the hamburger menu!");
}

$(function() {
    console.log( "ready!" );
    $("#hamburger").click(toggleMenu());
});