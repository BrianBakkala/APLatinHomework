<?php require_once 'FontStyles.php';
?>

<style>
    device
    {
		text-align: left;
		font-size: x-large;
		padding-bottom: 2px;
		cursor: default;
        border-bottom:2px solid gray;

        display:grid;
        grid-template-columns: 10% 45% 45%;
	}
    device div
    {

		padding: 5px;
	}

    .deviceName
    {
        font-weight:bold;
    }
    .deviceDefinition
    {
        font-style:italic;
        text-align:left;
    }


    device:nth-child(2n)
	{
		background-color: lightgray;
	}

</style>

<?php

require_once 'GenerateNotesandVocab.php';

$context = new Context;

if (Context::getTestStatus())
{
    echo "<script>";
    echo "document.getElementsByTagName('html')[0].innerHTML = ('nope')";
    echo "</script>";
}

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