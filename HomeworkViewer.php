<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'GenerateNotesandVocab.php';
require_once 'ForwardHTTPS.php';

require_once 'globals.php';
require_once 'FontStyles.php';
require_once 'HomeworkViewerStyles.php';
require_once 'SQLConnection.php';
require_once 'autoload.php';

use app\Context;

require_once 'SQLConnection.php';
require_once 'utility/debug.php';

$HW_number = $_GET['hw'];

if ($HW_number != null)
{
    $Data = getHomeworkAssignment($HW_number);
    $HWAssignment = $Data['Assignment'];
    $HWLines = $Data['Lines'];
    $TargetedDictionary = $Data['Dictionary'];
    $HWStartId = $Data['StartID'];
    $HWEndId = $Data['EndID'];
}
else
{

}

echo "<wrapper show-notes show-macrons >";
echo "<assignment>";

echo "<header >";

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

echo "<assignmentdata>";

echo "<table>";
echo "<tr>";
echo "<td>";

if ((SQLQ('SELECT (`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` = ' . ((int) $HW_number - 1))) != "")
{

    $PrevHW = SQLQ('SELECT MAX(`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` < ' . $HW_number);
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

if ((SQLQ('SELECT (`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` = ' . ((int) $HW_number + 1))) != "")
{
    $NextHW = SQLQ('SELECT Min(`HW`) FROM `' . Context::getHWDB() . '` WHERE `HW` > ' . $HW_number);

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

echo count($HWLines);

echo " lines";
echo "</span>";

echo "<span  ap-only  class = 'submenu-item'>";
echo "<duedate style = 'color:rgba(0,0,0,0); ' id = 'dueDate'>December 31";
echo "</duedate>";
echo "</span>";

echo "<span class = 'submenu-item'>";
echo "<A target = '_blank' href = 'https://aplatin.altervista.org/PDF.php?level=" . Context::getLevel() . "&hw=" . $HW_number . "'>";
echo "PDF";
echo "</A>";
echo "</span>";

echo "</submenu>";

echo "<submenu>";

echo "<span class = 'submenu-item'>";
echo "<a style = 'cursor:pointer;' onclick = 'toggleNotes(this)'>";
echo "Notes: <b  class = 'toggle-notes-text'>o</b>";
echo "</a>";
echo "</span>";

echo "<span class = 'submenu-item'>";
echo "<a style = 'cursor:pointer;' class = 'toggle-macrons' onclick = 'toggleMacrons(this)'>";
echo "Macrons: <b class = 'toggle-macrons-text'>o</b>";
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

echo "<notes>";
echo "<BR>";
echo displayNotesText($HWStartId, $HWEndId, $HWAssignment, Context::getBookTitle());
echo "<BR><BR><BR><BR><BR><BR><BR><BR>";
echo "</notes>";

echo "</wrapper>";

?>

<script>
    const ASSIGNMENT_ID = JSON.parse(`<?php echo json_encode($HW_number) ?>`);
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