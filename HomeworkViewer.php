<TITLE>Latin Homework Viewer</TITLE>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($actual_link, "&amp;") !== false)
{
    header('Location:' . str_replace("&amp;", "&", $actual_link));
}

require_once 'ForwardHTTPS.php';
require_once 'GenerateNotesandVocab.php';
require_once 'JSBackend.php';

// if(!isset($_GET['highlightedword']))
if (!isset($_GET['hw']) && isset($_GET['highlightedword']) && isset($_GET['title']))
{
    $temp_level = "AP";
    if (isset($_GET['level']))
    {
        $temp_level = $_GET['level'];
    }

    $turl = explode($actual_link, "?")[0];

    $addon = "hw=" . findHomeworkByWordID($_GET['title'], $_GET['highlightedword']);
    $addon .= "&highlightedword=" . $_GET['highlightedword'];

    header('Location:' . str_replace("&amp;", "&", ($turl . $addon)));

}

require_once 'FontStyles.php';
require_once 'HomeworkViewerStyles.php';
require_once 'SQLConnection.php';

require_once 'autoload.php';

use app\Context;

require_once 'SQLConnection.php';
require_once 'utility/debug.php';

echo "<wrapper showmacrons='true'";

if (!Context::getTestStatus())
{
    echo " shownotes='true' ";
}

echo ">";
echo "<assignment>";

echo "<header >";

if (!Context::getTestStatus())
{

    echo "<menubar>";
    echo "<BR>";

    echo "<span class = 'menu-bar-option'>";
    echo "<A   href = 'https://github.com/BrianBakkala/APLatinHomework'>";
    echo "GitHub";
    echo "</A>";
    echo "</span>";

    echo "<span ap-only  class = 'menu-bar-option'>";
    echo "<A target = '_blank' href = 'https://quizlet.com/MrBakkala/folders/ap-latin-vocab/sets'>";
    echo "Quizlet";
    echo "</A>";
    echo "</span>";

    echo "<span ap-only class = 'menu-bar-option'>";
    echo "·";
    echo "</span>";

    echo "<span ap-only class = 'menu-bar-option'>";
    echo "<A   href = 'https://aplatin.altervista.org/UnitsViewer.php'>";
    echo "Units";
    echo "</A>";
    echo "</span>";

    echo "<span class = 'menu-bar-option'>";
    echo "<A target = '_blank' href = 'https://aplatin.altervista.org/Dictionary.php?level=" . Context::getLevel() . "'>";
    echo "Dictionary";
    echo "</A>";
    echo "</span>";

    echo "<span ap-only  class = 'menu-bar-option'>";
    echo "<A target = '_blank' href = 'https://aplatin.altervista.org/VocabList.php'>";
    echo "Vocabulary";
    echo "</A>";
    echo "</span>";

    echo "<span ap-only  class = 'menu-bar-option'>";
    echo "<A target = '_blank' href = 'https://aplatin.altervista.org/LiteraryDevices.php'>";
    echo "Literary Devices";
    echo "</A>";
    echo "</span>";
    echo "</menubar>";
}

echo "<assignmentdata>";

echo "<table>";
echo "<tr>";
echo "<td>";

if ((SQLQ('SELECT (`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` = ' . ((int) $_GET['hw'] - 1))) != "")
{

    $PrevHW = SQLQ('SELECT MAX(`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` < ' . $_GET['hw']);
    echo "<A href = 'HomeworkViewer.php?level=" . Context::getLevel() . "&hw=" . $PrevHW . "'>";
    echo "<IMG id = 'leftarrow' SRC = 'Images/LHarrow.png'>";
    echo "</A>";

}

echo "</td>";
echo "<td>";

echo "<h1>";

echo "<i>";
echo Context::getLatinTitle();
echo "</i> ";

echo createReadableFloat($HWAssignment['StartBook']);
echo ".";
if ($HWAssignment['StartChapter'] != null)
{
    echo $HWAssignment['StartChapter'];
    echo ".";
}
echo $HWAssignment['StartLine'];

echo "–";
echo createReadableFloat($HWAssignment['EndBook']);
echo ".";
if ($HWAssignment['EndChapter'] != null)
{
    echo $HWAssignment['EndChapter'];
    echo ".";
}
echo $HWAssignment['EndLine'];

echo "</h1>";

echo "</td>";
echo "<td>";

if ((SQLQ('SELECT (`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` = ' . ((int) $_GET['hw'] + 1))) != "")
{
    $NextHW = SQLQ('SELECT Min(`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` > ' . $_GET['hw']);

    echo "<A href = 'HomeworkViewer.php?level=" . Context::getLevel() . "&hw=" . $NextHW . "'>";
    echo "<IMG id = 'rightarrow' SRC = 'Images/LHarrow.png'>";
    echo "</A>";
}

echo "</td>";
echo "</tr>";
echo "</table>";

echo "<author>";
echo Context::getAuthor();
echo "</author>";

echo "</assignmentdata>";

echo "<BR>";
echo "<submenu >";

echo "<span class = 'submenu-item'  style = 'font-weight:bold;'>";
echo "HW " . $HWAssignment['HW'];
echo "</span>";

echo "<span class = 'submenu-item'   >";

echo count(array_unique(array_map(function ($x)
{
    return $x['chapter'] . "." . $x['lineNumber'];
}, $HWLines)));

echo " lines";
echo "</span>";

echo "<span  ap-only  class = 'submenu-item'>";
echo "<duedate style = 'color:rgba(0,0,0,0); ' id = 'dueDate'>December 31";
echo "</duedate>";
echo "</span>";

if (!Context::getTestStatus())
{
    echo "<span class = 'submenu-item'>";
    echo "<A target = '_blank' href = 'https://aplatin.altervista.org/PDF.php?level=" . Context::getLevel() . "&hw=" . $_GET['hw'] . "'>";
    echo "PDF";
    echo "</A>";
    echo "</span>";
}
echo "</submenu>";

echo "<submenu>";
if (!Context::getTestStatus())
{
    echo "<span class = 'submenu-item'>";
    echo "<a style = 'cursor:pointer;' onclick = 'ToggleNotes(this)'>";
    echo "Notes: <b>on</b>";
    echo "</a>";
    echo "</span>";
}

echo "<span class = 'submenu-item'>";
echo "<a style = 'cursor:pointer;' onclick = 'ToggleMacrons(this)'>";
echo "Macrons: <b>on</b>";
echo "</a>";
echo "</span>";

echo "<select no-latin-3 onchange= 'SetDifficulty(this.value)'>";

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

// echo "<BR>";

$ChapterCitationText = "";
if ($HWAssignment['StartChapter'] != null)
{
    $ChapterCitationText = $HWAssignment['StartChapter'] . ".";
}

echo "</header>";

echo "<BR>";

echo displayLines(true, $HWAssignment, $HWLines, $TargetedDictionary);
echo "</assignment>";

if (!Context::getTestStatus())
{
    echo "<notes>";
    echo "<BR>";
    echo displayNotesText($HWStartId, $HWEndId, $HWAssignment, Context::getBookTitle());
    echo "<BR><BR><BR><BR><BR><BR><BR><BR>";
    echo "</notes>";
}

echo "</wrapper>";

?>

<body onload = "GetHWDueDate(); CheckSSE(); InitializeWords(); SetupNoteHighlights(); <?php
if (isset($_GET['highlightedword']))
{
    echo "	ScrollToWord('" . $_GET['highlightedword'] . "')";
}
?>">





<script>

function InitializeWords()
{
	words = document.getElementsByTagName('word')

	for (i = 0; i < words.length; i++)
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

			for (i = 0; i < words.length; i++)
			{
				words[i].onclick = function() {}
				words[i].onmouseover = function() {}
				words[i].onmouseout = function() {}

				words[i].ontouchstart = function()
				{
					this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"))
				}

			}

		}
	}
}
function ToggleNotes(element)
{
	const CurrentStatus = (document.getElementsByTagName("wrapper")[0].getAttribute("shownotes") == "true")
	const NewStatus = !CurrentStatus

	document.getElementsByTagName("wrapper")[0].setAttribute("shownotes", NewStatus.toString() )

	element.innerHTML = "Notes: <b>"+(NewStatus? "on" : "off")+"</b>"

}

function ToggleMacrons(element)
{
	const CurrentStatus = (document.getElementsByTagName("wrapper")[0].getAttribute("showmacrons") == "true")
	const NewStatus = !CurrentStatus

	document.getElementsByTagName("wrapper")[0].setAttribute("showmacrons", NewStatus.toString() )

	console.log(element)
	element.innerHTML = "Macrons: <b>"+(NewStatus? "on" : "off")+"</b>"

}

function CheckSSE()
{
	//non-IE/Edge Functionality
	if (typeof(EventSource) !== "undefined")
	{
		var Level = "<?php echo Context::getLevel(); ?>";
		var source = new EventSource("TestModeSSE.php?level="+Level+"&timestampupdate=true");
		Recheck = null;
		const StatusOnLoad = "<?php echo (Context::getTestStatus()) ? "1" : "0"; ?>";

		source.onmessage = function(event)
		{
			SSEResponse = JSON.parse(event.data.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, ''))
			if(Recheck == null)
			{
				Recheck = SSEResponse[0]["TestMode"+Level]
			}

			if (SSEResponse[0]["TestMode"+Level] != Recheck || StatusOnLoad != Recheck )
			{
				document.getElementsByTagName('html')[0].innerHTML = "";
				source.onmessage = function(event){}
				source.close();
				location.reload();
			}
		};
	}


}

function ScrollToWord(wordId)
{

	if(document.getElementById(""+wordId))
	{
		const yOffset = -200;
		newY = document.getElementById(""+wordId).getBoundingClientRect().top + window.pageYOffset + yOffset;
		window.scrollTo({top: newY, behavior: 'smooth'});
		document.getElementById(""+wordId).setAttribute('reveal', "true")
		history.pushState({state:wordId}, "State 1", "#"+wordId);
		history.pushState({state:wordId}, "State 2", window.location.href.split("#")[0]);
	}


}
function ScrollToNote(noteId)
{
	boundingele = document.getElementsByTagName('notes')[0];
	correctNote = [...document.getElementsByTagName('note')].filter(x=> x.getAttribute('noteid') == (""+noteId))[0]

	if(correctNote)
	{
		if((correctNote.getBoundingClientRect().top < 0 || correctNote.getBoundingClientRect().top > (window.innerHeight-50)))
		{
			const yOffset = -100;
			newY = correctNote.getBoundingClientRect().top + boundingele.scrollTop + yOffset;

			boundingele.scrollTo({top: newY, behavior: 'smooth'});
		}
	}

}

function SetDifficulty(occurenceThreshold)
{
	words = document.getElementsByTagName('assignment')[0].getElementsByTagName('word')
	for (w=0; w<words.length; w++)
	{
		if((+words[w].getAttribute('frequency')) <= (+occurenceThreshold))
		{
			words[w].setAttribute('reveal', 'true')
		}
		else
		{
			words[w].setAttribute('reveal', 'false')
		}
	}
}

function GetHWDueDate()
{
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				SheetData = (JSON.parse(Response).values)

				sd = 0;
				HWFound = false;

				while ( sd < SheetData.length && !HWFound  )
				{
					console.log(SheetData[sd])

					if(( (SheetData[sd][0]).startsWith("C") || (SheetData[sd][0]).startsWith("V")) && SheetData[sd][0].endsWith("<?php echo $_GET['hw']; ?>"))
					{

						console.log((SheetData[sd][0]))
						console.log((SheetData[sd][1]))
						DueDate = (SheetData[sd][1])
						HWFound = true;

						document.getElementById('dueDate').innerText = "" + DueDate + ""
						document.getElementById('dueDate').style.color = '<?php $context = new Context;
echo $CSSColors[Context::getBookTitle()]['HeaderTextColor'];?>'
					}
					sd++
				}
			}
		};
		xmlhttp.open("GET", "https://sheets.googleapis.com/v4/spreadsheets/<?php echo $DocumentID; ?>/values/Export?alt=json&key=AIzaSyCN9ZxUhMb9zQW7rK4ZSaP1S4NJ7EKc_es", true);

		xmlhttp.send();


}

function ResetNoteHighlights()
{
	noteElements = document.getElementsByTagName('note')

	for (var n= 0; n<noteElements.length; n++)
	{
		noteElements[n].removeAttribute('highlighted')
	}

}

function HighlightNotes(hoveredElement)
{
	var ThereIsAHighlightedWord = false

	noteElements = document.getElementsByTagName('note')

	for (var n= 0; n<noteElements.length; n++)
	{
		if(noteElements[n].getAttribute('associatedwords').split(",").indexOf(hoveredElement.getAttribute('wordid') ) != -1)
		{
			noteElements[n].setAttribute('highlighted', "true")
			ScrollToNote((+noteElements[n].getAttribute('noteid')))
			ThereIsAHighlightedWord = true;
		}
		else
		{
			noteElements[n].setAttribute('highlighted', "false")
		}
	}

	if(!ThereIsAHighlightedWord)
	{
		ResetNoteHighlights()
	}
}

function SetupNoteHighlights()
{
	wordElements = document.getElementsByTagName('word')

	for (var w= 0; w<wordElements.length; w++)
	{
		wordElements[w].onmouseover = function()
		{
			HighlightNotes(this)
		};

		wordElements[w].onmouseout = function()
		{
			ResetNoteHighlights()
		};
	}

}

</script>
<script async defer src="https://apis.google.com/js/api.js"></script>

<BR><BR><BR><BR><BR><BR><BR><BR>