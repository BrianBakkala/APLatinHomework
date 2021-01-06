 
<TITLE>AP Latin Units</TITLE>
<?php 

	require_once ( 'GenerateNotesandVocab.php');
	require_once ( 'HomeworkViewerStyles.php');




?>








<style> 
	html {
		text-align: center;
		font-family: "Palatino Linotype";
	}
 
	 homework[passed="true"] {
		 opacity:.5;
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
	// var_dump(SQLQuarry('SELECT * FROM `#APHW`  '));
	$HWs = SQLQuarry('SELECT `HW`, `BookTitle`,`StartBook`, `StartChapter`, `StartLine`, `EndBook`, `EndChapter`, `EndLine`, `Author` FROM `#APHW` WHERE `Unit` = ' . $u);
	echo "<unit  >";


	
	for ($hw = 0; $hw < $HWsCount; $hw++)
	{
		$citation = $HWs[$hw]["Author"] == "C" ? $HWs[$hw]["StartBook"].".".$HWs[$hw]["StartChapter"].".".$HWs[$hw]["StartLine"]." - ".$HWs[$hw]["EndBook"].".".$HWs[$hw]["EndChapter"].".".$HWs[$hw]["EndLine"] : $HWs[$hw]["StartBook"].".".$HWs[$hw]["StartLine"]." - ".$HWs[$hw]["EndBook"].".".$HWs[$hw]["EndLine"];
		echo "<a    href = 'http://aplatin.altervista.org/HomeworkViewer.php?hw=".$HWs[$hw]["HW"]."'>";
			echo "<homework id = 'hw".$HWs[$hw]["HW"]."' author = '".$HWs[$hw]["Author"] . "' citation = '".$citation."' title = '".$HWs[$hw]["BookTitle"]."' style = '   border:1px solid gray; display:inline-block;  padding:18 20 18 20px; margin:5px;'>";
				echo "<span style = 'font-size:large;' >".$HWs[$hw]["HW"]."</span><BR>";
				echo "<span  style = 'font-size:small;'  ><i>".$HWs[$hw]["BookTitle"] . "</i> ". $citation."</span><BR>";
				echo "<span class = 'dueDate' id = 'duedate".$HWs[$hw]["HW"]."' style = 'font-size:small;' >&nbsp</span>";
			echo "</homework>";
		echo "</a>";
	}
	echo "</unit>";

	if($u < ($UnitsCount))
	{
		echo "<hr style = 'width:50%; border-top: 1px solid #333;'>";
	}
}


?>


<script>



	SpreadsheetDocID = "1CKcfxPCIV2Kz7b7QAbhK6JJ5kroxVdZoreGDXvngjS8"

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				SheetData = (JSON.parse(Response).feed.entry)
				
				sd = 0;
				

				
				while ( sd < SheetData.length )
				{
					
					if((SheetData[sd].title["$t"]).startsWith("A")  )
					{
						var tempHWNum = (+(SheetData[sd].content['$t'].substring(1)))
						var tempDays = +(SheetData[sd+1]['gs$cell'].numericValue) -25568
						var tempD =  new Date ((  tempDays *1000*60*60*24))
						var tempDate = tempD.getFullYear() + "-"+ ("00"+(tempD.getMonth()+1)).slice(-2)+ "-"+ ("00"+tempD.getDate()).slice(-2)
						
						if(document.getElementById('duedate'+tempHWNum))
						{
							document.getElementById('duedate'+tempHWNum).innerText = tempD.toLocaleString('en-US', { weekday: 'long', year: '2-digit', month: 'long', day: 'numeric' }).slice(0, -4)
							document.getElementById('hw'+tempHWNum).setAttribute('passed',  ((tempD - new Date()) < 0))
						}
						




					}
					sd++
				}
			}
		};
		xmlhttp.open("GET", "https://spreadsheets.google.com/feeds/cells/" + SpreadsheetDocID + "/1/public/values?alt=json", true);
		
		xmlhttp.send();





</script>