<?php
require 'specificvars.php';
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
function formatAndQuery() { #first argument should be the query. %sv for strings to be escaped, %s for string and $d for int. the rest of the arguments should be the values in order
	global $DB;
	$args  = func_get_args();
  $query = array_shift($args); #remove the first element of the array as its own variable
	if(is_array($args[0])){$args = $args[0];}
  $query = str_replace("%sv","'%s'",$query);
	foreach ($args as $key => $val){
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
?>
