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
function getCurrentTable() {
	global $CurrentTable;
	return $CurrentTable;
}
function clean($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}
function checkUserPassword($user, $password) {
	global $DBUser, $DBPass, $Database, $LoginTableName, $DB;
	$DBtmp = $DB;
	$DB = new mysqli("localhost", $DBUser, $DBPass, $Database);
	$result = formatAndQuery("SELECT passhash FROM %s WHERE user LIKE %sv;",$LoginTableName, $user);
	if($result->num_rows > 0) {
		$result = $result->fetch_assoc();
	} else {
		return False;
	}
	if(!isset($result["passhash"])) {
		return False;
	}
	$DB->close();
	$DB = $DBtmp;
	return password_verify($password, $result["passhash"]);
}
function checkIsAdmin($user, $password) {
	global $DBUser, $DBPass, $Database, $LoginTableName, $DB;
	if(!checkUserPassword($user, $password)) {
		return False;
	}
	$DBTmp = $DB;
	$DB = new mysqli("localhost", $DBUser, $DBPass, $Database);
	$result = formatAndQuery("SELECT passhash, admin FROM %s WHERE user LIKE %sv;", $LoginTableName, $user);
	if($result->num_rows > 0) {
		$result = $result->fetch_assoc();
	} else {
		return False;
	}
	if(!isset($result["passhash"])) {
		return False;
	}
	if(password_verify($password, $result["passhash"]) && isset($result["admin"])) {
		return $result["admin"];
	} else {
		return False;
	}
	$DB->close();
	$DB = $DBtmp;
}
function getUserName() {
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	if(isset($_SESSION["loggedIn"]) and $_SESSION["loggedIn"] and checkUserPassword($_SESSION["user"], $_SESSION["pass"])) {
		global $DB, $DBTmp, $DBUser, $DBPass, $Database, $LoginTableName;
		$DBtmp = $DB;
		$DB = new mysqli("localhost", $DBUser, $DBPass, $Database);
		$result = formatAndQuery("SELECT name FROM %s WHERE user LIKE %sv;", $LoginTableName, $_SESSION["user"]);
		$DB->close();
		$DB = $DBtmp;
		if($result->num_rows > 0) {
			$result = $result->fetch_assoc();
		} else {
			return False;
		}
		if(isset($result["name"])) {
			return $result["name"];
		}
	}
	return False;
}
function createDBObject() {
	global $roDBUser, $roDBPass, $DBUser, $DBPass, $Database;
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	if (isset($_SESSION["loggedIn"]) and checkUserPassword($_SESSION["user"], $_SESSION["pass"])){
		$DB = new mysqli("localhost",$DBUser,$DBPass,$Database);
	} else {
		$DB = new mysqli("localhost",$roDBUser,$roDBPass,$Database);
	}
	return $DB;
}
function getRootDir() {
	global $appdir;
	return $appdir;
}
?>
