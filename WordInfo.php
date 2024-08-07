<html translate="no">

<TITLE>AP Latin Word Concordance Viewer</TITLE>

<STYLE>


html {
	margin:5px;
	font-family: "Palatino Linotype";
}


attline:not([main="true"]) {

	-webkit-transition: .25s all ease-in-out;
	transition: .25s all ease-in-out;
	font-size: 0;
}


attestations:hover attestation,
attestations:hover attestation highlight {
	color: lightgray;
	background-color: rgba(255, 255, 255, 0);
}

attestations attestation:hover {
	color: black;
	padding-bottom: 5px;
	padding-top: 5px;
}

attestations attestation:hover highlight,
highlight {
	background-color: lightblue;
	color: black;
}

highlight a
{
	color:inherit;
	text-decoration:none;
}


attestations *,
attestations,
body {
	width: fit-content;
}

attestation {
	color: black;
}


attestation:hover attline:not([main="true"]) {
	font-size: inherit;
}

attcitation {
	font-weight: bold;
	padding-right: 10px;
}

attestation {
	display: block;
	text-align: left;
	font-size: x-large;
	border-bottom: 1px solid lightgray;
	padding-bottom: 2px;
	cursor: default;
}

entryheader {
	display: block;
	font-size: 2.5vw;
}


definitionheader {
	display: block;
	font-size: 2vw;
}

speaker {
	color:gray;
	text-transform: lowercase;
	font-variant: small-caps;
	padding-left:10px;
}

</STYLE>

<?php

require_once 'autoload.php';

use app\Context;

require_once 'SQLConnection.php';
require_once 'GenerateNotesandVocab.php';

require_once 'SQLConnection.php';
require_once 'utility/debug.php';

if (isset($_GET['wordid']))
{
    $word = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `' . Context::getDict() . '` WHERE `id` = "' . $_GET['wordid'] . '"')[0];
}

echo "<A href = 'Dictionary.php?level=" . Context::getLevel() . "'>← Dictionary</A>";
echo "<BR>";

echo "<entryheader>";
echo "<b>";
echo parseAsterisks($word['entry']);
echo "</b>";
echo "</entryheader>";

echo "<definitionheader>";
echo "<i>";
echo parseAsterisks($word['definition']);
echo "</i>";
echo "</definitionheader>";
echo "<BR><BR>";

foreach (Context::DICT_DB as $title => $dictionary)
{
    if ($dictionary == Context::getDict())
    {
        if (in_array($title, Context::HAS_IDENTIFIED_SPEAKERS))
        {
            $SpeakerClause = ", `Speaker`";
        }
        else
        {
            $SpeakerClause = "";
        }

        $uses = SQLQuarry('SELECT `id`, `book`, `chapter`, `lineNumber`, `word`   ' . $SpeakerClause . ' FROM `' . Context::BOOK_DB[$title] . '` WHERE `definitionId` = ' . $word['id'] . '   OR  `secondaryDefId` = ' . $word['id'] . '  ORDER BY `book`, `chapter`, `lineNumber`, `id` ');

        $UseString = "";

        for ($u = 0; $u < count($uses); $u++)
        {
            $TheLineNumber = (((int) $uses[$u]['lineNumber']) - 0);
            $PrevLineNumber = 0;
            $NextLineNumber = 0;

            $WordsOnLine = SQLQuarry(' SELECT   `word`, `id`  FROM `' . Context::BOOK_DB[$title] . '`  WHERE `lineNumber` = ' . $uses[$u]['lineNumber'] . ' and `chapter` ' . ($uses[$u]['chapter'] == "" ? "IS NULL" : "=") . " " . $uses[$u]['chapter'] . ' and `book` = ' . $uses[$u]['book'] . ' ORDER BY `OrderOfText` ASC  ', false, "id");

            $temp_word_id = $uses[$u]['id'];
            $WordsOnLine = array_values($WordsOnLine);

            $AttLine = "";
            for ($w = 0; $w < count($WordsOnLine); $w++)
            {
                if ($w != 0)
                {
                    $AttLine .= " ";
                }
                if ($WordsOnLine[$w]['id'] == $temp_word_id)
                {
                    $AttLine .= "<highlight wid = '" . $WordsOnLine[$w]['id'] . "' style = 'cursor:pointer;''>";
                    $AttLine .= "<a target = '_blank' href = 'HomeworkViewer.php?level=" . Context::getLevel();
                    $AttLine .= "&title=" . $title;
                    $AttLine .= "&highlighted_word=" . $WordsOnLine[$w]['id'] . "'>";
                    $AttLine .= $WordsOnLine[$w]['word'];
                    $AttLine .= "</a>";
                    $AttLine .= "</highlight>";
                }
                else
                {
                    $AttLine .= $WordsOnLine[$w]['word'];
                }
            }

            if (SQLQ(' SELECT  `id` FROM `' . Context::BOOK_DB[$title] . '`  WHERE `lineNumber` = ' . ($TheLineNumber - 1) . ' and `chapter` ' . ($uses[$u]['chapter'] == "" ? "IS NULL" : "=") . " " . $uses[$u]['chapter'] . ' and `book` = ' . $uses[$u]['book'] . '   '))
            {
                $PrevLineNumber = ($TheLineNumber - 1);
                $AttPrevLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `' . Context::BOOK_DB[$title] . '`  WHERE `lineNumber` = ' . $PrevLineNumber . ' and `chapter` ' . ($uses[$u]['chapter'] == "" ? "IS NULL" : "=") . " " . $uses[$u]['chapter'] . ' and `book` = ' . $uses[$u]['book'] . '   ');
            }
            else
            {
                $AttPrevLine = "";
            }

            if (SQLQ(' SELECT  `id` FROM `' . Context::BOOK_DB[$title] . '`  WHERE `lineNumber` = ' . ($TheLineNumber + 1) . ' and `chapter` ' . ($uses[$u]['chapter'] == "" ? "IS NULL" : "=") . " " . $uses[$u]['chapter'] . ' and `book` = ' . $uses[$u]['book'] . '   '))
            {
                $NextLineNumber = ($TheLineNumber + 1);
                $AttNextLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `' . Context::BOOK_DB[$title] . '`  WHERE `lineNumber` = ' . $NextLineNumber . ' and `chapter` ' . ($uses[$u]['chapter'] == "" ? "IS NULL" : "=") . " " . $uses[$u]['chapter'] . ' and `book` = ' . $uses[$u]['book'] . '   ');
            }
            else
            {
                $AttNextLine = "";
            }

            $UseString .= "<attestation wordid = '" . $uses[$u]['id'] . "'>";

            if ($PrevLineNumber != 0)
            {
                $UseString .= "<attline>";
                $UseString .= "<attcitation>";
                $UseString .= $uses[$u]['book'];
                $UseString .= ($uses[$u]['chapter'] != "" ? "." . $uses[$u]['chapter'] : "");
                $UseString .= "." . $PrevLineNumber;
                $UseString .= "</attcitation>";
                $UseString .= $AttPrevLine . "<BR>";
                $UseString .= "</attline>";
            }

            $UseString .= "<attline  main = 'true'>";
            $UseString .= "<attcitation>";
            $UseString .= $uses[$u]['book'];
            $UseString .= ($uses[$u]['chapter'] != "" ? "." . $uses[$u]['chapter'] : "");
            $UseString .= "." . $TheLineNumber;
            $UseString .= "</attcitation>";
            $UseString .= $AttLine;

            $UseString .= "<speaker>";
            $UseString .= $uses[$u]['Speaker'];
            $UseString .= "</speaker>";

            $UseString .= "<BR>";
            $UseString .= "</attline>";

            if ($NextLineNumber != 0)
            {
                $UseString .= "<attline>";
                $UseString .= "<attcitation>";
                $UseString .= $uses[$u]['book'];
                $UseString .= ($uses[$u]['chapter'] != "" ? "." . $uses[$u]['chapter'] : "");
                $UseString .= "." . $NextLineNumber;
                $UseString .= "</attcitation>";
                $UseString .= $AttNextLine . "<BR>";
                $UseString .= "</attline>";
            }

            $UseString .= "</attestation>";

        }

        echo "<h1><i>" . Context::getEnglishTitle($title) . "</i>: " . getFrequencyByTitle($_GET['wordid'], $title) . "</h1>";
        echo "<attestations>";
        echo $UseString;
        echo "</attestations>";

    }
}

?>





<BR><BR><BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR><BR><BR>