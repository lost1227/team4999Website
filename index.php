<?php session_start(); ?>
<html>
	<head>
		<title>Scouting website</title>
		<link rel="stylesheet" href="style.css">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="no" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		
		<!--favicon generated by http://realfavicongenerator.net/-->
		<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
		<link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="/favicons/android-chrome-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="/favicons/manifest.json">
		<link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
		<link rel="shortcut icon" href="/favicons/favicon.ico">
		<meta name="apple-mobile-web-app-title" content="Scouting">
		<meta name="application-name" content="Scouting">
		<meta name="msapplication-config" content="/favicons/browserconfig.xml">
		<meta name="theme-color" content="#ffffff">
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
	
	<div hidden id="TeamSearch">
		<input name="TeamSearchbox" placeholder="Search for a team here!" pattern="[0-9]*" type="number">
		<div id="closesearchbar"> X </div>
	</div>
	
	<div hidden id="Filters">
		<div id="driveSystemDiv">
			<input type="checkbox" id="DriveSystemCheck">
				<select id="DriveSystemSelect">
					<option value="West Coast">West Coast</option>
					<option value="Mechanum">Mechanum</option>
					<option value="Tank">Tank</option>
					<option value="Swerve">Swerve</option>
				</select>
			</div>
		<?php
		$bools = array("Can pickup gear from floor","Can place gear on lift","Can catch fuel from hoppers","Can pickup fuel from floor","Can shoot in low goal","Can shoot in hight goal","Can climb rope","Brought own rope");
		foreach($bools as $bool) {
			echo('<label><input type="checkbox" id="'.$bool.'">'.$bool.'</label><br>');
		}
		?>
	</div>
	
	<div id="container">
	</div>
	<?php
	if(isset($_SESSION["loggedIn"])) {
		echo('<div id="loginbox"><p style="margin:2px;"><a href="logout.php">Log Out: '.$_SESSION["user"].'</a></p></div>');
	}
	?>
	
	<script src="scripts/jquery-3.1.1.min.js"></script>
	<script src="scripts/updateList.js"></script>
	<script src="scripts/hamburger.js"></script>
	<script src="scripts/search.js"></script>
	<script src="scripts/filters.js"></script>
</body>
</html>
