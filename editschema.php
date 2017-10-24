<?php session_start() ?>
<?php
require 'functions.php';
global $appdir;
?>
<?php

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
  "robotdata":{
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

function getTypeOptions($selected) {
  $out = "";
  $options = array("string"=>"Text Field","select"=>"Drop down","boolean"=>"Yes or No","number"=>"Number","textarea"=>"Text Area");
  foreach($options as $option=>$name) {
    if($selected == $option) {
      $out .= '<option value="'.$option.'" selected="selected">'.$name."</option>\n";
    } else {
      $out .= '<option value="'.$option.'">'.$name."</option>\n";
    }
  }
  return $out;
}

function getYearData($haystackJson, $needleYear) {
  foreach($haystackJson as $yeard) {
    if($yeard["year"] == $needleYear) {
      return $yeard;
    }
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
  <script src="<?php echo($appdir);?>scripts/jquery-3.1.1.min.js"></script>
</head>
<body>
  <?php
  if(isset($_GET["year"])) {
    $year = $_GET["year"];
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
   <p id="YearTitle"><?php echo($year); ?></p>
   <p>Robot data</p>
   <form action="<?php echo(htmlentities($_SERVER['PHP_SELF'])); ?>" method="post">
   <table>
     <tr>
       <th>Key</th>
       <th>Display Name</th>
       <th>Type</th>
       <th>Data</th>
     </tr>
     <?php
     $year = getYearData($json, $year);
     foreach($year["robotdata"] as $key => $data){
       echo('
        <tr>
          <td><input type="text" name="'.$key.'[key]" value="'.$key.'"></td>
          <td><input type="text" name="'.$key.'[display_name]" value="'.$data["display_name"].'"></td>
          <td><select name="'.$key.'[type]" class="datatselector" data-key="'.$key.'">
            '.getTypeOptions($data["type"]).'</select></td>
          <td class="datar" data-key="'.$key.'"></td>
        <tr>
       ');
     }
    ?>
  </table>
  <input type="submit">
</form>


</body>
</html>
