<?php session_start(); ?>
<?php
#Check if logged in and use read-only account if not
if (isset($_SESSION["loggedIn"])){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	$DB = new mysqli("localhost","ro","","frcteam4999");
}
#Check if accessed by post and apply the filters if so
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$query = 'SELECT Team FROM robots WHERE ';
	$index = 1;
	#loop through all the filters and apply each one, adding an 'AND' between each
	foreach($_POST as $key => $value) {
		$query = $query . $key . ' LIKE "' . $value . '"';
		if ($index != count($_POST) ){
			$query = $query . ' AND ';
		} else {
			$query = $query . ' ORDER BY Team ASC;';
		}
		$index++;
	}
	/*echo($_POST["Team"]);
	echo($query);
	echo(count($_POST));*/
} else {
	#If not accessed by POST, show all rows
	$query = "SELECT Team FROM robots ORDER BY Team ASC;";
}
#execute the query
$data = $DB->query($query);
#echo($data->num_rows);
#check if there were any results
if($data->num_rows > 0){
	while($row = $data->fetch_assoc()) { #loop through each row of the table
		echo('<a class="teamlink" href = /info.php?team='.str_replace(' ','_',$row["Team"]).'>');
		echo('<div class="infoRow">
			<p>Team: '.$row["Team"].'</p>
			</div></a>');
	}
} else {
	echo('<p>No results!</p>');
}
?>
