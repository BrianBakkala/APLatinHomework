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
	font-weight: bold;
}


defintionheader {
	display: block;
	font-size: 2vw;
	font-style: italic;
}


</STYLE>

<?php

require_once ( 'SQLConnection.php');

if(isset($_GET['wordid']))
{
	$word = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `#APDictionary` WHERE `id` = "'.$_GET['wordid'].'"')[0];
}

echo "<A href = 'Dictionary.php'>← Dictionary</A>";
echo "<BR>";

echo "<entryheader>";
echo $word['entry'];
echo "</entryheader>";

echo "<defintionheader>";
echo $word['definition'];
echo "</defintionheader>";
echo "<BR><BR>";


$usesV  = SQLQuarry('SELECT `id`, `book`, `lineNumber`, `word` FROM `#APAeneidText` WHERE `definitionId` = ' .$word['id'] . '   OR  `secondaryDefId` = ' .$word['id'] . '  ORDER BY `book`, `lineNumber`, `id` ');
$usesC  = SQLQuarry('SELECT `id`, `book`, `chapter`, `lineNumber`, `word` FROM `#APDBGText` WHERE `definitionId` = ' .$word['id'] . '   OR  `secondaryDefId` = ' .$word['id'] . '  ORDER BY `book`, `chapter`, `lineNumber`, `id` ');

$VergilUseString = "";
$CaesarUseString = "";


for($u = 0; $u < count($usesV); $u++)
{

	$VergilLineNumber = (((int) $usesV[$u]['lineNumber'])-0);
	$VergilPrevLineNumber = 0;
	$VergilNextLineNumber = 0;

	
	$AttLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APAeneidText`  WHERE `lineNumber` = '.$usesV[$u]['lineNumber'].' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' );
	
	if(SQLQ(' SELECT  `id` FROM `#APAeneidText`  WHERE `lineNumber` = '. ($VergilLineNumber-1) .' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' ))
	{
		$VergilPrevLineNumber = ($VergilLineNumber-1);
		$AttPrevLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APAeneidText`  WHERE `lineNumber` = '. $VergilPrevLineNumber .' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' );
	}
	else
	{
		$AttPrevLine = "";
	}

		
	if(SQLQ(' SELECT  `id` FROM `#APAeneidText`  WHERE `lineNumber` = '. ($VergilLineNumber+1) .' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' ))
	{
		$VergilNextLineNumber = ($VergilLineNumber+1);
		$AttNextLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APAeneidText`  WHERE `lineNumber` = '. $VergilNextLineNumber .' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' );
	}
	else
	{
		$AttNextLine = "";
	}


	//$AttNextLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APAeneidText`  WHERE `lineNumber` = '.$usesV[$u]['lineNumber'].' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' );
	
	$SearchableWord = $usesV[$u]['word'];
	$SearchableWord = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$SearchableWord);
	
	
	$RegexStatement = "(^|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])(".$SearchableWord.")($|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])";
	$AttLine = mb_ereg_replace($RegexStatement, "\\1<highlight>\\2</highlight>\\3", $AttLine, "i"); 
	
	
	$VergilUseString .= "<attestation wordid = '".$usesV[$u]['id']."'>";
	
	if($VergilPrevLineNumber != 0)
	{
		$VergilUseString .= "<attline>";
			$VergilUseString .= "<attcitation>";
			$VergilUseString .= $usesV[$u]['book'];
			$VergilUseString .= "." . $VergilPrevLineNumber;
			$VergilUseString .= "</attcitation>";
			$VergilUseString .= $AttPrevLine . "<BR>";
		$VergilUseString .= "</attline>";
	}
	
	$VergilUseString .= "<attline  main = 'true'>";
		$VergilUseString .= "<attcitation>";
		$VergilUseString .= $usesV[$u]['book'];
		$VergilUseString .= "." . $VergilLineNumber;
		$VergilUseString .= "</attcitation>";
		$VergilUseString .= $AttLine . "<BR>";
	$VergilUseString .= "</attline>";

	if($VergilNextLineNumber != 0)
	{
		$VergilUseString .= "<attline>";
			$VergilUseString .= "<attcitation>";
			$VergilUseString .= $usesV[$u]['book'];
			$VergilUseString .= "." . $VergilNextLineNumber;
			$VergilUseString .= "</attcitation>";
			$VergilUseString .= $AttNextLine . "<BR>";
		$VergilUseString .= "</attline>";
	}

	$VergilUseString .= "</attestation>";
	
}

for($u = 0; $u < count($usesC); $u++)
{

	$CaesarLineNumber = (((int) $usesC[$u]['lineNumber'])-0);
	$CaesarPrevLineNumber = 0;
	$CaesarNextLineNumber = 0;

	
	$AttLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APDBGText`  WHERE `lineNumber` = '.$usesC[$u]['lineNumber'].' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' );
	
	if(SQLQ(' SELECT  `id` FROM `#APDBGText`  WHERE `lineNumber` = '. ($CaesarLineNumber-1) .' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' ))
	{
		$CaesarPrevLineNumber = ($CaesarLineNumber-1);
		$AttPrevLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APDBGText`  WHERE `lineNumber` = '. $CaesarPrevLineNumber .' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' );
	}
	else
	{
		$AttPrevLine = "";
	}

		
	if(SQLQ(' SELECT  `id` FROM `#APDBGText`  WHERE `lineNumber` = '. ($CaesarLineNumber+1) .' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' ))
	{
		$CaesarNextLineNumber = ($CaesarLineNumber+1);
		$AttNextLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APDBGText`  WHERE `lineNumber` = '. $CaesarNextLineNumber .' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' );
	}
	else
	{
		$AttNextLine = "";
	}


	//$AttNextLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APDBGText`  WHERE `lineNumber` = '.$usesC[$u]['lineNumber'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' );
	
	$SearchableWord = $usesC[$u]['word'];
	$SearchableWord = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$SearchableWord);
	
	
	$RegexStatement = "(^|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])(".$SearchableWord.")($|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])";
	$AttLine = mb_ereg_replace($RegexStatement, "\\1<highlight>\\2</highlight>\\3", $AttLine, "i"); 
	
	
	$CaesarUseString .= "<attestation wordid = '".$usesC[$u]['id']."'>";

	if($CaesarPrevLineNumber != 0)
	{
		$CaesarUseString .= "<attline>";
			$CaesarUseString .= "<attcitation>";
			$CaesarUseString .= $usesC[$u]['book'];
			$CaesarUseString .= ".";
			$CaesarUseString .= $usesC[$u]['chapter'];
			$CaesarUseString .= "." . $CaesarPrevLineNumber;
			$CaesarUseString .= "</attcitation>";
			$CaesarUseString .= $AttPrevLine . "<BR>";
		$CaesarUseString .= "</attline>";
	}

	
	$CaesarUseString .= "<attline  main = 'true'>";
		$CaesarUseString .= "<attcitation>";
		$CaesarUseString .= $usesC[$u]['book'];
		$CaesarUseString .= ".";
		$CaesarUseString .= $usesC[$u]['chapter'];
		$CaesarUseString .= "." . $CaesarLineNumber;
		$CaesarUseString .= "</attcitation>";
		$CaesarUseString .= $AttLine . "<BR>";
	$CaesarUseString .= "</attline>";

	if($CaesarNextLineNumber != 0)
	{
		$CaesarUseString .= "<attline>";
			$CaesarUseString .= "<attcitation>";
			$CaesarUseString .= $usesC[$u]['book'];
			$CaesarUseString .= ".";
			$CaesarUseString .= $usesC[$u]['chapter'];
			$CaesarUseString .= "." . $CaesarNextLineNumber;
			$CaesarUseString .= "</attcitation>";
			$CaesarUseString .= $AttNextLine . "<BR>";
		$CaesarUseString .= "</attline>";
	}


	$CaesarUseString .= "</attestation>";
	
}

echo "<h1><i>Aeneid</i>: ". (count($usesV)/( 1+ (int) $word['IsTwoWords'])) ."</h1>"; 
echo "<attestations>"; 
echo $VergilUseString; 
echo "</attestations>"; 
echo "<h1><i>Dē Bellō Gallicō</i>: ". (count($usesC)/( 1+(int) $word['IsTwoWords'])) ."</h1>"; 
echo "<attestations>"; 
echo $CaesarUseString; 
echo "</attestations>"; 



?>
 



 
<BR><BR><BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR><BR><BR>