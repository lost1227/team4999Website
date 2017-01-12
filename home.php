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
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		
	</head>
<body>
	
	<div hidden id="hamburgermenu">
		this is supposed to be hidden
	</div>

	<h1 id="title">Scouting <img id="hamburger" src="images/hamburger.png" height="50" /></h1>
	<div id="container">
	</div>
	<?php
	if($_SESSION["loggedIn"]){
		echo('<a href="/edit.php">Edit</a>');
	} else {
		echo('<a href="/login.php">Log In</a>');
	}
	?>
	<?php
	if($_SESSION["loggedIn"]) {
		echo('<div id="login"><p style="margin:2px;"><a href="logout.php">Log Out: '.$_SESSION["user"].'</a></p></div>');
	}
	?>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
</body>
</html>