<?php session_start(); ?>
<?php
require 'functions.php';
if (!isset($_GET["team"]))	{
	echo("NO_TEAM");
} else {
	if(!checkTeamInDB($_GET["team"])) {
		echo("TRUE");
	} else {
		echo("FALSE");
	}
}
?>
