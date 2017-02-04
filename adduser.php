<?php session_start(); ?>
<html>
<head>
<title>Add User</title>
</head>
<body>
<?php
require 'functions.php';
if (isset($_SESSION["loggedIn"])){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=adduser.php');
	exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	try {
		formatAndQuery("CREATE USER %sv@'localhost' IDENTIFIED BY %sv;",$_POST["usr"],$_POST["pass"]);
		if(isset($_POST["admin"])) {
			$privs = "GRANT SELECT,INSERT,UPDATE,GRANT OPTION,CREATE USER,DELETE,CREATE ON frcteam4999.* TO %sv@'localhost';";
		} else {
			$privs = "GRANT SELECT,INSERT,UPDATE ON frcteam4999.* TO %sv@'localhost';";
		}
		try {
			formatAndQuery($privs,$_POST["usr"]);
		} catch (Exception $e) {
			echo("Exception: ".$e->getMessage());
			formatAndQuery("DROP USER %sv@'localhost';");
		}
	} catch (Exception $e) {
		echo("Exception: ".$e->getMessage());
	}
}
?>
<form id="addUser" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
<p>User:</p>
<input name="usr" type="text"><br>
<p>Password:</p>
<input name="pass" type="password"><br>
<label><input name="admin" type="checkbox">Admin</label><br>
<input type="submit">
</form>
</body>
</html>