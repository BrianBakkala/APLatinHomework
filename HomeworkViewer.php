<TITLE>AP Latin Homework Viewer</TITLE>


<?php 	require_once ( 'FontStyles.php');  ?>
<?php 	$CSSsrc = "HomeworkViewerStyles.css"; echo '<li'.'nk rel="stylesheet" type="text/css" href="'.$CSSsrc.'?'. rand(1, 100000)  ."00".date("U")."00".'">';  ?>



<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if(!isset($_GET['hw']))
{
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
	header('Location: $actual_link'.'?hw=1');
}

require_once ( 'SQLConnection.php');
require_once ( 'GenerateNotesandVocab.php');

echo "<wrapper shownotes='true'>";
echo "<assignment>";

echo "<header >";
echo "<menubar>";
echo "<BR>";


echo "<span class = 'menu-bar-option'>";
echo "<A   href = 'https://github.com/BrianBakkala/APLatinHomework'>";
echo "GitHub";
echo "</A>";
echo "</span>";


echo "<span class = 'menu-bar-option'>";
echo "<A   href = 'https://aplatin.altervista.org/UnitsViewer.php'>";
echo "Units";
echo "</A>";
echo "</span>";


echo "<span class = 'menu-bar-option'>";
echo "<A target = '_blank' href = 'https://aplatin.altervista.org/Dictionary.php'>";
echo "Dictionary";
echo "</A>";
echo "</span>";


echo "<span class = 'menu-bar-option'>";
echo "<A target = '_blank' href = 'https://quizlet.com/MrBakkala/folders/ap-latin-vocab/sets'>";
echo "Quizlet";
echo "</A>"; 
echo "</span>";
echo "</menubar>";


echo "<assignmentdata>";


echo "<table>";
echo "<tr>";
echo "<td>";

if($_GET['hw'] != "1")
{

	$PrevHW = SQLQ('SELECT MAX(`HW`) FROM `#APHW` WHERE `HW` < ' . $_GET['hw'] );
	echo "<A href = 'HomeworkViewer.php?hw=".$PrevHW."'>";
	echo "<IMG id = 'leftarrow' SRC = 'Images/LHarrow.png'>";
	echo "</A>";
	
}

echo "</td>";
echo "<td>";

$LatinBookTitle =
[
	"Aeneid" => "Aenēis",
	"DBG" => "Dē Bellō Gallicō",
	"InCatilinam" => "Ōrātiō in Catilinam" 
];

echo "<h1>";

	echo "<i>";
	echo $LatinBookTitle[$BookTitle];
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

echo "</td>";
echo "<td>";


if($_GET['hw'] != SQLQ('SELECT MAX(`HW`) FROM `#APHW` '))
{
	$NextHW = SQLQ('SELECT Min(`HW`) FROM `#APHW` WHERE `HW` > ' . $_GET['hw'] );

	echo "<A href = 'HomeworkViewer.php?hw=".$NextHW."'>";
	echo "<IMG id = 'rightarrow' SRC = 'Images/LHarrow.png'>";
	echo "</A>";	
}

echo "</td>";
echo "</tr>";
echo "</table>";
echo "</assignmentdata>";



echo "<BR>";
echo "<submenu >";

echo "<span class = 'submenu-item'  style = 'font-weight:bold;'>"; 
echo "HW ".$HWAssignment['HW'];
echo "</span>";

echo "<span class = 'submenu-item'>"; 
echo "<duedate style = 'color:rgba(0,0,0,0);' id = 'dueDate'>[Due Date]";
echo "</duedate>";
echo "</span>";

echo "<span class = 'submenu-item'>";
echo "<A target = '_blank' href = 'https://aplatin.altervista.org/GeneratePDF.php?hw=".  $_GET['hw'] . "'>";
echo "PDF";
echo "</A>";
echo "</span>";

echo "<span class = 'submenu-item'>"; 
echo "<a style = 'cursor:pointer;' onclick = 'document.getElementsByTagName(\"wrapper\")[0].setAttribute(\"shownotes\", document.getElementsByTagName(\"wrapper\")[0].getAttribute(\"shownotes\") == \"true\" ?  \"false\" : \"true\" )'>";
echo "Toggle Notes";
echo "</a>";
echo "</span>";

echo "<select onchange= 'SetDifficulty(this.value)'>";


	echo "<option value='0' selected disabled hidden> ";
	echo "Difficulty";
	echo "</option> ";

	echo "<option value = '500'>";
	echo "Absolute Scrub";
	echo "</option>";

	echo "<option value = '30'>";
	echo "#ezpz🍋squeezy";
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
	echo "Professional Latin interpreter";
	echo "</option>";

	echo "<option value = '1'>";
	echo "Ancient Roman God of Translation";
	echo "</option>";

	echo "<option value = '0'>";
	echo "I literally think in Latin 🦂🔴👨🏻‍🦲";
	echo "</option>";

echo "</select>";

echo "</submenu>";
echo "<BR>";

echo "<BR>";



$ChapterCitationText = "";
if($HWAssignment['StartChapter'] != null)
{
	$ChapterCitationText = $HWAssignment['StartChapter'] . "."; 
}

echo "</header>";

echo "<BR>";

	echo DisplayLines(true, $HWAssignment, $HWLines, $TargetedDictionary);
echo "</assignment>";

echo "<notes>";
	echo DisplayNotesText($HWStartId, $HWEndId, $HWAssignment, $BookTitle);
	echo "<BR><BR><BR><BR><BR><BR><BR><BR>";
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
		this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"))
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
						document.getElementById('dueDate').style.color = 'white'
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