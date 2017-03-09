<?php session_start(); ?>
<?php
require 'functions.php';
if(!isset($_SESSION["loggedIn"])) {
	echo("LOGIN_ERROR");
} elseif (!isset($_GET["team"]))	{
	echo("NO_TEAM");
} else {
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"momentu2_frcteam4999");
	$result = formatAndQuery('SELECT Team FROM '.getCurrentDB().' WHERE Team = %sv',$_GET["team"]);
	if($result->num_rows == 0) {
		echo("TRUE");
	} else {
		echo("FALSE");
	}
}
?>