<?php

require_once 'FontStyles.php';

require_once 'GenerateNotesandVocab.php';

use app\Context;

 

$Devices = SQLQuarry('SELECT `id`, `Device`, `Description` FROM `#APLiteraryDevices` ORDER BY `Device` ');

echo "<p  style='text-align:left;'><a href='Dictionary.php'>← Dictionary</a></p>";

foreach ($Devices as $dictionary)
{
    echo "<device>";

    echo "<div class = 'deviceName'>";
    echo $dictionary['Device'];
    echo "</div>";
    echo "<div class = 'deviceDefinition'>";
    echo parseNoteText($dictionary['Description'], false);
    echo "</div>";

    echo "<div class = 'deviceUses'>";

    $Uses = SQLQuarry('SELECT MIN(`#APNotesLocations`.`AssociatedWordId`) as `WordId`, `BookTitle` FROM `#APNotesText` INNER JOIN `#APNotesLocations` ON (`#APNotesText`.`NoteId` = `#APNotesLocations`.`NoteId` ) WHERE `Text` LIKE "%***' . $dictionary['Device'] . '***%" GROUP BY `#APNotesLocations`.`NoteId`');

    foreach ($Uses as $ind => $u)
    {
        if ($ind > 0)
        {
            echo ", ";
        }

        echo parseNoteText("<<" . $u['WordId'] . ">>", false, $u['BookTitle']);
    }

    echo "</div>";
    echo "</device>";
}

?>

<link rel="stylesheet" href="css/literary-devices.css<?php echo "?" . date("mds"); ?>">