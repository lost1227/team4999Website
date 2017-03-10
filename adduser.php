<?php session_start(); ?>
<html>
<head>
<title>Add User</title>
<style>
@font-face{
	font-family: "StormFaze";
	src: url("fonts/stormfaze.ttf");
}
body {
	background-image: url(images/grey.png);
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
require 'xmlapi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$xmlapi = new xmlapi("momentum4999.com", "momentu2",$_POST["cPpass"]);
	$xmlapi->set_port( 2083 );
	$create = $xmlapi->api2_query("momentu2","MysqlFE","createdbuser",array("dbuser"=>'momentu2_'.$_POST["usr"],"password"=>$_POST["pass"]));
	#echo(htmlspecialchars($create->asXML()));
	if($create->cpanelresult->data->result == 0) {
		$error = $create->cpanelresult->data->reason;
	} else {
		$addprivs = $xmlapi->api2_query("momentu2","MysqlFE","setdbuserprivileges",array("privileges"=>"SELECT,INSERT,UPDATE","db"=>"momentu2_frcteam4999","dbuser"=>'momentu2_'.$_POST["usr"]));
		#echo(htmlspecialchars($addprivs->asXML()));
		if($addprivs->cpanelresult->data->result == 0) {
			$error = $create->cpanelresult->data->reason;
		}
	}
}
?>
<form id="addUser" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
<p>Password for momentu2</p>
<input name="cPpass" type="password" <?php if($_SERVER["REQUEST_METHOD"] == "POST" and !isset($error)) { echo('value="'.$_POST["cPpass"].'"'); } ?> ><br>
<p>User:</p>
<input name="usr" type="text"><br>
<p>Password:</p>
<input id='pass1' oninput="checkBoxes()" type="password"><br>
<input id='pass2' oninput="checkBoxes()" name="pass" type="password"><br>
<p id="errorWarning" hidden>PASSWORDS DON'T MATCH</p>
<input id='Submit' type="submit">
<?php
if(isset($error) and !empty($error)) {
	echo('<p style="color: red; margin: 0px 5px;">'.$error.'</p>');
}
?>
</form>
</body>
</html>