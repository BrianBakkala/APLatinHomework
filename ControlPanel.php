

<style>
.testModeCheckers
{
	font-family: "Crimson Text";

	padding: 10px;
	border-radius:20%;
	border: 2px solid black;
	display:inline-block;
	text-align:center;
	cursor:pointer;
}

.testModeCheckers[activated="true"]
{
	background-color:lightblue;
}

.testModeCheckers[activated="true"]::after
{
	display:block;
	font-variant:small-caps;
	font-weight:bold;
	content:"testing";
}

.cb
{
	display:none;
}

#wrapper
{
	display:inline-grid;
}

</style>


<?php>

require_once ('GoogleClassroom/APLGSI.php');  
require_once ( 'FontStyles.php');
require_once ( 'GenerateNotesandVocab.php'); 







$context = new Context; 
$levArray = array_keys($context::LevelDictDB);

$statuses = SQLQuarry('SELECT * FROM `Control Panel` ')[0];

echo "<div id = 'wrapper'>";

foreach($levArray as $level)
{
	if($statuses['TestMode'.$level] == "1")
	{
		$checkedclause = " checked ";
		$activatedclause = "true";
	}
	else
	{
		$checkedclause = "";
		$activatedclause = "false";
	}
	echo "<div class = 'testModeCheckers' activated = '".$activatedclause."'>"; 
	echo "<input ".$checkedclause." onchange = 'ToggleTestMode(this)' class = 'cb' type = 'checkbox' level = '".$level."' id = 'testModeCheckbox".$level."'>";
	echo "<label for= 'testModeCheckbox".$level."'>";
	echo "Test Mode: ".$level."";
	echo "</label>";
	echo "</div>";
}
echo "</div>";

//  $context->GetTestStatus())





?>


<script>
function ToggleTestMode(clickedElement)
{
		var Level = clickedElement.getAttribute('level')
		var newval = clickedElement.checked ? "1" : "0";


		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				var Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				clickedElement.parentElement.setAttribute('activated', clickedElement.checked)
				console.log(Response)

			}
		};

		XMLURL = "AJAXAPL.php?toggletestmode=true&newval="+newval+"&level="+Level;
		xmlhttp.open("GET", XMLURL, true);
		xmlhttp.send();
		// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
		// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;
	

}
</script>

<span onclick = 'signOut()'>signout</span>