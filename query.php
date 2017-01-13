<?php session_start(); ?>
<?php
if (isset($_SESSION["loggedIn"])){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	$DB = new mysqli("localhost","ro","","frcteam4999");
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$Query = 'SELECT * FROM robots WHERE ';
	$index = 1;
	foreach($_POST as $key => $value) {
		$Query = $Query . $key . '="' . $value . '"';
		if (++$index != count($_POST) ){
			$Query = $Query . ' AND ';
		} else {
			$Query = $Query . ';';
		}
	}
	
} else {
	$query = "SELECT * FROM robots ORDER BY Team ASC;";
}
$data = $DB->query($query);
#echo($data->num_rows);
if($data->num_rows > 0){
	while($row = $data->fetch_assoc()) {
		echo('<a class="teamlink" href = /info.php?team='.str_replace(' ','_',$row["Team"]).'>');
		echo('<div class="infoRow">');
		foreach($row as $key => $value) {
			$key = str_replace('_',' ',$key);
			echo('<p>'.$key.': '.$value.'</p>');
		}
		echo('</div></a>');
	}
} else {
	echo('<p>No results!</p>');
}
?>
