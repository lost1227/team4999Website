<?php session_start(); ?>
<html>
<head>
<title>Scouting website</title>
<link rel="stylesheet" href="style.css">
<script>
var request = new XMLHttpRequest();
request.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		document.getElementById("container").innerHTML = this.responseText;
	}
};
request.open("GET","/query.php",true);
request.send();
var loop = window.setInterval(function() {
	request.open("GET","/query.php",true);
	request.send();
}, 5000);
</script>
</head>
<body>
<h1 id="title">Scouting</h1>
<div id="container">
</div>
<?php
if($_SESSION["loggedIn"]){
	echo('<a href="/edit.php">Edit</a>');
} else {
	echo('<a href="/login.php">Log In</a>');
}
?>
<div id="login">
<?php
if($_SESSION["loggedIn"]) {
	echo('<p style="margin:2px;"><a href="logout.php">Log Out: '.$_SESSION["user"])'.</a></p>');
}
?>
</div>
</body>
</html>