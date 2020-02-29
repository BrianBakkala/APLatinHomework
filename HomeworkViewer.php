<TITLE>AP Latin Homework Viewer</TITLE>


<STYLE>
	html {
		text-align: center;
		font-family: "Palatino Linotype";
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
		font-size: 0;
	}


	baseword,
	clitic {
		display: inline-block;
	}


	word[reveal="true"] entry {
		border-top: 1px solid lightgray;
	}

	word[reveal="true"] baseword,
	word[reveal="true"] clitic {
		border-right: 2px solid darkgray;
	}

	word[reveal="true"] baseword {
		border-left: 2px solid darkgray;
	}


	word[reveal="true"] entry,
	word[reveal="true"] definition {
		font-size: 18pt;

	}

	word[reveal="true"] baseword,
	word[reveal="true"] clitic {
		padding: 5px;
	}

	word[reveal="true"]:not([clitic=""]) clitic text::before {
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







	
</STYLE>


<?php

 
if(!isset($_GET['hw']))
{
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
	header('Location: $actual_link'.'?hw=1');
}

require_once ( 'SQLConnection.php');
 
$HWAssignment = SQLQuarry('SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author` FROM `#APHW` WHERE `HW` = ' . (int)$_GET['hw'])[0]  ;
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
$HWLines = SQLQuarry('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `#AP'.$BookTitle .'Text` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`');

$HWDefinitionIds = array_map(function($x){return $x['definitionId'];},$HWLines);
$HWDefinitionIds2 = array_map(function($x){return $x['secondaryDefId'];},$HWLines);
$HWDefinitionIds = array_unique(array_merge($HWDefinitionIds, $HWDefinitionIds2));

$TargetedDictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` , (SELECT COUNT(*) FROM `#AP'.$BookTitle.'Text` WHERE `definitionId` = `#APDictionary`.`id` OR `secondaryDefId` = `#APDictionary`.`id`) as `APfrequency` FROM `#APDictionary` WHERE `id` = '. implode(" OR `id` = ", $HWDefinitionIds) .' ', false, "id");

echo '<a target = "_blank" href = "https://github.com/BrianBakkala/APLatinHomework">';
echo '<svg height="32" class="octicon octicon-mark-github" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true"><path fill-rule="oddeven" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z" fill="black"></path></svg>';
echo "</a>";
if($_GET['hw'] != "1")
{

	$PrevHW = SQLQ('SELECT MAX(`HW`) FROM `#APHW` WHERE `HW` < ' . $_GET['hw'] );
	echo "<A href = 'HomeworkViewer.php?hw=".$PrevHW."'>";
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

$NextHW = SQLQ('SELECT Min(`HW`) FROM `#APHW` WHERE `HW` > ' . $_GET['hw'] );

echo "<A href = 'HomeworkViewer.php?hw=".$NextHW."'>";
echo "<IMG id = 'rightarrow' SRC = 'Images/LHarrow.png'>";
echo "</A  >";

echo "<BR>";

echo "<span style = 'color:rgba(0,0,0,0);' id = 'dueDate'>[Due Date]";
echo "</span>";

echo "<HR style = 'border-top: 1px solid #eee;'>";

$ChapterCitationText = "";
if($HWAssignment['StartChapter'] != null)
{
	$ChapterCitationText = $HWAssignment['StartChapter'] . "."; 
}



echo "<line citation = '".$HWAssignment['StartBook'].".".$ChapterCitationText.$HWAssignment['StartLine']."' num = '".$HWAssignment['StartLine']."'>";
foreach ($HWLines as $word)
{
	if($CurrentLine && $word['lineNumber'] != $CurrentLine)
	{
		$ChapterCitationText = "";
		if($HWAssignment['StartChapter'] != null)
		{
			$ChapterCitationText = $word['chapter']."."; 
		}

		echo "</line><line   citation = '".$word['book'].".".$ChapterCitationText.$word['lineNumber']."'    num = '".$word['lineNumber']."'>";
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


	echo "<word  baseword = '".$Noclitics."' clitic = '".$Clitic."' AP-frequency = '".$TargetedDictionary[$word['definitionId']]['APfrequency']."' reveal = 'false'  >";
		
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
				echo "<a target = '_blank' href = 'Dictionary.php?word=". $TargetedDictionary[$word['definitionId']]['entry'] . "'>";
				echo $TargetedDictionary[$word['definitionId']]['APfrequency'];
				echo "</a>";
			echo "</freq>";

	echo "</word>";
}
echo "</line>";









?>

<body onload = "GetAPLatinHW();">

<script>


words = document.getElementsByTagName('word')

for (i=0; i <words.length; i++ )
{
	words[i].onclick = function()
	{
		this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"))
	}
}






function GetAPLatinHW()
{
	SpreadsheetDocID = "1jviY2SsTXvHw-ubDAlHMv0o55-u8XIthHYpu5rbGmcQ"
 
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

						document.getElementById('dueDate').innerText = "(" + DueDate + ")" 
						document.getElementById('dueDate').style.color = 'black'
					}
					sd++
				}
			}
		};
		xmlhttp.open("GET", "https://spreadsheets.google.com/feeds/cells/" + SpreadsheetDocID + "/1/public/values?alt=json", true);
		
		xmlhttp.send();
 

}






















</script>
<BR><BR><BR><BR><BR><BR><BR><BR>