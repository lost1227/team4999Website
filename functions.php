<?php
require 'specificvars.php';
$image_root = "photos/";
$acceptableFileTypes = array("jpg","png","jpeg","gif","bmp");
date_default_timezone_set("America/Los_Angeles");

function writeToLog($string, $log) {
	if (!file_exists("./logs/")) {
		mkdir("./logs/",0777,true);
	}
	file_put_contents("./logs/".$log.".log", date("d-m-Y_h:i:s")."-- ".$string."\r\n", FILE_APPEND);
}
function formatAndQuery() { #first argument should be the query. %sv for strings to be escaped, %s for string and $d for int. the rest of the arguments should be the values in order
	global $DB;
	$args  = func_get_args();
    $query = array_shift($args); #remove the first element of the array as its own variable
		if(is_array($args[0])){$args = $args[0];}
    $query = str_replace("%sv","'%s'",$query);
	foreach ($args as $key => $val)
    {
        $args[$key] = $DB->real_escape_string($val);
		$args[$key] = htmlspecialchars($val);
    }
	$query  = vsprintf($query, $args);
	#writeToLog("Old Query: ".$query,"query");
	$query = str_replace('\'\'',"null",$query);
	#writeToLog("New Query: ".$query,"query");
	writeToLog("Query: ".$query,"query");
    $result = $DB->query($query);
    if (!$result)
    {
        throw new Exception($DB->error." [$query]");
    }
    return $result;
}
function getCurrentDB() {
	return "2017ROBOTS";
}
function clean($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}
function checkUserPassword($user, $password) {
	$DB = new mysqli("localhost", $DBUser, $DBPass, $Database);
	$result = formatAndQuery("SELECT passhash FROM %s WHERE user LIKE %sv;",$LoginTableName, $user);
	if($data->num_rows > 0) {
		$result = $result->fetch_assoc();
	} else {
		return False;
	}
	if(!isset($result["passhash"])) {
		return False;
	}
	return password_verify($password, $result["passhash"]);
}
function checkIsAdmin($user, $password) {
	$DB = new mysqli("localhost", $DBUser, $DBPass, $Database);
	$result = formatAndQuery("SELECT passhash FROM %s WHERE user LIKE %sv;",$LoginTableName, $user);
	if($data->num_rows > 0) {
		$result = $result->fetch_assoc();
	} else {
		return False;
	}
	if(!isset($result["passhash"])) {
		return False;
	}
	if(password_verify($password, $result["passhash"]) && isset($result["admin"])) {
		return $result["admin"] != 0
	} else {
		return False;
	}
}
function createDBObject() {
	session_start();
	if (isset($_SESSION["loggedIn"]) and checkUserPassword($_SESSION["user"], $_SESSION["pass"])){
		$DB = new mysqli("localhost",$DBUser,$DBPass,"momentu2_frcteam4999");
	} else {
		$DB = new mysqli("localhost","momentu2_ro","aRza#p=XckDC","momentu2_frcteam4999");
	}
	return $DB;
}
?>
