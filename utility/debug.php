
<?php 


	function var_explain($variablezzz)
	{
		highlight_string("<?php \n" .var_export($variablezzz, true).  "\n?>");
	}

	function isJson($string)
	{
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	function JSON_pretty($JSONString)
	{
		if (isJson($JSONString))
		{
			return prettyPrint($JSONString);
		}
		return $JSONString;

	}

	function prettyPrint($json)
	{
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = strlen($json);

		for ($i = 0; $i < $json_length; $i++)
		{
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if ($ends_line_level !== NULL)
			{
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ($in_escape)
			{
				$in_escape = false;
			}
			else if ($char === '"')
			{
				$in_quotes = !$in_quotes;
			}
			else if (!$in_quotes)
			{
				switch ($char)
				{
					case '}':
					case ']':
						$level--;
						$ends_line_level = NULL;
						$new_line_level = $level;
						break;

					case '{':
					case '[':
						$level++;
					case ',':
						$ends_line_level = $level;
						break;

					case ':':
						$post = " ";
						break;

					case " ":
					case "\t":
					case "\n":
					case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
						break;
				}
			}
			else if ($char === '\\')
			{
				$in_escape = true;
			}
			if ($new_line_level !== NULL)
			{
				$result .= "\n".str_repeat("\t", $new_line_level);
			}
			$result .= $char.$post;
		}

		return $result;
	}

	function echoHint($hint)
	{
		if( $_SERVER['HTTP_HOST'] == "aplatin.altervista.org" ||  (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], "aplatin.altervista") && !str_contains($_SERVER['HTTP_REFERER'], $_SERVER['SCRIPT_NAME'])))
		{

			if($hint != null && is_string($hint))
			{
				$hint = trim($hint);
			}
			
			if( isJson($hint))
			{
				header('Content-Type: application/json');
				$json_pretty = json_encode(json_decode($hint), JSON_PRETTY_PRINT);
				echo $json_pretty;
			}
			else
			{
				echo $hint;
			}

		}
		else
		{
			echo "Security check failed.";
		}
	}

?>