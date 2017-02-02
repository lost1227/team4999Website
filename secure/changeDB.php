<?php session_start(); ?>
<html>
<head>
<title>Change DB</title>
<?php
require '../functions.php';
if (isset($_SESSION["loggedIn"])){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=changeDB.php');
	exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if(!empty($_POST["NEW_DB"])){
		formatAndQuery("CREATE TABLE frcteam4999.%s LIKE frcteam4999.Template;",$_POST["NEW_DB"]);
		formatAndQuery("UPDATE Control SET CurrentDB = %sv;",$_POST["NEW_DB"]);
	} else {
		if(isset($_POST["DB_SELECT"])) {
			formatAndQuery("UPDATE Control SET CurrentDB = %sv;",$_POST["DB_SELECT"]);
		}
	}
}
?>
</head>
<body>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
<p>DB:</p>
<select name="DB_SELECT">
<?php
$result = $DB->query("SELECT CurrentDB FROM Control;");
$result = $result->fetch_assoc();
$currentDB = $result["CurrentDB"];

$tables = array();
$tablesRes = $DB->query("SHOW Tables;");
while($row = $tablesRes->fetch_assoc()){
	if($row["Tables_in_frcteam4999"] != "Control" and $row["Tables_in_frcteam4999"] != "Template") {
		if($row["Tables_in_frcteam4999"] == $currentDB){
			echo('<option value="'.$row["Tables_in_frcteam4999"].'" selected>'.$row["Tables_in_frcteam4999"]."</option>");
		} else {
			echo('<option value="'.$row["Tables_in_frcteam4999"].'">'.$row["Tables_in_frcteam4999"]."</option>");
		}
	}
}
?>
</select>
<p>Add:</p>
<input type="text" name="NEW_DB"><br>
<input type="submit" value="Submit">
</form>
</body>
</html>