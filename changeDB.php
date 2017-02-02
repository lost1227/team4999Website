<?php session_start(); ?>
<html>
<head>
<title>Change DB</title>
<?php
require 'functions.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if(isset($_POST["DB_SELECT"])) {
		formatAndQuery("UPDATE Control SET CurrentDB = $sv;",$_POST["DB_SELECT"]);
	}
}
?>
</head>
<body>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<p>DB:</p>
<select id="DB_SELECT">
<?php
if (isset($_SESSION["loggedIn"])){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=changeDB.php');
	exit();
}
$result = $DB->query("SELECT CurrentDB FROM Control;");
$result = $result->fetch_assoc();
$currentDB = $result["CurrentDB"];

$tables = array();
$tablesRes = $DB->query("SHOW Tables;");
while($row = $tablesRes->fetch_assoc()){
	if($row["Tables_in_frcteam4999"] != "Control") {
		if($row["Tables_in_frcteam4999"] == $currentDB){
			echo('<option value="'.$row["Tables_in_frcteam4999"].'" selected>'.$row["Tables_in_frcteam4999"]."</option>");
		} else {
			echo('<option value="'.$row["Tables_in_frcteam4999"].'">'.$row["Tables_in_frcteam4999"]."</option>");
		}
	}
}
?>
<input type="submit" value="Submit">
</form>
</body>
</html>