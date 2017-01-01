<?php session_start(); ?>
<?php
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
	$data = $DB->query("SELECT * FROM robots ORDER BY Team DESC;");
	#echo($data->num_rows);
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			echo('<div class="infoRow">');
			foreach($row as $key => $value) {
				echo('<p>'.$key.': '.$value.'</p>');
			}
			echo('</div>');
		}
	} else {
		echo('<p>No results!</p>');
	}
}
?>