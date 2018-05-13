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

function getTypeOptions($selected, $js = false) {
  $out = "";
  $options = array("string"=>"Text Field","select"=>"Drop down","boolean"=>"Yes or No","number"=>"Number","textarea"=>"Text Area");
  foreach($options as $option=>$name) {
    if($selected == $option) {
      $out .= '<option value="'.$option.'" selected="selected">'.$name."</option>\n";
    } else {
      $out .= '<option value="'.$option.'">'.$name."</option>\n";
    }
  }
  if($js) { $out = str_replace("\n", "','", $out); }
  return $out;
}
function getSelectOptions($key,$data,$ctx) {
  $out = "";
  if($data["type"] == "select" && isset($data["values"]) ) {
    $out .= "<table class=\"selectoptions\" ".'data-name="'.$ctx.'['.$key.'][values]"'.">\n";
    foreach($data["values"] as $value) {
      $out .= '<tr><td><input class="f_select" type="text" name="'.$ctx.'['.$key.'][values][]" value="'.$value."\"></td></tr>\n";
    }
    $out .= '<tr><td><button class="addSelectOption">Add</button></td></tr>'."\n";
    $out .= "</table>";
  } else {
    $out .= "<table class=\"hiddenselectoptions\" ".'data-name="'.$ctx.'['.$key.'][values]"'.">\n";
    $out .= '<tr><td><input class="f_select" type="text" name="'.$ctx.'['.$key.'][values][]" ></td></tr>'."\n";
    $out .= '<tr><td><button class="addSelectOption">Add</button></td></tr>'."\n";
    $out .= "</table>";
  }
  return $out;
}

if(!file_exists("schema.json")) {
  $json = array();
} else {
  $json = json_decode(file_get_contents("schema.json"), True);
}

# PROCESS FORM DATA
if($_SERVER["REQUEST_METHOD"] == "POST" && checkIfValidUser()) {

  if(!checkCSRFToken($_POST["token"])) {
    die("Bad CSRF Token");
  }

  $updated = array("year"=>$_POST["year"],"robotdata"=>array(), "matchdata"=>array());
  $year = $_POST["year"];
  $data = array();
  if(isset($_POST["robotdata"])){
    $data["robotdata"] = $_POST["robotdata"];
  }
  if(isset($_POST["matchdata"])){
    $data["matchdata"] = $_POST["matchdata"];
  }
  /*
    Example form data:

    robotdata[key1][key]:key1
    robotdata[key1][display_name]:Key 1
    robotdata[key1][type]:string
    robotdata[key2][key]:key2
    robotdata[key2][display_name]:Key 2
    robotdata[key2][type]:select
    robotdata[key2][values][]:Option 1
    robotdata[key2][values][]:Option 2
    robotdata[key2][values][]:Option 3
    matchdata[Key3][key]:Key3
    matchdata[Key3][display_name]:UniqueKey 2
    matchdata[Key3][type]:select
    matchdata[Key3][values][]:Option 1
    matchdata[Key3][values][]:Option 2
    matchdata[Key3][values][]:Option 3
    matchdata[Key3][values][]:Option 4
    matchdata[key4][key]:key4
    matchdata[key4][display_name]:Unique Key 3
    matchdata[key4][type]:boolean
    matchdata[key5][key]:key5
    matchdata[key5][display_name]:Unique Key 3
    matchdata[key5][type]:textarea

  */
  $olddataindex = getYearData($json,$year);
  if($olddataindex === false) {
    end($json);
    $olddataindex = key($json)+1;
    reset($json);
    $olddata = array("year"=>$year, "robotdata"=>array(), "matchdata"=>array());
  } else {
    $olddataindex = $olddataindex[0];
    $olddata = $json[$olddataindex];
  }


  foreach($data as $dkey=>$dval) {
    $rn = array();
    foreach($dval as $key=>$value) {
      $nkey = clean($value["key"]); // store the new key
      if($key != $nkey) {
        if(isset($olddata[$dkey][$key])) {
          $rn[] = $key;
          // echo($dkey.'['.$key .'] has been renamed to '. $nkey);

          // UPDATE (robotdata or matchdata) SET key = $nkey WHERE key = $key
          // loop through the robotdata or matchdata table and update the key to the new key
          if($dkey == "robotdata") {
            renameKeyInTable($RobotDataTable, $key, $nkey, $year);
          } elseif ($dkey = "matchdata") {
            renameKeyInTable($EventDataTable, $key, $nkey, $year);
          }

        } else {
          // echo($dkey.'['.$nkey .'] has been added');
          // Nothing needs to be updated
        }
      }
      unset($value["key"]); // remove the key field from the array
      // (hopefully) safeguard against xss
      $value["display_name"] = clean($value["display_name"]);
      if(isset($value["values"])) {
        foreach($value["values"] as $i=>$d) {
          $value["values"][$i] = clean($d);
        }
      }

      $updated[$dkey][$nkey] = $value; // store the array of data under the key saved above
    }
    foreach($olddata[$dkey] as $jdkey=>$jdval) { // loop through the old data
      if(!isset($updated[$dkey][$jdkey]) && !in_array($jdkey, $rn)) {
        // echo($jdkey . ' has been deleted');
        if($dkey == "robotdata") {
          deleteKeyInTable($RobotDataTable, $jdkey, $year);
        } elseif ($dkey = "matchdata") {
          deleteKeyInTable($EventDataTable, $jdkey, $year);
        }
      } // if the old data contains a key the new data does not, it has been deleted
    }
  }

  $json[$olddataindex] = $updated;

  file_put_contents("schema.json", json_encode($json, JSON_PRETTY_PRINT) );
}

?>
<html>
<head>
  <title>Edit Information Collection</title>
  <script src="<?php echo($appdir);?>scripts/jquery-3.1.1.min.js"></script>
  <script src="<?php echo($appdir);?>scripts/editschema.js"></script>
  <script>
  var crindx = 0;
  var cmindx = 0;
  function getRobotDataRow() {
    crindx++;
    return ['<tr>',
              '<td><input type="text" name="robotdata[' + crindx + '][key]" class="f_key"></td>',
              '<td><input type="text" name="robotdata[' + crindx + '][display_name]" class="f_name"></td>',
              '<td><select name="robotdata[' + crindx + '][type]" class="datatselector" >',
                '<?php echo(getTypeOptions("",true)); ?></select></td>',
              '<td class="datar" >',
                '<table class="hiddenselectoptions" data-name="robotdata[' + crindx + '][values]">',
                  '<tr><td><input class="f_select" type="text" name="robotdata[' + crindx + '][values][]" ></td></tr>',
                  '<tr><td><button class="addSelectOption">Add</button></td></tr>',
                '</table>',
              '</td>',
            '</tr>'
          ].join("\n");
  }
  function getMatchDataRow() {
    cmindx++;
    return ['<tr>',
              '<td><input type="text" name="matchdata[' + cmindx + '][key]" class="f_key"></td>',
              '<td><input type="text" name="matchdata[' + cmindx + '][display_name]" class="f_name"></td>',
              '<td><select name="matchdata[' + cmindx + '][type]" class="datatselector" >',
                '<?php echo(getTypeOptions("", true)); ?></select></td>',
              '<td class="datar" >',
                '<table class="hiddenselectoptions" data-name="matchdata[' + cmindx + '][values]">',
                  '<tr><td><input class="f_select" type="text" name="matchdata[' + cmindx + '][values][]" ></td></tr>',
                  '<tr><td><button class="addSelectOption">Add</button></td></tr>',
                '</table>',
              '</td>',
            '</tr>'
          ].join("\n");
  }
  </script>
  <link rel="stylesheet" href="<?php global $appdir; echo($appdir);?>styles/editschema.css">
</head>
<body>
  <?php
  if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["year"])) {
    $year = $_GET["year"];
  } else if(!($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["year"]))) {
    echo('<table><tr><th>Select year:</th></tr>');
    if(count($json) > 0 ) {
      foreach($json as $year) {
        echo('<tr><td class="yearchoice" data-year='.$year["year"].'>'.$year["year"].'</td></tr>');
      }
    }
    echo('<tr><td>
      <input id="addyear" type="number" name="year">
      <button id="addyearb">Add</button>
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
        var year = $(e.target).data("year");
        $("#selectyear").val(year);
        $("#selectyearf").submit();
      });
      $("#addyearb").click(function(e) {
        $("#selectyear").val($("#addyear").val());
        $("#selectyearf").submit();
      });
    });
    </script>');
    exit();
  }
   ?>
   <p id="YearTitle"><?php echo($year); ?></p>
   <form action="<?php echo(htmlentities($_SERVER['PHP_SELF'])); ?>" method="post" id="mainf">
     <input type="hidden" name="year" value="<?php echo($year); ?>">
     <fieldset>
       <legend>Robot data</legend>
       <table>
         <tr>
           <th>Key</th>
           <th>Display Name</th>
           <th>Type</th>
           <th>Data</th>
         </tr>
         <?php
         $yeard = $year;
         $year = getYearData($json, $year);
         if($year === false) {
           $year = array("year"=>$yeard, "robotdata"=>array(), "matchdata"=>array());
         } else {
           $year = $year[1];
         }
         foreach($year["robotdata"] as $key => $data){
           echo('
            <tr>
              <td><input type="text" name="robotdata['.$key.'][key]" value="'.$key.'" class="f_key"></td>
              <td><input type="text" name="robotdata['.$key.'][display_name]" value="'.$data["display_name"].'" class="f_name"></td>
              <td><select name="robotdata['.$key.'][type]" class="datatselector">
                '.getTypeOptions($data["type"]).'</select></td>
              <td class="datar">'.getSelectOptions($key, $data, "robotdata").'</td>
            </tr>
           ');
         }
        ?>
        <tr><td colspan="4"><button id="addrobotrow">Add row</button></tr>
      </table>
    </fieldset>
    <fieldset>
      <legend>Match data</legend>
      <table>
        <tr>
          <th>Key</th>
          <th>Display Name</th>
          <th>Type</th>
          <th>Data</th>
        </tr>
        <?php
        foreach($year["matchdata"] as $key => $data){
          echo('
           <tr>
             <td><input type="text" name="matchdata['.$key.'][key]" value="'.$key.'" class="f_key"></td>
             <td><input type="text" name="matchdata['.$key.'][display_name]" value="'.$data["display_name"].'" class="f_name" ></td>
             <td><select name="matchdata['.$key.'][type]" class="datatselector">
               '.getTypeOptions($data["type"]).'</select></td>
             <td class="datar" >'.getSelectOptions($key, $data, "matchdata").'</td>
           </tr>
          ');
        }
       ?>
       <tr><td colspan="4"><button id="addmatchrow">Add row</button></tr>
     </table>
   </fieldset>
  <input type="hidden" name="token" value="<?php echo(getCSRFToken()); ?>">
  <input type="submit">
</form>


</body>
</html>
