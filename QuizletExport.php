<?

require_once ( 'SQLConnection.php');

function GetAPFrequency($definitionIdNumber)
{
	$TwoWordCheck = SQLQ('SELECT `IsTwoWords` FROM `#APDictionary` WHERE `id` = ' . $definitionIdNumber);
	$Aeneiduses = SQLQ('SELECT COUNT(`id`) FROM `#APAeneidText` WHERE `definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber );
	$Tmesis = SQLQ('SELECT COUNT(`id`) FROM `#APAeneidText` WHERE (`definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber . ') and `Tmesis` = 1 ');
	$DBGuses = SQLQ('SELECT COUNT(`id`) FROM `#APDBGText` WHERE `definitionId` = ' .$definitionIdNumber . ' OR `secondaryDefId` = ' .$definitionIdNumber );
	
	$AeneidusesCount = (($Aeneiduses - ($Tmesis/2)) /( 1+(int) $TwoWordCheck ));
	
	$TotalFreq = (((int) $AeneidusesCount) + ((int) $DBGuses));

	return $TotalFreq;
}


$dictionary = SQLQuarry('SELECT `id`, `entry`, `definition` FROM `#APDictionary` WHERE `id` <> -1 ', false, "id");

foreach ($dictionary as &$entry)
{
	$entry["freq"] = GetAPFrequency($entry["id"]);
}

usort($dictionary, function ($a, $b) {
	global $Conversion;

	$a = $a['freq'];
	$b = $b['freq'];

	
	return $b <=> $a;
	

});






function QuizletExport($dictionary, $topNum = 10000, $minNum = 0, $MaxNum = 10000)
{
	
	$dictionary = array_filter($dictionary, function ($entry) use($minNum) {

		return $entry['freq'] >= $minNum;
	
	});

	$dictionary = array_filter($dictionary, function ($entry) use($MaxNum) {

		return $entry['freq'] <= $MaxNum;
	
	});

	$dictionary = array_slice($dictionary, 0, $topNum); 


	$output = '';
	foreach($dictionary as $entry)
	{
		$output .= $entry['entry'] . "𓄋".$entry['definition'] . "𓄂";
	}

	return "<textarea style = ' '>".$output."</textarea>";
}


echo QuizletExport($dictionary, 1000000 ,5 ,20);
?>