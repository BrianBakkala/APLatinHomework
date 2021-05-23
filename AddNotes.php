<TITLE>Add Notes</TITLE>
 
<?php

require_once ('GoogleClassroom/APLGSI.php');  

require_once ( 'GenerateNotesandVocab.php');
require_once ( 'FontStyles.php');
require_once ( 'HomeworkViewerStyles.php');
require_once ( 'SQLConnection.php');
$context = new Context;

echo "<wrapper shownotes = 'true'>";

echo "<assignment>";


if($_GET['hw'] != "1")
{

	$PrevHW = SQLQ('SELECT MAX(`HW`) FROM `#APHW` WHERE `HW` < ' . $_GET['hw'] );
	echo "<A href = 'AddNotes.php?level=".$context->GetLevel()."&hw=".$PrevHW."'>";
	echo "<IMG id = 'leftarrow' SRC = 'Images/LHarrow.png'>";
	echo "</A>";
	
}

echo "<h1>";

	echo "<i>";
	echo $context->GetEnglishTitle();
	echo "</i> ";

	echo ReadableFloat($HWAssignment['StartBook']);
	echo ".";
	if($HWAssignment['StartChapter'] != null)
	{
		echo $HWAssignment['StartChapter'];
		echo ".";
	}
	echo $HWAssignment['StartLine'];

	echo "–";
	echo ReadableFloat($HWAssignment['EndBook']);
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

	echo "<A href = 'AddNotes.php?level=".$context->GetLevel()."&hw=".$NextHW."'>";
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
echo "<line citation = '".ReadableFloat($HWAssignment['StartBook']).".".$ChapterCitationText.$temp_start_line."' num = '".$temp_start_line."'>";


$CliticList = GetCliticList($TargetedDictionary);


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
		preg_match('/('. implode("|", $CliticList['no_hyphens_with_dollar_signs']). ')/', $Noclitics, $clitics);
		$Clitic = $clitics[0];

		$Noclitics = mb_ereg_replace("(". implode("|", $CliticList['no_hyphens_with_dollar_signs']). ")","",$Noclitics);


		$SplitPos = preg_match('/('. implode("|", $CliticList['no_hyphens']). ')[.!;,]?$/', $word['word'], $position, PREG_OFFSET_CAPTURE);
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


echo "<testmode>";
echo "</testmode>";
echo "</wrapper>";







?>

<body onload = 'GoogleCheck();'>





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

		XMLURL = "AJAXAPL.php?addnote=true&booktitle=<?php echo  $context->GetBookTitle();?>&level=<?php   echo $context->GetLevel();?>&notetext="+NoteText+"&wordids=" + NoteWords+"&linecitations=" + NoteLines;
		xmlhttp.open("GET", XMLURL, true);
		xmlhttp.send();
		console.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
		// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;
	

}


function TypeLine(ele)
{
	document.getElementById("submitNoteLines").value += "," + ele.parentElement.getAttribute('citation')
}








const CLIENT_ID = '448443480105-krbg7mnhjqd7s4kdevurs1dtffe1uf1t.apps.googleusercontent.com';
const API_KEY = 'AIzaSyCN9ZxUhMb9zQW7rK4ZSaP1S4NJ7EKc_es';
const DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest", "https://www.googleapis.com/discovery/v1/apis/classroom/v1/rest"];
const SCOPES = (["https://www.googleapis.com/auth/calendar.events", "https://www.googleapis.com/auth/calendar.readonly", "https://www.googleapis.com/auth/classroom.topics.readonly",  "https://www.googleapis.com/auth/classroom.coursework.students"].join(" "));

const GoogleClassroomCourseName = "AP Latin E12";



function InitializeCalendarGAPI()
{
	gapi.load('client:auth2', function()
	{
		gapi.client.init(
		{
			apiKey: API_KEY,
			clientId: CLIENT_ID,
			discoveryDocs: DISCOVERY_DOCS,
			scope: SCOPES
			
		}).then(function()
		{
			// PullGoogleClassroomCalendars();
			FindClassroomCourse(GoogleClassroomCourseName);

		}, function(error)
		{
			return new Promise(function(resolve, reject)
			{
				reject();
			});
		});

	})

}

function SignInWithCheck()
{
	(gapi.auth2.getAuthInstance().signIn())
	.then(
		function()
		{
			InitializeCalendarGAPI() 
		}
	);
}













</script>
<BR><BR><BR><BR><BR><BR><BR><BR>