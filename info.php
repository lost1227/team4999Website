<?php session_start(); ?>
<html>
<head>
</head>
<body>
</body>
<?php
$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
$team = $_GET["team"];
echo('<h1>'.$team.'</h1>');
$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			foreach($row as $key => $value) {
				if ($key == "Team") {
					echo('<a href = /info.php?team='.$value.'>');
				}
				echo('<p>'.$key.': '.$value.'</p>');
				if ($key == "Team") {
					echo('</a>');
				}
			}
		}
	} else {
		echo('<p>No results!</p>');
	}
?>
</html>