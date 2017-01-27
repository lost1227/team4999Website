<?php session_start(); ?>
<html>
<head>
	<title>Edit Team</title>
	<style>
	p {
		margin: 0px;
	}
	#image {
		max-width: 25%;
		display: block;
	}
	#image:hover {
		max-width: 100%;
	}
	</style>
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
</head>
<body>
<?php
require 'functions.php';
#check if logged in and redirect if not
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	if(isset($_GET["team"])){
		header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=edit.php?team='.$_GET["team"]);
	} else {
		header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=edit.php');
	}
	exit();
}
#get columns into an associative array
$columnData = $DB->query('DESCRIBE robots;');
$columns = array();
while($row = $columnData->fetch_assoc()) {
	$columns[] = $row;
}
#handle submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	#check if team exists
	$data = formatAndQuery('SELECT Team FROM robots WHERE Team = %d;',$_POST["Team"]);
	if($data->num_rows == 0){ # add team if it doesn't exist yet
		formatAndQuery('INSERT INTO robots (Team) VALUES (%d);',$_POST["Team"]);
	}
	$update = 'UPDATE robots SET %s = %sv WHERE Team = %d;';
	foreach($columns as $column) {
		if($column["Field"] != "Team" and $column["Field"] != "Stored_Images"){
			formatAndQuery($update,$column["Field"],$_POST[$column["Field"]],$_POST["Team"]);
		}
	}
	if (is_uploaded_file($_FILES["image"]["tmp_name"])) {
		$image_root = "photos/";
		if (!file_exists($image_root)) {
			mkdir($image_root,0777,true);
		}
		$image_dir = $image_root . $_POST["Team"] ."/";
		if (!file_exists($image_dir)) {
			mkdir($image_dir,0777,true);
		}
		#the files are stored in the DB as a comma-separated list of paths. Separate that into an array of strings
		$filesResultObj = formatAndQuery("SELECT Stored_Images FROM robots WHERE Team = %d",$_POST["Team"]);
		$filesResult = $filesResultObj->fetch_assoc();
		writeToLog("Results: ".$filesResult["Stored_Images"],"images");
		if(!empty($filesResult["Stored_Images"])) {
			$files = explode(",",$filesResult["Stored_Images"]);
		} else {
			$files = array();
		}
		$biggestFile = 0;
		if (!empty($files)) {
			foreach($files as $file) {
				#get file base without extension
				writeToLog("File from results before basename: ".$file,"images");
				$file = basename($file, "." . pathinfo(basename($file),PATHINFO_EXTENSION));
				writeToLog("File from results after basename: ".$file,"images");
				if($file > $biggestFile) {
					$biggestFile = $file;
				}
			}
		}
		$imgeFileExtension = pathinfo(basename($_FILES["image"]["name"]),PATHINFO_EXTENSION);
		$target_file_path = $image_dir . ($biggestFile + 1) .".". pathinfo(basename($_FILES["image"]["name"]),PATHINFO_EXTENSION);
		$continueUpload = TRUE;
		//check if is image
		if(getimagesize($_FILES["image"]["tmp_name"]) == FALSE) {
			writeToLog("INVALID FILE","images");
			$continueUpload = FALSE;
		}
		//check if acceptable image (trusts extension)
		$acceptableFileTypes = array("jpg","png","jpeg","gif");
		if(!in_array($imgeFileExtension,$acceptableFileTypes)) {
			writeToLog("INVALID FILE","images");
			$continueUpload = FALSE;
		}
		if($continueUpload) {
			if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_path)) {
				writeToLog("ERROR MOVING UPLOADED FILE","images");
			} else {
				# add the new file to the files array
				$files[] = basename($target_file_path);
				
				#create a string by placing every member of the array in a string, separated by commas
				$DB_entry = "";
				foreach($files as $file) {
					writeToLog("New file result: ".$file,"images");
					$DB_entry = $DB_entry . $file . ",";
				}
				writeToLog("New DB entry: ".$DB_entry,"images");
				$DB_entry = rtrim($DB_entry,",");
				writeToLog($DB_entry,"images");
				formatAndQuery("UPDATE robots SET Stored_Images = %sv WHERE Team = %d",$DB_entry,$_POST["Team"]);
			}
		}
	}
	header( 'Location: https://frcteam4999.jordanpowers.net/info.php?team='.$_POST["Team"]);
}
#check if creating a new entry, or editing an existing entry
#creates an associative array of the existing entry
if(isset($_GET["team"])){
	$data = formatAndQuery('SELECT * FROM robots WHERE Team = %d;',$_GET["team"]);
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
		echo('<h1>Team: '.$_GET["team"].'</h1>');
	}
}
#create the form
echo('<form id="edit" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post" autocomplete="off" enctype="multipart/form-data">');
foreach($columns as $column) {
	#remove underscores from column names
	$PrettyColumn = str_replace('_',' ',$column["Field"]);
	if(!($column["Field"] == "Team" and isset($_GET["team"]))){ #Check if creating a new team
		echo('<p>'.$PrettyColumn.':</p>');
		switch($column["Field"]) {
			#check all numbers
			case("Team"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9]*" required>');
				break;
			case("Width"):
			case("Depth"):
			case("Height"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9.]*"><span>inches</span>');
				break;
			case("Weight"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9.]*"><span>lbs</span>');
				break;
			case("Drive_System"): #Create select menu with options for each type of drive system. The array $options can have new drive systems added to it to create more options
				echo('<select name="'.$column["Field"].'">');
				$options=array("West Coast","Mechanum","Tank","Swerve");
				foreach($options as $option) {
					if ($option == $row[$column["Field"]]) { #if the value is already set, set the option to that value
						echo('<option value="'.$option.'" selected>'.$option.'</option>');
					} else {
						echo('<option value="'.$option.'">'.$option.'</option>');
					}
				}
				echo('</select>');
				break;
			case("Can_pickup_gear_from_floor"):
			case("Can_place_gear_on_lift"):
			case("Can_catch_fuel_from_hoppers"):
			case("Can_pickup_fuel_from_floor"):
			case("Can_shoot_in_low_goal"):
			case("Can_shoot_in_high_goal"):
			case("Can_climb_rope"):
			case("Brought_own_rope"):
				echo('<select name="'.$column["Field"].'">');
				if ($row[$column["Field"]] == 0) {
					echo('<option value="0" selected>No</option>
					<option value="1">Yes</option>');
				} else {
					echo('<option value="0">No</option>
					<option value="1" selected>Yes</option>');
				}
				echo('</select>');
				break;
			case("Autonomous_capabilities"):
			case("Other_info"):
				echo('<textarea rows="4" cols="50" name="'.$column["Field"].'">'.$row[$column["Field"]].'</textarea>');
				break;
			case("Stored_Images"):
				echo('<input type="file" name="image">');
				/*if(isset($row[$column["Field"]])) {
					echo('<img id="image" src="'.$row[$column["Field"]].'" alt="Image">');
				}*/
				break;
			default:
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'">');
		}
		/*echo('<p>'.$PrettyColumn.':</p><br>
			<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');*/
	} elseif ($column["Field"] == "Team" and isset($_GET["team"])) {#Set the team to a hidden value
		echo('<input type="hidden" name="Team" value="'.$row[$column["Field"]].'">');
	}
}
echo('<input type="submit" value="Submit"></form>');
?>
	<p>All values are in lbs and inches</p>
<script src="scripts/universal.js"></script>
</body>
</html>
