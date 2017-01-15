<?php session_start(); ?>
<html>
	<head>
		<title>Log in</title>
		<link rel="stylesheet" href="style.css">
		<?php
		$redirect = $_GET['redirect'];
		function clean($data) {
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;
		}
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$user = clean($_POST["user"]);
			$pass = clean($_POST["pass"]);
			$redirect = $_POST["redirect"];
			$DB = new mysqli("localhost",$user,$pass,"frcteam4999");
			$_SESSION["user"] = $user;
			$_SESSION["pass"] = $pass;
			if (!$DB->connect_error) {
				$_SESSION["loggedIn"] = True;
			}
		}
		if (isset($_SESSION["loggedIn"])) {
			if (isset($_POST["redirect"])) {
				header( 'Location: https://frcteam4999.jordanpowers.net/'.clean($_POST["redirect"]));
				exit();
			}
			header( 'Location: https://frcteam4999.jordanpowers.net/home.php');
		}
		?>
	
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	
	
	</head>
	<body>
		<?php
			if ($DB->connect_error) {
				echo('<p>' . $DB->connect_error . '</p>');
			}
		?>
		<form id="loginform" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<p id="userlabel">Username:</p><br>
			<input id="usernamefield" type="text" name="user" <?php if($_SERVER["REQUEST_METHOD"] == "POST"){echo('value="'.$user.'"');}?>><br>
			<p id="passlabel">Password:</p><br>
			<input id="passwordfield" type="password" name="pass"><br>
			<?php
			if(isset($redirect)){
				echo('<input type="hidden" name="redirect" value="'.$redirect.'">');
			}
			?>
			<div id="submitbtn" onclick="javascript:parentNode.submit();"></div>
		</form>
	</body>
</html>
