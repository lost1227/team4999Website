<?php
require 'specificvars.php';

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors', TRUE);

$image_root = "photos/";
$acceptableFileTypes = array("jpg","png","jpeg","gif","bmp");
date_default_timezone_set("America/Los_Angeles");

$explodeseparator = ",";

function writeToLog($string, $log) {
	if (!file_exists("./logs/")) {
		mkdir("./logs/",0777,true);
	}
	file_put_contents("./logs/".$log.".log", date("d-m-Y_h:i:s")."-- ".$string."\r\n", FILE_APPEND);
}

function logToJS($str) {
	echo('<script>console.log("'.clean($str).'");</script>');
}

function formatAndQuery() { #first argument should be the query. %sv for strings to be escaped, %s for string and $d for int. the rest of the arguments should be the values in order
	global $DB;
	if(!isset($DB)){
		$DB = createDBObject();
	}
	$args  = func_get_args();
  $query = array_shift($args); #remove the first element of the array as its own variable
	if(is_array($args[0])){$args = $args[0];}
  $query = str_replace("%sv","'%s'",$query);
	foreach ($args as $key => $val){
    $args[$key] = $DB->real_escape_string($val);
		#$args[$key] = htmlspecialchars($val);
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

function dbclean($data) {
	global $DB;
	$data = $DB->real_escape_string($data);
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

/**
 * Gets the full name of a user
*/
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

function getYearData($haystackJson, $needleYear) {
  foreach($haystackJson as $index=>$yeard) {
    if($yeard["year"] == $needleYear) {
      return array($index,$yeard);
    }
  }
	return false;
}

function getDefaultYear() {
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	if(isset($_SESSION["year"])) {
		return $_SESSION["year"];
	}
	if(file_exists("schema.json")) {
		$json = json_decode(file_get_contents("schema.json"), True);
		$year = date("Y");
		for($i = 0; $i < 10; $i++) {
			$data = getYearData($json, $year - $i);
			if(!($data === false)) {
				return $year - $i;
			}
		}
	}

}

/**
 * Gets the data associated some keys
 * @param $table The table to retrieve the data from. Either $RobotDataTable or $EventDataTable
 * @param $id The robot or event to retrieve the info about
 * @param $keys The keys to retrieve data from. Either the "robotdata" or "matchdata" sections of schema.json
 * @return Array The keys object provided, with an additional field added to each key called "data_value"
*/
function retrieveKeys($table, $id, $keys) {
	global $DB, $RobotDataTable, $EventDataTable;
	if($table == $RobotDataTable) {
		$stmt = $DB->prepare("SELECT data_value FROM ".dbclean($table)." WHERE robotid = \"".dbclean($id)."\" AND data_key = ?;");
	} elseif ($table == $EventDataTable) {
		$stmt = $DB->prepare("SELECT data_value FROM ".dbclean($table)." WHERE eventid = \"".dbclean($id)."\" AND data_key = ?;");
	} else {
		return false;
	}
	$key = "";
	$stmt->bind_param("s",$key);
	$out = array();
	foreach($keys as $key=>$data) {
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows > 0) {
			$stmt->bind_result($value);
			$stmt->fetch();
			$data["data_value"] = $value;
		}
		$out[$key] = $data;
		$stmt->free_result();
	}
	$stmt->close();

	return $out;
}

/**
 * Updated data in the table, making a new row if necessary
 * @param $table Table to update. Either $RobotDataTable or $EventDataTable
 * @param $id Robot and/or event id to update
 * @param $keyvalues An associative array containing the keys to be inserted and their values. Example format: ["key_1"=>"value 1", "key_2"=>"value 2"]
*/
function updateDBKeys($table, $id, $keyvalues) {
	global $DB, $RobotDataTable, $EventDataTable;
	$stmt = $DB->prepare("INSERT INTO ".dbclean($table)." VALUES ('".dbclean($id)."',?,?) ON DUPLICATE KEY UPDATE data_value = VALUES(data_value)");

	$key = "";
	$value = "";
	$stmt->bind_param("ss",$key,$value);
	foreach($keyvalues as $key=>$value) {
		$stmt->execute();
	}
	$stmt->close();
}

/**
 * Adds an id to a Team
 * @param $team The team to add the id to
 * @param $idtype The type of id to add. Either "robotid" or "eventid"
 * @param $newId The new id to add
*/
function addIdToTeam($team, $idtype, $newId) {
	global $TeamDataTable, $explodeseparator;
	if($idtype == "robotid") {
		formatAndQuery("UPDATE %s SET robotids = CONCAT(robotids, %sv) WHERE number = %sv", $TeamDataTable, $explodeseparator.$newId, $team);
	} else {
		formatAndQuery("UPDATE %s SET eventids = CONCAT(eventids, %sv) WHERE number = %sv", $TeamDataTable, $explodeseparator.$newId, $team);
	}
}

function separate($input) {
	global $explodeseparator;
	if(empty($input)){
		return array();
	} else {
		return explode($explodeseparator, trim($input, ","));
	}
}

function getTeamIds($team) {
	global $TeamDataTable;
	$results = formatAndQuery("SELECT robotids,eventids FROM %s WHERE number = %d;",$TeamDataTable,$team);
	if($results->num_rows <= 0) {
		return false;
	} else {
		$result = $results->fetch_assoc();
		$robotids = separate($result["robotids"]);
		$eventids = separate($result["eventids"]);
		return array("robotids"=>$robotids, "eventids"=>$eventids);
	}
}

function getIdsForYear($table, $year, $ids) {
	global $DB, $RobotDataTable, $EventDataTable;
	if($table == $RobotDataTable) {
		$stmt = $DB->prepare("SELECT data_value FROM ".dbclean($table)." WHERE robotid = ? AND data_key = \"year\";");
	} elseif ($table == $EventDataTable) {
		$stmt = $DB->prepare("SELECT data_value FROM ".dbclean($table)." WHERE eventid = ? AND data_key = \"year\";");
	} else {
		return false;
	}
	$id = "";
	$stmt->bind_param("s",$id);
	$out = array();
	foreach($ids as $id) {
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows > 0) {
			$stmt->bind_result($val);
			$stmt->fetch();
			if($year == $val) {
				$out[] = $id;
			}
		}
		$stmt->free_result();
	}
	$stmt->close();
	return $out;
}

function renameKeyInTable($table, $oldkey, $newkey, $year) {
	global $DB, $RobotDataTable, $EventDataTable;
	if($table == $RobotDataTable) {
		$idres = formatAndQuery('SELECT robotid FROM %s WHERE data_key = "year" AND data_value = %d;',$RobotDataTable, $year);
	} elseif ($table == $EventDataTable) {
		$idres = formatAndQuery('SELECT eventid FROM %s WHERE data_key = "year" AND data_value = %d;',$EventDataTable, $year);
	} else {
		return false;
	}
	$ids = array();
	if($idres->num_rows > 0) {
		while($d = $idres->fetch_assoc()) {
			if($table == $RobotDataTable) {
				$ids[] = $d["robotid"];
			} elseif ($table == $EventDataTable) {
				$ids[] = $d["eventid"];
			}
		}
	}

	if($table == $RobotDataTable) {
		$stmt = $DB->prepare("UPDATE ".dbclean($RobotDataTable)." SET data_key = \"".dbclean($newkey)."\" WHERE data_key = \"".dbclean($oldkey)."\" AND robotid = ?;");
	} elseif ($table == $EventDataTable) {
		$stmt = $DB->prepare("UPDATE ".dbclean($EventDataTable)." SET data_key = \"".dbclean($newkey)."\" WHERE data_key = \"".dbclean($oldkey)."\" AND eventid = ?;");
	} else {
		return false;
	}
	$id = "";
	$stmt->bind_param("s",$id);
	foreach($ids as $id) {
		$stmt->execute();
	}
	$stmt->close();
	return true;
}

function deleteKeyInTable($table, $key, $year) {
	global $DB, $RobotDataTable, $EventDataTable;
	if($table == $RobotDataTable) {
		$idres = formatAndQuery('SELECT robotid FROM %s WHERE data_key = "year" AND data_value = %d;',$RobotDataTable, $year);
	} elseif ($table == $EventDataTable) {
		$idres = formatAndQuery('SELECT eventid FROM %s WHERE data_key = "year" AND data_value = %d;',$EventDataTable, $year);
	} else {
		return false;
	}
	$ids = array();
	if($idres->num_rows > 0) {
		while($d = $idres->fetch_assoc()) {
			if($table == $RobotDataTable) {
				$ids[] = $d["robotid"];
			} elseif ($table == $EventDataTable) {
				$ids[] = $d["eventid"];
			}
		}
	}

	if($table == $RobotDataTable) {
		$stmt = $DB->prepare("DELETE FROM ".dbclean($RobotDataTable)." WHERE data_key = \"".dbclean($key)."\" AND robotid = ?;");
	} elseif ($table == $EventDataTable) {
		$stmt = $DB->prepare("DELETE FROM ".dbclean($EventDataTable)." WHERE data_key = \"".dbclean($key)."\" AND robotid = ?;");
	} else {
		return false;
	}
	$id = "";
	$stmt->bind_param("s",$id);
	foreach($ids as $id) {
		$stmt->execute();
	}
	$stmt->close();
	return true;
}
/**
 * Gets a unique ID, checking that the ID is unique against the $RobotDataTable and the $EventDataTable
 * @param $prefix A prefix to apply to the ID. Usually rb_ for a robot and mt_ for a match
*/
function getNewId($prefix) {
	global $RobotDataTable, $EventDataTable;
	$id = uniqid($prefix);
	$res = formatAndQuery("SELECT * FROM %s WHERE robotid = %sv UNION SELECT * FROM %s WHERE eventid = %sv;", $RobotDataTable, $id, $EventDataTable, $id);
	if($res->num_rows > 0) {
		return getNewId($prefix);
	} else {
		return $id;
	}
}
/**
 * Deletes a robot or a match
 * @param $datatable $RobotDataTable or $EventDataTable
 * @param $id The id to remove
*/
function deleteItem($datatable, $id) {
	global $DB, $RobotDataTable, $EventDataTable, $TeamDataTable, $explodeseparator;

	if(empty($id)) {
		throw new Exception("ID IS EMPTY!");
	}

	if($datatable == $RobotDataTable) {
		$column = "robotids";
		$column2 = "robotid";
	} elseif ($datatable == $EventDataTable) {
		$column = "eventids";
		$column2 = "eventid";
	} else {
		return false;
	}

	$data = formatAndQuery("SELECT %s FROM %s WHERE %s LIKE \"%%%s%%\"",$column,$TeamDataTable,$column,$id);

	if($data->num_rows > 0) {

		$stmt = $DB->prepare("UPDATE ".$TeamDataTable." SET ".$column." = ? WHERE ".$column." = ?");

		if($stmt === False) {
			die("Error: ".$DB->error);
		}

		$idscompact = "";
		$newidscompact = "";
		$stmt->bind_param("ss",$newidscompact, $idscompact);

		while($row = $data->fetch_assoc()) {
			$idscompact = $row[$column];
			if(empty($idscompact)) {
				continue;
			}
			$ids = explode($explodeseparator,$idscompact);
			unset($ids[array_search($id, $ids)]);
			$newidscompact = implode($explodeseparator,$ids);
			$stmt->execute();
		}
		$stmt->close();
	}

	formatAndQuery("DELETE FROM %s WHERE %s = %sv", $datatable, $column2, $id);

}
/**
 * Checks if a team is already in the database.
 * @return Boolean A boolean indicating if the team is already set
*/
function checkTeamInDB($team) {
	global $TeamDataTable;
	$result = formatAndQuery('SELECT number FROM %s WHERE number = %sv', $TeamDataTable, $team);
	return $result->num_rows > 0;
}

function getUniqueFilename($dir) {
	if(file_exists($dir)) {
		$bases = array();
		$files = scandir($dir);
		foreach($files as $file) {
			//var_dump($file);
			$bases[] = basename($file, ".".pathinfo(basename($file),PATHINFO_EXTENSION));
		}
		do {
			$id = uniqid("pic_");
		} while(in_array($id, $bases));
		return $id;
	} else {
		return False;
	}
}
/**
 * Recursively removes a folder along with all its files and directories
 *
 * @param String $path
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
	$objects = scandir($dir);
	foreach ($objects as $object) {
		if ($object != "." && $object != "..") {
			if (is_dir($dir."/".$object))
				rrmdir($dir."/".$object);
			else
				unlink($dir."/".$object);
			}
		}
		rmdir($dir);
	}
}

/**
 * Sets a random token used to preform CSRF verification on form requests
*/
function setCSRFToken() {
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	$_SESSION["CSRF_token"] = clean(bin2hex(openssl_random_pseudo_bytes(16)));
}

/**
 * Gets the current CSRF token
 * @return String The current CSRF token
 */
 function getCSRFToken() {
	 if(session_status() == PHP_SESSION_NONE) {
		 session_start();
	 }
	 return $_SESSION["CSRF_token"];
 }

/**
 * Checks if the given token matches the CSRF_token for the current session
 * @param String $token The token to be checked against the saved token
 */
function checkCSRFToken($token) {
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	return $_SESSION["CSRF_token"] === $token;
}

?>
