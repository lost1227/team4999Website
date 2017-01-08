<?php session_start(); ?>
<html>
<head>
</head>
<body>
</body>
<?php
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	$DB = new mysqli("localhost","ro","","frcteam4999");
}
$team = str_replace('_',' ',$_GET["team"]);
$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			foreach($row as $key => $value) {
				$key = str_replace('_',' ',$row);
				if ($key == "Team") {
					echo('<a href = /edit.php?team='.str_replace(' ','_',$value).'>');
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