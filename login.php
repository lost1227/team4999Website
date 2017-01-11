<?php session_start(); ?>
<html>
<head>
<title>Log in</title>
<link rel="stylesheet" href="style.css">
<?php
function clean($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$user = clean($_POST["user"]);
	$pass = clean($_POST["pass"]);
	$DB = new mysqli("localhost",$user,$pass,"frcteam4999");
	$_SESSION["user"] = $user;
	$_SESSION["pass"] = $pass;
	if (!$DB->connect_error) {
		$_SESSION["loggedIn"] = True;
	}
}
if ($_SESSION["loggedIn"]) {
	if (isset($_POST["redirect"])) {
		header( 'Location: https://frcteam4999.jordanpowers.net/'.clean($_POST["redirect"]));
		exit();
	}
	header( 'Location: https://frcteam4999.jordanpowers.net/home.php');
}
?>
</head>
<body>
<?php
if ($DB->connect_error) {
	echo('<p>' . $DB->connect_error . '</p>');
}
?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<p>Username:</p><br>
	<input type="text" name="user"><br>
	<p>Password:</p><br>
	<input type="password" name="pass"><br>
	<?php
	if(isset($_GET['redirect'])){
		echo('<input type="hidden" name="redirect" value="'.$_GET['redirect'].'">');
	}
	?>
	<input type="submit" value="Submit">
</form>
</body>
</html>