<?php session_start(); ?>
<html>
<head>
	<title>Edit Team</title>
	<link rel="stylesheet" href="styles/edit.css">
	<!--favicon generated by http://realfavicongenerator.net/-->
	<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/favicons/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="/favicons/manifest.json">
	<link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/favicons/favicon.ico">
	<meta name="apple-mobile-web-app-title" content="Scouting">
	<meta name="application-name" content="Scouting">
	<meta name="msapplication-config" content="/favicons/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
	<script src="scripts/jquery-3.1.1.min.js"></script>
	<script src="scripts/edit.js"></script>
</head>
<body>
<div id="main">
<form action="<?php echo(htmlentities($_SERVER['PHP_SELF'])); ?>" method="post" id="mainf">
<?php
require 'functions.php';
function image_fix_orientation(&$image, $filename) {
    $exif = exif_read_data($filename);

    if (!empty($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 3:
                $image = imagerotate($image, 180, 0);
                break;

            case 6:
                $image = imagerotate($image, -90, 0);
                break;

            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }
    }
}

function getAccordion($title,$content) {
	return '<div class="accordion">
		<button class="accordionbutton">'.$title.'</button>
		<div class="accordioncontent">'.$content.'</div>
		</div>';
}

$acceptableFileTypes = array("jpg","png","jpeg","gif","bmp",);
#check if logged in and redirect if not
if (isset($_SESSION["loggedIn"])){
	$DB = createDBObject();
} else {
	if(isset($_GET["team"])){
		header( 'Location: '.getRootDir().'login.php?redirect=edit.php?team='.$_GET["team"]);
	} else {
		header( 'Location: '.getRootDir().'login.php?redirect=edit.php');
	}
	exit();
}
if($_SERVER["REQUEST_METHOD"] == "GET") {
	if(!isset($_GET["team"])) {
		header( 'Location: '.getRootDir().'index.php');
	} else {
		$team = clean($_GET["team"]);
	}
}
if($_SERVER["REQUEST_METHOD"] == "POST") {
	if(!isset($_POST["team"])) {
		header( 'Location: '.getRootDir().'index.php');
	} else {
		$team = clean($_POST["team"]);
	}
}

	$data = getTeamIds($team);
	if($data === false) {
		$robotids = array();
		$eventids = array();
	} else {
		$robotids = $data["robotids"];
		$eventids = $data["eventids"];
	}

if(file_exists("schema.json")) {
	$json = json_decode(file_get_contents("schema.json"), True);
	$year = getYearData($json, getDefaultYear())[1];
	if($year === false) {
		echo("<p>Schema for this year does not exist</p>");
		exit();
	}
} else {
	echo("<p>schema.json does not exist!</p>");
	exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
	$robotdata = $_POST["robot"];
	$robotschema = $year["robotdata"];
	foreach($robotdata as $robotid=>$robot){
		$changedKeyValues = array();
		if(in_array($robotid, $robotids)) {
			foreach($robot as $robotkey=>$robotdata) {
				if(isset($robotschema[$robotkey])) {
					$changedKeyValues[$robotkey] = clean($robotdata);
				}
			}
		} else {
			// TODO: Account for newly added robots
		}
		updateDBKeys($RobotDataTable, $robotid, $changedKeyValues);
	}

	$eventdata = $_POST["event"];

	// TODO: Account for updates in event data

}

$robotids = getIdsForYear($RobotDataTable, $year["year"], $robotids);
$eventids = getIdsForYear($EventDataTable, $year["year"], $eventids);
echo("<p>Robots:</p>");
if(count($robotids) > 0) {
	foreach($robotids as $index=>$robotid) {
		$data = retrieveKeys($RobotDataTable, $robotid, $year["robotdata"]);
		if(isset($data["name"])) {
			$title = $data["name"];
		} else {
			$title = "Robot #" . ($index + 1);
		}
		$content = "";
		foreach($data as $key=>$value) {
			$content .= '<div class="keypair">';
			switch($value["type"]) {
				case "string":
					$content .= '<p class="key">'.$value["display_name"].': </p><input type="text" name="robot['.$robotid.']['.$key.']" value="'.$value["data_value"].'">';
					break;
				case "select":
					$content .= '<p class="key">'.$value["display_name"].': </p><select name="robot['.$robotid.']['.$key.']">';
					foreach($value["values"] as $option) {
						if($option == $value["data_value"]) {
							$content .= '<option value="'.$option.'" selected="selected">'.$option.'</option>';
						} else {
							$content .= '<option value="'.$option.'">'.$option.'</option>';
						}
					}
					$content .= '</select>';
					break;
				case "boolean": // store booleans as strings in the DB, where "true" is true
					if($value["data_value"] == "true") {
						$content .= '<label><p class="key">'.$value["display_name"].': </p><input type="checkbox" name="robot['.$robotid.']['.$key.']" checked></label>';
					} else {
						$content .= '<label><p class="key">'.$value["display_name"].': </p><input type="checkbox" name="robot['.$robotid.']['.$key.']"></label>';
					}
					break;
				case "number":
					$content .= '<p class="key">'.$value["display_name"].': </p><input type="number" name="robot['.$robotid.']['.$key.']" value="'.$value["data_value"].'">';
					break;
				case "textarea":
					$content .= '<p class="key">'.$value["display_name"].': </p><input type="number" name="robot['.$robotid.']['.$key.']">'.$value["data_value"].'</textarea>';
					break;
			}
			$content .= '</div>';
		}
		echo(getAccordion($title, $content));
	}
} else {
	echo("<p>No data!</p>");
}
echo("<p>Events:</p>");
if(count($eventids) > 0) {
	foreach($eventids as $index=>$eventid) {
		$data = retrieveKeys($EventDataTable, $eventid, $year["matchdata"]);
		if(isset($data["name"])) {
			$title = $data["name"];
		} else {
			$title = "Event #" . ($index + 1);
		}
		$content = "";
		foreach($data as $key=>$value) {
			$content .= '<div class="keypair">';
			switch($value["type"]) {
				case "string":
					$content .= '<p class="key">'.$value["display_name"].': </p><input type="text" name="event['.$eventid.']['.$key.']" value="'.$value["data_value"].'">';
					break;
				case "select":
					$content .= '<p class="key">'.$value["display_name"].': </p><select name="event['.$eventid.']['.$key.']">';
					foreach($value["values"] as $option) {
						if($option == $value["data_value"]) {
							$content .= '<option value="'.$option.'" selected="selected">'.$option.'</option>';
						} else {
							$content .= '<option value="'.$option.'">'.$option.'</option>';
						}
					}
					$content .= '</select>';
					break;
				case "boolean": // store booleans as strings in the DB, where "true" is true
					if($value["data_value"] == "true") {
						$content .= '<label><p class="key">'.$value["display_name"].': </p><input type="checkbox" name="event['.$eventid.']['.$key.']" value="true" checked></label>';
					} else {
						$content .= '<label><p class="key">'.$value["display_name"].': </p><input type="checkbox" name="event['.$eventid.']['.$key.']" value="false"></label>';
					}
					break;
				case "number":
					$content .= '<p class="key">'.$value["display_name"].': </p><input type="number" name="event['.$eventid.']['.$key.']" value="'.$value["data_value"].'">';
					break;
				case "textarea":
					$content .= '<p class="key">'.$value["display_name"].': </p><textarea name="event['.$eventid.']['.$key.']">'.$value["data_value"].'</textarea>';
					break;
			}
			$content .= '</div>';
		}
		echo(getAccordion($title, $content));
	}
} else {
	echo("<p>No data!</p>");
}
echo('<input type="hidden" name="team" value="'.$team.'">');
?>
<input type="submit" value="Save">
</form>
</div>
</body>
</html>
