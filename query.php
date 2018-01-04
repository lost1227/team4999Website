<?php session_start(); ?>
<?php
require 'functions.php';

function getTeamsForRobotids($ids) {
	global $DB, $TeamDataTable;
	$rbquery = $DB->prepare("SELECT number FROM ".dbclean($TeamDataTable)." WHERE robotids LIKE ?");
	$rteams = array();
	$rb = "";
	if($rbquery === false) {
		die("Error: ".$DB->error);
	}
	$rbquery->bind_param("s",$rb);
	foreach($ids as $rb) {
		$rb = '%'.$rb.'%';
		$rbquery->execute();
		$result = $rbquery->get_result();
		if($result->num_rows > 0) {
			while($rrow = $result->fetch_assoc()) {
				if(!in_array($rrow["number"], $rteams)){
					$rteams[] = $rrow["number"];
				}
			}
		}
	}
	return $rteams;
}

function getTeamsForMatchids($ids) {
	global $DB, $TeamDataTable;
	$mquery = $DB->prepare("SELECT number FROM ".dbclean($TeamDataTable)." WHERE eventids LIKE ?");
	$mteams = array();
	$m = "";
	if($mquery === false) {
		die("Error: ".$DB->error);
	}
	$mquery->bind_param("s",$m);
	foreach($ids as $m) {
		$m = '%'.$m.'%';
		$mquery->execute();
		$result = $mquery->get_result();
		if($result->num_rows > 0) {
			while($mrow = $result->fetch_assoc()) {
				if(!in_array($mrow["number"], $mteams)){
					$mteams[] = $mrow["number"];
				}
			}
		}
	}
	return $mteams;
}

#Check if logged in and use read-only account if not
$DB = createDBObject();
#Check if accessed by post and apply the filters if so
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$filters = json_decode(file_get_contents('php://input'), true);

	$robotfilters = $filters["robot"];
	$matchfilters = $filters["match"];
	$teamfilter = $filters["team"];

	# Check the robot table and event table for robotids and matchids that match the given filters

	/*
		Need to be able to differentiate robotkeys from matchkeys
		Test the robotdatatable for each robotfilter
			1.Make an array with the first filter
			2.Test the results of subsequent filters to see if they're in the array
		Test the eventdatatable for each matchfilter

		Test for teams:
			1. If both robot and event filters were specified, find teams that have both a robot and a match that fitted the Filters
			2. If only robot or only event filters were specified, find teams that only match robot or event filters
	*/
	$first = true;
	$robots = array();
	foreach($robotfilters as $key=>$value) {
		$rbdata = formatAndQuery("SELECT robotid FROM %s WHERE data_key = %sv AND data_value LIKE \"%%%s%%\"", $RobotDataTable, $key, $value);
		$result = array();
		if($rbdata->num_rows > 0) {
			while($rbrow = $rbdata->fetch_assoc()) {
				$result[] = $rbrow["robotid"];
			}
		}
		if($first) {
			$first = false;
			$robots = $result;
		} else {
			$robots = array_intersect($robots, $result);
		}
	}

	$first = true;
	$matches = array();
	foreach($matchfilters as $key=>$value) {
		$mdata = formatAndQuery("SELECT eventid FROM %s WHERE data_key = %sv AND data_value LIKE \"%%%s%%\"", $EventDataTable, $key, $value);
		$result = array();
		if($mdata->num_rows > 0) {
			while($mrow = $mdata->fetch_assoc()) {
				$result[] = $mrow["eventid"];
			}
		}
		if($first) {
			$first = false;
			$matches = $result;
		} else {
			$matches = array_intersect($matches, $result);
		}
	}

	if(count($robotfilters) > 0 && count($matchfilters) > 0) {
		$teams = array_intersect(getTeamsForRobotids($robots), getTeamsForMatchids($matches));
	} elseif (count($robotfilters) > 0) {
		$teams = getTeamsForRobotids($robots);
	} elseif (count($matchfilters) > 0) {
		$teams = getTeamsForMatchids($matches);
	} elseif (!empty($teamfilter)) {
		$data = formatAndQuery('SELECT number FROM %s WHERE number LIKE "%%%s%%" ORDER BY number ASC',$TeamDataTable,$teamfilter);
		$teams = array();
		if($data->num_rows > 0){
			while($row = $data->fetch_assoc()) {
				$teams[] = $row["number"];
			}
		}
	} else {
		echo("Invalid JSON data. At least one filter is needed, or use GET");
	}

} else {
	#If not accessed by POST, show all rows
	$data = formatAndQuery('SELECT number FROM %s ORDER BY number ASC',$TeamDataTable);
	$teams = array();
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			$teams[] = $row["number"];
		}
	}
}

echo('<a class="teamlink" href="logout.php?redirect=login.php"><div class="infoRow year"><p>'.clean(getDefaultYear()).'</p></div></a>');
if(count($teams) > 0){
	sort($teams);
	foreach($teams as $team) {
		echo('<a class="teamlink" href = "info.php?team='.$team.'">');
		echo('<div class="infoRow">
			<p>Team: '.$team.'</p>
			</div></a>');
	}
} else {
	echo('<p style="text-align: center; font-family: StormFaze; width: inherit; font-size: 25px; padding: 10px;">No results!</p>');
}
?>
