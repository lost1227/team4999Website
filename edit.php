<?php session_start(); ?>
<html>
<head>
</head>
<body>
<?php
#check if logged in and redirect if not
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=edit.php');
	exit();
}
#get columns into an associative array
$columnData = $DB->query('DESCRIBE robots;');
$columns = array();
while($row = $columnData->fetch_assoc()) {
	$columns[] = $row;
}
#handle submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
}
#check if creating a new entry, or editing an existing entry
#creates an associative array the existing entry
if(isset($_GET["team"])){
	$team = str_replace('_',' ',$_GET["team"]);
	$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
	} else {
		unset($team);
	}
	echo('<h1>Team:'.$team.'</h1>');
}
#create the form
echo('<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">');
foreach($columns as $column) {
	#remove underscores from column names
	$column["Field"] = str_replace('_',' ',$column["Field"]);
	#don't allow editing of established team number
	if(!($column["Field"] == "Team" and isset($team))){
		if($column["Type"] == "text" or strpos($column["Type"], 'int') !== false) {
			echo('<p>'.$column["Field"].':</p><br>
				<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');
		}
	} elseif ($column["Field"] == "Team" and isset($team)) {
		echo('<input type="hidden" name="team" value="'.$team.'">');
	}
}
echo('<input type="submit" value="Submit"></form>');
?>
</body>
</html>