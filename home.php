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
var loop;
function getSys() {
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("container").innerHTML = this.responseText;
		}
	}
	request.open("GET","query.php",true);
	loop = window.setTimeout(function() {
		request.open("GET","query.php",true);
	}, 5000);
}
</script>
</head>
<body onload="getSys()">
<div id="normal">
<h1 id="title">Scouting</h1>
<p>Logged in as <?php echo($_SESSION["user"]); ?></p>
<div id="container">
<!--<div class="infoRow">
	<p>Team: Team1</p>
	<p>Drive System: sampleDriveSystem</p>
</div>
<div class="infoRow">
	<p>Team: Team2</p>
	<p>Drive System: sampleDriveSystem</p>
</div>-->
</div>
</div>
<div id="noLogin" <?php if ($_SESSION["loggedIn"]) { echo('style="display: none;"'); } ?>>
<p>You are not logged in. Please click <a href="https://frcteam4999.jordanpowers.net/login.php">here</a> if you are not redirected.</p>
</div>
</body>
</html>