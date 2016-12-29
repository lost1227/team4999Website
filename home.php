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
}
?>
</style>
</head>
<body>
<div id="normal">
<h1 id="title">Scouting</h1>
<div id="container">
<div class="infoRow">
	<p>Team: Team1</p>
	<p>Drive System: sampleDriveSystem</p>
</div>
<div class="infoRow">
	<p>Team: Team2</p>
	<p>Drive System: sampleDriveSystem</p>
</div>
</div>
</div>
<div id="noLogin" <?php if ($_SESSION["loggedIn"]) { echo('style="display: none;"'); } ?>>
<p>You are not logged in. Please click <a href="https://frcteam4999.jordanpowers.net/login.php">here</a> if you are not redirected.</p>
</div>
</body>
</html>