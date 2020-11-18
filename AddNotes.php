<TITLE>Add Notes</TITLE>
 
<?php


if(!isset($_GET['hw']))
{
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
	header('Location: $actual_link'.'?hw=1');
}

require_once ( 'GenerateNotesandVocab.php');
require_once ( 'FontStyles.php');
require_once ( 'HomeworkViewerStyles.php');
require_once ( 'SQLConnection.php');

echo "<wrapper shownotes = 'true'>";

echo "<assignment>";


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




echo "<div  style = 'background-color:rgba(255,255,255,.9); position: -webkit-sticky; position: sticky; top:0px;' >";

echo "<BR>";

echo "<form id = 'submitNotesForm'>";
echo "WORDS <input id = 'submitNoteWords' type = 'text' size = '40'>";
echo "<BR>";
echo "LINES <input id = 'submitNoteLines' type = 'text' size = '40'>";
echo "<BR>";
echo "<BR>";
echo "NOTE";
echo "<BR>";
echo "<textarea id = 'submitNoteNote' cols = 60 rows = 4></textarea>";
echo "<BR>";
echo "<input id = 'submitNoteSubmit' onclick = 'AddNote()' type = 'button' value = 'Submit'>";
echo "<BR>";
echo "<BR>";
echo "<span id = 'previewNoteWords'></span>";
echo "</form>";

echo "<HR style = 'border-top: 1px solid #eee;'>";

echo "</div>";
$ChapterCitationText = "";
if($HWAssignment['StartChapter'] != null)
{
	$ChapterCitationText = $HWAssignment['StartChapter'] . "."; 
}


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


	echo "<word idnum = '".$word['id']."' fullword = '".$word['word']."' baseword = '".$Noclitics."' clitic = '".$Clitic."' frequency = '".$TargetedDictionary[$word['definitionId']]['APfrequency']."' reveal = 'false'  >";
		
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
			echo $word['id'] . " | d " . $word['definitionId'];
		echo "</freq>";
		
	echo "</word>";
}
echo "<span style = 'cursor:pointer;' onclick = 'TypeLine(this)'>(+)</span></line>";
echo "</assignment>";



echo "<notes style = 'background-color:white'>";
	echo DisplayNotesText($HWStartId, $HWEndId, $HWAssignment, $BookTitle);
echo "</notes>";

echo "</wrapper>";







?>

<body >

<script>

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
				location.reload();
			}
		};

		XMLURL = "AJAXAPL.php?addnote=true&level=<?php echo $Level;?>&booktitle=<?php echo $BookTitle;?>&notetext="+NoteText+"&wordids=" + NoteWords+"&linecitations=" + NoteLines;
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