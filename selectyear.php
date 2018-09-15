<html>
<head>
<link rel="stylesheet" href="styles/selectyear.css">
<?php
require 'functions.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["year"])) {
        $_SESSION["year"] = $_POST["year"];
        header('Location: index.php');
        exit();
    }
}
?>
</head>
<body>
<div id="maindiv">
<h1>Select year</h1>
<form action="<?php echo(clean($_SERVER["PHP_SELF"]));?>" method="post">
<?php
if(file_exists("schema.json")) {
    $json = json_decode(file_get_contents("schema.json"), True);
    echo('<select id="yearselectfield" name="year">');
    $years = array();
    foreach($json as $year) {
        $years[] = $year["year"];
    }
    rsort($years);
    foreach($years as $year) {
        if(isset($_SESSION["year"]) && $_SESSION["year"] === $year){
            echo('<option value='.$year.' selected="selected">'.$year.'</option>');
        } else {
            echo('<option value='.$year.'>'.$year.'</option>');
        }
    }
    echo('</select>');
    echo('<input id="submitbtn" type="submit">');
} else {
    echo("<h2>Schema.json does not exist!</h2>");
}
?>
</form>
</div>
</body>
</html>