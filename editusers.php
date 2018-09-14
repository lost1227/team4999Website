<?php session_start(); ?>
<?php
require 'functions.php';
function checkIfDBContainsUser($user) {
  global $DB, $LoginTableName;
  $results = formatAndQuery("SELECT user FROM %s WHERE user = %sv;", $LoginTableName, $user );
  return ($results->num_rows > 0);
}
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

#PROCESS FORM DATA
if($_SERVER["REQUEST_METHOD"] == "POST" and checkIfValidUser()) {
  if(!checkCSRFToken($_POST["token"])) {
    die("Bad CSRF Token");
  }
  $DB = createDBObject();
  if(isset($_POST["formtype"])) {
    if($_POST["formtype"] == "adduser" and checkPostVarsSet($_POST, array("user", "pass","name")) and !checkIfDBContainsUser($_POST["user"])) {
      $admin = (isset($_POST["admin"]) and $_POST["admin"] == "true") ? 'TRUE' : 'FALSE';
      formatAndQuery("INSERT INTO %s VALUES ( %sv, %sv, %sv, %s );",$LoginTableName,$_POST["user"], password_hash($_POST["pass"], PASSWORD_DEFAULT), $_POST["name"], $admin);
    } elseif ($_POST["formtype"] == "deluser" and isset($_POST["user"]) and checkIfDBContainsUser($_POST["user"])) {
      formatAndQuery("DELETE FROM %s WHERE user = %sv;", $LoginTableName, $_POST["user"]);
    }
  }
}
?>
<html>
<head>
  <script src="<?php global $appdir; echo($appdir);?>scripts/jquery-3.1.1.min.js"></script>
  <link rel="stylesheet" href="<?php global $appdir; echo($appdir);?>styles/edituser.css">
  <script>
  $(document).ready(function() {
    $(".passbox").on("input", function checkBoxes(e) {
    	if ($('#pass1').val() != $('#pass2').val()) {
    		$('#submit').prop("disabled", true);
    		$('#errorWarning').show();
    	} else {
    		$('#submit').prop("disabled", false);
    		$('#errorWarning').hide();
    	}
    });
    $(".trashbutton").click(function deleteUser(e) {
      var user = $(e.target).data("user");
      if(window.confirm("Are you sure you want to delete user " + user + "?")) {
        $("#deluser").val(user);
        $("#deluserf").submit();
      }
    });
  });
  </script>
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
  </style>
  <script>
		function setUrl(url) {
			document.location.href = url;
		}
	</script>
</head>
<body>
  <div id="main">
    <img src="images/back.png" id="back" onclick="setUrl('<?php require 'specificvars.php'; echo($appdir.'index.php'); ?>')">
    <table>
      <tr>
        <th>User</th>
        <th>Name</th>
        <th>Delete</th>
      </tr>
      <?php
      if(checkIfValidUser()) {
        if(!isset($DB)) {
          $DB = createDBObject();
        }
        $results = formatAndQuery("SELECT user, name FROM %s;", $LoginTableName);
        if($results->num_rows > 0) {
          while($data = $results->fetch_assoc()) {
            echo('<tr>');
            echo('<td>'.clean($data["user"]).'</td>');
            echo('<td>'.clean($data["name"]).'</td>');
            echo('<td><img src="images/red-trash-512.jpg" width="15" class="trashbutton" data-user="'.clean($data["user"]).'"></td>');
            echo('<tr>');
          }
        }
      } else {
        echo('<tr><td colspan= "3" style="text-align: center;">You are not logged in and/or you are not an admin</td></tr>');
      }

      ?>
      <tr id="addusr">
        <form id="addUserf" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
          <td>
            <input id="adduser" name="user" type="text" placeholder="Username"><input id="pass1" name="pass" type="password" class="passbox" placeholder="Password"><input id="pass2" type="password" class="passbox" placeholder="Confirm Password">
            <p id="errorWarning" hidden>PASSWORDS DON'T MATCH</p>
          </td>
          <td><input id="name" name="name" type="text" placeholder = "Name"><label><input type="checkbox" name="admin" value="true">Admin</label></td>
          <td><input id="submit" type="image" src="images/plus-4-xxl.png" width="15"></td>
          <input type="hidden" name="formtype" value="adduser">
          <input type="hidden" name="token" value="<?php echo(getCSRFToken()); ?>">
        </form>
      </tr>
    </table>
  </div>
  <form style="display: none;" id="deluserf" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
    <input id="deluser" name="user" type="hidden" value="">
    <input type="hidden" name="formtype" value="deluser">
    <input type="hidden" name="token" value="<?php echo(getCSRFToken()); ?>">
  </form>
</body>
</html>
