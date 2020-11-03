<?php

require_once ( 'SQLConnection.php');






if(!isset($_GET['level']))
{
	$Level = 'AP';
}
else
{
	$Level = $_GET['level'];
}

$LevelDB = [
	"AP"=> "#APHW",
	"4" => "^Latin4HW"
];

if(isset($_GET['hw']))
{
	$HWAssignment = SQLQuarry('SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author`, `BookTitle`, `AddToBeginning`, `SubtractFromEnd`  FROM `'.$LevelDB[$Level].'` WHERE `HW` = ' . (int)$_GET['hw'])[0]  ;

	$Conjunction = " AND ";

	if($HWAssignment['StartChapter'] == null && $HWAssignment['EndChapter'] == null)
	{
		$WhereClause = ' ( `lineNumber` >= '.$HWAssignment['StartLine'].'  AND   `lineNumber` <= '.$HWAssignment['EndLine']. ')';
	}

	if($HWAssignment['StartChapter'] != null && $HWAssignment['EndChapter'] != null)
	{

		if($HWAssignment['StartChapter'] == $HWAssignment['EndChapter'] )
		{
			$WhereClause = ' `chapter` = "'.$HWAssignment['StartChapter'].'" AND (  `lineNumber` >= '.$HWAssignment['StartLine'].' AND   `lineNumber` <= '.$HWAssignment['EndLine']. ')';
		}
		else
		{
			$WhereClause = '(( `chapter` = "'.$HWAssignment['StartChapter'].'" AND   `lineNumber` >= '.$HWAssignment['StartLine'].')  OR  ( `chapter` = "'.$HWAssignment['EndChapter'].'" AND   `lineNumber` <= '.$HWAssignment['EndLine'].')  )  ';
		}
		
	}
}



if(isset($_GET['title'] ))
{
	$BookTitle = $_GET['title'];	
}
else if(!isset($HWAssignment['BookTitle'] ) || $HWAssignment['BookTitle'] == "")
{
	$BookTitle = "Aeneid";
	if($HWAssignment['Author'] == "C")
	{
		$BookTitle = "DBG";	
	}
}
else
{
	$BookTitle = $HWAssignment['BookTitle'];
}


$BookDB = [
	"Aeneid" => "#APAeneidText",
	"DBG" => "#APDBGText",
	"InCatilinam" => "^Latin4InCatilinamText"

]; 


$DictDB = [
	"Aeneid" => "#APDictionary",
	"DBG" => "#APDictionary",
	"InCatilinam" => "^Latin4Dictionary"

]; 


$NotesDB = [
	"Aeneid" => "#APNotes",
	"DBG" => "#APNotes",
	"InCatilinam" => "^Latin4Notes"

]; 

$LevelDictDB =[
	"4" => "^Latin4Dictionary",
	"AP" => "#APDictionary"
];



// echo ('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `'.$BookDB[$BookTitle].'` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`');
if(isset($_GET['hw']))
{
	$HWStartId = ((int) SQLQ('SELECT MIN(`id`) FROM `'.$BookDB[$BookTitle].'` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $HWAssignment['AddToBeginning']) );
	$HWEndId = ((int) SQLQ('SELECT MAX(`id`) FROM `'.$BookDB[$BookTitle].'` WHERE  `book` = '.$HWAssignment['EndBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $HWAssignment['SubtractFromEnd']) );
	$HWLines = SQLQuarry('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `'.$BookDB[$BookTitle].'` WHERE ( `book` = '.$HWAssignment['StartBook'].' or  `book` = '.$HWAssignment['EndBook'].') AND `id` >= '. $HWStartId .' AND `id` <= '. $HWEndId .' ORDER BY `book`, `chapter`, `lineNumber`, `id`');


	$HWDefinitionIds = array_map(function($x){return $x['definitionId'];},$HWLines);
	$HWDefinitionIds2 = array_map(function($x){return $x['secondaryDefId'];},$HWLines);
	$HWDefinitionIds = array_unique(array_merge($HWDefinitionIds, $HWDefinitionIds2));


	$TargetedDictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords`  FROM `'.$DictDB[$BookTitle].'` WHERE `id` <> 0 and `id` <> -1 and ( `id` = '. implode(" OR `id` = ", $HWDefinitionIds) .')   ORDER BY replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "Ā", "A") , "Ē", "E") , "Ī", "I") , "Ō", "O") , "Ū", "U") , "-", ""), "—, ", "")  COLLATE utf8_general_ci   ', false, "id"); 
}


function GetAPFrequency($definitionIdNumber)
{ 
	$TwoWordCheck = SQLQ('SELECT `IsTwoWords` FROM `#APDictionary` WHERE `id` = ' . $definitionIdNumber);
	$Aeneiduses = SQLQ('SELECT COUNT(`id`) FROM `#APAeneidText` WHERE `definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber );
	$Tmesis = SQLQ('SELECT COUNT(`id`) FROM `#APAeneidText` WHERE (`definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber . ') and `Tmesis` = 1 ');
	$DBGuses = SQLQ('SELECT COUNT(`id`) FROM `#APDBGText` WHERE `definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber );
	
	$AeneidusesCount = (($Aeneiduses - ($Tmesis/2)) /( 1+(int) $TwoWordCheck ));
	
	$TotalFreq = (((int) $AeneidusesCount) + ((int) $DBGuses));

	return $TotalFreq;
}

function ParseNoteText($inputText, $showdevices)
{
	$outputText = $inputText;

	$literaryDevices = SQLQuarry('SELECT `Device`, `Description` FROM `#APLiteraryDevices`', false, "Device");
	$literaryDevices = array_map(function($x){		return $x['Description'];	}, ($literaryDevices)) ;
	$literaryDevices = array_flip(array_map('strtolower',  array_flip($literaryDevices) ));
	
	if($showdevices == true)
	{
	$outputText = preg_replace_callback("/\*\*\*(".implode('|', array_keys($literaryDevices) ).")\*\*\*/", function ($matches) use($literaryDevices) {
		
		// print_r($literaryDevices);
		return "<span class = 'literarydevice' device='" .$matches[1]."'>".$matches[1]."<span class='tooltiptext'><B><U>".$matches[1]."</u></B><BR>".$literaryDevices[$matches[1]]."</span></span>";
		
		;}, $outputText);
	}
	else
	{
		$outputText = preg_replace("/\*\*\*(".implode('|', array_keys($literaryDevices) ).")\*\*\*/", '<u>'.'\\1'.'</u>', $outputText);
	}
	$outputText = preg_replace("/\*\*(.*?)\*\*/","<b>\\1</b>",$outputText);
	$outputText = preg_replace("/\*(.*?)\*/","<i>\\1</i>",$outputText);

	return $outputText;
}

function DisplayNotesText($hwstart, $hwend, $hwassignment, $title, $literaryDevices = true)
{
	global $NotesDB;
	global $BookDB;

	$WordNotes = SQLQuarry(' SELECT `'.$NotesDB[$title].'Locations`.`NoteId`, `AssociatedWordId`,   `'.$NotesDB[$title].'Text`.`Text`, `BookTitle`, `sub`.`word`,`sub`.`book`,`sub`.`chapter`, `sub`.`lineNumber` FROM `'.$NotesDB[$title].'Locations` INNER JOIN `'.$NotesDB[$title].'Text` ON (`'.$NotesDB[$title].'Text`.`NoteId` = `'.$NotesDB[$title].'Locations`.`NoteId`) INNER JOIN (SELECT `id`, `book`,`chapter`, `word`,`lineNumber`   FROM `'.$BookDB[$title].'`) as `sub` ON (`sub`.`id` = `AssociatedWordId` )  WHERE (`sub`.`id` >= '.$hwstart.' AND `sub`.`id` <= '.$hwend.') AND `BookTitle` = "'.$title.'" AND `AssociatedLineCitation` = "" ORDER BY `AssociatedWordId`');

	
	if($title != "Aeneid")
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.',`sub`.`chapter`, '.', `sub`.`lineNumber`)";
	}
	else
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.', `sub`.`lineNumber`)";
	}
	

	$LineNotes = SQLQuarry('SELECT `'.$NotesDB[$title].'Locations`.`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `'.$NotesDB[$title].'Text`.`Text`, `BookTitle`, `book`, `chapter`, `lineNumber` FROM `'.$NotesDB[$title].'Locations` INNER JOIN `'.$NotesDB[$title].'Text` ON (`'.$NotesDB[$title].'Text`.`NoteId` = `'.$NotesDB[$title].'Locations`.`NoteId`) LEFT JOIN (SELECT `id`, `book`, `chapter`, `lineNumber` FROM `'.$BookDB[$title].'`) as `sub` ON ( `AssociatedLineCitation` =  '.$ConcatText.'   ) WHERE (`sub`.`id` >= '.$hwstart.' AND `sub`.`id` <= '.$hwend.') AND `BookTitle` = "'.$title.'" GROUP BY `AssociatedLineCitation` ORDER BY `lineNumber`');
	
	$CondensedNotes = array();

	// print_r($WordNotes);
	// print_r($LineNotes);


	foreach($WordNotes as $note)
	{
		if(!isset($CondensedNotes[$note["NoteId"]]))
		{
			$CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => $note["AssociatedWordId"]);
			$CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
			$CondensedNotes[$note["NoteId"]]["WL"] = "Word";
			$CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
			$CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
			$CondensedNotes[$note["NoteId"]]["LastWordId"] = $note["AssociatedWordId"];
			$CondensedNotes[$note["NoteId"]]["phrase"] = $note["word"]; 
			$CondensedNotes[$note["NoteId"]]["lines"] = array($note["lineNumber"]); 
			$CondensedNotes[$note["NoteId"]]["comparableCitation"] = $note["AssociatedWordId"]; 
		}
		else
		{
			if(($CondensedNotes[$note["NoteId"]]["LastWordId"]+1) == $note["AssociatedWordId"])
			{
				$CondensedNotes[$note["NoteId"]]["phrase"] .= " ". $note["word"]; 
			}
			else
			{
				$CondensedNotes[$note["NoteId"]]["phrase"] .= " … ". $note["word"]; 
			}


			$CondensedNotes[$note["NoteId"]]["LastWordId"] = $note["AssociatedWordId"];

			array_push($CondensedNotes[$note["NoteId"]]["lines"], $note["lineNumber"]); 
			$CondensedNotes[$note["NoteId"]]["lines"] = array_unique ($CondensedNotes[$note["NoteId"]]["lines"]); 
			sort($CondensedNotes[$note["NoteId"]]["lines"]);
		}
	}

	foreach($LineNotes as $note)
	{
		if(!isset($CondensedNotes[$note["NoteId"]]))
		{
			$CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => $note["AssociatedWordId"]);
			$CondensedNotes[$note["NoteId"]]["WL"] = "Line";
			$CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
			$CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
			$CondensedNotes[$note["NoteId"]]["phrase"] = "";
			$CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
			$CondensedNotes[$note["NoteId"]]["lines"] = array(substr($note["AssociatedLineCitation"], 2)); 
			$CondensedNotes[$note["NoteId"]]["comparableCitation"] = $note["AssociatedWordId"]; 

		}
		else
		{
			array_push($CondensedNotes[$note["NoteId"]]["lines"], substr($note["AssociatedLineCitation"], 2));
			// $CondensedNotes[$note["NoteId"]]["lines"] = array_unique ($CondensedNotes[$note["NoteId"]]["lines"]); 
			sort($CondensedNotes[$note["NoteId"]]["lines"]);
		}
	}

	// print_r($CondensedNotes);

	usort($CondensedNotes, function ($a, $b) {

		$A = $a["comparableCitation"];
		$B = $b["comparableCitation"];
		
		if($A != $B)
		{
			return $A < $B ? -1 : 1;
		}

		if($a["WL"] != $b["WL"])
		{
			return $a["WL"] != "Word" ? -1 : 1;
		}

		return 0;
			
	});

	// print_r($CondensedNotes);

	$outputText = "";
	$lastLinesText = null;
	foreach($CondensedNotes as $Cnote)
	{
		$outputText .= "<note >";
		$linestext = count($Cnote["lines"]) > 1 ? min($Cnote["lines"]) . "–" .  max($Cnote["lines"]) :   $Cnote["lines"][0];
		
		if($lastLinesText != $linestext)
		{
			$outputText .= "<span style = 'font-family:Cinzel'>". $linestext .". </span>";
		}
		else
		{
			$outputText .= "<span style = 'color:rgba(0,0,0,0); font-family:Cinzel'>". $linestext .". </span>";
		}
		$lastLinesText = $linestext;
		
		$outputText .= "<B>". preg_replace("/[;,()?!\.:\"\']/","", $Cnote['phrase']) ;
		$outputText .= $Cnote['phrase'] == "" ? "" : ":";
		$outputText .= " </B>";
		$outputText .= ParseNoteText($Cnote['Text'], $literaryDevices);
		$outputText .= "</note> ";
		// echo implode("|", $Cnote['comparableCitation']);
		$outputText .= "<BR>";
	}

	return $outputText  ;

}

function DisplayVocabText($dictionary, $condensed = false)
{
	$outputtext = "";
	foreach($dictionary as $entry)
	{
		$freq = GetAPFrequency($entry['id']);
	
		if($condensed == true && $freq <= 5)
		{
			$outputtext .=  "<b>";
			$outputtext .=  $entry['entry'];
			$outputtext .=  "</b>";
			$outputtext .=  " ";
			$outputtext .=  "<i>";
			$outputtext .=  $entry['definition'];
			$outputtext .=  "</i>";


			$outputtext .=  " ";

			$outputtext .=  "(";
			// var_dump($Tmesis);
				$outputtext .=  $freq;
			$outputtext .=  ")";


			$outputtext .= "<BR>";
		}
		else if ($condensed != true)
		{
			$outputtext .=  "<vocabword id = '".$entry['id']."' >";
			$outputtext .= "<span style = 'font-weight:bold;'>";
			$outputtext .=  $entry['entry'];
			$outputtext .=  "</span>";
			$outputtext .=  " ";
			$outputtext .=  "<span style = 'font-style:italic;'>";
			$outputtext .=  $entry['definition'];
			$outputtext .=  "</span>";


			$outputtext .=  " ";

			$outputtext .=  "<span>(";
			// var_dump($Tmesis);
				$outputtext .=  $freq;
			$outputtext .=  ")</span>";


			$outputtext .= "</vocabword>";
		}



	}

	return $outputtext;
}  

//DisplayLines(true, $HWAssignment, $HWLines, $TargetedDictionary, $BookTitle)
function DisplayLines($showvocab,  $assignment, $lines, $dictionary, $linespacing = 2)
{ 

	$outputtext= "";
		
	$ChapterCitationText = "";
	if($assignment['StartChapter'] != null)
	{
		$ChapterCitationText = $assignment['StartChapter'] . "."; 
	}



		if($assignment['AddToBeginning'] > 0)
	{
		$temp_start_line = $assignment['StartLine'] -1 ;
	}
	else
	{
		$temp_start_line = $assignment['StartLine'];
	}

	if($showvocab == true)
	{
		$outputtext .=  "<line citation = '".$assignment['StartBook'].".".$ChapterCitationText.$temp_start_line."' num = '".$temp_start_line."'>";
	}

	$CurrentLine = null;

	$CliticList = array_filter($dictionary, function($word){ return ($word ['entry'][0] == "-");});
	$CliticList = array_map( function($word){return $word['entry'];}, $CliticList) ;
	$CliticList = array_values(array_unique($CliticList));

	foreach ($lines as $word)
	{
		if($CurrentLine && $word['lineNumber'] != $CurrentLine)
		{
			$ChapterCitationText = "";
			if($assignment['StartChapter'] != null)
			{
				$ChapterCitationText = $word['chapter']."."; 
			}
			if($showvocab == true)
			{
				$outputtext .= "</line>";
			}
			if($showvocab != true)
			{
				for ($i = 0; $i< $linespacing; $i++)
				{
					$outputtext .= "<BR>";  
				}
			}
			if($showvocab == true)
			{
				$outputtext .= "<line   citation = '".$word['book'].".".$ChapterCitationText.$word['lineNumber']."'    num = '".$word['lineNumber']."'>";
			}
		}
		$CurrentLine = $word['lineNumber'];

		$Noclitics =$word['word'];
		$Noclitics = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$Noclitics);
		$Clitic = "";
		$split1 = $word['word'];


		if($word["secondaryDefId"] != -1)
		{
			preg_match('/('. implode("|", array_map(function($val) { return ltrim($val, '-')."$";} , $CliticList)). ')/', $Noclitics, $clitics);
			$Clitic = $clitics[0];

			$Noclitics = mb_ereg_replace('('. implode("|", array_map(function($val) { return ltrim($val, '-')."$";} , $CliticList)). ')',"",$Noclitics);


			$SplitPos = preg_match('/('. implode("|", array_map(function($val) { return ltrim($val, '-');} , $CliticList)). ')[.!;,]?$/', $word['word'], $position, PREG_OFFSET_CAPTURE);
			$split1 = substr($word['word'], 0, $position[0][1] );
			$split2 = substr($word['word'], $position[0][1]);
		}

		if($showvocab == true)
		{
			$outputtext .= "<word  baseword = '".$Noclitics."' clitic = '".$Clitic."' defintionid = '".$word['definitionId']."' wordid = '".$word['id']."' AP-frequency = '". GetAPFrequency($word['definitionId'])."' reveal = 'false'  >";
			
			$outputtext .= "<baseword>";
				
				$outputtext .= "<text>";
					$outputtext .= $split1;
				$outputtext .= "</text>";

				
				$outputtext .= "<entry>";
					$outputtext .= $dictionary[$word['definitionId']]['entry'];
				$outputtext .= "</entry>";

				$outputtext .= "<definition>";
					$outputtext .= $dictionary[$word['definitionId']]['definition'];
				$outputtext .= "</definition>";
				

			$outputtext .= "</baseword>";

			if($word["secondaryDefId"] != -1)
			{
				$outputtext .= "<clitic>";
				
					$outputtext .= "<text>";
						$outputtext .= $split2;
					$outputtext .= "</text>";

					$outputtext .= "<entry>";
						$outputtext .= $dictionary[$word['secondaryDefId']]['entry'];
					$outputtext .= "</entry>";

					$outputtext .= "<definition>";
						$outputtext .= $dictionary[$word['secondaryDefId']]['definition'];
					$outputtext .= "</definition>";
					
				$outputtext .= "</clitic>";
			}

				$outputtext .= "<freq>"; 
				
					global $Level;

					if($Level == "AP")
					{
						$outputtext .= "<a target = '_blank' href = 'WordViewer.php?wordid=". $word['definitionId'] . "'>";
						$outputtext .= GetAPFrequency($word['definitionId']);
						$outputtext .= "</a>";
					}
					
				$outputtext .= "</freq>";
		}
		else
		{
			$outputtext .= $split1;
			if($word["secondaryDefId"] != -1)
			{
				$outputtext .=  $split2;
			}
			$outputtext .=  " ";
		}	

		$outputtext .= "</word>"; 
	}
	if($showvocab == true)
	{
		$outputtext .= "</line>";
	}

	

	return $outputtext;
}
?>