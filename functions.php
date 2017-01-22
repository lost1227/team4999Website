<?php
function writeToLog($string, $log) {
	file_put_contents("/var/www/frcteam4999.jordanpowers.net/logs/".$log.".log", date("d-m-Y_h:i:s")."-- ".$string."\r\n", FILE_APPEND);
}
function formatAndQuery() { #first argument should be the query. %sv for strings to be escaped, %s for string and $d for int. the rest of the arguments should be the values in order
	global $DB;
	$args  = func_get_args();
    $query = array_shift($args); #remove the first element of the array as its own variable
    $query = str_replace("%sv","'%s'",$query);
	foreach ($args as $key => $val)
    {
        $args[$key] = $DB->real_escape_string($val);
    }
	$query  = vsprintf($query, $args);
    $result = $DB->query($query);
    if (!$result)
    {
        throw new Exception($DB->error." [$query]");
    }
    return $result;
}
?>