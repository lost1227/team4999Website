<?php session_start(); ?>
<?php
require 'functions.php';
function checkPostVarsSet($postData, $expectedKeys) {
  foreach($expectedKeys as $key) {
    if(!isset($postData[$key])) {
      return False;
    }
    if(empty($postData[$key])) {
      return False;
    }
  }
  return True;
}
#check if logged in and redirect if not
if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"]) {
	header( 'Location: '.getRootDir().'login.php?redirect=editusers.php');
	exit();
}

if(!checkIsAdmin($_SESSION["userid"])) {
  header('Location: index.php');
  exit();
}

$DB = createDBObject();

#PROCESS FORM DATA
if($_SERVER["REQUEST_METHOD"] == "POST") {
  if(!checkCSRFToken($_POST["token"])) {
    die("Bad CSRF Token");
  }
  if(checkPostVarsSet($_POST,array("action","uid"))){
    $stmt = $DB->prepare("UPDATE ".$LoginTableName." SET `admin` = ".($_POST["action"] == "promote" ? "1" : "0")." WHERE `userid` = ?");
    if($stmt === False) {
      die("Error preparing statement: ".$DB->error);
    }
    $stmt->bind_param("s",$_POST["uid"]);
    $stmt->execute();
    $stmt->close();
  }
}

if(!checkIsAdmin($_SESSION["userid"])) {
  header('Location: index.php');
  exit();
}
?>
<html>
<head>
  <link rel="stylesheet" href="styles/edituser.css">
  <style>
  @font-face{
  	font-family: "StormFaze";
  	src: url("../fonts/stormfaze.ttf");
  }
  body {
      margin: 0;
      background-image: url("images/grey.png");
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
  table {
    margin: auto;
  }
  #back {
  	width: 50px;
  	position: relative;
  	left: -55px;
  }
  #back:hover {
  	filter: opacity(.8);
  	cursor: pointer;
  }
  #back:active {
  	box-shadow: inset 0 0 10px 2px grey;
  }
  form {
    margin: 0;
  }
  </style>
  <script>
		function setUrl(url) {
			document.location.href = url;
		}
	</script>
</head>
<body>
  <div id="main">
    <img src="images/back.png" id="back" onclick="setUrl('index.php')">
    <table>
      <tr>
        <th>Name</th>
        <th>Admin</th>
        <th>Actions</th>
      </tr>
      <?php
      $stmt = $DB->prepare("SELECT `name`, `userid`, `admin` FROM ".$LoginTableName);
      if($stmt === false) {
        die("Error preparing statement: ".$DB->error);
      }
      $stmt->execute();
      $stmt->bind_result($name, $uid, $admin);
      while($stmt->fetch()) {
        echo('<tr>
                <td>'.clean($name).'</td>
                <td>'.($admin?"Admin":"User").'</td>
                <td>
                  <form action='.htmlspecialchars($_SERVER["PHP_SELF"]).' method="POST">
                    <input type="hidden" name="uid" value="'.$uid.'">
                    <input type="hidden" name="action" value="'.($admin?"demote":"promote").'">
                    <input type="hidden" name="token" value="'.clean(getCSRFToken()).'">
                    <input type="submit" value="'.($admin?"Demote":"Promote").'">
                  </form>
                </td>
              </tr>
            ');
      }
      $stmt->close();
      ?>
    </table>
  </div>
</body>
</html>
