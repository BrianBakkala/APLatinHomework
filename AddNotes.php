<TITLE>AP Latin Homework Viewer</TITLE>

<?php 	$CSSsrc = "GlobalStyles.css"; echo '<li'.'nk rel="stylesheet" type="text/css" href="'.$CSSsrc.'?'. rand(1, 100000)  ."00".date("U")."00".'">';  ?>
<STYLE>
	html {
		text-align: center;
		-webkit-tap-highlight-color:  rgba(255, 255, 255, 0); 
	}

	h1 { 
		font-size: 24pt;
		display:inline-block;
		margin-block-end: 0em;
	}

	line {

		display: block;
		padding-bottom: 1.5em;
	}

	line::before {
		content: attr(citation);
		top:17.6pt;
		position:relative;
		font-size: 12pt;
		vertical-align: middle;
		text-align: center;
		padding: 8px;
		color:gray;
		
	}

	word {
		cursor: pointer;
		display: inline-block;
		padding: 6.5px;
		vertical-align: top;
	}

	word:hover {
		border-radius: 8px;
		background-color: cornsilk;
	}

	text {
		font-size: 24pt;
		display: inline-block;
		text-align: center;
		padding-bottom: 6px;
	}


	entry {
		font-weight: bold;
		display: block;
		text-align: center;
	}

	definition {
		padding-left: 3px;
		font-style: italic;
		text-align: center;
		display: block;
	}

	entry,
	definition {
		-webkit-transition: .25s all ease-in-out; 
		transition: .25s all ease-in-out;
		/*transition-delay: .5s*/
		font-size: 0;
	}


	baseword,
	clitic {
		display: inline-block;
	}


	word[reveal="true"] entry,
	word[preview="true"] entry
	{
		border-top: 1px solid lightgray;
	}


	word[reveal="true"] baseword,
	word[reveal="true"] clitic
	{
		border-right: 2px solid darkgray;
	}

	word[reveal="true"] baseword
	{
		border-left: 2px solid darkgray;
	}

	
	word[preview="true"] baseword,
	word[preview="true"] clitic
	{
		border-right: 2px solid darkgray;
	}

	word[preview="true"] baseword
	{
		border-left: 2px solid darkgray;
	}


	word[reveal="true"] entry,
	word[preview="true"] entry,
	word[reveal="true"] definition,
	word[preview="true"] definition
	{
		font-size: 18pt;

	}

	word[reveal="true"] baseword,
	word[preview="true"] baseword,
	word[reveal="true"] clitic,
	word[preview="true"] clitic
	{
		padding: 5px;
	}

	word[reveal="true"]:not([clitic=""]) clitic text::before,
	word[preview="true"]:not([clitic=""]) clitic text::before
	{
		content: "-";
	}
	
	freq {
		color: rgba(0, 0, 0, 0);
		display: block;
	}

	freq a {
		color: inherit;
    cursor: pointer;
    text-decoration: none;
	}

	freq a:hover {
		background-color: darkgray;
 
	}


	word:hover freq {

		color: rgba(0, 0, 0, 1);
	}

	#rightarrow,
	#leftarrow {
		height: 2.5em;
		display:inline-block;

		padding-left:1em;
		padding-right:1em;

	}

	#leftarrow {
		transform: scaleX(-1);
	}

	assignment, vocab, notes
	{
		display:inline-block;
	}
	vocab, notes
	{	
		font-size:0;
		vertical-align: top;
		text-align:left;
		padding-left:10px;
		-webkit-transition: .25s all ease-in-out; 
		transition: .25s all ease-in-out;
	}

	wrapper[showvocab="true"] vocab
	{
		font-size:14px;
		
	}

	wrapper[shownotes="true"] notes
	{
		font-size:14px;
		
	}

	vocabword
	{
		display:block;
	}

	
</STYLE>


<?php


if(!isset($_GET['hw']))
{
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
	header('Location: $actual_link'.'?hw=1');
}

require_once ( 'SQLConnection.php');
require_once ( 'QuizletExport.php');

$HWAssignment = SQLQuarry('SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author`, `AddToBeginning`, `SubtractFromEnd`  FROM `#APHW` WHERE `HW` = ' . (int)$_GET['hw'])[0]  ;
// var_dump($HWAssignment);

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

$BookTitle = "Aeneid";
if($HWAssignment['Author'] == "C")
{
	$BookTitle = "DBG";	
}

// echo ('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `#AP'.$BookTitle .'Text` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`');

$HWStartId = ((int) SQLQ('SELECT MIN(`id`) FROM `#AP'.$BookTitle .'Text` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $HWAssignment['AddToBeginning']) );
$HWEndId = ((int) SQLQ('SELECT MAX(`id`) FROM `#AP'.$BookTitle .'Text` WHERE  `book` = '.$HWAssignment['EndBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`') - ((int) $HWAssignment['SubtractFromEnd']) );
$HWLines = SQLQuarry('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `#AP'.$BookTitle .'Text` WHERE ( `book` = '.$HWAssignment['StartBook'].' or  `book` = '.$HWAssignment['EndBook'].') AND `id` >= '. $HWStartId .' AND `id` <= '. $HWEndId .' ORDER BY `book`, `chapter`, `lineNumber`, `id`');


$HWDefinitionIds = array_map(function($x){return $x['definitionId'];},$HWLines);
$HWDefinitionIds2 = array_map(function($x){return $x['secondaryDefId'];},$HWLines);
$HWDefinitionIds = array_unique(array_merge($HWDefinitionIds, $HWDefinitionIds2));

$TargetedDictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` , (SELECT COUNT(*) FROM `#AP'.$BookTitle.'Text` WHERE `definitionId` = `#APDictionary`.`id` OR `secondaryDefId` = `#APDictionary`.`id`) as `APfrequency` FROM `#APDictionary` WHERE `id` <> 0 and `id` <> -1 and ( `id` = '. implode(" OR `id` = ", $HWDefinitionIds) .')   ORDER BY replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "Ā", "A") , "Ē", "E") , "Ī", "I") , "Ō", "O") , "Ū", "U") , "-", ""), "—, ", "")  COLLATE utf8_general_ci   ', false, "id");

// echo QuizletExport($TargetedDictionary);



echo '<a target = "_blank" href = "https://github.com/BrianBakkala/APLatinHomework">';
echo '<svg height="32" class="octicon octicon-mark-github" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true"><path fill-rule="oddeven" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z" fill="black"></path></svg>';
echo "</a>";
if($_GET['hw'] != "1")
{

	$PrevHW = SQLQ('SELECT MAX(`HW`) FROM `#APHW` WHERE `HW` < ' . $_GET['hw'] );
	echo "<A href = 'AddNotes.php?hw=".$PrevHW."'>";
	echo "<IMG id = 'leftarrow' SRC = 'Images/LHarrow.png'>";
	echo "</A>";
	
}

echo "<h1>";
	echo "HW ".$HWAssignment['HW']." | ";
	echo "<i>";
	if($HWAssignment['Author'] == "C")
	{
		echo "Dē Bellō Gallicō";
	}
	else
	{
		echo "Aeneid";
	}
	echo "</i> ";

	echo $HWAssignment['StartBook'];
	echo ".";
	if($HWAssignment['StartChapter'] != null)
	{
		echo $HWAssignment['StartChapter'];
		echo ".";
	}
	echo $HWAssignment['StartLine'];

	echo "–";
	echo $HWAssignment['EndBook'];
	echo ".";
	if($HWAssignment['EndChapter'] != null)
	{
		echo $HWAssignment['EndChapter'];
		echo ".";
	}
	echo $HWAssignment['EndLine'];

echo "</h1>";



if($_GET['hw'] != SQLQ('SELECT MAX(`HW`) FROM `#APHW` '))
{


	$NextHW = SQLQ('SELECT Min(`HW`) FROM `#APHW` WHERE `HW` > ' . $_GET['hw'] );

	echo "<A href = 'AddNotes.php?hw=".$NextHW."'>";
	echo "<IMG id = 'rightarrow' SRC = 'Images/LHarrow.png'>";
	echo "</A>";
	
}





echo "<BR>";

echo "<duedate style = 'color:rgba(0,0,0,0);' id = 'dueDate'>[Due Date]";
echo "</duedate>";
echo " | ";

echo "<A   href = 'https://aplatin.altervista.org/UnitsViewer.php'>";
echo "Units";
echo "</A>";
echo " | ";

echo "<A target = '_blank' href = 'https://aplatin.altervista.org/Dictionary.php'>";
echo "Dictionary";
echo "</A>";
echo " | ";

echo "<A target = '_blank' href = 'https://quizlet.com/MrBakkala/folders/ap-latin-vocab/sets'>";
echo "Quizlet";
echo "</A>";
echo " | ";

echo "<select onchange= 'SetDifficulty(this.value)'>";


	echo "<option value='0' selected disabled hidden> ";
	echo "Difficulty";
	echo "</option> ";

	echo "<option value = '500'>";
	echo "Absolute Scrub";
	echo "</option>";

	echo "<option value = '30'>";
	echo "ezpz 🍋 squeezy";
	echo "</option>";

	echo "<option value = '20'>";
	echo "Easy";
	echo "</option>";

	echo "<option value = '10'>";
	echo "Medium";
	echo "</option>";

	echo "<option value = '5'>";
	echo "Hard";
	echo "</option>";

	echo "<option value = '3'>";
	echo "I am a professional Latin translator";
	echo "</option>";

	echo "<option value = '1'>";
	echo "I am the Roman God of Latin";
	echo "</option>";

	echo "<option value = '0'>";
	echo "I literally think in Latin";
	echo "</option>";

echo "</select>";

echo " | ";
echo "<a style = 'cursor:pointer;' onclick = 'document.getElementsByTagName(\"wrapper\")[0].setAttribute(\"shownotes\", document.getElementsByTagName(\"wrapper\")[0].getAttribute(\"shownotes\") == \"true\" ?  \"false\" : \"true\" )'>";
echo "Notes Sidebar";
echo "</a>";



echo " | ";
echo "<a style = 'cursor:pointer;' onclick = 'document.getElementsByTagName(\"wrapper\")[0].setAttribute(\"showvocab\", document.getElementsByTagName(\"wrapper\")[0].getAttribute(\"showvocab\") == \"true\" ?  \"false\" : \"true\" )'>";
echo "Vocab Sidebar";
echo "</a>";

echo "<HR style = 'border-top: 1px solid #eee;'>";


echo "<form id = 'submitNotesForm'>";
echo "WORDS<input id = 'submitNoteWords' type = 'text' size = '40'>";
echo "LINES<input id = 'submitNoteLines' type = 'text' size = '40'>";
echo "<BR>";
echo "<BR>";
echo "<textarea id = 'submitNoteNote' cols = 40 rows = 2></textarea>";
echo "<input id = 'submitNoteSubmit' onclick = 'AddNote()' type = 'button' value = 'Submit'>";
echo "<BR>";
echo "<BR>";
echo "<span id = 'previewNoteWords'></span>";
echo "</form>";

echo "<HR style = 'border-top: 1px solid #eee;'>";

$ChapterCitationText = "";
if($HWAssignment['StartChapter'] != null)
{
	$ChapterCitationText = $HWAssignment['StartChapter'] . "."; 
}

echo "<wrapper shownotes = 'true'>";

echo "<assignment>";


if($HWAssignment['AddToBeginning'] > 0)
{
	$temp_start_line = $HWAssignment['StartLine'] -1 ;
}
else
{
	$temp_start_line = $HWAssignment['StartLine'];
}
echo "<line citation = '".$HWAssignment['StartBook'].".".$ChapterCitationText.$temp_start_line."' num = '".$temp_start_line."'>";
foreach ($HWLines as $word)
{
	if($CurrentLine && $word['lineNumber'] != $CurrentLine)
	{
		$ChapterCitationText = "";
		if($HWAssignment['StartChapter'] != null)
		{
			$ChapterCitationText = $word['chapter']."."; 
		}

		echo " <span style = 'cursor:pointer;' onclick = 'TypeLine(this)'>(+)</span></line><line   citation = '".$word['book'].".".$ChapterCitationText.$word['lineNumber']."'    num = '".$word['lineNumber']."'>";
	}
	$CurrentLine = $word['lineNumber'];

	$Noclitics =$word['word'];
	$Noclitics = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$Noclitics);
	$Clitic = "";
	$split1 = $word['word'];
	if($word["secondaryDefId"] != -1)
	{
		preg_match('/(que$|ne$|ve$|cum$)/', $Noclitics, $clitics);
		$Clitic = $clitics[0];

		$Noclitics = mb_ereg_replace("(que$|ne$|ve$|cum$)","",$Noclitics);


		$SplitPos = preg_match('/(que|ne|ve|cum)[.!;,]?$/', $word['word'], $position, PREG_OFFSET_CAPTURE);
		$split1 = substr($word['word'], 0, $position[0][1] );
		$split2 = substr($word['word'], $position[0][1]);
	}


	echo "<word idnum = '".$word['id']."' fullword = '".$word['word']."' baseword = '".$Noclitics."' clitic = '".$Clitic."' AP-frequency = '".$TargetedDictionary[$word['definitionId']]['APfrequency']."' reveal = 'false'  >";
		
		echo "<baseword>";
			
			echo "<text>";
				echo $split1;
			echo "</text>";
		
			echo "<entry>";
				echo $TargetedDictionary[$word['definitionId']]['entry'];
			echo "</entry>";

			echo "<definition>";
				echo $TargetedDictionary[$word['definitionId']]['definition'];
			echo "</definition>";

		echo "</baseword>";
 
		if($word["secondaryDefId"] != -1)
		{
			echo "<clitic>";
			
				echo "<text>";
					echo $split2;
				echo "</text>";
		
				echo "<entry>";
					echo $TargetedDictionary[$word['secondaryDefId']]['entry'];
				echo "</entry>";

				echo "<definition>";
					echo $TargetedDictionary[$word['secondaryDefId']]['definition'];
				echo "</definition>";

			echo "</clitic>";
		}

			echo "<freq>"; 
			echo "<a onclick = 'document.getElementById(\"submitNoteWords\").value += \", ".$word['id']."\"'>";

			if($BookTitle != "DBG")
			{
				$uses  = SQLQuarry('SELECT `id`, `book`, `lineNumber`, `word` FROM `#APAeneidText` WHERE `definitionId` = ' .$word['definitionId'] . '   OR  `secondaryDefId` = ' .$word['definitionId'] . '  ORDER BY `book`, `lineNumber`, `id` ');
				$Tmesis  = SQLQuarry('SELECT `id` FROM `#APAeneidText` WHERE (`definitionId` = ' .$word['definitionId'] . '   OR  `secondaryDefId` = ' .$word['definitionId'] . ') and `Tmesis` = 1  ');
			}
			else
			{
				$uses = SQLQuarry('SELECT `id`, `book`, `chapter`, `lineNumber`, `word` FROM `#APDBGText` WHERE `definitionId` = ' .$word['definitionId'] . '   OR  `secondaryDefId` = ' .$word['definitionId'] . '  ORDER BY `book`, `chapter`, `lineNumber`, `id` ');
			}
			// var_dump($Tmesis);
			
			echo ((count($uses) - (count($Tmesis)/2)) /( 1+(int) $TargetedDictionary[$word['definitionId']]['IsTwoWords'] ));
			echo "</a>";
		echo "</freq>";
		
	echo "</word>";
}
echo "<span style = 'cursor:pointer;' onclick = 'TypeLine(this)'>(+)</span></line>";
echo "</assignment>";
echo "<vocab>";

	//[id] => 1 [entry] => -que [definition] => and [IsTwoWords] => 0 [APfrequency] => 277 

	foreach($TargetedDictionary as $entry)
	{
		echo "<vocabword id = '".$entry['id']."' >";
			echo "<span style = 'font-weight:bold;'>";
			echo $entry['entry'];
			echo "</span>";
			echo " ";
			echo "<span style = 'font-style:italic;'>";
			echo $entry['definition'];
			echo "</span>";

			if($BookTitle != "DBG")
			{
				$uses  = SQLQuarry('SELECT `id`, `book`, `lineNumber`, `word` FROM `#APAeneidText` WHERE `definitionId` = ' . $entry['id'] . '   OR  `secondaryDefId` = ' . $entry['id'] . '  ORDER BY `book`, `lineNumber`, `id` ');
				$Tmesis  = SQLQuarry('SELECT `id`   FROM `#APAeneidText` WHERE (`definitionId` = ' . $entry['id'] . '   OR  `secondaryDefId` = ' . $entry['id'] . ') AND `Tmesis` = 1 ');
			}
			else
			{
				$uses = SQLQuarry('SELECT `id`, `book`, `chapter`, `lineNumber`, `word` FROM `#APDBGText` WHERE `definitionId` = ' . $entry['id'] . '   OR  `secondaryDefId` = ' . $entry['id'] . '  ORDER BY `book`, `chapter`, `lineNumber`, `id` ');
				$Tmesis = [];
			}
			echo " ";

			echo "<span>(";
			// var_dump($Tmesis);
				echo ((count($uses) - (count($Tmesis)/2)) /( 1+(int) $entry['IsTwoWords'] ));
			echo ")</span>";


		echo "</vocabword>";
	}

echo "</vocab>";


function ParseNoteText($inputText)
{
	$outputText = "";


	$literaryDevices = SQLQuarry('SELECT `Device` FROM `#APLiteraryDevices`', true);
	$literaryDevices = array_map('strtolower', $literaryDevices);
	
	$outputText = preg_replace("/\*(".implode('|', $literaryDevices ).")\*/","<a href = 'LiteraryDevices.php?device=\\1'><span style = 'font-weight:bold; font-variant: small-caps;'>\\1</span></a>",$inputText);

	return $outputText;
}

echo "<notes>";
	// echo $HWStartId ;
	// echo $HWEndId ;

	$WordNotes = SQLQuarry(' SELECT `#APNotesLocations`.`NoteId`, `AssociatedWordId`,   `#APNotesText`.`Text`, `Author`, `sub`.`word`,`sub`.`book`,`sub`.`chapter`, `sub`.`lineNumber` FROM `#APNotesLocations` INNER JOIN `#APNotesText` ON (`#APNotesText`.`NoteId` = `#APNotesLocations`.`NoteId`) INNER JOIN (SELECT `id`, `book`,`chapter`, `word`,`lineNumber`   FROM `#AP'.$BookTitle .'Text`) as `sub` ON (`id` = `AssociatedWordId` )  WHERE (`sub`.`id` >= '.$HWStartId.' AND `sub`.`id` <= '.$HWEndId.') AND `AssociatedLineCitation` = "" ORDER BY `AssociatedWordId`');
	
	if($HWAssignment['Author'] == "C")
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.',`sub`.`chapter`, '.', `sub`.`lineNumber`)";
	}
	else
	{
		$ConcatText = "CONCAT(`sub`.`book`, '.', `sub`.`lineNumber`)";
	}
	

	$LineNotes = SQLQuarry('SELECT `#APNotesLocations`.`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `#APNotesText`.`Text`, `Author`, `book`, `chapter`, `lineNumber` FROM `#APNotesLocations` INNER JOIN `#APNotesText` ON (`#APNotesText`.`NoteId` = `#APNotesLocations`.`NoteId`) LEFT JOIN (SELECT `id`, `book`, `chapter`, `lineNumber` FROM `#AP'.$BookTitle .'Text`) as `sub` ON ( `AssociatedLineCitation` =  '.$ConcatText.'   ) WHERE (`sub`.`id` >= '.$HWStartId.' AND `sub`.`id` <= '.$HWEndId.') GROUP BY `AssociatedLineCitation`');



	$CondensedNotes = array();

	// print_r($WordNotes);
	// print_r($LineNotes);


	foreach($WordNotes as $note)
	{
		if(!isset($CondensedNotes[$note["NoteId"]]))
		{
			$CondensedNotes[$note["NoteId"]] = array(["AssociatedWordId"] => $note["AssociatedWordId"]);
			$CondensedNotes[$note["NoteId"]]["WNLN"] = "WN";
			$CondensedNotes[$note["NoteId"]]["Author"] = $note["Author"];
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
				$CondensedNotes[$note["NoteId"]]["phrase"] .= " ... ". $note["word"]; 
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
			$CondensedNotes[$note["NoteId"]] = array(["AssociatedWordId"] => $note["AssociatedWordId"]);
			$CondensedNotes[$note["NoteId"]]["WNLN"] = "LN";
			$CondensedNotes[$note["NoteId"]]["Author"] = $note["Author"];
			$CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
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

		$a = $a["comparableCitation"];
		$b = $b["comparableCitation"];
		
		if($a != $b)
		{
			return $a < $b ? -1 : 1;
		}

		return 0;
			
	});

	foreach($CondensedNotes as $Cnote)
	{
		echo "<note >";
		$linestext = count($Cnote["lines"]) > 1 ? min($Cnote["lines"]) . "–" .  max($Cnote["lines"]) :   $Cnote["lines"][0];
		echo "<span style = 'font-family:Trajan'>". $linestext ." </span>";
		echo "<B>". preg_replace("/[;,:]/","", $Cnote['phrase']) ." </B>";
		echo ParseNoteText($Cnote['Text']);
		echo "</note> ";
		// echo implode("|", $Cnote['comparableCitation']);
		echo "<BR>";
	}


echo "</notes>";

echo "</wrapper>";







?>

<body onload = "GetAPLatinHW();">

<script>

function SetDifficulty(occurenceThreshold)
{
	words = document.getElementsByTagName('assignment')[0].getElementsByTagName('word')
	for (w=0; w<words.length; w++)
	{
		if((+words[w].getAttribute('ap-frequency')) <= (+occurenceThreshold))
		{
			words[w].setAttribute('reveal', 'true')
		}
		else
		{
			words[w].setAttribute('reveal', 'false')
		}
	}
}
// alert(navigator.msMaxTouchPoints)
words = document.getElementsByTagName('word')

for (i=0; i <words.length; i++ )
{
	words[i].onclick = function()
	{
		document.getElementById("submitNoteWords").value += ","+this.getAttribute('idnum')
		document.getElementById("previewNoteWords").innerText += " "+this.getAttribute('fullword')
		document.getElementById("submitNoteNote").focus();
	}
	// words[i].onmouseover = function()
	// {
	// 	this.setAttribute("preview", ("true"))
	// }
	// words[i].onmouseout = function()
	// {
	// 	this.setAttribute("preview", ("false"))
	// }

	words[i].ontouchstart = function()
	{
		this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"))
		
		for (i=0; i <words.length; i++ )
		{
			words[i].onclick = function(){}
			words[i].onmouseover = function(){}
			words[i].onmouseout = function(){}

			words[i].ontouchstart = function()
			{
				this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"))
			}

		}


	}
}

function GetAPLatinHW()
{
	SpreadsheetDocID = "1CKcfxPCIV2Kz7b7QAbhK6JJ5kroxVdZoreGDXvngjS8"
 
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				SheetData = (JSON.parse(Response).feed.entry)
				
				sd = 0;
				HWFound = false;
				
				while ( sd < SheetData.length && !HWFound  )
				{
					
					if((SheetData[sd].title["$t"]).startsWith("A") && SheetData[sd].content["$t"].endsWith("<?php echo $_GET['hw']; ?>"))
					{
						DueDate = (SheetData[sd+1].content["$t"])
						HWFound = true;

						document.getElementById('dueDate').innerText = "" + DueDate + "" 
						document.getElementById('dueDate').style.color = 'black'
					}
					sd++
				}
			}
		};
		xmlhttp.open("GET", "https://spreadsheets.google.com/feeds/cells/" + SpreadsheetDocID + "/1/public/values?alt=json", true);
		
		xmlhttp.send();
 

}



function AddNote()
{
		NoteWords = document.getElementById('submitNoteWords').value
		NoteLines = document.getElementById('submitNoteLines').value
		NoteText = document.getElementById('submitNoteNote').value

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')

				NoteWords = document.getElementById('submitNoteWords').value = "";
				NoteLines = document.getElementById('submitNoteLines').value = "";
				NoteText = document.getElementById('submitNoteNote').value = "";
			}
		};

		XMLURL = "AJAXAPL.php?addnote=true&author=<?php echo $HWAssignment['Author'];?>&notetext="+NoteText+"&wordids=" + NoteWords+"&linecitations=" + NoteLines;
		xmlhttp.open("GET", XMLURL, true);
		xmlhttp.send();
		console.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
		// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;
	

}


function TypeLine(ele)
{
	document.getElementById("submitNoteLines").value += "," + ele.parentElement.getAttribute('citation')
}






















</script>
<BR><BR><BR><BR><BR><BR><BR><BR>