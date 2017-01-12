
$(function() {
    console.log( "ready!" );
    
    $("#hamburger").click(function(){
		console.log("You just clicked the hamburger menu!");
		$("#hamburgermenu").slideToggle();
	});
	
});