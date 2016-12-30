<?php session_start(); ?>
<html>
<head>
<title>Scouting website</title>
<link rel="stylesheet" href="style.css">
<style>
#container {
	width: 90%;
	margin: auto;
	background-color: #FDFDFD;
	padding: 20px;
	border-radius: 5px;
}
<?php
if (!$_SESSION["loggedIn"]) {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php');
	echo('#normal { display: none; }');
	exit();
}
?>
</style>
<script>
var request = new XMLHttpRequest();
request.onreadystatechange = function() {
	//console.log("readystate changed");
	if (this.readyState == this.DONE && this.status == 200) {
		//console.log("answer recieved");
		document.getElementById("container").innerHTML = this.responseText;
	}
};
request.open("GET","/query.php",true);
request.send();
var loop = window.setInterval(function() {
	//console.log("request sent");
	request.open("GET","/query.php",true);
	request.send();
}, 5000);
</script>
</head>
<body>
<div id="normal">
<h1 id="title">Scouting</h1>
<!--<p>Logged in as <?php echo($_SESSION["user"]); ?></p>-->
<div id="container">
</div>
<div id="login">
<a href="logout.php"><?php echo($_SESSION["user"]); ?></a>
</div>
</div>
<div id="noLogin" <?php if ($_SESSION["loggedIn"]) { echo('style="display: none;"'); } ?>>
<p>You are not logged in. Please click <a href="https://frcteam4999.jordanpowers.net/login.php">here</a> if you are not redirected.</p>
</div>
</body>
</html>