<?php session_start(); ?>
<html>
<head>
<title>Add User</title>
<style>
@font-face{
	font-family: "StormFaze";
	src: url("/fonts/stormfaze.ttf");
}
body {
	background-image: url(/images/grey.png);
	margin: 0px;
	background-attachment: fixed;
}
form {
	width: 80%;
	margin: auto;
	background-color: rgba(255, 255, 255, 0.67);
	padding: 15px 20px;
	min-height: 100%;
	box-shadow: 0px 0px 10px 1px #06ceff;
	font-family: StormFaze;
}
#errorWarning {
	color: red;
	margin: 5px 0px;
}
</style>
<script>
function checkBoxes() {
	if (document.getElementById('pass1').value != document.getElementById('pass2').value) {
		document.getElementById('Submit').disabled = true;
		document.getElementById('errorWarning').style.display = "block";
	} else {
		document.getElementById('Submit').disabled = false;
		document.getElementById('errorWarning').style.display = "none";
	}
}
</script>
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
			try {
				formatAndQuery("GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP ON frcteam4999.* TO %sv@'localhost';",$_POST["usr"]);
				formatAndQuery("GRANT CREATE USER ON *.* TO %sv@'localhost' WITH GRANT OPTION;",$_POST["usr"]);
			} catch (Exception $e) {
				echo("Exception: ".$e->getMessage());
				formatAndQuery("DROP USER %sv@'localhost';",$_POST["usr"]);
			}
		} else {
			try {
				formatAndQuery("GRANT SELECT,INSERT,UPDATE ON frcteam4999.* TO %sv@'localhost';",$_POST["usr"]);
			} catch (Exception $e) {
				echo("Exception: ".$e->getMessage());
				formatAndQuery("DROP USER %sv@'localhost';");
			}
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
<input id='pass1' oninput="checkBoxes()" type="password"><br>
<input id='pass2' oninput="checkBoxes()" name="pass" type="password"><br>
<p id="errorWarning" hidden>PASSWORDS DON'T MATCH</p>
<label style="font-family: arial;"><input name="admin" type="checkbox">Admin</label><br>
<input id='Submit' type="submit">
</form>
</body>
</html>