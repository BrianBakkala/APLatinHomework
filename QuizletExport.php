<?

require_once ( 'SQLConnection.php');
require_once ( 'GenerateNotesandVocab.php');




$dictionary = SQLQuarry('SELECT `id`, `entry`, `definition` FROM `#APDictionary` WHERE `id` <> -1 ', false, "id");

foreach ($dictionary as &$entry)
{
	$entry["freq"] = GetFrequencyByLevel($entry["id"]);
}

usort($dictionary, function ($a, $b) {

	$a = $a['freq'];
	$b = $b['freq'];

	
	return $b <=> $a;
	

});






function QuizletExport($dictionary, $wordLimit = 10000, $minNum = 0, $MaxNum = 10000)
{
	
	$dictionary = array_filter($dictionary, function ($entry) use($minNum) {

		return $entry['freq'] >= $minNum;
	
	});

	$dictionary = array_filter($dictionary, function ($entry) use($MaxNum) {

		return $entry['freq'] <= $MaxNum;
	
	});

	$dictionary = array_slice($dictionary, 0, $wordLimit); 

	usort($dictionary, function ($a, $b) {
	
		$a = StripMacrons($a['entry']);
		$b = StripMacrons($b['entry']);
	
		$a = preg_replace("/[, —-]/","", $a);
		$b = preg_replace("/[, —-]/","", $b);
		
		$a = strtolower($a);
		$b = strtolower($b);
				
		return $a <=> $b;
		
	
	});


	$output = '';
	foreach($dictionary as $entry)
	{
		$temptext = "**".$entry['entry']."** " . "[".$entry['freq']."]" . "𓄋".$entry['definition'] . "𓄂";
		$temptext  = preg_replace("/\*\*\*\*/","", $temptext);
		$temptext  = preg_replace("/\*\*/","𓆱", $temptext);
		$temptext  = preg_replace("/\*/","", $temptext);
		$temptext  = preg_replace("/𓆱/","*", $temptext);
		$output .= $temptext;
	}

	return "<textarea cols = 110 rows = 20 style = ' '>".$output."</textarea>";
}


echo QuizletExport($dictionary, 100, 1 ,10000);
?>