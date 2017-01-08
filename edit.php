<?php session_start(); ?>
<html>
<head>
</head>
<body>
<?php
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php');
	exit();
}
$columnData = $DB->query('DESCRIBE robots;');
$columns = array();
while($row = $columnData->fetch_assoc()) {
	$columns[] = $row;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	echo('<script>window.alert("This is still in progress.</script>');
	# Deal with the form submission here
}
if(isset($_GET["team"])){
	$team = $_GET["team"];
	$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
	} else {
		unset($team);
	}
	echo('<h1>Team:'.$team.'</h1>');
}
echo('<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">');
foreach($columns as $column) {
	if(!($column["Field"] == "Team" and isset($team))){
		if($column["Type"] == "text" or strpos($column["Type"], 'int') !== false) {
			echo('<p>'.$column["Field"].':</p><br>
				<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');
		}
	}
}
echo('<input type="submit" value="Submit"></form>');
?>
</body>
</html>