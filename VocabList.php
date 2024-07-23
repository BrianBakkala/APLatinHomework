<html translate="no">

<TITLE>AP Latin Vocab List</TITLE>


<?php

require_once 'FontStyles.php';
require_once 'GenerateNotesandVocab.php';
require_once 'autoload.php';

use app\Context;

?>



<link rel="stylesheet" href="css/vocab-list.css<?php echo "?" . date("mds"); ?>">



<script>
function GetWordInfo(clickedElement)
{
	WordElement = clickedElement
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}

	window.open( 'WordInfo.php?wordid=' + WordElement.getAttribute('wordid'), '_blank');
}


</script>

<?php

require_once 'SQLConnection.php';
require_once 'GenerateNotesandVocab.php';

$Conversion = [
    "-" => "-",

    "ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
    "Ā" => "a", "Ē" => "e", "Ī" => "i", "Ō" => "o", "Ū" => "u", "Ȳ" => "y",
    "a" => "a", "b" => "b", "c" => "c", "d" => "d", "e" => "e", "f" => "f", "g" => "g", "h" => "h", "i" => "i", "j" => "j", "k" => "k", "l" => "l", "m" => "m", "n" => "n", "o" => "o", "p" => "p", "q" => "q", "r" => "r", "s" => "s", "t" => "t", "u" => "u", "v" => "v", "w" => "w", "x" => "x", "y" => "y", "z" => "z",
    "A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h", "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z",
];

$Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `#APDictionary` ');
$words_ids = array_map(function ($x)
{
    return $x['id'];
}, $Dictionary);

$Frequencies = getFrequencyTable($words_ids);

if (isset($_GET['unit']))
{
    $unit = $_GET['unit'];
    $Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `#APDictionary` ');
    $Frequencies = getFrequencyTable($words_ids, getHomeworkAssignmentsInUnits($unit));
}

foreach ($Dictionary as $index => $word)
{
    if (isset($_GET['unit']) && $Frequencies[$word['id']] == 0)
    {
        unset($Dictionary[$index]);
    }
    else
    {
        $Dictionary[$index]['frequency'] = $Frequencies[$word['id']];
    }
}

usort($Dictionary, function ($a, $b)
{
    global $Conversion;

    $A = $a;
    $B = $b;
    $a = $a['entry'];
    $b = $b['entry'];

    $a = mb_ereg_replace("\W", "", $a);
    $b = mb_ereg_replace("\W", "", $b);

    $a = preg_split('/(?!^)(?=.)/u', $a);
    $a = array_map(function ($x)
    {
        global $Conversion;
        return (isset($Conversion[$x])) ? $Conversion[$x] : $x;
    }, $a);
    $a = implode("", $a);

    $b = preg_split('/(?!^)(?=.)/u', $b);
    $b = array_map(function ($x)
    {
        global $Conversion;
        return (isset($Conversion[$x])) ? $Conversion[$x] : $x;
    }, $b);
    $b = implode("", $b);

    if ($B['frequency'] != $A['frequency'])
    {
        return $B['frequency'] <=> $A['frequency'];
    }
    else
    {
        if (strtolower($a) < strtolower($b))
        {
            return -1;
        }
        else if (strtolower($a) > strtolower($b))
        {
            return 1;
        }
        else
        {
            return 0;
        };
    }

});

echo "<p  style='text-align:left;'><a href='Dictionary.php'>← Dictionary</a></p>";
echo "<units>";

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$actual_link = explode("?", $actual_link)[0];

echo "Units: ";
echo "<a href='" . $actual_link . "'>";
echo "<unit>";
echo "All";
echo "</unit>";
echo "</a>";
echo "|";
for ($u = 1; $u <= 8; $u++)
{
    // echo "<a href='".http_build_query(["unit"=> $u])  ."'>";
    echo "<a href='" . $actual_link . "?" . http_build_query(["unit" => $u]) . "'>";

    echo "<unit>";
    echo $u;
    echo "</unit>";
    echo "</a>";
}
echo "</units>";

foreach ($Dictionary as $word)
{
    if ($word['entry'] != "")
    {

        echo "<word  wordid = " . $word['id'] . "  ";
        echo ">";

        echo "<attestations>[";

        echo $word['frequency'];

        echo "] </attestations>";
        echo "<entry>";
        echo "<b>";

        echo parseAsterisks($word['entry']);

        echo "</b>";
        echo "</entry>";
        echo "<definition> ";
        echo "<i>";

        echo parseAsterisks($word['definition']);

        echo "</i>";
        echo "</definition>";

        // if($word['frequency'] <= 50)
        // {
        echo "<div  onclick = 'GetWordInfo(this) '  class = 'InfoButton' style = 'background: url(Images/LHinfo.png) no-repeat; background-size: contain;'  ></div>";

        // }

        echo "</word><BR>";
    }

}

?>



<BR><BR><BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR><BR><BR>