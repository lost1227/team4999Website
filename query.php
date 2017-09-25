<?php session_start(); ?>
<?php
require 'functions.php';
#Check if logged in and use read-only account if not
$DB = createDBObject();
#Check if accessed by post and apply the filters if so
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$query = 'SELECT Team FROM '.getCurrentDB().' WHERE ';
	$index = 1;
	#loop through all the filters and apply each one, adding an 'AND' between each
	$params = array();
	foreach($_POST as $key => $value) {
		$query .= '%s LIKE %sv';
		if ($index != count($_POST) ){
			$query .= ' AND ';
		} else {
			$query .= ' ORDER BY Team ASC;';
		}
		$params[] = $key;
		$params[] = $value;
		$index++;
	}
} else {
	#If not accessed by POST, show all rows
	$query = 'SELECT Team FROM '.getCurrentDB().' ORDER BY Team ASC;';
}
#execute the query
writeToLog("Using query: ".$query, "filters");
$data = formatAndQuery($query,$params);
if($data == false) {
	writeToLog($query . " gave the error ".$DB->error,"filters");
}
#echo($data->num_rows);
#check if there were any results
if($data->num_rows > 0){
	while($row = $data->fetch_assoc()) { #loop through each row of the table
		echo('<a class="teamlink" href = info.php?team='.str_replace(' ','_',$row["Team"]).'>');
		echo('<div class="infoRow">
			<p>Team: '.$row["Team"].'</p>
			</div></a>');
	}
} else {
	echo('<p style="text-align: center; font-family: StormFaze; width: inherit; font-size: 25px; padding: 10px;">No results!</p>');
}
?>
