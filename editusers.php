<?php session_start(); ?>
<?php require 'functions.php'; ?>
<?php
function checkIfValidUser() {
  return (isset($_SESSION["loggedIn"]) and $_SESSION["loggedIn"] and checkUserPassword($_SESSION["user"], $_SESSION["pass"]) and checkIsAdmin($_SESSION["user"], $_SESSION["pass"]));
}
function checkPostVarsSet($postData, $expectedKeys) {
  foreach($expectedKeys as $key) {
    if(!isset($postData[$key])) {
      return False;
    }
  }
  return True;
}
if($_SERVER["REQUEST_METHOD"] == "POST" and checkIfValidUser()) {
  $DB = createDBObject();
  if(isset($_POST["formtype"])) {
    if($_POST["formtype"] == "adduser" and checkPostVarsSet($_POST, array("user", "pass","name"))) {
      $admin = (isset($_POST["admin"] and $_POST["admin"] == "true")) ? 'TRUE' : 'FALSE';
      formatAndQuery("INSERT INTO %s VALUES ( %sv, %sv, %sv, %s );",$LoginTableName,$_POST["user"], password_hash($_POST["pass"]), $_POST["name"], $admin);
    } elseif ($_POST["formtype"] == "deluser" and isset($_POST["user"])) {
      formatAndQuery("DELETE FROM %s WHERE user = %sv;", $LoginTableName, $_POST["user"]);
    }
  }
}
?>
<html>
<head>
  <script src="/scripts/jquery-3.1.1.min.js"></script>
  <script>
  $(".passbox").click(function checkBoxes(e) {
  	if ($('#pass1').val() != $('#pass2').val()) {
  		$('#submit').prop("disabled", true);
  		$('#errorWarning').show();
  	} else {
  		$('#submit').prop("disabled", false);
  		$('#errorWarning').hide();
  	}
  });
  $(".trashbutton").click(function deleteUser(e) {
    $user = e.target.data("user");
    if(window.confirm("Are you sure you want to delete user " + $user + "?")) {
      $("#deluser").val($user);
      $("#deluserf").submit();
    }
  });
  </script>
</head>
<body>
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
          echo('<td>'.clean($data["user"]).'</td>');
          echo('<td>'.clean($data["name"]).'</td>');
          echo('<img src="/images/red=trash=512.jpg" width="15" class="trashbutton" data-user="'.clean($data["user"]).'">');
        }
      }
    }

    ?>
    <tr id="addusr">
      <form id="addUser" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
        <td>
          <input id="adduser" name="user" type="text"><input id="pass1" name="pass" type="password" class="passbox"><input id="pass2" type="password" class="passbox">
          <p id="errorWarning" hidden>PASSWORDS DON'T MATCH</p>
        </td>
        <td><input id="name" name="name" type="text"><label><input type="checkbox" name="admin" value="true">Admin</label></td>
        <td><input id="submit" type="image" src="/images/plus-512.ico" width="15"></td>
        <input type="hidden" name="formtype" value="adduser">
      </form>
    </tr>
  </table>
  <form style="display: none;" id="deluserf" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" autocomplete="off">
    <input id="deluser" name="user" type="hidden" value="">
    <input type="hidden" name="formtype" value="deluser">
  </form>
</body>
</html>
