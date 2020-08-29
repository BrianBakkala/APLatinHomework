<TITLE>AP Latin Homework Viewer</TITLE>
 
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ( 'SQLConnection.php');

function SanitizeString($str)
{
	$str = trim($str);
	
	$str = preg_replace("/\\\\/","",$str); 
	$str = preg_replace("/\"/","\\\"",$str); 
	$str = preg_replace("/[\n\r]/","\\n",$str); 
	$str = preg_replace("/\n\n/","\\n",$str); 

	$str = preg_replace('/[[:cntrl:]]/', '', $str);

	//$str = htmlentities($str, ENT_XML1);
	//$str = XMLEntities($str);
	
	//$str = preg_replace("/&/","&amp;amp;",$str); 
 
	//$str = addslashes($str);

	return $str;
}

function TargetSpecificSheetTab($documentKey, $sheetname = "Export")
{ 
 
	$pageNum = 1;
	$foundpagenum = false;
	do
	{
		$url = "https://spreadsheets.google.com/feeds/cells/". $documentKey ."/".$pageNum."/public/values?alt=json";
		
		if(@file_get_contents($url)!= false)
		{
			$url_json = json_decode(file_get_contents($url));
			$url_json = json_decode(json_encode($url_json), True);
			$data = $url_json;

			if($data['feed']['title']['$t'] == $sheetname)
			{
				$foundpagenum = true;
			}
		}
		$pageNum++;
	}
	while ($pageNum < 25 && !$foundpagenum);

	return $url ;
}

function ScrubGoogleSheetJSON($url)
{
 	$url_json = json_decode(file_get_contents($url));
	$url_json = json_decode(json_encode($url_json), True);
 	$data = $url_json["feed"]["entry"];
	
	 $newjsonstring= "";
	for ($d=0; $d< count($data); $d++)
	{
		$newjsonstring .= '"';
		$newjsonstring .= SanitizeString($data[$d]["title"]["\$t"]);
		$newjsonstring .= '":"';
		$newjsonstring .= SanitizeString($data[$d]["content"]["\$t"]);
		$newjsonstring .= '"';
		$newjsonstring .= ", ";
	}
 
	return       ("{".$newjsonstring.' "last":"last"}') ;
}

function CreateCatalogDatabase($scrubbedarraystring)
{
	//#debug#
	// echo $scrubbedarraystring;

	$oldarray =   json_decode( $scrubbedarraystring, True);
	$newarray = [];
	 
 	$alphabet = str_split( "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
	//print_r( $scrubbedarray);
	
	$headers = ['0'];
	
	foreach ($oldarray as $key => $value)
	{
		if(str_split($key)[1] == "1" && !isset(str_split($key)[2]))
		{ 
			array_push($headers , $value);
		}
	}


  // print_r($oldarray);
	foreach ($oldarray as $key => $value)
	{ 	
		if(str_split($key)[0] == "A")
		{ 
			$temp_array = [];
			for($j=1; $j<count($headers); $j++)
			{
				if(isset( $oldarray[$alphabet[$j-1] . substr($key, 1)]    ) )
				{
					$temp_array+= [$headers[$j] => SanitizeString($oldarray[$alphabet[$j-1] . substr($key, 1)])];
				}
				
			}  

			if($value != $headers[1] )//exclude header row
			{
					$newarray += [(substr($value, 1)) => $temp_array];
			}
		}

	}
	// print_r($newarray);
	return $newarray;

}

?>


<?php 

$GSA =  ( CreateCatalogDatabase(ScrubGoogleSheetJSON(TargetSpecificSheetTab("1CKcfxPCIV2Kz7b7QAbhK6JJ5kroxVdZoreGDXvngjS8", "Export"))));


SQLRun("TRUNCATE `#APHW`");

$TerminalLines = [];

for ($m = 1; $m <=count($GSA); $m++ )
{
	$ActiveRow = $GSA[$m];

	$SplitStart  = explode(".", $ActiveRow["Start Line"]);

	$StartBook = $SplitStart[0];
	$StartLine = $SplitStart[count($SplitStart)-1];
	if(count($SplitStart) == 2)
	{
		$StartChapter = "null";
		
	}
	else
	{
		$StartChapter = $SplitStart[1];
	}


	$SplitEnd  = explode(".", $ActiveRow["End Line"]);

	$EndBook = $SplitEnd[0];
	$EndLine = $SplitEnd[count($SplitEnd)-1];
	if(count($SplitEnd) == 2)
	{
		$EndChapter = "null";
	}
	else
	{
		$EndChapter = $SplitEnd[1];
	}

	array_push($TerminalLines, array($EndBook, $EndChapter, $EndLine ));
 
 

	SQLRun("INSERT INTO `#APHW` (`HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author`) 
	
	VALUES (
		
		".$ActiveRow["HW Number"].", 

		".$StartBook.", 
		".$StartChapter.", 
		".$StartLine.", 

		".$EndBook.", 
		".$EndChapter.", 
		".$EndLine.", 

		'".$ActiveRow["Author"]."'
	);");
  
}

foreach ($TerminalLines as $linecode)
{
	if ($linecode[1] == null)
	{
		$temp_author = "V";
		$temp_line =  implode(' ', SQLQuarry('SELECT `word` FROM `#APAeneidText` WHERE `book` = '.$linecode[0].' and `lineNumber` = '.$linecode[2].'', true));
	}
	else
	{
		$temp_author = "C";
		$temp_line = implode(' ', SQLQuarry('SELECT `word` FROM `#APDBGText` WHERE `book` = '.$linecode[0].' and `chapter` = '.$linecode[1].' and `lineNumber` = '.$linecode[2].'', true));
	}
	
	if(strpos($temp_line, ". ") != false)
	{
		$periodsplits = explode(".", $temp_line);
		$firstPart = $periodsplits[0];
		$lastPart = $periodsplits[count($periodsplits)-1];
		$WordsBefore = count(explode(" ", $firstPart));
		$WordsAfter = count(explode(" ", $lastPart))-1;

		if($linecode[1] == null)
		{
			$Chaptacode = ' IS NULL ';
		}
		else
		{
			$Chaptacode = 'AND `EndChapter` = '.$linecode[1].'';
		}
		$HWNum = SQLQ('SELECT `HW` FROM `#APHW` WHERE `EndBook` = '.$linecode[0].' '.$Chaptacode.' AND `EndLine` = '.$linecode[2].'');
		SQLRun('UPDATE `#APHW` SET  `SubtractFromEnd`="'.$WordsAfter.'" WHERE `HW` = ' .$HWNum );
		SQLRun('UPDATE `#APHW` SET  `AddToBeginning`="'.$WordsAfter.'" WHERE `HW` = ' . (1+$HWNum) );
	}


}


echo "Assignments synchronized.";


?> 


<SCRIPT>

console.log(<?php echo (json_encode($GSA))?> )


</SCRIPT>
<BR><BR><BR><BR><BR><BR><BR><BR>