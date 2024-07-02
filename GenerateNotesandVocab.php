
<?php

require_once 'autoload.php';

use app\Context;

require_once 'SQLConnection.php';
require_once 'utility/debug.php';

$DocumentID = "11j0cC45e8RBiHbt0FKzJ-gHUZ_fEpDQzVo-cEU5eYAU";
$ExportPageNumber = 7;

if (isset($_GET['hw']))
{
    $Data = getHomeworkAssignment($_GET['hw']);
    $HWAssignment = $Data['Assignment'];
    $HWLines = $Data['Lines'];
    $TargetedDictionary = $Data['Dictionary'];
    $HWStartId = $Data['StartID'];
    $HWEndId = $Data['EndID'];
}

// echo ('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `'.$BOOK_DB[$BookTitle].'` WHERE  `book` = '.$HWAssignment['StartBook'].' AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`');

function createReadableFloat($input)
{
    $input = $input . "";
    $input = rtrim($input, " 0");
    $input = rtrim($input, ".");
    return $input;
}

function getHomeworkLineIDs($HWNum, $hwdb, $title)
{

    $Assignment = latinQuery([$HWNum], 'SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author`, `BookTitle`, `AddToBeginning`, `SubtractFromEnd`  FROM `' . $hwdb . '` WHERE `HW` = ?')[0];

    if ($Assignment['StartChapter'] == null && $Assignment['EndChapter'] == null)
    {
        $WhereClause = ' ( `lineNumber` >= ' . $Assignment['StartLine'] . '  AND   `lineNumber` <= ' . $Assignment['EndLine'] . ')';
        $WhereClause2 = $WhereClause;
    }

    if ($Assignment['StartChapter'] != null && $Assignment['EndChapter'] != null)
    {

        if ($Assignment['StartChapter'] == $Assignment['EndChapter'])
        {
            $WhereClause = ' `chapter` = "' . $Assignment['StartChapter'] . '" AND (  `lineNumber` >= ' . $Assignment['StartLine'] . ' AND   `lineNumber` <= ' . $Assignment['EndLine'] . ')';
            $WhereClause2 = $WhereClause;
        }
        else
        {
            $WhereClause = '(( `chapter` = "' . $Assignment['StartChapter'] . '" AND   `lineNumber` >= ' . $Assignment['StartLine'] . ')     )  ';
            $WhereClause2 = '(   ( `chapter` = "' . $Assignment['EndChapter'] . '" AND   `lineNumber` <= ' . $Assignment['EndLine'] . ')  )  ';
        }

    }

    $StartId = ((int) latinQuery([$Assignment['StartBook']], 'SELECT MIN(`OrderOfText`) FROM `' . Context::getTextDB() . '` WHERE  `book` = ? AND ' . $WhereClause . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`', true, false, true) - ((int) $Assignment['AddToBeginning']));
    $EndId = ((int) latinQuery([$Assignment['EndBook']], 'SELECT MAX(`OrderOfText`) FROM `' . Context::getTextDB() . '` WHERE  `book` = ? AND ' . $WhereClause2 . ' ORDER BY `book`, `chapter`, `lineNumber`, `id`', true, false, true) - ((int) $Assignment['SubtractFromEnd']));

    return array(
        "StartID" => $StartId,
        "EndID" => $EndId,
        "StartBook" => $Assignment['StartBook'],
        "EndBook" => $Assignment['EndBook'],
        "Assignment" => $Assignment,
    );

}

function getHomeworkAssignment($HWNum, $hwdb = null, $title = null)
{

    if (!isset($HWNum))
    {
        $HWNum = (int) $_GET['hw'];
    }

    if (!isset($hwdb))
    {
        $hwdb = Context::getHWDB();
    }

    if (!isset($title))
    {
        $title = latinQuery([$HWNum], 'SELECT  `BookTitle` FROM `' . $hwdb . '` WHERE `HW` = ?', true, false, true);
    }

    $tempids = getHomeworkLineIDs($HWNum, $hwdb, $title);
    $StartId = (int) $tempids['StartID'];
    $EndId = (int) $tempids['EndID'];
    $Assignment = $tempids['Assignment'];

    $Lines = latinQuery([$tempids['StartBook'], $tempids['EndBook'], $StartId, $EndId], 'SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `' . Context::BOOK_DB[$title] . '` WHERE ( `book` = ? or  `book` = ?) AND  `OrderOfText` >= ? AND `OrderOfText` <= ? ORDER BY `book`, `chapter`, `lineNumber`, `OrderOfText`');

    $HWDefinitionIds = array_map(function ($x)
    {
        return $x['definitionId'];
    }, $Lines);
    $HWDefinitionIds2 = array_map(function ($x)
    {
        return $x['secondaryDefId'];
    }, $Lines);
    $HWDefinitionIds = array_unique(array_merge($HWDefinitionIds, $HWDefinitionIds2));

    $TD = latinQuery([], 'SELECT `id`, `entry`, `definition`, `IsTwoWords`  FROM `' . Context::getDict() . '` WHERE `id` <> 0 and `id` <> -1 and ( `id` = ' . implode(" OR `id` = ", $HWDefinitionIds) . ')   ORDER BY replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "Ā", "A") , "Ē", "E") , "Ī", "I") , "Ō", "O") , "Ū", "U") , "-", ""), "—, ", "")  COLLATE utf8_general_ci   ', false, "id");

    return [
        "Assignment" => $Assignment,
        "Title" => Context::BOOK_DB[$title],
        "Lines" => $Lines,
        "StartID" => $StartId,
        "EndID" => $EndId,
        "Dictionary" => $TD,
    ];

}

function parseAsterisks($input)
{
    $input = preg_replace("/\*\*\*\*(.*?)\*\*\*\*/", "<b>\\1</b>", $input);
    $input = preg_replace("/\*\*\*(.*?)\*\*\*/", "<i>\\1</i>", $input);
    $input = preg_replace("/\*\*(.*?)\*\*/", "</b>\\1<b>", $input);
    $input = preg_replace("/\*(.*?)\*/", "</i>\\1<i>", $input);

    return $input;
}

function getCitationByWordID($title, $wordid)
{

    if ($title == null)
    {
        $title = Context::getBookTitle();
    }

    $wordid = $wordid . "";
    $hwdb = Context::BOOK_DB[$title];

    $tempdata = latinQuery([$wordid], 'SELECT *   FROM `' . $hwdb . '` WHERE `id` = ?')[0];

    $citationoutput = "";
    $citationoutput .= $tempdata['book'];

    if ($tempdata['chapter'] != null)
    {
        $citationoutput .= "." . $tempdata['chapter'];
    }

    $citationoutput .= "." . $tempdata['lineNumber'];

    return $citationoutput;

}

function findHomeworkByWordID($title = null, $wordid = 1)
{

    if ($title == null)
    {
        $title = Context::getBookTitle();
    }

    $wordid = $wordid . "";

    $lev = array_flip(Context::LEVEL_DICT_DB)[Context::DICT_DB[$title]];
    $hwdb = Context::LEVEL_DB[$lev];

    $HWnums = latinQuery([$title], 'SELECT `HW` FROM `' . $hwdb . '` WHERE `BookTitle` =  ?', true);
    $HWnums = array_map(function ($x)
    {
        return (int) $x;
    }, $HWnums);

    $a = 0;
    do
    {
        $temp_id_nums = getHomeworkLineIDs($HWnums[$a], $hwdb, $title);
        $tempStart = $temp_id_nums['StartID'];
        $tempEnd = $temp_id_nums['EndID'];

        $lineIdsArray = latinQuery([$temp_id_nums['StartBook'], $temp_id_nums['EndBook'], $temp_id_nums['StartID'], $temp_id_nums['EndID']], 'SELECT `id`  FROM `' . Context::BOOK_DB[$title] . '` WHERE ( `book` = ? or  `book` = ?) AND  `OrderOfText` >= ? AND `OrderOfText` <= ?', true);
        $lineIdsArray = array_map(function ($x)
        {
            return (int) $x;
        }, $lineIdsArray);

        $a++;

    } while (isset($HWnums[$a]) && !in_array($wordid, $lineIdsArray));

    return $HWnums[$a - 1];
}

function getCliticList($dictionary)
{
    $cliticList = array_filter($dictionary, function ($word)
    {
        return ($word['entry'][0] == "-");
    });
    $cliticList = array_map(function ($word)
    {
        return $word['entry'];
    }, $cliticList);
    $cliticList = array_values(array_unique($cliticList));

    return array(
        "normal" => $cliticList = array_values(array_unique($cliticList)),
        "no_hyphens" => array_map(function ($val)
        {
            return ltrim($val, '-');
        }, $cliticList),
        "no_hyphens_with_dollar_signs" => array_map(function ($val)
        {
            return ltrim($val, '-') . "$";
        }, $cliticList),
    );
}

function getHomeworkAssignmentsInUnits(...$units)
{
    $WhereClause = 'WHERE 0 ';
    foreach ($units as $unit)
    {
        $WhereClause .= ' OR  `Unit` = "' . $unit . '" ';

    }
    return latinQuery([], 'SELECT `HW`  FROM `ap_homework`  ' . $WhereClause, true);
}

function convertIntegerArrayToRanges($array, $last = array(), $done = array())
{
    if ($array == array())
    {
        return $done;
    }

    $h = $array[0];
    $t = array_slice($array, 1);

    if ($last == array())
    {
        $last = array($array[0], $array[0]);
    }
    if ($t[0] == 1 + $last[1])
    {
        return convertIntegerArrayToRanges($t, array($last[0], $h + 1), $done);
    }
    $done[] = $last;
    return convertIntegerArrayToRanges($t, array(), $done);
}

function getFrequencyTable($defidsarray = null, $hwAssignmentsScope = array())
{

    if ($defidsarray == null)
    {
        $temp_assignment = (getHomeworkAssignment($_GET['hw'], Context::getHWDB())['Lines']);
        $defidsarray = array_map(function ($x)
        {
            return $x['definitionId'];
        }, $temp_assignment);
        $defidsarray = array_unique($defidsarray);
    }

    $CorrectDictionary = Context::LEVEL_DICT_DB[Context::getLevel()];

    if (count($hwAssignmentsScope) == 0)
    {
        $AllTextsClause_Array = [];
        foreach (Context::DICT_DB as $k => $d)
        {
            if ($d == $CorrectDictionary)
            {
                $ProseException = "";
                if (!in_array($k, Context::IS_POETRY))
                {
                    $ProseException = " NULL as ";
                }
                array_push($AllTextsClause_Array, "(SELECT `id`, `definitionId`, `secondaryDefId`, " . $ProseException . " `Tmesis` FROM `" . Context::BOOK_DB[$k] . "`)");
            }
        }

    }
    else
    {
        $AllTextsClause_Array = [];

        foreach ($hwAssignmentsScope as $hw)
        {
            $HWAss = getHomeworkAssignment($hw);
            $IDRangesForAssignment = convertIntegerArrayToRanges(array_map(function ($x)
            {
                return (int) $x['id'];
            }, $HWAss['Lines']));
            // var_dump($IDRangesForAssignment);

            $WhereClause0 = " WHERE 0 ";
            foreach ($IDRangesForAssignment as $range)
            {
                $tempStart = $range[0];
                $tempEnd = $range[1];
                $WhereClause0 .= " OR (`id` >= " . $tempStart . " AND `id` <= " . $tempEnd . ") ";
            }
            // echo $WhereClause0 ;

            $ProseException = "";
            if (!in_array($HWAss['Title'], Context::IS_POETRY))
            {
                $ProseException = " NULL as ";
            }

            array_push($AllTextsClause_Array, ("SELECT `id`, `definitionId`, `secondaryDefId`, " . $ProseException . " `Tmesis` FROM `" . $HWAss['Title'] . "` " . $WhereClause0));
        }
        $AllTextsClause_Array = array_unique($AllTextsClause_Array);

    }

    $AllTextsClause = "( " . implode(" UNION ALL ", $AllTextsClause_Array) . " ) as `combined`";

    // var_dump($AllTextsClause);

    $WhereClause = " WHERE 0 ";
    foreach ($defidsarray as $defnumba)
    {
        $WhereClause .= "OR (`definitionId` = " . $defnumba . ") ";
    }
    $WhereClause2 = " WHERE 0 ";
    foreach ($defidsarray as $defnumba)
    {
        $WhereClause2 .= "OR (`id` = " . $defnumba . ") ";
    }

    $primaryuses = latinQuery([], ' SELECT `definitionId` , SUM(
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
			as `frequency` FROM ' . $AllTextsClause . '  INNER JOIN (SELECT `id` as `did`,  `IsTwoWords` FROM `' . $CorrectDictionary . '`  ' . $WhereClause2 . ' ) as `dict` on (`dict`.`did` = `definitionId`)  ' . $WhereClause . '  GROUP BY  `definitionId`   ');

    $secondarydefidsarray = latinQuery([], 'SELECT `id` FROM `#APDictionary` WHERE `entry` LIKE "-%" ', true);

    $WhereClause = " WHERE 0 ";
    foreach ($secondarydefidsarray as $sdefnumba)
    {
        $WhereClause .= "OR (`secondaryDefId` = " . $sdefnumba . ") ";
    }

    $secondaryuses = latinQuery([], ' SELECT `secondaryDefId` , COUNT(`id`) as `frequency` FROM ' . $AllTextsClause . '  ' . $WhereClause . '  GROUP BY  `secondaryDefId`   ');
    $uses = array_merge($primaryuses, $secondaryuses);

    $freqs = [];
    foreach ($uses as $use)
    {
        if (isset($use['definitionId']))
        {
            $freqs[$use['definitionId']] = ((int) $use['frequency']);
        }
        else if (isset($use['secondaryDefId']))
        {
            $freqs[$use['secondaryDefId']] = ((int) $use['frequency']);
        }
    }

    return $freqs;

}

function getFrequencyByLevel($definitionIdNumber, $level = "AP")
{

    $usecount = 0;

    foreach (Context::DICT_DB as $t => $d)
    {
        if ($d == Context::getDict())
        {
            $usecount += getFrequencyByTitle($definitionIdNumber, $t);
        }
    }
    return $usecount;
}

function getFrequencyByTitle($definitionIdNumber, $title)
{

    $TwoWordCheck = latinQuery([$definitionIdNumber], 'SELECT `IsTwoWords` FROM `' . Context::getDict() . '` WHERE `id` = ?');
    $uses = latinQuery([$definitionIdNumber, $definitionIdNumber], 'SELECT COUNT(`id`) FROM `' . Context::getTextDB($title) . '` WHERE `definitionId` = ? OR `secondaryDefId` = ?', true, false, true);

    if ($title == "Aeneid")
    {
        $Tmesis = latinQuery([$definitionIdNumber, $definitionIdNumber], 'SELECT COUNT(`id`) FROM `' . Context::getTextDB($title) . '` WHERE (`definitionId` = ? OR `secondaryDefId` = ?) and `Tmesis` = 1 ', true, false, true);
        $uses = (($uses - ($Tmesis / 2)));
    }

    $uses = ((int) $uses / ((((int) $TwoWordCheck) + 1)));

    return ((int) $uses);
}

function parseNoteText($inputText, $showdevices = true, $title = null)
{

    $outputText = $inputText;

    if ($title == null)
    {
        $title = Context::getBookTitle();
    }

    $literaryDevices = latinQuery([], 'SELECT `Device`, `Description` FROM `#APLiteraryDevices`', false, "Device");
    $literaryDevices = array_map(function ($x)
    {
        return $x['Description'];
    }, ($literaryDevices));
    $literaryDevices = array_flip(array_map('strtolower', array_flip($literaryDevices)));

    if ($showdevices == true)
    {
        $outputText = preg_replace_callback("/\*\*\*(" . implode('|', array_keys($literaryDevices)) . ")\*\*\*/", function ($matches) use ($literaryDevices)
        {

            // print_r($literaryDevices);
            return "<span class = 'literarydevice' device='" . $matches[1] . "'>" . $matches[1] . "<span class='tooltiptext'><B><U>" . $matches[1] . "</u></B><BR>" . $literaryDevices[$matches[1]] . "</span></span>";;
        }, $outputText);
    }
    else
    {
        $outputText = preg_replace("/\*\*\*(" . implode('|', array_keys($literaryDevices)) . ")\*\*\*/", '<u>' . '\\1' . '</u>', $outputText);
    }

    $outputText = preg_replace("/\*\*(.*?)\*\*/", "<b>\\1</b>", $outputText);
    $outputText = preg_replace("/\*(.*?)\*/", "<i>\\1</i>", $outputText);

    $outputText = preg_replace_callback("/\|\|(.*?)\|\|/",
        function ($matches) use ($title)
        {
            $m = $matches[0];
            $m = preg_replace("/^\|\|\[(.*)\]\|/", "<quotetitle onclick = 'ToggleQuote(this)'>" . "\\1" . "</quotetitle><quoteline>", $m);
            $m = preg_replace("/\|\|/", "</quoteline>", $m);
            $m = preg_replace("/\|/", "</quoteline><quoteline>", $m);
            // echo "\n";        echo "\n";        var_dump($m);        echo "\n";        echo "\n";
            // $m = preg_replace("/<quoteline><\/quoteline><quoteline>/","<quoteline>",$m);
            // $m = preg_replace("/<\/quoteline><quoteline><\/quoteline>/","</quoteline>",$m);
            // $m = preg_replace("/<quoteline>$/","</quoteline",$m);

            return "<quote>" . $m . "</quote>";

        }, $outputText);

    // $outputText = preg_replace_callback("/\<\<(\d*?)\>\>/","<a target = '_blank' href = 'http://aplatin.altervista.org/HomeworkViewer.php?level=".Context::getLevel()."&hw=".findHomeworkByWordID(Context::getBookTitle(), "\1")."&highlightedword="."\\1"."'>\\1</a>", $outputText);
    $outputText = preg_replace_callback("/\<\<(\d*?)\>\>/",
        function ($matches) use ($title)
        {
            $m = $matches[0];
            $m = preg_replace("/\<\<(\d*?)\>\>/", "\\1", $m);

            return "<a target = '_blank' href = 'http://aplatin.altervista.org/HomeworkViewer.php?level=" . Context::getLevel() . "&title=" . $title . "&highlightedword=" . $m . "'>" . getCitationByWordID($title, $m) . "</a>";

        }, $outputText);
    return $outputText;
}

function stripMacrons($inputText)
{
    $stripMacronsArray = [
        "ǖ" => "u", "ü" => "u", "ï" => "i",
        "ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
        "Ā" => "A", "Ē" => "E", "Ī" => "I", "Ō" => "O", "Ū" => "U", "Ȳ" => "Y",
    ];

    $nomacronsarray = preg_split('/(?!^)(?=.)/u', $inputText);
    $nomacronstext = implode("", array_map(function ($x) use ($stripMacronsArray)
    {
        return (isset($stripMacronsArray[$x])) ? $stripMacronsArray[$x] : $x;
    }, $nomacronsarray));

    return $nomacronstext;
}

function displayNotesText($hwstart, $hwend, $hwassignment, $title, $literaryDevices = true)
{

    if ($title == "")
    {
        $title = Context::getBookTitle();
    }

    $WordNotes = latinQuery([

        $hwstart,
        $hwend,
        $title,

    ],

        ' SELECT `' . Context::getNotesDB() . 'Locations`.`NoteId`,`OrderOfText`, `AssociatedWordId`, `AssociatedWordId` as `FirstWordId`,  `' . Context::getNotesDB() . 'Text`.`Text`, `BookTitle`, `sub`.`word`,`sub`.`book`,`sub`.`chapter`, `sub`.`lineNumber` FROM `' . Context::getNotesDB() . 'Locations` INNER JOIN `' . Context::getNotesDB() . 'Text` ON (`' . Context::getNotesDB() . 'Text`.`NoteId` = `' . Context::getNotesDB() . 'Locations`.`NoteId`) INNER JOIN (SELECT `id`,`OrderOfText`, `book`,`chapter`, `word`,`lineNumber`   FROM `' . Context::getTextDB() . '`) as `sub` ON (`sub`.`id` = `AssociatedWordId` )  WHERE (`sub`.`OrderOfText` >= ' . $hwstart . ' AND `sub`.`OrderOfText` <= ' . $hwend . ')  AND (`sub`.`book` = (SELECT `book` FROM `' . Context::getTextDB() . '` WHERE `id` = ?) OR `sub`.`book` = (SELECT `book` FROM `' . Context::getTextDB() . '` WHERE `id` = ?) ) AND `BookTitle` = ? AND `AssociatedLineCitation` = "" ORDER BY `sub`.`OrderOfText`, `AssociatedWordId`');

    if (in_array($title, Context::HAS_CHAPTERS))
    {
        $ConcatText = "CONCAT(`sub`.`book`, '.',`sub`.`chapter`, '.', `sub`.`lineNumber`)";
    }
    else
    {
        $ConcatText = "CONCAT(`sub`.`book`, '.', `sub`.`lineNumber`)";
    }

    $LineNotes = latinQuery([], 'SELECT `' . Context::getNotesDB() . 'Locations`.`NoteId`,   `AssociatedWordId` as `FirstWordId`, `sub`.`id` as `AssociatedWordId`,  `AssociatedLineCitation`, `' . Context::getNotesDB() . 'Text`.`Text`, `BookTitle`, `book`, `chapter`, `lineNumber` FROM `' . Context::getNotesDB() . 'Locations` INNER JOIN `' . Context::getNotesDB() . 'Text` ON (`' . Context::getNotesDB() . 'Text`.`NoteId` = `' . Context::getNotesDB() . 'Locations`.`NoteId`) LEFT JOIN (SELECT `id`, `book`, `chapter`, `lineNumber` FROM `' . Context::getTextDB() . '`) as `sub` ON ( `AssociatedLineCitation` =  ' . $ConcatText . '   ) WHERE (`sub`.`id` >= ' . $hwstart . ' AND `sub`.`id` <= ' . $hwend . ') AND (`sub`.`book` = (SELECT `book` FROM `' . Context::getTextDB() . '` WHERE `id` = ' . $hwstart . ') OR `sub`.`book` = (SELECT `book` FROM `' . Context::getTextDB() . '` WHERE `id` = ' . $hwend . ') ) AND `BookTitle` = "' . $title . '" ORDER BY `AssociatedLineCitation`, `lineNumber`');

    $CondensedNotes = array();

    foreach ($WordNotes as $note)
    {
        $templinecitation = "";

        $templinecitation = ($note["lineNumber"]);

        if ($hwassignment['StartChapter'] != $hwassignment['EndChapter'])
        {
            $templinecitation = ($note["chapter"]) . "." . $templinecitation;
        }

        if ($hwassignment['StartBook'] != $hwassignment['EndBook'])
        {
            $templinecitation = ($note["book"]) . "." . $templinecitation;
        }

        if (!isset($CondensedNotes[$note["NoteId"]]))
        {
            $CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => array($note["AssociatedWordId"]));
            $CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
            $CondensedNotes[$note["NoteId"]]["WL"] = "Word";
            $CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
            $CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
            $CondensedNotes[$note["NoteId"]]["LastWordId"] = $note["OrderOfText"];
            $CondensedNotes[$note["NoteId"]]["phrase"] = $note["word"];
            $CondensedNotes[$note["NoteId"]]["lines"] = array($templinecitation);
            $CondensedNotes[$note["NoteId"]]["comparableCitation"] = $note["AssociatedWordId"];
        }
        else
        {
            if (($CondensedNotes[$note["NoteId"]]["LastWordId"] + 0.5) == $note["OrderOfText"])
            {
                $CondensedNotes[$note["NoteId"]]["phrase"] .= " " . $note["word"];
                // $CondensedNotes[$note["NoteId"]]["phrase"] .=  "(".$note["OrderOfText"].")";
            }
            else if (($CondensedNotes[$note["NoteId"]]["LastWordId"] + 1) == $note["OrderOfText"])
            {
                $CondensedNotes[$note["NoteId"]]["phrase"] .= " " . $note["word"];
                // $CondensedNotes[$note["NoteId"]]["phrase"] .=  "(".$note["OrderOfText"].")";
            }
            else
            {
                $CondensedNotes[$note["NoteId"]]["phrase"] .= " … " . $note["word"];
                // $CondensedNotes[$note["NoteId"]]["phrase"] .=  "(".$note["OrderOfText"].")";
            }

            $CondensedNotes[$note["NoteId"]]["LastWordId"] = $note["OrderOfText"];
            array_push($CondensedNotes[$note["NoteId"]]["AssociatedWordId"], $note["AssociatedWordId"]);

            array_push($CondensedNotes[$note["NoteId"]]["lines"], $templinecitation);
            $CondensedNotes[$note["NoteId"]]["lines"] = array_unique($CondensedNotes[$note["NoteId"]]["lines"]);
            sort($CondensedNotes[$note["NoteId"]]["lines"]);
        }
    }

    foreach ($LineNotes as $note)
    {
        $templinecitation = "";
        if ($note["chapter"] == "" || $note["chapter"] == null || !isset($note["chapter"]))
        {
            $templinecitation = ($note["lineNumber"]);
        }
        else
        {
            $templinecitation = ($note["chapter"] . "." . $note["lineNumber"]);
        }

        if (!isset($CondensedNotes[$note["NoteId"]]))
        {
            $CondensedNotes[$note["NoteId"]] = array("AssociatedWordId" => array($note["AssociatedWordId"]));
            $CondensedNotes[$note["NoteId"]]["WL"] = "Line";
            $CondensedNotes[$note["NoteId"]]["BookTitle"] = $note["BookTitle"];
            $CondensedNotes[$note["NoteId"]]["Text"] = $note["Text"];
            $CondensedNotes[$note["NoteId"]]["phrase"] = "";
            $CondensedNotes[$note["NoteId"]]["NoteId"] = $note["NoteId"];
            $CondensedNotes[$note["NoteId"]]["lines"] = array($templinecitation);
            $CondensedNotes[$note["NoteId"]]["comparableCitation"] = $note["FirstWordId"];

            // echo ($CondensedNotes[$note["NoteId"]]['AssociatedWordId']. " |");

        }
        else
        {
            if (!in_array($templinecitation, $CondensedNotes[$note["NoteId"]]["lines"]))
            {
                array_push($CondensedNotes[$note["NoteId"]]["lines"], $templinecitation);
            }

            // var_dump($CondensedNotes[$note["NoteId"]] );
            array_push($CondensedNotes[$note["NoteId"]]["AssociatedWordId"], $note["AssociatedWordId"]);
            // echo ($note['AssociatedWordId']. " x");

            // $CondensedNotes[$note["NoteId"]]["lines"] = array_unique ($CondensedNotes[$note["NoteId"]]["lines"]);
            sort($CondensedNotes[$note["NoteId"]]["lines"]);
        }
    }

    // print_r($CondensedNotes);

    usort($CondensedNotes, function ($a, $b)
    {

        $A = $a["comparableCitation"];
        $B = $b["comparableCitation"];

        if ($A != $B)
        {
            return $A < $B ? -1 : 1;
        }

        if ($a["WL"] != $b["WL"])
        {
            return $a["WL"] != "Word" ? -1 : 1;
        }

        return 0;

    });

    // print_r($CondensedNotes);

    $outputText = "";
    $lastLinesText = null;
    foreach ($CondensedNotes as $Cnote)
    {
        $outputText .= "<note ";
        $outputText .= "noteid = '" . $Cnote["NoteId"] . "'";
        $outputText .= "cc = '" . $Cnote["comparableCitation"] . "' associatedwords = '" . (gettype($Cnote["AssociatedWordId"]) == "array"
            ? implode(",", $Cnote["AssociatedWordId"]) :
            0) . "' >";
        // var_dump($Cnote["lines"]);
        $linestext = count($Cnote["lines"]) > 1 ? min($Cnote["lines"]) . "–" . max($Cnote["lines"]) : $Cnote["lines"][0];

        if ($lastLinesText != $linestext)
        {
            $outputText .= "<span style = 'font-family:Cinzel'>" . $linestext . " </span>";
        }
        else
        {
            $outputText .= "<span style = 'color:rgba(0,0,0,0); font-family:Cinzel'>" . $linestext . " </span>";
        }
        $lastLinesText = $linestext;

        $filternote = preg_replace("/[\[\];,()?!\.:\"\']/", "", $Cnote['phrase']);
        $filternote = preg_replace("/—/", "", $filternote);
        $outputText .= "<B>" . $filternote;
        $outputText .= $Cnote['phrase'] == "" ? "" : ":";
        $outputText .= " </B>";
        $outputText .= parseNoteText($Cnote['Text'], $literaryDevices);
        $outputText .= "</note> ";
        // echo implode("|", $Cnote['comparableCitation']);
    }

    return $outputText;

}

function displayVocabText($dictionary, $condensed = false)
{
    $outputtext = "";
    foreach ($dictionary as $entry)
    {
        $freq = getFrequencyByLevel($entry['id']);

        if ($condensed == true && $freq <= 10)
        {
            $outputtext .= "<b>";
            $outputtext .= preg_replace('/\*/', '', $entry['entry']);
            $outputtext .= "</b>";
            $outputtext .= " ";
            $outputtext .= "<i>";
            $outputtext .= preg_replace('/\*/', '', $entry['definition']);
            $outputtext .= "</i>";

            if (!isset($_GET['level']) || (isset($_GET['level']) && $_GET['level'] == "AP"))
            {

                $outputtext .= " ";

                $outputtext .= "(";
                // var_dump($Tmesis);
                $outputtext .= $freq;
                $outputtext .= ")";

            }

            $outputtext .= "<BR>";
        }
        else if ($condensed != true)
        {
            $outputtext .= "<vocabword id = '" . $entry['id'] . "' >";
            $outputtext .= "<span style = 'font-weight:bold;'>";
            $outputtext .= preg_replace('/\*/', '', $entry['entry']);
            $outputtext .= "</span>";
            $outputtext .= " ";
            $outputtext .= "<span style = 'font-style:italic;'>";
            $outputtext .= preg_replace('/\*/', '', $entry['definition']);
            $outputtext .= "</span>";

            if (!isset($_GET['level']) || (isset($_GET['level']) && $_GET['level'] == "AP"))
            {
                $outputtext .= " ";

                $outputtext .= "<span>(";
                // var_dump($Tmesis);
                $outputtext .= $freq;
                $outputtext .= ")</span>";

            }

            $outputtext .= "</vocabword>";
        }

    }

    return $outputtext;
}

//displayLines(true, $HWAssignment, $HWLines, $TargetedDictionary, $BookTitle)
function displayLines($showVocab, $assignment, $lines, $dictionary, $linespacing = 2)
{

    $frequencyTable = getFrequencyTable();

    $outputText = "";

    $ChapterCitationText = "";
    if ($assignment['StartChapter'] != null)
    {
        $ChapterCitationText = $assignment['StartChapter'] . ".";
    }

    if ($assignment['AddToBeginning'] > 0)
    {
        $temp_start_line = $assignment['StartLine'] - 1;
    }
    else
    {
        $temp_start_line = $assignment['StartLine'];
    }

    if ($showVocab == true)
    {
        $outputText .= "<line citation = '" . createReadableFloat($assignment['StartBook']) . "." . $ChapterCitationText . $temp_start_line . "' num = '" . $temp_start_line . "'>";
    }

    $CurrentLine = null;

    $cliticList = getCliticList($dictionary);

    foreach ($lines as $word)
    {
        if (isset($CurrentLine) && $word['lineNumber'] != $CurrentLine)
        {
            $ChapterCitationText = "";
            if ($assignment['StartChapter'] != null)
            {
                $ChapterCitationText = $word['chapter'] . ".";
            }
            if ($showVocab == true)
            {
                $outputText .= "</line>";
            }
            if ($showVocab != true)
            {
                for ($i = 0; $i < $linespacing; $i++)
                {
                    $outputText .= "<BR>";
                }
            }
            if ($showVocab == true)
            {
                $outputText .= "<line   citation = '" . createReadableFloat($word['book']) . "." . $ChapterCitationText . $word['lineNumber'] . "'    num = '" . $word['lineNumber'] . "'>";
            }
        }
        $CurrentLine = $word['lineNumber'];

        $outputText .= displayWord($word, $showVocab, $assignment, $dictionary, $linespacing, $cliticList, $frequencyTable);
    }

    if ($showVocab == true)
    {
        $outputText .= "</line>";
    }

    return $outputText;
}

function displayWord($word, $showVocab, $assignment, $dictionary, $linespacing, $cliticList, $frequencyTable)
{
    $outputText = "";

    $Noclitics = $word['word'];
    $Noclitics = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]", "", $Noclitics);
    $Clitic = "";
    $split1 = $word['word'];

    if ($word["secondaryDefId"] != -1)
    {

        preg_match('/(' . implode("|", $cliticList['no_hyphens_with_dollar_signs']) . ')/', $Noclitics, $clitics);
        $Clitic = $clitics[0];

        $Noclitics = mb_ereg_replace('(' . implode("|", $cliticList['no_hyphens_with_dollar_signs']) . ')', "", $Noclitics);

        $SplitPos = preg_match('/(' . implode("|", $cliticList['no_hyphens']) . ')[.!;,\)]?$/', $word['word'], $position, PREG_OFFSET_CAPTURE);
        $split1 = substr($word['word'], 0, $position[0][1]);
        $split2 = substr($word['word'], $position[0][1]);
    }

    if ($showVocab == true)
    {
        $outputText .= "<word  baseword = '" . $Noclitics . "' clitic = '" . $Clitic . "' defintionid = '" . $word['definitionId'] . "' wordid = '" . $word['id'] . "' id = '" . $word['id'] . "' frequency = '" . getFrequencyByLevel($word['definitionId'], Context::getLevel()) . "' reveal = ";

        if (isset($_GET['highlightedword']) && (((int) $_GET['highlightedword']) == ((int) $word['id'])))
        {
            $outputText .= "'true'";
        }
        else
        {
            $outputText .= "'false'";
        }

        $outputText .= " >";

        $outputText .= "<baseword>";

        $outputText .= "<text>";
        $outputText .= $split1;
        $outputText .= "</text>";
        $outputText .= "<nomacrons>";
        $outputText .= stripMacrons($split1);
        $outputText .= "</nomacrons>";

        $outputText .= "<entry>";
        $outputText .= "<b>";

        $outputText .= parseAsterisks($dictionary[$word['definitionId']]['entry']);

        $outputText .= "</b>";
        $outputText .= "</entry>";

        $outputText .= "<definition>";
        $outputText .= "<i>";

        $outputText .= parseAsterisks($dictionary[$word['definitionId']]['definition']);

        $outputText .= "</i>";
        $outputText .= "</definition>";

        $outputText .= "</baseword>";
        if ($word["secondaryDefId"] != -1)
        {
            $outputText .= "<clitic>";

            $outputText .= "<text>";
            $outputText .= $split2;
            $outputText .= "</text>";
            $outputText .= "<nomacrons>";
            $outputText .= stripMacrons($split2);
            $outputText .= "</nomacrons>";

            $outputText .= "<entry>";
            $outputText .= "<b>";
            $outputText .= parseAsterisks($dictionary[$word['secondaryDefId']]['entry']);
            $outputText .= "</b>";
            $outputText .= "</entry>";

            $outputText .= "<definition>";
            $outputText .= "<i>";

            $tempdeftext = $dictionary[$word['secondaryDefId']]['definition'];
            $tempdeftext = preg_replace("/\*(.*?)\*/", "</i>\\1<i>", $tempdeftext);
            $outputText .= parseAsterisks($tempdeftext);

            $outputText .= "</i>";
            $outputText .= "</definition>";

            $outputText .= "</clitic>";
        }

        $outputText .= "<freq>";

        $outputText .= "<a target = '_blank' href = ' ";
        $outputText .= " WordInfo.php?level=" . Context::getLevel() . "&wordid=" . $word['definitionId'] . "";
        $outputText .= "'>";
        $outputText .= ((int) $frequencyTable[$word['definitionId']]);
        $outputText .= "</a>";

        $outputText .= "</freq>";
    }
    else
    {
        $outputText .= $split1;
        if ($word["secondaryDefId"] != -1)
        {
            $outputText .= $split2;
        }
        $outputText .= " ";
    }

    $outputText .= "</word>";

    return $outputText;
}
