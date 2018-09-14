<?php
session_start();

require 'functions.php';

/**
* Preform a GET request on a specified url with the specified parameters
* @param string $url The url to query
* @param array $opts The url paramteters
* @return string The server's response
*/
function get_query_slack($url,$opts) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url.'?'.http_build_query($opts));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}
/**
* Exchanges a verification code for an access token
* @see https://api.slack.com/docs/oauth
* @param string $code A temporary authorization code
* @return array The access token and scopes
*/
function getTokenFromVerificationCode($code) {
	global $slack_clientid, $slack_clientsecret;
	$data = array(
	  "client_id"=>$slack_clientid,
	  "client_secret"=>$slack_clientsecret,
	  "code"=>$code
	);
	return json_decode(get_query_slack("https://slack.com/api/oauth.access",$data),true);
}

function redirectToLogin() {
	global $slack_clientid;
	$_SESSION["oauth_state"] = bin2hex(openssl_random_pseudo_bytes(8));
	writeToLog("Authenticating user with state ".$_SESSION["oauth_state"], "oauth");
	header('Location: https://slack.com/oauth/authorize?scope=identity.basic&client_id='.$slack_clientid.'&state='.$_SESSION["oauth_state"]);
	exit();
}

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] && isset($_SESSION["name"]) && isset($_SESSION["userid"])) {
	if(isset($_GET["redirect"])) {
		header('Location: '.$_GET["redirect"]);
	} else{
		header('Location: index.php');
	}
	exit();
}

if(isset($_GET["code"]) && isset($_GET["state"]) && $_GET["state"] === $_SESSION["oauth_state"]) {
	unset($_SESSION["oauth_state"]);
	$tokendata = getTokenFromVerificationCode($_GET["code"]);
	if(!(isset($tokendata["ok"]) && $tokendata["ok"])) {
		if(isset($tokendata["error"])) {
			writeToLog("Token exchange failed with error: ".$tokendata["error"], "oauth");
			redirectToLogin();
		} else {
			writeToLog("Token exchange failed", "oauth");
			redirectToLogin();
		}
	}
	$userdata = json_decode(get_query_slack("https://slack.com/api/users.identity",array("token"=>$tokendata["access_token"])), true);
	if(!(isset($userdata["ok"]) && $userdata["ok"])) {
		if(isset($userdata["error"])) {
			writeToLog("Error retrieving user data: ".$userdata["error"], "oauth");
			redirectToLogin();
		} else {
			writeToLog("Error retrieving user data", "oauth");
			redirectToLogin();
		}
	}
	if(in_array($userdata["team"]["id"], $slack_teamids)) {
		$_SESSION["loggedin"] = true;
		$_SESSION["name"] = $userdata["user"]["name"];
		$_SESSION["userid"] = $userdata["user"]["id"];
		setCSRFToken();
		checkUserInTable($userdata["user"]["name"], $userdata["user"]["id"]);
		writeToLog("Successfully logged in as ".$_SESSION["name"], "oauth");
		if(isset($_SESSION["login_redirect"])) {
			$redirect = $_SESSION["login_redirect"];
			unset($_SESSION["login_redirect"]);
			header('Location: '.$redirect);
		} else {
			header('Location: index.php');
		}
		exit();
	} else {
		writeToLog("User from workspace ".$userdata["team"]["id"]." attempted to log in", "oauth");
		redirectToLogin();
	}
} else {
	if(isset($_GET["redirect"])) {
		$_SESSION["login_redirect"] = $_GET["redirect"];
	}
	redirectToLogin();
}
?>
