<?php session_start(); ?>
<html>
<head>
	<title>Edit Team</title>
</head>
<body>
<?php
function writeToLog($string, $log) {
	file_put_contents("/var/www/frcteam4999.jordanpowers.net/logs/".$log.".log", date("d-m-Y_h:i:s")."-- ".$string."\r\n", FILE_APPEND);
}
function clean($data) {
		  $data = trim($data);
		  $data = mysqli_real_escape_string($data);
		  $data = htmlspecialchars($data);
		  return $data;
		}

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
	$data = $DB->query('SELECT Team FROM robots WHERE Team="'.clean($_POST["Team"]).'";');
	if($data->num_rows == 0){
		$DB->query('INSERT INTO robots (Team) VALUES ('.clean($_POST["Team"]).');');
	}
	foreach($columns as $column) {
		if($column["Field"]!="Team") {
			$DB->query('UPDATE robots SET '.$column["Field"].'="'.$_POST[$column["Field"]].'" WHERE Team = "'.clean($_POST["Team"]).'";');
			writeToLog("Set ".$column["Field"]." to ".$_POST[$column["Field"]],"EditData");
		}
	}
	header( 'Location: https://frcteam4999.jordanpowers.net/info.php?team='.clean($_POST["Team"]));
}
#check if creating a new entry, or editing an existing entry
#creates an associative array of the existing entry
if(isset($_GET["team"])){
	$team = str_replace('_',' ',$_GET["team"]);
	$data = $DB->query('SELECT * FROM robots WHERE Team = "'.$team.'";');
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
	} else {
		unset($team);
	}
	echo('<h1>Team: '.$team.'</h1>');
}
#create the form
echo('<form id="edit" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post" autocomplete="off">');
foreach($columns as $column) {
	#remove underscores from column names
	$PrettyColumn = str_replace('_',' ',$column["Field"]);
	if(!($column["Field"] == "Team" and isset($team))){ #Check if creating a new team
		echo('<p>'.$PrettyColumn.':</p><br>');
		switch($column["Field"]) {
			#check all numbers
			case("Team"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9]*" required><br>');
				break;
			case("Width"):
			case("Depth"):
			case("Height"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9]*"><span>inches</span><br>');
				break;
			case("Weight"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9]*"><span>lbs</span><br>');
				break;
			case("Drive_System"): #Create select menu with options for each type of drive system. The array $options can have new drive systems added to it to create more options
				echo('<select name="'.$column["Field"].'">');
				$options=array("West Coast","Mechanum","Tank","Swerve");
				foreach($options as $option) {
					if ($option == $row[$column["Field"]]) { #if the value is already set, set the option to that value
						echo('<option value='.$option.' selected>'.$option.'</option>');
					} else {
						echo('<option value='.$option.'>'.$option.'</option>');
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
				echo('<textarea form="edit" rows="4" cols="50" name='.$column["Field"].'">'.$row[$column["Field"]].'</textarea><br>');
				break;
			default:
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');
		}
		/*echo('<p>'.$PrettyColumn.':</p><br>
			<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');*/
	} elseif ($column["Field"] == "Team" and isset($team)) {#Set the team to a hidden value
		echo('<input type="hidden" name="Team" value="'.$row[$column["Field"]].'">');
	}
}
echo('<input type="submit" value="Submit"></form>');
?>
	<p>All values are in lbs and inches</p>
</body>
</html>
