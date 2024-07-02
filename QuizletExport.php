<?

require_once ( 'SQLConnection.php');
require_once ( 'GenerateNotesandVocab.php');





function ArrayPartition(Array $list, $p) {
    $listlen = count($list);
    $partlen = floor($listlen / $p);
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for($px = 0; $px < $p; $px ++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}


echo QuizletExport($dictionary, 10000000, 1,      30 ,        10000             );

function QuizletExport($dictionary, $wordLimit = 10000, $parts = 1, $minNum = 0, $MaxNum = 10000, $ProperNouns = "exclude")
{

	if ($ProperNouns == "include")
	{
		$PNClause = "";
	}
	else if ($ProperNouns == "exclude")
	{
		$PNClause = " AND ASCII(`entry`) BETWEEN 97 AND 122 ";
	}
	else if ($ProperNouns == "only")
	{
		$PNClause = " AND ASCII(`entry`) BETWEEN 65 AND 90 ";
	}

	$dictionary = SQLQuarry('SELECT `id`, `entry`, `definition` FROM `#APDictionary` WHERE `id` <> -1 ' . $PNClause, false, "id");

	foreach ($dictionary as &$entry)
	{
		$entry["freq"] = getFrequencyByLevel($entry["id"]);
	}

	usort($dictionary, function ($a, $b) {

		$a = $a['freq'];
		$b = $b['freq'];


		return $b <=> $a;


	});


	$dictionary = array_filter($dictionary, function ($entry) use($minNum) {

		return $entry['freq'] >= $minNum;

	});

	$dictionary = array_filter($dictionary, function ($entry) use($MaxNum) {

		return $entry['freq'] <= $MaxNum;

	});

	$dictionary = array_slice($dictionary, 0, $wordLimit);

	usort($dictionary, function ($a, $b) {

		$a = stripMacrons($a['entry']);
		$b = stripMacrons($b['entry']);

		$a = preg_replace("/[, —-]/","", $a);
		$b = preg_replace("/[, —-]/","", $b);

		$a = strtolower($a);
		$b = strtolower($b);

		return $a <=> $b;


	});


	$output = array_fill(0, $parts, '');

	$split_dictionary = ArrayPartition($dictionary, $parts);
	for ($p=0; $p <$parts; $p++)
	{
		foreach($split_dictionary[$p] as $entry)
		{
			$temptext = "**".$entry['entry']."** " . "[".$entry['freq']."]" . "𓄋".$entry['definition'] . "𓄂";
			$temptext  = preg_replace("/\*\*\*\*/","", $temptext);
			$temptext  = preg_replace("/\*\*/","𓆱", $temptext);
			$temptext  = preg_replace("/\*/","", $temptext);
			$temptext  = preg_replace("/𓆱/","*", $temptext);
			$output[$p] .= $temptext;
		}
	}

	$returnable = "";

	$returnable .= "<table>";
	$returnable .= "<tr>";
	for ($p=0; $p <$parts; $p++)
	{
		$returnable .= "<td>";

			$returnable .= "<textarea cols = 50 rows = 2>AP Latin Vocab (";
			if($minNum != $MaxNum)
			{
				$returnable .= $minNum."–".$MaxNum;
			}
			else
			{
				$returnable .= $MaxNum;
			}
			$returnable .= " uses)";
			if($parts>1)
			{
				$returnable .= " [part ".($p + 1)." of ".$parts."]";
			}
			else
			{
				$returnable .= "";
			}
			$returnable .= "</textarea>";
			$returnable .= "<BR>";
			$returnable .= "<textarea cols = 50 rows = 2>";
			if($parts>1)
			{
				$returnable .= "Part ".($p + 1)." of the ";
			}
			else
			{
				$returnable .= "";
			}
			$returnable .= "Latin vocabulary used";
			if (abs($minNum - $MaxNum) == 1)
			{
				$returnable .= " ".$minNum." or ".$MaxNum;
			}
			else if($minNum != $MaxNum)
			{
				$returnable .= " between ".$minNum." and ".$MaxNum;
			}
			else
			{
				$returnable .= " ".$MaxNum;
			}
			$returnable .= " time";
			if($MaxNum != 1)
			{
				$returnable .= "s";
			}
			$returnable .= " in the AP Latin curriculum</textarea>";



			$returnable .= "<BR>";
			$returnable .= "<textarea cols = 55 rows = 8 style = ' '>".$output[$p]."</textarea>";


			$returnable .= "<BR>(".count($split_dictionary[$p]);
			$returnable .= " words)";

		$returnable .= "</td>";

		if($p%3==0 && $p!=0)
		{
			$returnable .= "</tr>";
			$returnable .= "<tr>";

		}
	}
	$returnable .= "</tr>";
	$returnable .= "</table>";


	return $returnable;
}


?>
