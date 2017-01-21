<?php session_start(); ?>
<?php
	if(!isset($_GET["team"])) {
		header( 'Location: https://frcteam4999.jordanpowers.net/home.php');
	}
?>
<html>
	<head>
		<link rel="stylesheet" href="styles/info.css">
		<title>Team: <?php echo(str_replace('_',' ',$_GET["team"])); ?></title>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	</head>
	<body>
	
	</body>
	<?php
	require 'functions.php';
	if ($_SESSION["loggedIn"]){
		$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
	} else {
		$DB = new mysqli("localhost","ro","","frcteam4999");
	}
	$team = str_replace('_',' ',$_GET["team"]);
	formatAndQuery('SELECT * FROM robots WHERE Team = %d;',$team);
	#$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			foreach($row as $key => $value) {
				$key = str_replace('_',' ',$key);
				switch($key) {
					case("Can_pickup_gear_from_floor"):
					case("Can_place_gear_on_lift"):
					case("Can_catch_fuel_from_hoppers"):
					case("Can_pickup_fuel_from_floor"):
					case("Can_shoot_in_low_goal"):
					case("Can_shoot_in_high_goal"):
					case("Can_climb_rope"):
					case("Brought_own_rope"):
						if($value = 1) {
							echo('<p>'.$key.': Yes</p>');
						} else {
							echo('<p>'.$key.': No</p>');
						}
					default:
						echo('<p>'.$key.': '.$value.'</p>');
						break;
				}
			}
		}
	} else {
		echo('<p>No results!</p>');
	}
	echo('<p id="edit"><a href=/edit.php?team='.str_replace(' ','_',$value).'>Edit</a></p>');
	?>
</html>
