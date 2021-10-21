<?php

require_once ( 'SQLConnection.php'); 
require_once ( 'GenerateNotesandVocab.php'); 
$context = new Context;

$hint = "";

$Conversion = [
	"-" => "-",

	"ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
	"Ā" => "a", "Ē" => "e", "Ī" => "i", "Ō" => "o", "Ū" => "u", "Ȳ" => "y",
	"a" => "a", "b" => "b", "c" => "c", "d" => "d", "e" => "e", "f" => "f", "g" => "g", "h" => "h", "i" => "i", "j" => "j", "k" => "k", "l" => "l", "m" => "m", "n" => "n", "o" => "o", "p" => "p", "q" => "q", "r" => "r", "s" => "s", "t" => "t", "u" => "u", "v" => "v", "w" => "w", "x" => "x", "y" => "y", "z" => "z",
	"A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h", "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z"
];

function GetForms($entry)
{
	$forms =  preg_split('/(\||,)/', $entry);;
	$forms = array_map(function($x)
	{
		$p = strip_tags($x);
		$p = preg_replace('/\*/', '', $p);
		$p = StripMacrons(strtolower(trim($p)));
		$p = ltrim($p, '-');

		return $p;
		
	}, $forms);

	return $forms;
}

function GetMatchingScore($filtertext, $entry)
{
	$forms =  GetForms($entry);
	$formslengths = array_map(function($x) use($filtertext)
	{
		$searchtext = StripMacrons(strtolower($filtertext));
		if(strpos($x, $searchtext) !== false)
		{
			return ( ((double) strlen($searchtext))  / ((double) strlen($x))  );
		}
		else
		{
			return 0;
		}
	},$forms);

	// return  implode("/", $forms);
	return  max($formslengths);
}

function GetPositionScore($filtertext, $entry)
{
	$forms =  GetForms($entry);
	$formslengths = array_map(function($x) use($filtertext)
	{
		$searchtext = StripMacrons(strtolower($filtertext));
		if(strpos($x, $searchtext) !== false)
		{
			return strpos($x, $searchtext);
		}
		else
		{
			return 1000;
		}
	},$forms);

	return  min($formslengths);
}

//////////////////////////////////////

if (isset($_REQUEST["toggletestmode"]))
{
	$temp_Testmode = "TestMode" . $_GET['level'];
	$currentMode = SQLQ('SELECT   `TestModeAP`  FROM `Control Panel`  ');
	$newMode = (int) $_REQUEST["newval"];

	SQLRun('UPDATE `Control Panel` SET `'.$temp_Testmode.'` = '.$newMode.' WHERE `Control Panel`.`id` = 1;');
	$hint = $_REQUEST['level'] . " " . $newMode;
}

if (isset($_REQUEST["updatedefinition"]))
{

	//SQLRun('UPDATE `~DeanReferrals` SET `DeanNotes`="'. $_REQUEST["deansnotes"] .'" WHERE `ReferralID` = "'. $_REQUEST["referralid"] .'"');
	//   echo ('UPDATE `#AP'.$_REQUEST["authortext"].'Text` SET `definitionId` = '. $_REQUEST["def1"] .' , `secondaryDefId` = '. $_REQUEST["def2"] .'  WHERE `id` = '. $_REQUEST["wordid"] .';');
	SQLRun('UPDATE `'.$context::BookDB[ $_REQUEST["title"]].'` SET `definitionId` = '. $_REQUEST["def1"] .' , `secondaryDefId` = '. $_REQUEST["def2"] .'  WHERE `id` = '. $_REQUEST["wordid"] .';');
	$hint = SQLQ('SELECT `definition` FROM  `'.$context::LevelDictDB[$_REQUEST['level']] .'` WHERE  `id` = '. $_REQUEST["def1"] .'  ');
	if( $_REQUEST["def2"]  != -1)
	{
		$hint .= " | ";
		$hint .= SQLQ('SELECT `definition` FROM `'.$context::DictDB[ $_REQUEST["title"]].'` WHERE  `id` = '. $_REQUEST["def2"] .'  ');
	}
} 

if (isset($_REQUEST["filterdictionary"]))
{
	$nomacronsfilterarray = preg_split('/(?!^)(?=.)/u', $_REQUEST["filtertext"]);
	$nomacronsfiltertext = implode("",array_map(function($x)
		{
			global $Conversion;
			return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
		}, $nomacronsfilterarray)); 
		
	$Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `'.$context::LevelDictDB[$_REQUEST['level']] .'` WHERE `id` > 0 AND (REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "ō", "o") COLLATE UTF8_GENERAL_CI LIKE "%'.$nomacronsfiltertext.'%" OR `definition` COLLATE UTF8_GENERAL_CI LIKE "%'.$nomacronsfiltertext.'%") '); 

	if(strlen($_REQUEST["filtertext"]) < 2 && count($Dictionary) >500)
	{
		$hint = "Too many results.";
	}
	else
	{
		
		if(count($Dictionary) > 0)
		{
			$DefIDs = array_map(function($x){return $x['id'];}, $Dictionary);
			$Frequencies = GetFreqTable($DefIDs);
		}

		$matchScore = GetMatchingScore($nomacronsfiltertext, $word['entry']);
		usort($Dictionary, function ($a, $b) use($nomacronsfiltertext) {

			$scoreA = GetMatchingScore($nomacronsfiltertext, $a['entry']);
			$scoreB = GetMatchingScore($nomacronsfiltertext, $b['entry']);
			$posA = GetPositionScore($nomacronsfiltertext, $a['entry']);
			$posB = GetPositionScore($nomacronsfiltertext, $b['entry']);

			if($posA != $posB  )
			{
				return $posA <=> $posB;
			}
			else if ($scoreA !=  $scoreB  )
			{
				return $scoreB <=> $scoreA;
			}
			else
			{
				global $Conversion;
	
				$a = $a['entry'];
				$b = $b['entry'];
	
				$a = mb_ereg_replace("\W","",$a); 
				$b = mb_ereg_replace("\W","",$b); 
				
				$a = preg_split('/(?!^)(?=.)/u', $a);
				$a = array_map(function($x)
				{
					global $Conversion;
					return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
				}, $a);
				$a = implode("", $a);
				
				
				$b = preg_split('/(?!^)(?=.)/u', $b);
				$b = array_map(function($x)
				{
					global $Conversion;
					return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
				}, $b);
				$b = implode("", $b);
				
				
				if(strtolower($a) < strtolower($b))
				{
					return -1;
				}
				else if(strtolower($a) > strtolower($b))
				{
					return 1;
				}
				else
				{
					return 0;
				};
			}
			
		
		});

		global $Conversion;

		foreach($Dictionary as $word)
		{

			$searchablestring = ( mb_ereg_replace("[()]", "",  $word['entry']) . " " .$word['definition']);
			$searchablestringChars = preg_split('/(?!^)(?=.)/u', $searchablestring);
			$searchablestringChars = array_map(function($x)
			{
				global $Conversion;
				return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
			}, $searchablestringChars);

			$searchablestring = implode("", $searchablestringChars);

			// $hint.=preg_match('/'. $_REQUEST["filtertext"] . '/', $searchablestring ) ;

			$filterString =$_REQUEST["filtertext"];
			$filterString = preg_split('/(?!^)(?=.)/u', $filterString);
			$filterString=array_map(function($x)
			{
				global $Conversion;
				return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
			}, $filterString);
			$filterString = implode("", $filterString);

			if (preg_match('/'.$filterString.	'/', $searchablestring))
			{


				$hightlightablestring = $word['entry'] . "⸻" . $word['definition'];

				if($_REQUEST["filtertext"] !== "")
				{
					$filterRegex =  $_REQUEST["filtertext"];
					$filterRegex = preg_split('/(?!^)(?=.)/u', $filterRegex);
					$filterRegex = array_map(function($x)
					{
			
						$Conversion = [
							"ā" => "[aā]", "ē" => "[eē]", "ī" => "[iī]", "ō" => "[oō]", "ū" => "[uū]", "ӯ" => "[yӯ]",
							"Ā" => "[AĀ]", "E" => "[EĒ]", "Ī" => "[IĪ]", "Ō" => "[OŌ]", "Ū" => "[UŪ]", "Ȳ" => "[YȲ]", 
							"a" => "[aā]", "e" => "[eē]", "i" => "[iī]", "o" => "[oō]", "u" => "[uū]", "y" => "[yӯ]",
							"A" => "[AĀ]", "E" => "[EĒ]", "I" => "[IĪ]", "O" => "[OŌ]", "U" => "[UŪ]", "Y" => "[YȲ]", 
						];
			
						if (isset($Conversion[$x]))
						{
							return $Conversion[$x];
			
						}
						else
						{
							return $x;
						}
			
					}, $filterRegex);
			
					$filterRegex=implode("[()]?", $filterRegex);
			
					$hightlightablestring = mb_ereg_replace("(".$filterRegex.")", "<highlight>\\1</highlight>", $hightlightablestring, "i"); 
				}
				
					$hint .= "<word  wordid = ". $word['id'] ."  ";
					$hint .= ">";
					
						$hint .= "<attestations>["; 
	
						$hint .= $Frequencies[$word['id']] ; 
	
						$hint .= "] </attestations>"; 
					$hint .= "<entry>";
					$hint .= "<b>";
					
					$entrytext =  explode("⸻", $hightlightablestring)[0];
					$hint .= ConvertAsterisks( $entrytext );
					
					$hint .= "</b>";
					$hint .= "</entry>";
					$hint .= "<definition>";
					$hint .= "<i>";
					
					$deftext =  explode("⸻", $hightlightablestring)[1];
					$hint .= ConvertAsterisks( $deftext ); 
					
					$hint .= "</i>"; 
					$hint .= "</definition>"; 
					
					$hint .= "<img  onclick = 'SaveEntry(this) '  style = 'display:none;' class = 'savebutton' src = 'Images/LHcheck.png'>";
					$hint .= "<img  onclick = 'EditEntry(this)'  class = 'editbutton' src = 'Images/LHedit.png'>";
					$hint .= "<img  onclick = 'GetWordInfo(this) '  class = 'InfoButton' src = 'Images/LHinfo.png'>";
					$hint .= "<img  onclick = 'DeleteEntry(this) '  class = 'deletebutton' src = 'Images/LHx.png'>";
					$hint .= "<div  id = 'Matching Score'  style = 'display:none;' >".GetMatchingScore($nomacronsfiltertext, $word['entry'])."</div>";
					$hint .= "<div  id = 'Position Score'  style = 'display:none;' >".GetPositionScore($nomacronsfiltertext, $word['entry'])."</div>";



					$hint .= "</word>";
				
			}

		}

	

		$hint === "" ? "No results." : $hint;

	}
}

if (isset($_REQUEST["deletedictionaryentry"]))
{
	SQLRun( 'DELETE FROM `'.$context->GetDict().'` WHERE `id` = ' . $_REQUEST["wordid"]);
}

if (isset($_REQUEST["updatedictionary"]))
{ 
	SQLRun('UPDATE `'.$context->GetDict().'` SET `entry` = "'.$_REQUEST["newentry"].'", `definition` = "'.$_REQUEST["newdefinition"].'"   WHERE `id` = '.$_REQUEST["wordid"].';');

	$hint = '{"definition":"'.$_REQUEST["newdefinition"].'", "entry":"'.$_REQUEST["newentry"].'"}';
}

if (isset($_REQUEST["addnote"]))
{ 
	

	$NoteId = SQLRun('INSERT INTO `'.$context::LevelNotesDB[$_REQUEST["level"]].'Text` (`Text`) VALUES ("'.addslashes ($_REQUEST["notetext"]).'");'); 
	
	
	if( $_REQUEST["wordids"] != "")
	{
		$WIDs = explode(",", $_REQUEST["wordids"]);
		$WIDs = array_unique(array_filter($WIDs));
		foreach($WIDs as $wid)
		{
			SQLRun("INSERT INTO `".$context::LevelNotesDB[$_REQUEST["level"]]."Locations` (`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `BookTitle`) VALUES (".$NoteId.", ". $wid.", '', '".$_REQUEST["booktitle"]."');"); 
		}
	}
	else
	{
		$LIDs = explode(",", $_REQUEST["linecitations"]);
		$LIDs = array_unique(array_filter($LIDs));
		foreach($LIDs as $lid)
		{ 
			
			$FirstID = SQLQ('SELECT MIN(`id`)  FROM `'.$context::BookDB[ $_REQUEST["booktitle"]].'` WHERE CONCAT(`book`, ".", `lineNumber`) = "'. $lid.'" OR  CONCAT(`book`, ".", `chapter`, ".", `lineNumber`) = "'. $lid.'"');
			
			SQLRun ("INSERT INTO `".$context::LevelNotesDB[$_REQUEST["level"]]."Locations` (`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `BookTitle`) VALUES (".$NoteId.",  ".$FirstID.", '". $lid."', '".$_REQUEST["booktitle"]."');"); 
			
		}

	}

	$hint = "Notes Added";
}

/////////////////////////////

if (isset($_REQUEST["endsession"]))
{
	SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. $_REQUEST["sessid"] .'"  ');
	SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. $_REQUEST["sessid"] .'"  ');
	SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. base64_encode($_REQUEST["sessid"]) .'"  ');
	SQLRun('DELETE FROM `_UserSessions` WHERE `SessionID` = "'. base64_decode($_REQUEST["sessid"]) .'"  ');
	$hint = 'Removed';
}

/////////////////////////////




$hint = trim($hint);

echo $hint == "" ? "No results" : $hint;  



?> 
