<?



function QuizletExport($dictionary)
{
	$output = '';
	foreach($dictionary as $entry)
	{
		$output .= $entry['entry'] . "𓄋".$entry['definition'] . "𓄂";
	}

	return "<textarea style = 'display:none;'>".$output."</textarea>";
}


?>