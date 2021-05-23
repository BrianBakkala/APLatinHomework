<?php 

	require_once ('SQLConnection.php');

	
	
	$ButtonBoolean = "display:none;";
 
	if (isset($_GET["showbutton"]))
	{
		$ButtonString = ($_GET["showbutton"]);
		if ($ButtonString == "true")
		{
			$ButtonBoolean = "";
		}

	}
	
	date_default_timezone_set("America/New_York"); 
	$SessExp = SQLQ('SELECT `exp`  FROM `_UserSessions` WHERE `SessionID` = "' . base64_encode(session_id()) . '"  ');
	$SessExp = "" ? 0 :  ((int) $SessExp);
	$TimeDiff = (date("U",time()) - ((int) $SessExp ));
	$TimeoutCheck =  ($TimeDiff < 0); 


	if(isset($_GET['endsess']) && $_GET['endsess']== "true"  || $TimeoutCheck)
	{
		session_start();
		session_destroy(); 
		session_start();
	}
	else
	{
		session_start();
	}
	
?>

<html><CENTER> 

<head>


<meta name="google-signin-client_id" content="790205495686-ks9kmt2e4n6696khie0dhr796u88quer.apps.googleusercontent.com">


 
<body id = 'signInBody'>



  <TITLE>Admin Sign In</TITLE>

<script>

	function ManageUserSession()
	{
		var GoogleAuth = gapi.auth2.getAuthInstance();
		Email = GoogleAuth.currentUser.get().getBasicProfile().getEmail();

		id_token = GoogleAuth.currentUser.get().getAuthResponse().id_token;

		var xhr = new XMLHttpRequest();
		xhr.open('POST', '/GoogleClassroom/ValidateToken.php');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onload = function()
		{

			UserEmail = xhr.responseText
			console.log('Verified User:  ' + (UserEmail));
			console.log("<?php if (isset($_GET['redirect'])){echo preg_replace("/"."𓄋"."/", "&" , $_GET['redirect']);} ?>")

			RedirectURL = "<?php if (isset($_GET['redirect'])){echo preg_replace("/"."𓄋"."/", "&" , $_GET['redirect']);} ?>";

				window.location.href = RedirectURL


		};
		xhr.send('sessid=' + "<?php echo base64_encode(session_id()); ?>" + '&idtoken=' + id_token); 
		// cnsole.log('sessid='+"<?php echo base64_encode(session_id()); ?>"+'&idtoken=' + id_token)

	}

	function onSuccess(googleUser)
	{
		var profile = googleUser.getBasicProfile();
		// console.log('Name: ' + profile.getName());
		// console.log('Email: ' + profile.getEmail());
		//    alert(profile.getEmail().toLowerCase().slice((-('amsacs.org').length)))

		if (profile.getEmail().toLowerCase()  != ("b.bakkala@amsacs.org").toLowerCase())
		{
			signOut();
			document.getElementById('invalidEmailText').innerHTML = "Not you."
		}

			if("<?php if(isset($_GET['signout']) ) {echo  $_GET['signout'];}?>" == "true" && document.getElementById('my-signin2').getAttribute('initialSignOut') != "true") 
			{
				document.getElementById('my-signin2').setAttribute('initialSignOut', "true")
				signOut();
			}
	
		else
		{
			setTimeout(function()
			{ 
				Email = gapi.auth2.getAuthInstance().currentUser.get().getBasicProfile().getEmail();
				ManageUserSession();

			}, 1);
		}

	}

	function onFailure(error)
	{
		console.log(error);
	}

	function renderButton()
	{
		gapi.signin2.render('my-signin2',
		{
			'scope': 'profile email',
			'width': 240,
			'height': 50,
			'longtitle': true,
			'theme': 'dark',
			'onsuccess': onSuccess,
			'onfailure': onFailure
		});
	}

	function signOut()
	{
		var auth2 = gapi.auth2.getAuthInstance();
		auth2.signOut().then(function()
		{
			console.log('User signed out.');
		});
	} 

</script>
   
<TABLE id = 'signinbutton' <? if(isset($_GET['endsess'])){echo " style = 'opacity:1;' ";}?> cellpadding = "20" >
	<TR>
		<TD align = "center">
			<CENTER>
			<div <?php  //if((isset($_GET['endsess']) && ($_GET['endsess']== "true") )) {  echo " style = 'display:none;' "; } ?> style = "text-align:center;" id="my-signin2"></div>
			<BR>
			<div   style = 'display:none; margin-left:-15px;'  id='spinnerWrapper' class='loading'>
				<div class='loader' id='spinnerSpinner'></div>
			</div>
			<BR>
			<div id = "invalidEmailText" style= "color:red;">&nbsp;</div>
		</TD>
	</TR>
</TABLE>

<BR>
<div id = "SignOutButton" style = "<?php echo $ButtonBoolean?>" class = "NexusButton" onclick="window.location.href = 'https://accounts.google.com/AccountChooser?service=lso&hl=en';" >Switch Accounts</div>
<div id = "templink"></div>
  

<?php
 
		echo '  <script src="https://apis.google.com/js/platform.js';
		
			echo '?onload=renderButton';
	 
		echo '" async defer></script>';
	 
?>


 

</body>
</html>