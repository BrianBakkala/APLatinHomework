<html translate="no">

<TITLE>AP Latin Vocab List</TITLE>

 
<?php 	require_once ( 'FontStyles.php');
	
	
	?>


<style> 
 
	html {
		text-align: center; 
		background-color:lightgray;
	}

	#filterdict {
		font-size: 3em;
		font-family: inherit;
		text-align: center;
		padding: 10px;
	}

	highlight {
		/* background-color: cornsilk; */
		color: #cc2929;
	}

	word {
		display: inline-block;
		text-align: left;
		font-size: x-large;
		padding-bottom: 2px;
		cursor: default;
		width:90%;
	}

	word:nth-child(4n)
	{
		background-color: white;
	}

	word:last-child
	{
		border-bottom: 3px solid white;
	}
	
	word[used-in-ap="true"] {
		/* background-color:lightblue; */
	}

	definition,
	.editDef {
		font-family: inherit;
		font-size: inherit; 
	}

	.editDef {
		font-style: italic;
	}
	
	.editEntry {
		font-weight: bold;
	}

	entry,
	.editEntry {
		font-family: inherit;
		font-size: inherit;
		padding-left: 5px;
	}

	.editDef,
	.editEntry {
		padding: 10px;
		background-color: lightyellow;
	}
 
	.editbutton,
	.infobutton,
	.deletebutton {
		display:inline-block;
		margin-left:15px;
		position: relative;
		height: 24px;
		top: 4px;
		user-select: none;
		opacity: 0;
	}

	word:hover .editbutton, word:hover .infobutton  {
		cursor: pointer;
		padding-left: 18px;
		opacity: 1;
	}

	word:hover .deletebutton {
		padding-left: 10px;
		cursor: pointer;
		opacity: 1;
	}

	.savebutton {
		cursor: pointer;
		position: relative;
		height: 38px;
		top: 10px;
		padding-left: 10px;
	}

	.spinner {
		display: inline-block;
		position: relative;
		top: 1px;
		left: 1px;
		border: 3px solid rgba(0, 0, 0, 0);
		border-radius: 50%;
		border-top: 3px solid black;
		width: 13px;
		height: 15px;
		-webkit-animation: spin .75s linear infinite;
		animation: spin .75s linear infinite;
	}

	.spinnerbig {
		display: inline-block;
		position: relative;
		top: 1px;
		left: 1px;
		border: 10px solid rgba(0, 0, 0, 0);
		border-radius: 100%;
		border-top: 10px solid black;
		width: 5em;
		height: 5em;
		-webkit-animation: spin 1s linear infinite;
		animation: spin 1s linear infinite;
	}

	/* Safari */
	@-webkit-keyframes spin {
		0% {
			-webkit-transform: rotate(0deg);
		}

		100% {
			-webkit-transform: rotate(360deg);
		}
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}



	attestation {
		position: relative;
		display: inline-block;
		border-bottom: 1px dotted black;
	}

	/* Tooltip text */
	attestation attline {
		visibility: hidden;
		white-space: nowrap;
		background-color: #555;
		color: #fff;
		text-align: center;
		padding: 5px;
		border-radius: 6px;

		position: absolute;
		z-index: 1;
		bottom: 125%;
		left: 50%;
		margin-left: -60px;

		opacity: 0;
		transition: opacity 0.3s;
	}

	attestation:hover attline {
		visibility: visible;
		opacity: 1;
	}

</style>

<script>
function GetWordInfo(clickedElement)
{
	WordElement = clickedElement
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}

	window.open( 'WordViewer.php?wordid=' + WordElement.getAttribute('wordid'), '_blank');
}


</script>

<?php

require_once ( 'SQLConnection.php');
require_once ( 'GenerateNotesandVocab.php'); 



$Conversion = [
	"-" => "-",
	
	"ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
	"Ā" => "a", "Ē" => "e", "Ī" => "i", "Ō" => "o", "Ū" => "u", "Ȳ" => "y",
	"a" => "a", "b" => "b", "c" => "c", "d" => "d", "e" => "e", "f" => "f", "g" => "g", "h" => "h", "i" => "i", "j" => "j", "k" => "k", "l" => "l", "m" => "m", "n" => "n", "o" => "o", "p" => "p", "q" => "q", "r" => "r", "s" => "s", "t" => "t", "u" => "u", "v" => "v", "w" => "w", "x" => "x", "y" => "y", "z" => "z",
	"A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h", "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z"
];


$Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `#APDictionary` ');
$words_ids  = array_map(function($x){return $x['id'];}, $Dictionary);
$Frequencies = GetFreqTable($words_ids);

foreach($Dictionary as $index=>$word)
{
	$Dictionary[$index]['frequency'] = $Frequencies[$word['id']];
}



usort($Dictionary, function ($a, $b) {
	global $Conversion;
	
	$A = $a;
	$B = $b;
	$a = $a['entry'];
	$b = $b['entry'];
	
	$a = mb_ereg_replace("\W","",$a); 
	$b = mb_ereg_replace("\W","",$b); 
	
	$a = preg_split('/(?!^)(?=.)/u', $a);
	$a = array_map(function($x)
	{
		global $Conversion;
		return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
	}, $a);
	$a = implode("", $a);
	
	
	$b = preg_split('/(?!^)(?=.)/u', $b);
	$b = array_map(function($x)
	{
		global $Conversion;
		return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
	}, $b);
	$b = implode("", $b);
	
	if($B['frequency'] != $A['frequency'])
	{
		return $B['frequency'] <=> $A['frequency'];
	}
	else
	{
		if(strtolower($a) < strtolower($b))
		{
			return -1;
		}
		else if(strtolower($a) > strtolower($b))
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

foreach($Dictionary as $word)
{
	if($word['entry'] != "")
	{
		
		echo "<word  wordid = ". $word['id'] ."  ";
		echo ">";
		
		echo "<attestations>["; 

		echo $word['frequency'] ; 

		echo "] </attestations>"; 
	echo "<entry>";
	echo "<b>";

	echo ConvertAsterisks( $word['entry'] );

	echo "</b>";
	echo "</entry>";
	echo "<definition> ";
	echo "<i>";

	echo ConvertAsterisks( $word['definition'] ); 

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