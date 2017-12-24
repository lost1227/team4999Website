<?php
session_start();

require 'functions.php';
if (isset($_SESSION["loggedIn"])){
	$DB = createDBObject();
} else {
	exit();
}
if($_SERVER["REQUEST_METHOD"] == "GET") {
  if(isset($_GET["prefix"])) {
    echo(getNewId($_GET["prefix"]));
  }
}
 ?>
