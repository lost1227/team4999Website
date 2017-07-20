<?php
$image_root = "photos/";
$acceptableFileTypes = array("jpg","png","jpeg","gif","bmp");

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
?>
