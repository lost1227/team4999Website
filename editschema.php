<?php session_start() ?>
<?php

require 'functions.php';
function checkIfValidUser() {
  return (isset($_SESSION["loggedIn"]) and $_SESSION["loggedIn"] and checkIsAdmin($_SESSION["user"], $_SESSION["pass"]));
}
if(!checkIfValidUser()) {
  header( 'Location: login.php?redirect=editschema.php');
}

/*
JSON File Structure:
[{
  "year":"2017",
  "rototdata":{
    "key1":{"type":"string","display_name":"Key 1"}
  },
  "matchdata":{
    "key2":{"type":"number","display_name":"Key 2"}
  }
}]
*/

function getPlaceholder($type, $displayName, $data = array()) {
  if(!in_array($type, array("string","select","boolean","number","textarea"))) {
    throw new InvalidArgumentException($type." is not a valid type");
  }
  if($type == "select") {
    return array("type"=>"select", "values"=>$data, "display_name"=>$displayName);
  } else {
    return array("type"=>$type, "display_name"=>$displayName);
  }
}

if(!file_exists("schema.json")) {
  $json = array();
} else {
  $json = json_decode(file_get_contents("schema.json"), True);
}

?>
<html>
<head>
  <title>Edit Information Collection</title>
  <script src="<?php global $appdir; echo($appdir);?>scripts/jquery-3.1.1.min.js"></script>
</head>
<body>
  <?php
  if(isset($_GET["year"])) {
    $year = $_GET["year"];
    echo("You have selected: ". $year);
  } else {
    echo('<table><tr><th>Select year:</th></tr>');
    if(count($json) > 0 ) {
      foreach($json as $year) {
        echo('<tr><td class="yearchoice" data-year='.$year["year"].'>'.$year["year"].'</td></tr>');
      }
    }
    echo('<tr><td>
      <form action='.htmlentities($_SERVER['PHP_SELF']).' method="get" autocomplete="off">
        <input id="addyear" type="number" name="year">
        <input type="submit" value="Add">
      </form>
      </td></tr>');
    echo('</table>');
    echo('
    <form id="selectyearf" style="display: none;" action='.htmlentities($_SERVER['PHP_SELF']).' method="get" autocomplete="off">
      <input id="selectyear" name="year" value="">
    </form>
    ');
    echo('<script>
    $(document).ready(function() {
      $(".yearchoice").click(function(e) {
        $user = $(e.target).data("year");
        $("#selectyear").val($user);
        $("#selectyearf").submit();
      });
    });
    </script>');
    exit();
  }
   ?>

</body>
</html>
