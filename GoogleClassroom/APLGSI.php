<?php


session_start();

require_once ('SQLConnection.php');  



$Verified= [];
$Verified['SessID'] =    (base64_encode(session_id()))  ;

$Verified['Email'] = SQLQ('SELECT  `email` FROM `_UserSessions` WHERE `SessionID` = "'.   (base64_encode(session_id())) .'"');
$Verified['Token'] = SQLQ('SELECT  `access_token` FROM `_UserSessions` WHERE `SessionID` = "'.   (base64_encode(session_id())) .'"');

$tempEmail = $Verified['Email'];

if(strpos($Verified['Email'], ".") ==1)  
{
	$tempEmail = substr_replace($tempEmail, '', 1, 1);
}
else  
{
	$tempEmail = substr_replace($tempEmail, '.', 1, 0 );
}

$SessExp = SQLQ('SELECT `exp`  FROM `_UserSessions` WHERE `SessionID` = "' . base64_encode(session_id()) . '"  ');
$SessExp = "" ? 0 :  ((int) $SessExp);
$TimeDiff = abs(date("U",time()) - ((int) $SessExp ));


$TimeDiff = ((int)($TimeDiff / 60)) . ":" . str_pad(($TimeDiff %60), 2, "000000", STR_PAD_LEFT ) ;
$Verified['Timeout'] = $TimeDiff;



function CheckSessionTimeout($SessID, $email)
{
	$SessExp = SQLQ('SELECT `exp`  FROM `_UserSessions` WHERE `SessionID` = "' . $SessID . '" AND `email` = "' . $email . '" ');
	date_default_timezone_set("America/New_York"); 
	
	$TimeDiff = (date("U",time()) - (int) $SessExp);
	
	if($SessExp !== "")
	{
		if($TimeDiff < 0)
		{
			$TempBool = true;
		}
		else
		{
			$TempBool = false;
		}
	}
	else
	{
		$TempBool = false;
	}

	$returnarray = [];
	$returnarray['TimeoutBoolean'] = $TempBool;
	$returnarray['TimeLeft'] = (abs((int)($TimeDiff/60)) < 10 ? '0'.abs((int)($TimeDiff/60)):abs((int)($TimeDiff/60))).":".(abs($TimeDiff%60) < 10 ? "0" . abs($TimeDiff%60): abs($TimeDiff%60)) ;

	return $returnarray; 

}

if($_SERVER['SCRIPT_NAME'] != "/Nexus/Mailer.php")
{
	$SessionCheck = CheckSessionTimeout((base64_encode(session_id())), $Verified['Email']);

	if($SessionCheck['TimeoutBoolean'])
	{
		echo " 
		<script>

			function GoogleCheck()
			{
				console.log('Google Sign-in verified user: " .$Verified['Email']. "')
				console.log('Google Sign-in token expiration: " . $SessionCheck['TimeLeft'] . "')
				
				document.getElementsByTagName('html')[0].setAttribute('user', '" .$Verified['Email']. "' );

				if(typeof(OptionalGSIHandler) === 'function')
				{
					OptionalGSIHandler('" .$Verified['Email']. "')
				}


			}

			function EndSession(RedirectAddress)
			{
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function()
				{ 
					if (this.readyState == 4 && this.status == 200)
					{
						Response = this.responseText.replace(/(\\r\\n\\t|\\n|\\r\\t)/gm, ' ').replace(/^\s+|\s+$/gm, '')
						if(Response == 'Removed')
						{
							if(typeof('signOut') == 'function')
							{
								signOut();
							}
							URL = '../AdminSignIn.php?signout=true&endsess=true'

							if(RedirectAddress)
							{
								URL+='&redirect='+RedirectAddress.replace(/&/g, '𓄋')
							}

							window.location.href = URL
						}
					}
				};
				
				XMLURL = '../Photon/JS/AJAXAPL.php?endsession=true&sessid=". base64_encode(session_id()) . "&email=". $Verified['Email'] ."';
				xmlhttp.open('GET', XMLURL, true);
				xmlhttp.send();
				console.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + '/'  + XMLURL);
				// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + '/'  + XMLURL;

			}

		</script>
			
			
			
		";
	}
	else 
	{
		SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. base64_encode(session_id()) .'"  ');
		SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. session_id() .'"  ');
		SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. session_id() .'"  ');
		SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. base64_encode(session_id()) .'"  ');
		SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. base64_decode(session_id()) .'"  ');



		if(isset($_GET['redirect']))
		{
			header("Location: ". "https://".  $_SERVER['SERVER_NAME'] ."/AdminSignIn.php?endsess=true&" . $_GET['redirect']);
		}
		else
		{
			$scrubbedURL =  preg_replace("/"."&"."/", "𓄋"  ,  "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
			header("Location: ". "https://".  $_SERVER['SERVER_NAME'] ."/AdminSignIn.php?endsess=true&redirect=". $scrubbedURL);
		}
		die();
	}
}
				



?>




<!-- ///////////////////Start Google Sign-In -->
 
<script>  


function signOut()
{
	 
		window.location.href = "../../AdminSignIn.php?signout=true&endsess=true&redir=" + window.location.href;
 
}
 

</script>

<?php
 
		echo '  <script src="https://apis.google.com/js/platform.js';  
	 
		echo '" async defer></script>';
	 
?>


<!-- <span style = 'color:steelblue; cursor:pointer' onclick = 'signOut();'>Google Sign Out</span>  -->
<!--<span id  = 'GSIstatus'> </span>-->
<!-- //////////////////End Google Sign-In -->