<?php session_start(); ?>
<?php
session_unset();
session_destroy();
header( 'Location: https://frcteam4999.jordanpowers.net/index.php')
?>
<html>
<head>
<title>Logged out</title>
</head>
<body>
<p>You have been logged out. Please click <a href="/index.php">here</a> if you are not automatically redirected</p>
</body>
</html>