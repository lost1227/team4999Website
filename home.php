<?php session_start(); ?>
<html>
	<head>
		<title>Scouting website</title>
		<link rel="stylesheet" href="style.css">
		<script src="scripts/hamburger.js"></script>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		
	</head>
<body>

	<h1 id="title">Scouting <img id="hamburger" src="images/hamburger.png" height="50" /></h1>
	
	<div hidden id="hamburgermenu">
		<ul id="menuitems">
			<li id="searchli">Search</li>
			<li id="filterli">Filters</li>
			<a href="/edit.php"><li id="addli">Add Robot</li></a>
		</ul>
	</div>
	
	<div id="container">
	</div>
	<?php
	if($_SESSION["loggedIn"]) {
		echo('<div id="loginbox"><p style="margin:2px;"><a href="logout.php">Log Out: '.$_SESSION["user"].'</a></p></div>');
	}
	?>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script src="scripts/hamburger.js"></script>
</body>
</html>
