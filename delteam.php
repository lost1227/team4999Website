<?php
session_start();
require 'functions.php';
?>
<html>
<head>
  <?php
  if(!(isset($_POST["team"]) && isset($_POST["token"]) && checkCSRFToken($_POST["token"])) ) {
    header('Location: index.php');
    exit();
  }
  #check if logged in and redirect if not
  if (isset($_SESSION["loggedin"])){
  	$DB = createDBObject();
  } else {
  	header( 'Location: login.php?redirect=delteam.php?team='.$_GET["team"]);
  	exit();
  }
  if(isset($_POST["confirm"]) && $_POST["confirm"]) {
    $data = getTeamIds($_POST["team"]);
  	if($data === false) {
      header('Location: index.php');
      exit();
  	} else {
  		$robotids = $data["robotids"];
  		$eventids = $data["eventids"];
  	}

    foreach($robotids as $robotid) {
      deleteItem($RobotDataTable, $robotid);
      if (file_exists($image_root . $robotid)) {
        rrmdir($image_root . $robotid);
      }
    }

    foreach($eventids as $eventid) {
  		if(!isset($eventdata[$eventid])) {
  			deleteItem($EventDataTable, $eventid);
  			if (file_exists($image_root . $eventid)) {
  				rrmdir($image_root . $eventid);
  			}
  		}
  	}

    formatAndQuery("DELETE FROM `%s` WHERE `number` = %s;", $TeamDataTable, $_POST["team"]);

    header('Location: index.php');
    exit();
  }

  ?>

  <style>
  @font-face{
  	font-family: "StormFaze";
  	src: url("../fonts/stormfaze.ttf");
  }
  body {
      margin: 0;
      background-image: url("../images/grey.png");
      background-attachment: fixed;
  }
  * {
      font-family: arial;
  }
  #main {
  	width: 60%;
  	margin: auto;
  	background-color: rgba(255, 255, 255, 0.67);
  	padding: 15px 70px;
  	min-height: 100%;
  	box-shadow: 0px 0px 10px 1px #06ceff;
  }
  h1, h1>* {
  	font-family: StormFaze, arial, sans-serif;
    text-align: center;
  }
  ul {
    text-align: left;
    margin: 0px;
  }
  #delhead {
    margin: 0px;
  }
  #confirmdiv {
    margin: 5px 0px;
    text-align: center;
    display: block;
  }
  </style>
</head>
<body>
  <div id="main">
    <form id="mainf" action="<?php echo(htmlentities($_SERVER['PHP_SELF'])); ?>" method="post">
      <h1>Delete <span style="color: red;">all data</span> for team <?php echo(clean($_POST["team"])); ?>?</h1>
      <h3>This includes data from previous years!</h3>
      <?php
      $data = getTeamIds($_POST["team"]);
    	if($data !== false) {
    		$robotids = $data["robotids"];
    		$eventids = $data["eventids"];

        $yearData = array();
        foreach($robotids as $robotid) {
          $data = retrieveKeys($RobotDataTable, $robotid, array("year"=>array()));
          if(isset($yearData[$data["year"]["data_value"]]["robots"])) {
            $yearData[$data["year"]["data_value"]]["robots"]++;
          } else {
            $yearData[$data["year"]["data_value"]]["robots"] = 1;
          }
        }
        foreach($eventids as $eventid) {
          $data = retrieveKeys($EventDataTable, $eventid, array("year"=>array()));
          if(isset($yearData[$data["year"]["data_value"]]["matches"])) {
            $yearData[$data["year"]["data_value"]]["matches"]++;
          } else {
            $yearData[$data["year"]["data_value"]]["matches"] = 1;
          }
        }
        echo('<p id="delhead">Data to be deleted:</p>');
        echo("<ul>");
        foreach($yearData as $year=>$data) {
          echo("<li>".$year.": ".count($data["robots"])." robots, ".count($data["matches"])." matches</li>");
        }
        echo("</ul>");


    	}
       ?>
       <div id="confirmdiv">
         <button id="confirm">Delete</button>
       </div>
      <input type="hidden" name="team" value="<?php echo(clean($_POST["team"])); ?>">
      <input type="hidden" name="token" value="<?php echo(getCSRFToken()); ?>">
      <input type="hidden" name="confirm" value="true">
    </form>
  </div>
  <script src="scripts/jquery-3.1.1.min.js"></script>
  <script>
  $(document).ready(function(){
    $("#confirm").click(function(e) {
      if(window.confirm("Are you sure?")) {
        $("#mainf").submit();
      }
      return false;
    });
  });
  </script>
</body>
</html>
