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
require_once 'globals.php';
 
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

// echo count(array_unique(array_map(function ($x)
// {
//     return $x['chapter'] . "." . $x['lineNumber'];
// }, $HWLines)));

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

echo "<select no-latin-3 onchange= 'setDifficulty(this.value)'>";

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

echo displayLines($HWAssignment, $HWLines, $TargetedDictionary);
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

<script>
    const ASSIGNMENT_ID = JSON.parse(`<?php echo json_encode($_GET['hw']) ?>`);
    const HIGHLIGHTED_WORD = JSON.parse(`<?php echo json_encode($_GET['highlighted_word'] ?? 0) ?>`);
</script>

<script src="js/global/utility.js<?php echo "?" . date("md"); ?>" defer></script>
<script src="js/homework-viewer.js<?php echo "?" . date("mdHms"); ?>"></script>
<script async defer src="https://apis.google.com/js/api.js"></script>

<body onload = "getHomeworkDueDate();  InitializeWords(); SetupNoteHighlights(); <?php
if (isset($_GET['highlighted_word']))
{
    echo "	ScrollToWord('" . $_GET['highlighted_word'] . "')";
}
?>">



<BR><BR><BR><BR><BR><BR><BR><BR>