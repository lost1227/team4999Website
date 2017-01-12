//test to see if jquery is loading properly

function toggleMenu(){
	console.log("You just clicked the hamburger menu!");
}

$(function() {
    console.log( "ready!" );
    $("#hamburger").click(toggleMenu());
});