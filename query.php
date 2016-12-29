<?php session_start(); ?>
<?php
if ($_SESSION["loggedIn"]){
	$data = $_SESSION["DB"]->query("SELECT * FROM robots;");
	#echo($data);
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			echo('<div class="infoRow">');
				echo('<p>Team: ' . $row["TeamNum"] . '</p>');
				echo('<p>Drive System: ' . $row["DrvSys"] . '</p>');
		}
	} else {
		echo('<p>No results!</p>');
	}
}
?>