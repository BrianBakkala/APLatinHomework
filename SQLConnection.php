<?php
//error_reporting(E_ALL);

// Create connection
$SQLerrorMessage = "error";
if ($_SERVER['HTTP_HOST'] == "nexusinfo.altervista.org")
{
	$servername = "localhost";
	$username = "nexusinfo@localhost";
	$password = "!";
	$dbname = "my_nexusinfo";
}
else if ($_SERVER['HTTP_HOST'] == "3.214.153.235" || $_SERVER['HTTP_HOST'] == "lighthouseattendance.com")
{
	$servername = "ls-75287f5db4cdbe2a2e66ab05983d1d6f49f25e8c.cvk2nsrbydso.us-east-1.rds.amazonaws.com";
	$username = "bbakkala";
	$password = base64_decode("TmFudHVja2V0ITUx");
	$dbname = "dblighthouse";
}
else if ($_SERVER['HTTP_HOST'] == 'lighthouse.csamsacs.org')
{
	$servername = "lighthouse.csamsacs.org";
	$username = "bakkala";
	$password = "Verysecure01!!";
	$dbname = "LH_";

} 
else if ($_SERVER['HTTP_HOST'] == 'aplatin.altervista.org')
{
	$servername = "localhost";
	$username = "aplatin"; 
	$password = "!";
	$dbname = "my_aplatin";

}
else   
{

	$servername = "localhost:3306";
	$username = "lighthouse";
	$password = "bnmBNM123!@#";
	$dbname = "LH_";
} 

$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error)
	{
		die("Connection failed: " . $mysqli->connect_error);
	}

function FlattenArray(array $array, $unique = true) 
{
	$return = array();
	array_walk_recursive($array, function ($a) use (&$return)
	{
		$return[] = $a;
	});

	if ($unique)
	{
		$return = array_unique($return);
	}
	$return = array_values($return);

	return $return;

}

function SQLQ($sql_code)
{
	$SQLerrorMessage = "error";

	global $servername;
	global $username;
	global $password;
	global $dbname;

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	/* change character set to utf8 */
	if (!$conn -> set_charset("utf8"))
	{
		printf("Error loading character set utf8: %s\n", $conn -> error);
		exit();
	}

	// Check connection
	if ($conn->connect_error)
	{
		die("Connection failed: " . $conn->connect_error);
	}

	$result = mysqli_query($conn, $sql_code);
	if ($result) while ($row = mysqli_fetch_assoc($result))
	{
		foreach ($row as $key => $field)
		{ 
			$text = $field;
		}
	}
	else
	{
		echo  "Error:" . ($conn->error) . "<BR>";
		return  "Error:" . ($conn->error) . "<BR>";
	}

	if (isset($text))
	{
		return $text;
	}
	else
	{
		return "";
		//		return var_dump($result);
		
	}
}

function SQLRun($sql_code)
{
	global $servername;
	global $username;
	global $password;
	global $dbname;

	// Create connection
	$mysqli  = new mysqli($servername, $username, $password, $dbname);
	
	/* change character set to utf8 */
	if (!$mysqli -> set_charset("utf8"))
	{
		printf("Error loading character set utf8: %s\n", $mysqli -> error);
		exit();
	}
 
	// Check connection
	if ($mysqli->connect_error)
	{
		die("Connection failed: " . $mysqli->connect_error);
	}

	$mysqli->query($sql_code);

	//$temporary_id = $mysqli->insert_id;
	$temporary_id  = mysqli_insert_id($mysqli);
	if(isset($temporary_id))
	{
		return $temporary_id;
	}
	else
	{
		return "noid";
	}
}

function SQLQuarry($sql_code, $flatten = false, $uniquevalue = null)
{
	global $servername;
	global $username;
	global $password;
	global $dbname;

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	

	/* change character set to utf8 */
	if (!$conn -> set_charset("utf8"))
	{
		printf("Error loading character set utf8: %s\n", $conn -> error);
		exit();
	}

	// Check connection
	if ($conn->connect_error)
	{
		die("Connection failed: " . $conn->connect_error);
	}

	$result = mysqli_query($conn, $sql_code);
	$result = mysqli_fetch_all($result, MYSQLI_ASSOC);

	$temparray = [];

	if( $uniquevalue !== null)
	{
		foreach ($result as $row)
		{ 
			if(isset($row[$uniquevalue]))
			{
				$temparray[$row[$uniquevalue]] = $row;
			}
		}

		$result = $temparray;
	}

	if ($flatten)
	{
		if($flatten = "all")
		{
			$result = FlattenArray($result, false);
		}
		else
		{
			$result = FlattenArray($result, true);
		}
	}
	return $result;
}

?>
