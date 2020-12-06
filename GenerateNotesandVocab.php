<?php

require_once ( 'SQLConnection.php');






if(isset($_GET['hw']))
{
	$Data = GetHWAssignment($_GET['hw']);
	$HWAssignment = $Data['Assignment'];
	$HWLines = $Data['Lines'];
	$TargetedDictionary =$Data['Dictionary'];
	$HWStartId =$Data['StartID'];
	$HWEndId =$Data['EndID'];
};

class Context
{
	
	public const Poetry =
	[
		"Aeneid",
		"Catullus"
	];
	
	public const SpeakerColumn =
	[
		"Aeneid"
	];
	
	public const LevelDictDB =
	[
		"3" => "~Latin3Dictionary",
		"4" => "^Latin4Dictionary",
		"AP" => "#APDictionary"
	];
	
	public const LevelNotesDB =
	[
		"3" => "~Latin3Notes",
		"4" => "^Latin4Notes",
		"AP" => "#APNotes"
	];
	
	public const LevelDB = [
		"AP"=> "#APHW",
		"4" => "^Latin4HW",
		"3" => "~Latin3HW"
	];
	
	public const BookDB = 
	[
		"Aeneid" => "#APAeneidText",
		"DBG" => "#APDBGText",
		"InCatilinam" => "^Latin4InCatilinamText",
		"Catullus" => "~Latin3CatullusText"

	]; 

	public const DictDB = 
	[
		"Aeneid" => "#APDictionary",
		"DBG" => "#APDictionary",
		"InCatilinam" => "^Latin4Dictionary",
		"Catullus" => "~Latin3Dictionary"
	]; 
	
	public const LatinBookTitle =
	[
		"Aeneid" => "Aenēis",
		"DBG" => "Commentāriī Dē Bellō Gallicō",
		"InCatilinam" => "Ōrātiō in Catilinam Prīma in Senātū Habita",
		"Catullus" => "Carmina Catullī" 
	];

	public const EnglishBookTitle =
	[
		"Aeneid" => "Aeneid",
		"DBG" => "De Bello Gallico",
		"InCatilinam" => "In Catilinam",
		"Catullus" => "Catullus" 
	];



	public function GetHWDB()
	{
		return self::LevelDB[self::GetLevel()];
	}

	public function GetNotesDB()
	{
		return self::LevelNotesDB[self::GetLevel()];
	}

	public function GetDict()
	{
		return self::LevelDictDB[self::GetLevel()];
	}

	public function GetTextDB()
	{
		return self::BookDB[self::GetBookTitle()];
	}

	public function GetLatinTitle()
	{
		return self::LatinBookTitle[self::GetBookTitle()];
	}

	public function GetEnglishTitle()
	{
		return self::EnglishBookTitle[self::GetBookTitle()];
	}
	

	public function GetTestStatus()
	{
		$tl = self::GetLevel();
		return (SQLQ('SELECT `TestMode'.$tl.'`  FROM `Control Panel`') == "1");
	}
	
	public function GetLevel()
	{
		if(!isset($_GET['level']))
		{		
			if(isset($_GET['title']))
			{
				$d = self::DictDB[$_GET['title']];
				$l = array_flip(self::LevelDictDB)[$d];
			}
			else
			{
				$l = 'AP';
			}
		}
		else
		{
			$l = $_GET['level'];
		}
		return $l;
	}

	public function GetBookTitle()
	{

		if(isset($_GET['title'] ))
		{
			$bt = $_GET['title'];	
		}
		else if(isset($_GET['hw'] ))
		{
			$bt = SQLQ('SELECT `BookTitle` FROM `'. self::LevelDB[self::GetLevel()] .'` WHERE `HW` = ' . $_GET['hw']);
		}

		return $bt;
	}
	
}

// echo ('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `'.$BookDB[$BookTitle].'` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`');


function GetHWAssignment($HWNum, $hwdb = null, $title = null)
{
	$context = new Context;

	if(!isset($HWNum))
	{
		$HWNum = (int) $_GET['hw'];
	}

	if(!isset($hwdb))
	{
		$hwdb = $context->GetHWDB();
	}

	if(!isset($title))
	{
		$title = $context->GetBookTitle();
	}

	$Assignment = SQLQuarry('SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author`, `BookTitle`, `AddToBeginning`, `SubtractFromEnd`  FROM `'.$hwdb.'` WHERE `HW` = ' . ((int) $HWNum) )[0] ;

	if($Assignment['StartChapter'] == null && $Assignment['EndChapter'] == null)
	{
		$WhereClause = ' ( `lineNumber` >= '.$Assignment['StartLine'].'  AND   `lineNumber` <= '.$Assignment['EndLine']. ')';
	}

	if($Assignment['StartChapter'] != null && $Assignment['EndChapter'] != null)
	{

		if($Assignment['StartChapter'] == $Assignment['EndChapter'] )
		{
			$WhereClause = ' `chapter` = "'.$Assignment['StartChapter'].'" AND (  `lineNumber` >= '.$Assignment['StartLine'].' AND   `lineNumber` <= '.$Assignment['EndLine']. ')';
		}
		else
		{
			$WhereClause = '(( `chapter` = "'.$Assignment['StartChapter'].'" AND   `lineNumber` >= '.$Assignment['StartLine'].')  OR  ( `chapter` = "'.$Assignment['EndChapter'].'" AND   `lineNumber` <= '.$Assignment['EndLine'].')  )  ';
		}
		
	}
	
	$StartId = ((int) SQLQ('SELECT MIN(`id`) FROM `'.$context::BookDB[$title].'` WHERE  `book` = '.$Assignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $Assignment['AddToBeginning']) );
	$EndId = ((int) SQLQ('SELECT MAX(`id`) FROM `'.$context::BookDB[$title].'` WHERE  `book` = '.$Assignment['EndBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $Assignment['SubtractFromEnd']) );
 
	$Lines = SQLQuarry('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `'.$context::BookDB[$title].'` WHERE ( `book` = '.$Assignment['StartBook'].' or  `book` = '.$Assignment['EndBook'].') AND `id` >= '. $StartId .' AND `id` <= '. $EndId .' ORDER BY `book`, `chapter`, `lineNumber`, `id`');

	$HWDefinitionIds = array_map(function($x){return $x['definitionId'];},$Lines);
	$HWDefinitionIds2 = array_map(function($x){return $x['secondaryDefId'];},$Lines);
	$HWDefinitionIds = array_unique(array_merge($HWDefinitionIds, $HWDefinitionIds2));


	$TD = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords`  FROM `'.$context->GetDict().'` WHERE `id` <> 0 and `id` <> -1 and ( `id` = '. implode(" OR `id` = ", $HWDefinitionIds) .')   ORDER BY replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "Ā", "A") , "Ē", "E") , "Ī", "I") , "Ō", "O") , "Ū", "U") , "-", ""), "—, ", "")  COLLATE utf8_general_ci   ', false, "id"); 

	return [
		"Assignment" => $Assignment,
		"Lines" => $Lines,
		"StartID" => $StartId,
		"EndID" => $EndId,
		"Dictionary" => $TD
	];

}

function FindHWByWordID($title, $wordid)
{
	$context = new Context;

	$lev = array_flip($context::LevelDictDB)[$context::DictDB[$title]];
	$hwdb = $context::LevelDB[$lev];

	$HWnums = SQLQuarry('SELECT `HW` FROM `'.$hwdb.'`', true);
	$a = 0;
	
	do
	{
		$a++;
		
		$temp_assignment = ( GetHWAssignment($HWnums[$a], $hwdb, $title)['Lines']);
		$temp_assignment = array_map( function($x){return $x['id'];}, $temp_assignment);
	}
	while (isset($HWnums[$a+1]) && !in_array($wordid, $temp_assignment) );

	return (int) ($a+1);
}

function GetCliticList($dictionary)
{
	$CliticList = array_filter($dictionary, function($word){ return ($word ['entry'][0] == "-");});
	$CliticList = array_map( function($word){return $word['entry'];}, $CliticList) ;
	$CliticList = array_values(array_unique($CliticList));
	

	return array(
		"normal" => $CliticList = array_values(array_unique($CliticList)),
		"no_hyphens" => array_map(function($val) { return ltrim($val, '-');} , $CliticList),
		"no_hyphens_with_dollar_signs" => array_map(function($val) { return ltrim($val, '-')."$";} , $CliticList)
	);
}

function GetFreqTable($defidsarray = null, $HWNum = null, $hwdb = null, $level = null)
{
	$context = new Context;

	if(!isset($HWNum))
	{
		$HWNum = $_GET['hw'];
	}

	if(!isset($hwdb))
	{
		$hwdb = $context->GetHWDB();
	}

	if(!isset($level))
	{
		$level = $context->GetLevel();
	}

	if(!$defidsarray)
	{
		$temp_assignment = ( GetHWAssignment($HWNum, $hwdb)['Lines']);
		$defidsarray = array_map( function($x){return $x['definitionId'];}, $temp_assignment);
		$defidsarray = array_unique($defidsarray);
	}

	$CorrectDictionary = $context::LevelDictDB[$level];

	
	
	$AllTextsClause_Array = [];
	foreach($context::DictDB as $k=>$d)
	{
		if($d == $CorrectDictionary)
		{
			$ProseException = "";
			if(!in_array($k, $context::Poetry))
			{
				$ProseException = " NULL as ";
			}
			array_push($AllTextsClause_Array, "(SELECT `id`, `definitionId`, `secondaryDefId`, ".$ProseException." `Tmesis` FROM `".$context::BookDB[$k]."`)");
		}
	}

	$AllTextsClause = "( ".implode(" UNION ALL ",$AllTextsClause_Array)." ) as `combined`";
	
	$WhereClause = " WHERE 0 ";
	foreach ($defidsarray as $defnumba)
	{
		$WhereClause .= "OR (`definitionId` = ".$defnumba.") ";
	}
	
	$primaryuses = SQLQuarry(' SELECT `definitionId` , SUM(
		CASE WHEN `id` IS NOT NULL
			THEN CASE WHEN `Tmesis` <> 0 THEN 0.5
			ELSE 
				CASE WHEN `IsTwoWords` <> 0 THEN 0.5
					ELSE 1
				END
			END
			ELSE 0
		END
		)
			as `frequency` FROM '.$AllTextsClause.'  INNER JOIN (SELECT `id` as `did`,  `IsTwoWords` FROM `'.$CorrectDictionary.'` ) as `dict` on (`dict`.`did` = `definitionId`)  '.$WhereClause.'  GROUP BY  `definitionId`   ' );
		
	$secondarydefidsarray = SQLQuarry('SELECT `id` FROM `#APDictionary` WHERE `entry` LIKE "-%" ', true);


	$WhereClause = " WHERE 0 ";
	foreach ($secondarydefidsarray as $sdefnumba)
	{
		$WhereClause .= "OR (`secondaryDefId` = ".$sdefnumba.") ";
	}
	
	$secondaryuses =   SQLQuarry(' SELECT `secondaryDefId` , COUNT(`id`) as `frequency` FROM '.$AllTextsClause.'  '.$WhereClause.'  GROUP BY  `secondaryDefId`   ' );
	$uses =  array_merge($primaryuses,  $secondaryuses);

	$freqs = [];
	foreach ($uses as $use)
	{
		if(isset($use['definitionId']))
		{
			$freqs[$use['definitionId']] = ((int) $use['frequency']);
		}
		else if(isset($use['secondaryDefId']))
		{
			$freqs[$use['secondaryDefId']] = ((int) $use['frequency']);
		}
	}

	return $freqs;

}

function GetFrequencyByLevel($definitionIdNumber, $level = "AP")
{

	$context = new Context;

	$usecount = 0;

	foreach($context::DictDB as $t => $d)
	{
		if($d == $context->GetDict())
		{
			$usecount += GetFrequencyByTitle($definitionIdNumber, $t);
		}
	} 
	return $usecount;
}

function GetFrequencyByTitle($definitionIdNumber, $title)
{
	$context = new Context;
	
	$TwoWordCheck = SQLQ('SELECT `IsTwoWords` FROM `'.$context::DictDB[$title].'` WHERE `id` = ' . $definitionIdNumber);
	$uses = SQLQ('SELECT COUNT(`id`) FROM `'.$context::BookDB[$title].'` WHERE `definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber );
	
	if($title == "Aeneid")
	{
		$Tmesis = SQLQ('SELECT COUNT(`id`) FROM `'.$context::BookDB[$title].'` WHERE (`definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber . ') and `Tmesis` = 1 ');
		$uses = (($uses - ($Tmesis/2)));
	}

	$uses = ((int)$uses / ((((int) $TwoWordCheck) + 1))  );
	
	return ((int) $uses)  ;
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

function StripMacrons($inputText)
{
	$StripMacronsArray = [
		"ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
		"Ā" => "A", "Ē" => "E", "Ī" => "I", "Ō" => "O", "Ū" => "U", "Ȳ" => "Y"
	];

	$nomacronsarray = preg_split('/(?!^)(?=.)/u', $inputText);
	$nomacronstext = implode("",array_map(function($x) use($StripMacronsArray)
	{		
		return (isset($StripMacronsArray[$x])) ?  $StripMacronsArray[$x] : $x;
	}, $nomacronsarray)); 
	
	return $nomacronstext;
}

function DisplayNotesText($hwstart, $hwend, $hwassignment, $title, $literaryDevices = true)
{
	$context = new Context;

	if($title == "")
	{
		$title= $context->GetBookTitle();
	}
	

	$WordNotes = SQLQuarry(' SELECT `'.$context->GetNotesDB().'Locations`.`NoteId`, `AssociatedWordId`,   `'.$context->GetNotesDB().'Text`.`Text`, `BookTitle`, `sub`.`word`,`sub`.`book`,`sub`.`chapter`, `sub`.`lineNumber` FROM `'.$context->GetNotesDB().'Locations` INNER JOIN `'.$context->GetNotesDB().'Text` ON (`'.$context->GetNotesDB().'Text`.`NoteId` = `'.$context->GetNotesDB().'Locations`.`NoteId`) INNER JOIN (SELECT `id`, `book`,`chapter`, `word`,`lineNumber`   FROM `'.$context->GetTextDB().'`) as `sub` ON (`sub`.`id` = `AssociatedWordId` )  WHERE (`sub`.`id` >= '.$hwstart.' AND `sub`.`id` <= '.$hwend.') AND `BookTitle` = "'.$title.'" AND `AssociatedLineCitation` = "" ORDER BY `AssociatedWordId`');

	
	if($title != "Aeneid")
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.',`sub`.`chapter`, '.', `sub`.`lineNumber`)";
	}
	else
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.', `sub`.`lineNumber`)";
	}
	

	$LineNotes = SQLQuarry('SELECT `'.$context->GetNotesDB().'Locations`.`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `'.$context->GetNotesDB().'Text`.`Text`, `BookTitle`, `book`, `chapter`, `lineNumber` FROM `'.$context->GetNotesDB().'Locations` INNER JOIN `'.$context->GetNotesDB().'Text` ON (`'.$context->GetNotesDB().'Text`.`NoteId` = `'.$context->GetNotesDB().'Locations`.`NoteId`) LEFT JOIN (SELECT `id`, `book`, `chapter`, `lineNumber` FROM `'.$context->GetTextDB().'`) as `sub` ON ( `AssociatedLineCitation` =  '.$ConcatText.'   ) WHERE (`sub`.`id` >= '.$hwstart.' AND `sub`.`id` <= '.$hwend.') AND `BookTitle` = "'.$title.'" ORDER BY `AssociatedLineCitation`, `lineNumber`');
	
	$CondensedNotes = array();

	// print_r($WordNotes);
	// print_r($LineNotes);


	foreach($WordNotes as $note)
	{
		$templinecitation = "";
		if($note["chapter"] == "" || $note["chapter"] == NULL || !isset($note["chapter"]))
		{
			$templinecitation = ($note["lineNumber"]) ;
		}
		else
		{
			$templinecitation = ($note["chapter"].".".$note["lineNumber"]) ;
		}

		if(!isset($CondensedNotes[$note["NoteId"]]))
		{
			$CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => array($note["AssociatedWordId"]));
			$CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
			$CondensedNotes[$note["NoteId"]]["WL"] = "Word";
			$CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
			$CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
			$CondensedNotes[$note["NoteId"]]["LastWordId"] = $note["AssociatedWordId"];
			$CondensedNotes[$note["NoteId"]]["phrase"] = $note["word"]; 
			$CondensedNotes[$note["NoteId"]]["lines"] = array($templinecitation); 
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
			array_push($CondensedNotes[$note["NoteId"]]["AssociatedWordId"], $note["AssociatedWordId"]);

			array_push($CondensedNotes[$note["NoteId"]]["lines"], $templinecitation); 
			$CondensedNotes[$note["NoteId"]]["lines"] = array_unique ($CondensedNotes[$note["NoteId"]]["lines"]); 
			sort($CondensedNotes[$note["NoteId"]]["lines"]);
		}
	}

	foreach($LineNotes as $note)
	{
		$templinecitation = "";
		if($note["chapter"] == "" || $note["chapter"] == NULL || !isset($note["chapter"]))
		{
			$templinecitation = ($note["lineNumber"]) ;
		}
		else
		{
			$templinecitation = ($note["chapter"].".".$note["lineNumber"]) ;
		}

		if(!isset($CondensedNotes[$note["NoteId"]]))
		{
			$CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => $templinecitation);
			$CondensedNotes[$note["NoteId"]]["WL"] = "Line";
			$CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
			$CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
			$CondensedNotes[$note["NoteId"]]["phrase"] = "";
			$CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
			$CondensedNotes[$note["NoteId"]]["lines"] = array($templinecitation); 
			$CondensedNotes[$note["NoteId"]]["comparableCitation"] = $note["AssociatedWordId"]; 

		}
		else
		{
			array_push($CondensedNotes[$note["NoteId"]]["lines"], $templinecitation);
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
		$outputText .= "<note ";
		$outputText .= "noteid = '" . $Cnote["NoteId"] . "'";
		$outputText .= "cc = '".$Cnote["comparableCitation"]."' associatedwords = '".( gettype($Cnote["AssociatedWordId"]) == "array" ? implode(",", $Cnote["AssociatedWordId"]): 0)."' >";
		$linestext = count($Cnote["lines"]) > 1 ? min($Cnote["lines"]) . "–" .  max($Cnote["lines"]) :   $Cnote["lines"][0];

		if($lastLinesText != $linestext)
		{
			$outputText .= "<span style = 'font-family:Cinzel'>". $linestext ." </span>";
		}
		else
		{
			$outputText .= "<span style = 'color:rgba(0,0,0,0); font-family:Cinzel'>". $linestext ." </span>";
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
		$freq = GetFrequencyByLevel($entry['id']);
	
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
	$context = new Context;

	$Frequencies = GetFreqTable();


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

	$CliticList = GetCliticList($dictionary);

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
			
			preg_match('/('. implode("|", $CliticList['no_hyphens_with_dollar_signs']). ')/', $Noclitics, $clitics);
			$Clitic = $clitics[0];

			$Noclitics = mb_ereg_replace('('. implode("|", $CliticList['no_hyphens_with_dollar_signs']). ')',"",$Noclitics);


			$SplitPos = preg_match('/('. implode("|", $CliticList['no_hyphens']). ')[.!;,]?$/', $word['word'], $position, PREG_OFFSET_CAPTURE);
			$split1 = substr($word['word'], 0, $position[0][1] );
			$split2 = substr($word['word'], $position[0][1]);
		}

		if($showvocab == true)
		{
			$outputtext .= "<word  baseword = '".$Noclitics."' clitic = '".$Clitic."' defintionid = '".$word['definitionId']."' wordid = '".$word['id']."' id = '".$word['id']."' frequency = '". GetFrequencyByLevel($word['definitionId'], $context->GetLevel())."' reveal = ";
			
			if(isset($_GET['highlightedword']) && ( ((int) $_GET['highlightedword']) == ((int) $word['id']) ) )
			{
				$outputtext .= "'true'";
			}
			else
			{
				$outputtext .= "'false'";
			}
			
			$outputtext .= " >";
			
			$outputtext .= "<baseword>";
				
				$outputtext .= "<text>";
					$outputtext .= $split1;
				$outputtext .= "</text>";
			$outputtext .= "<nomacrons>";
				$outputtext .= StripMacrons($split1);
			$outputtext .= "</nomacrons>";

				
				$outputtext .= "<entry>";
					$outputtext .= $dictionary[$word['definitionId']]['entry'];
				$outputtext .= "</entry>";

				$outputtext .= "<definition>";
				$outputtext .= "<i>";

					$tempdeftext = $dictionary[$word['definitionId']]['definition'];	
					$tempdeftext = preg_replace("/\*(.*?)\*/","</i>\\1<i>", $tempdeftext);				
					$outputtext .= $tempdeftext;

				$outputtext .= "</i>";
				$outputtext .= "</definition>";
				

			$outputtext .= "</baseword>";

			if($word["secondaryDefId"] != -1)
			{
				$outputtext .= "<clitic>";
				
					$outputtext .= "<text>";
						$outputtext .= $split2;
					$outputtext .= "</text>";
					$outputtext .= "<nomacrons>";
						$outputtext .= StripMacrons($split2);
					$outputtext .= "</nomacrons>";

					$outputtext .= "<entry>";
						$outputtext .= $dictionary[$word['secondaryDefId']]['entry'];
					$outputtext .= "</entry>";

					$outputtext .= "<definition>";
					$outputtext .= "<i>";

						$tempdeftext = $dictionary[$word['secondaryDefId']]['definition'];	
						$tempdeftext  = preg_replace("/\*(.*?)\*/","</i>\\1<i>", 	$tempdeftext);					
						$outputtext .= $tempdeftext;

					$outputtext .= "</i>";
					$outputtext .= "</definition>";

				$outputtext .= "</clitic>";
			}

				$outputtext .= "<freq>"; 
					
					$outputtext .= "<a target = '_blank' href = 'WordViewer.php?level=".$context->GetLevel()."&wordid=". $word['definitionId'] . "'>";
					$outputtext .= ((int) $Frequencies[$word['definitionId']]);
					$outputtext .= "</a>";
					
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