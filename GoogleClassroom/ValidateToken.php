<?php

require_once ( '../SQLConnection.php');
require_once '../vendor/autoload.php';




$jwt = new \Firebase\JWT\JWT;
$jwt::$leeway = 60;

$id_token = $_POST["idtoken"]; 
$sessID = $_POST["sessid"];
$CLIENT_ID = "448443480105-5it7jncqi2b3t2g7br1ful9q1no188rt.apps.googleusercontent.com";
$client = new Google_Client(['client_id' => $CLIENT_ID]); // Specify the CLIENT_ID of the app that accesses the backend
$client -> setScopes('email');
$payload = $client -> verifyIdToken($id_token);

if ($payload)
{
	$userid = $payload['sub'];

	global $servername;
	global $username;
	global $password;
	global $dbname;

	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection 

	if ($conn -> connect_error)
	{
		die("Connection failed: ".$conn -> connect_error);
	}

	if( $payload['email'] ===  'bbakkala@amsacs.org') 
	{
		$SessId = SQLQ('SELECT `SessionID` FROM `_UserSessions` WHERE  `SessionID` = "'.$sessID.'" ');
		// echo ('SELECT `SessionID` FROM `_UserSessions` WHERE  `email` = "'.$payload['email'].'" AND `SessionID` = "'.$payload['iat'].'"');
		if($SessId == "") 
		{
			SQLRun("INSERT INTO  `_UserSessions` (`SessionID`, `email`, `sub`, `jti`, `exp`, `access_token`) VALUES ('".  $sessID."', '".  $payload['email']."', '".  $payload['sub']."', '".  $payload['jti']."', '".  $payload['exp']."', '".  $id_token ."');");
			// echo "INSERT INTO  `_UserSessions` (`SessionID`, `email`, `sub`, `jti`, `exp`) VALUES ('".  $payload['iat']."', '".  $payload['email']."', '".  $payload['sub']."', '".  $payload['jti']."', '".  $payload['exp']."');";
			$SessId =  $payload['iat'];
		}
		else
		{
			SQLRun("UPDATE `_UserSessions` SET `email`='".  $payload['email']."', `sub`='".  $payload['sub']."', `jti`= '".  $payload['jti']."',`exp`='".  $payload['exp']."' ,`access_token`='".  $id_token."'  WHERE `email` =  '".$sessID. "' ");
		}

		echo $payload['email'];
	}
	else
	{
		"Not you.";
	}
	// Signed in as: array(16) {
	//   ["iss"]=>
	//   string(19) "accounts.google.com"
	//   ["azp"]=>
	//   string(72) "790205495686-a1k3sk0u1upouohijv83vtumdfd55h2a.apps.googleusercontent.com"
	//   ["aud"]=>
	//   string(72) "790205495686-a1k3sk0u1upouohijv83vtumdfd55h2a.apps.googleusercontent.com"
	//   ["sub"]=>
	//   string(21) "113862177406856056677"
	//   ["hd"]=>
	//   string(10) "amsacs.org"
	//   ["email"]=>
	//   string(20) "b.bakkala@amsacs.org"
	//   ["email_verified"]=>
	//   bool(true)
	//   ["at_hash"]=>
	//   string(22) "iparlBxK3YYnqjuzmSEcuw"
	//   ["name"]=>
	//   string(13) "Brian Bakkala"
	//   ["picture"]=>
	//   string(89) "https://lh3.googleusercontent.com/a-/AAuE7mDnszKJQk1LNIulZ8B37kMuvl8VMXTJAtgmpAgabQ=s96-c"
	//   ["given_name"]=>
	//   string(5) "Brian"
	//   ["family_name"]=>
	//   string(7) "Bakkala"
	//   ["locale"]=>
	//   string(2) "en"
	//   ["iat"]=>
	//   int(1571961341)
	//   ["exp"]=>
	//   int(1571964941)
	//   ["jti"]=>
	//   string(40) "7f0d22f121dfe3604480ef792a54bcd9c01e1250"


}
else
{
	echo "nope";
}



















?>