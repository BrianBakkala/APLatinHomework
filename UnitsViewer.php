 
<TITLE>AP Latin Units</TITLE>
<?php 

	require_once ( 'HomeworkViewerStyles.php');

?>

<style> 
	html {
		text-align: center;
		font-family: "Palatino Linotype";
	}
 
	 homework[author="C"]:hover, homework[author="V"]:hover { background-color:darkgray;}

	 homework[author="C"]
	 {
		  background-color:<?php echo $CSSColors["DBG"]['BackgroundColor'];?>; 
	 }

	 homework[author="V"]
	 {
		  background-color:<?php echo $CSSColors["Aeneid"]['BackgroundColor'];?>; 
	 }
a:link { color: #000000; text-decoration: none}
a:visited { color: #000000; text-decoration: none}
a:hover{ color: #ffffff; text-decoration: underline}
a:active { color: #000000; text-decoration: none}
 
</style>



<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ( 'SQLConnection.php');

$UnitsCount = SQLQ('SELECT MAX(`Unit`) FROM `#APHW` ');

for ($u = 1; $u <= $UnitsCount; $u++)
{
	// echo "<h2>Unit ".$u."</h2>";

	$HWsCount = SQLQ('SELECT COUNT(`Unit`) FROM `#APHW` WHERE `Unit` = ' . $u);
	$HWs = SQLQuarry('SELECT `HW`, `StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author` FROM `#APHW` WHERE `Unit` = ' . $u);
	echo "<unit  >";


	
	for ($hw = 0; $hw < $HWsCount; $hw++)
	{
		$citation = $HWs[$hw]["Author"] == "C" ? $HWs[$hw]["StartBook"].".".$HWs[$hw]["StartChapter"].".".$HWs[$hw]["StartLine"]." - ".$HWs[$hw]["EndBook"].".".$HWs[$hw]["EndChapter"].".".$HWs[$hw]["EndLine"] : $HWs[$hw]["StartBook"].".".$HWs[$hw]["StartLine"]." - ".$HWs[$hw]["EndBook"].".".$HWs[$hw]["EndLine"];
		echo "<a    href = 'http://aplatin.altervista.org/HomeworkViewer.php?hw=".$HWs[$hw]["HW"]."'>";
			echo "<homework author = '".$HWs[$hw]["Author"] . "' citation = '".$citation."' style = '   border:1px solid gray; display:inline-block;  padding:18 20 18 20px; margin:5px;'>";
				echo "<span style = 'font-size:large;' >".$HWs[$hw]["HW"]."</span><BR>";
				echo "<span  style = 'font-size:small;'  >".$HWs[$hw]["Author"] . " ". $citation."</span>";
			echo "</homework>";
		echo "</a>";
	}
	echo "</unit>";

	echo "<hr style = 'width:50%; border-top: 1px solid #333;''>";
}


?>
