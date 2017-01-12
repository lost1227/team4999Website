<?php session_start(); ?>
<?php
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	$DB = new mysqli("localhost","ro","","frcteam4999");
}
$data = $DB->query("SELECT * FROM robots ORDER BY Team ASC;");
#echo($data->num_rows);
if($data->num_rows > 0){
	while($row = $data->fetch_assoc()) {
		echo('<div class="infoRow">');
		foreach($row as $key => $value) {
			$key = str_replace('_',' ',$key);
			if($key == "Team"){
				echo('<a class="teamlink" href = /info.php?team='.str_replace(' ','_',$value).'>');
			}
			echo('<p>'.$key.': '.$value.'</p>');
			if($key == $row[$row.count() - 1]){
				echo('</a>');
			}
		}
		echo('</div>');
	}
} else {
	echo('<p>No results!</p>');
}
?>