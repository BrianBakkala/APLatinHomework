
<TITLE>AP Latin Units</TITLE>
<?php

require_once 'globals.php';
require_once 'GenerateNotesandVocab.php';
require_once 'HomeworkViewerStyles.php';

?>


<style>

	homework[author="C"]
	{
		background-color:<?php echo $CSSColors["DBG"]['BackgroundColor']; ?>;
	}

	homework[author="V"]
	{
		background-color:<?php echo $CSSColors["Aeneid"]['BackgroundColor']; ?>;
	}

</style>

<script src="js/global/utility.js<?php echo "?" . date("mds"); ?>" defer></script>
<link rel="stylesheet" href="css/units-viewer.css<?php echo "?" . date("mds"); ?>">

<script>

function getDates()
{

	ajaxAjacis(DOCUMENT.url, {}		,
	function ({ r, t })
		{
 			const data = r.values

			for(const row of r.values)
			{
				if((row[0]).startsWith("V") || (row[0]).startsWith("C")  )
			 	{
					const hwNumber = parseInt(row[0].slice(1));
					const tempDate = new Date (row[1]);
					const formattedDate = tempDate.toLocaleString('en-US', { weekday: 'long', year: '2-digit', month: 'long', day: 'numeric' }).slice(0, -4)

					document.querySelector('homework[hw-id="'+hwNumber+'"] .due-date').innerText =formattedDate
				}

			}
		},

		"GET"
	);
}


</script>

<body onload = 'getDates()'>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'SQLConnection.php';

$UnitsCount = SQLQ('SELECT MAX(`Unit`) FROM `ap_homework` ');

for ($u = 1; $u <= $UnitsCount; $u++)
{
    // echo "<h2>Unit ".$u."</h2>";

    $HWsCount = SQLQ('SELECT COUNT(`Unit`) FROM `ap_homework` WHERE `Unit` = ' . $u);
    // var_dump(SQLQuarry('SELECT * FROM `ap_homework`  '));
    $HWs = SQLQuarry('SELECT `HW`, `BookTitle`,`StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author` FROM `ap_homework` WHERE `Unit` = ' . $u);
    echo "<unit  >";

    for ($hw = 0; $hw < $HWsCount; $hw++)
    {

        $assignment_temp = getHomeworkAssignment($HWs[$hw]["HW"], "ap_homework");

        $HWLines_temp = $assignment_temp['Lines'];
        $LineCount_temp = count(array_unique(array_map(function ($x)
        {
            return $x['chapter'] . "." . $x['lineNumber'];
        }, $HWLines_temp)));

        $citation = $HWs[$hw]["Author"] == "C" ? $HWs[$hw]["StartBook"] . "." . $HWs[$hw]["StartChapter"] . "." . $HWs[$hw]["StartLine"] . " - " . $HWs[$hw]["EndBook"] . "." . $HWs[$hw]["EndChapter"] . "." . $HWs[$hw]["EndLine"] : $HWs[$hw]["StartBook"] . "." . $HWs[$hw]["StartLine"] . " - " . $HWs[$hw]["EndBook"] . "." . $HWs[$hw]["EndLine"];
        echo "<a    href = 'http://aplatin.altervista.org/HomeworkViewer.php?hw=" . $HWs[$hw]["HW"] . "'>";
        echo "<homework id = 'hw" . $HWs[$hw]["HW"] . "' hw-id = '" . $HWs[$hw]["HW"] . "' author = '" . $HWs[$hw]["Author"] . "' citation = '" . $citation . "' title = '" . $HWs[$hw]["BookTitle"] . "' style = '   border:1px solid gray; display:inline-block;'>";
        echo "<span class = 'fontL' ><B>HW" . $HWs[$hw]["HW"] . "</B></span> ";
        echo "<span class = 'fontL' >(" . $LineCount_temp . ")</span><BR>";
        echo "<span  class = 'fontS'  ><i>" . $HWs[$hw]["BookTitle"] . "</i> " . $citation . "</span><BR>";
        echo "<span class = 'due-date' id = 'duedate_" . $HWs[$hw]["HW"] . "' class = 'fontS' >&nbsp</span>";
        echo "</homework>";
        echo "</a>";
    }
    echo "</unit>";

    if ($u < ($UnitsCount))
    {
        echo "<hr style = 'width:50%; border-top: 1px solid #333;'>";
    }
}

?>


