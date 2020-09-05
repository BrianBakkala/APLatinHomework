<?php

require_once ( 'SQLConnection.php'); 

$hint = "";

$Conversion = [
	"-" => "-",

	"ā" => "a", "ē" => "e", "ī" => "i", "ō" => "o", "ū" => "u", "ӯ" => "y",
	"Ā" => "a", "Ē" => "e", "Ī" => "i", "Ō" => "o", "Ū" => "u", "Ȳ" => "y",
	"a" => "a", "b" => "b", "c" => "c", "d" => "d", "e" => "e", "f" => "f", "g" => "g", "h" => "h", "i" => "i", "j" => "j", "k" => "k", "l" => "l", "m" => "m", "n" => "n", "o" => "o", "p" => "p", "q" => "q", "r" => "r", "s" => "s", "t" => "t", "u" => "u", "v" => "v", "w" => "w", "x" => "x", "y" => "y", "z" => "z",
	"A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h", "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z"
];


// ///////////////////////////


if (isset($_REQUEST["updatedefinition"]))
{
	//SQLRun('UPDATE `~DeanReferrals` SET `DeanNotes`="'. $_REQUEST["deansnotes"] .'" WHERE `ReferralID` = "'. $_REQUEST["referralid"] .'"');
	//   echo ('UPDATE `#AP'.$_REQUEST["authortext"].'Text` SET `definitionId` = '. $_REQUEST["def1"] .' , `secondaryDefId` = '. $_REQUEST["def2"] .'  WHERE `id` = '. $_REQUEST["wordid"] .';');
	SQLRun('UPDATE `#AP'.$_REQUEST["authortext"].'Text` SET `definitionId` = '. $_REQUEST["def1"] .' , `secondaryDefId` = '. $_REQUEST["def2"] .'  WHERE `id` = '. $_REQUEST["wordid"] .';');
	$hint = SQLQ('SELECT `definition` FROM `#APDictionary` WHERE  `id` = '. $_REQUEST["def1"] .'  ');
	if( $_REQUEST["def2"]  != -1)
	{
		$hint .= " | ";
		$hint .= SQLQ('SELECT `definition` FROM `#APDictionary` WHERE  `id` = '. $_REQUEST["def2"] .'  ');
	}
} 

if (isset($_REQUEST["filterdictionary"]))
{
	if(strlen($_REQUEST["filtertext"]) < 2)
	{
		$hint = "Too many results.";
	}
	else
	{
		$nomacronsfilterarray = preg_split('/(?!^)(?=.)/u', $_REQUEST["filtertext"]);
		$nomacronsfiltertext = implode("",array_map(function($x)
			{
				global $Conversion;
				return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
			}, $nomacronsfilterarray)); 


		$Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition`, `IsTwoWords` FROM `#APDictionary` WHERE `id` > 0 AND (REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`entry` , "ā", "a") , "ē", "e") , "ī", "i") , "ō", "o") , "ū", "u") , "ō", "o") LIKE "%'.$nomacronsfiltertext.'%" OR `definition` LIKE "%'.$nomacronsfiltertext.'%")'); 
		usort($Dictionary, function ($a, $b) {
			global $Conversion;

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
			
		
		});

		global $Conversion;
		foreach($Dictionary as $word)
		{

			$searchablestring = ( mb_ereg_replace("[()]", "",  $word['entry']) . " " .$word['definition']);
			$searchablestringChars = preg_split('/(?!^)(?=.)/u', $searchablestring);
			$searchablestringChars = array_map(function($x)
			{
				global $Conversion;
				return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
			}, $searchablestringChars);

			$searchablestring = implode("", $searchablestringChars);

			// $hint.=preg_match('/'. $_REQUEST["filtertext"] . '/', $searchablestring ) ;

			$filterString =$_REQUEST["filtertext"];
			$filterString = preg_split('/(?!^)(?=.)/u', $filterString);
			$filterString=array_map(function($x)
			{
				global $Conversion;
				return (isset($Conversion[$x])) ?  $Conversion[$x] : $x;
			}, $filterString);
			$filterString = implode("", $filterString);

			if (preg_match('/'.$filterString.	'/', $searchablestring))
			{


				$hightlightablestring = $word['entry'] . "⸻" . $word['definition'];

				if($_REQUEST["filtertext"] !== "")
				{
					$filterRegex =  $_REQUEST["filtertext"];
					$filterRegex = preg_split('/(?!^)(?=.)/u', $filterRegex);
					$filterRegex = array_map(function($x)
					{
			
						$Conversion = [
							"ā" => "[aā]", "ē" => "[eē]", "ī" => "[iī]", "ō" => "[oō]", "ū" => "[uū]", "ӯ" => "[yӯ]",
							"Ā" => "[AĀ]", "E" => "[EĒ]", "Ī" => "[IĪ]", "Ō" => "[OŌ]", "Ū" => "[UŪ]", "Ȳ" => "[YȲ]", 
							"a" => "[aā]", "e" => "[eē]", "i" => "[iī]", "o" => "[oō]", "u" => "[uū]", "y" => "[yӯ]",
							"A" => "[AĀ]", "E" => "[EĒ]", "I" => "[IĪ]", "O" => "[OŌ]", "U" => "[UŪ]", "Y" => "[YȲ]", 
						];
			
						if (isset($Conversion[$x]))
						{
							return $Conversion[$x];
			
						}
						else
						{
							return $x;
						}
			
					}, $filterRegex);
			
					$filterRegex=implode("[()]?", $filterRegex);
			
					$hightlightablestring = mb_ereg_replace("(".$filterRegex.")", "<highlight>\\1</highlight>", $hightlightablestring, "i"); 
				}

				$UsedInVergil = (SQLQ('SELECT `id` FROM `#APAeneidText` WHERE `definitionId` = ' .$word['id'])== "") ? "false" : "true";
				$UsedInVergil2 = (SQLQ('SELECT `id` FROM `#APAeneidText` WHERE `secondaryDefId` = ' .$word['id'])== "") ? "false" : "true";
				$UsedInCaesar = (SQLQ('SELECT `id` FROM `#APDBGText` WHERE `definitionId` = ' .$word['id'])== "") ? "false" : "true";
				$UsedInCaesar2 = (SQLQ('SELECT `id` FROM `#APDBGText` WHERE `secondaryDefId` = ' .$word['id'])== "") ? "false" : "true";

				// if($UsedInVergil == "true" || $UsedInVergil2  == "true" || $UsedInCaesar  == "true" || $UsedInCaesar2  == "true")
				{
				
					$hint .= "<word  wordid = ". $word['id'] ."  ";
					$hint .= " onclick = 'this.setAttribute(\"reveal\", (this.getAttribute(\"reveal\") == \"true\" ? \"false\" : \"true\"))' ";
					$hint .= ">";
					$hint .= "<entry>";
					
					$hint .= explode("⸻", $hightlightablestring)[0];

					$hint .= "</entry>";
					$hint .= "<definition>";
					
					$hint .= explode("⸻", $hightlightablestring)[1];
					
					$hint .= "</definition>"; 
					
					$usesV  = SQLQuarry('SELECT `book`, `chapter`, `lineNumber`, `word` FROM `#APAeneidText` WHERE `definitionId` = ' .$word['id'] . '   OR  `secondaryDefId` = ' .$word['id'] . '  ORDER BY `id` ');
					$Tmesis  = SQLQuarry('SELECT   `word` FROM `#APAeneidText` WHERE `definitionId` = ' .$word['id'] . '   OR  `secondaryDefId` = ' .$word['id'] . ' AND `Tmesis` = 1  ORDER BY `id` ');
					$usesC  = SQLQuarry('SELECT `book`, `chapter`, `lineNumber`, `word` FROM `#APDBGText` WHERE `definitionId` = ' .$word['id'] . '   OR  `secondaryDefId` = ' .$word['id'] . '  ORDER BY `id` ');

					$VergilUseString = "";
					
					for($u = 0; $u < count($usesV); $u++)
					{
						if($u != 0)
						{
						$VergilUseString .= ", ";
						}



						$VergilUseString .= "<attestation>";
						$VergilUseString .= "<attcitation>";
						$VergilUseString .= $usesV[$u]['book'];
						if( $usesV[$u]['chapter'] != NULL)
						{
							$VergilUseString .= "." . $usesV[$u]['chapter'];
						}
						$VergilUseString .= "." .$usesV[$u]['lineNumber'];
						$VergilUseString .= "</attcitation>";

						
						$VergilUseString .= "<attline>";

						$AttLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APAeneidText`  WHERE `lineNumber` = '.$usesV[$u]['lineNumber'].' and `book` = '.$usesV[$u]['book'].' GROUP BY `lineNumber`' );

						$SearchableWord = $usesV[$u]['word'];
						$SearchableWord = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$SearchableWord);


						$RegexStatement = "(^|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])(".$SearchableWord.")($|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])";
						$AttLine = mb_ereg_replace($RegexStatement, "\\1<span style='background-color:lightblue;'>\\2</span>\\3", $AttLine, "i"); 
		
						
						$VergilUseString .= $AttLine;
						$VergilUseString .= "</attline>";

						$VergilUseString .= "</attestation>";
						
					}

					$CaesarUseString = "";
					
					for($u = 0; $u < count($usesC); $u++)
					{
						if($u != 0)
						{
							$CaesarUseString .= ", ";
						}

						$CaesarUseString .= "<attestation>";
						$CaesarUseString .= "<attcitation>";
						$CaesarUseString .= $usesC[$u]['book'];
						if( $usesC[$u]['chapter'] != NULL)
						{
							$CaesarUseString .= "." . $usesC[$u]['chapter'];
						}
						$CaesarUseString .= "." .$usesC[$u]['lineNumber'];
						$CaesarUseString .= "</attcitation>";

						
						$CaesarUseString .= "<attline>";

						$AttLine = SQLQ(' SELECT  GROUP_CONCAT(`word` ORDER BY `id` ASC SEPARATOR " ") FROM `#APDBGText`  WHERE `lineNumber` = '.$usesC[$u]['lineNumber'].' and `chapter` = '.$usesC[$u]['chapter'].' and `book` = '.$usesC[$u]['book'].' GROUP BY `lineNumber`' );

						$SearchableWord = $usesC[$u]['word'];
						$SearchableWord = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$SearchableWord);


						$RegexStatement = "(^|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])(".$SearchableWord.")($|[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ])";
						$AttLine = mb_ereg_replace($RegexStatement, "\\1<span style='background-color:lightblue;'>\\2</span>\\3", $AttLine, "i"); 
		
						
						$CaesarUseString .= $AttLine;
						$CaesarUseString .= "</attline>";

						$CaesarUseString .= "</attestation>";
						
					}
					
					$hint .= "<img  onclick = 'SaveEntry(this) '  style = 'display:none;' class = 'savebutton' src = 'Images/LHcheck.png'>";
					$hint .= "<img  onclick = 'EditEntry(this)'  class = 'editbutton' src = 'Images/LHedit.png'>";
					$hint .= "<img  onclick = 'GetWordInfo(this) '  class = 'InfoButton' src = 'Images/LHinfo.png'>";
					$hint .= "<img  onclick = 'DeleteEntry(this) '  class = 'deletebutton' src = 'Images/LHx.png'>";
					

					$hint .= "<attestations>"; 
					$hint .= "<i>Aeneid</i>: ". ((count($usesV) - (count($TmesisV)/2)) /( 1+ (int) $word['IsTwoWords'])) ."" ;
					
					$hint .= "; ";  
			
					$hint .= "<i>Dē Bellō Gallicō</i>: ". (count($usesC)/( 1+(int) $word['IsTwoWords'])) ."" ; 
					$hint .= "</attestations>"; 


					$hint .= "</word>";
				}
			}

		}

	

		$hint === "" ? "No results." : $hint;

	}
}

if (isset($_REQUEST["deletedictionaryentry"]))
{
	SQLRun( 'DELETE FROM `#APDictionary` WHERE `id` = ' . $_REQUEST["wordid"]);
}


if (isset($_REQUEST["updatedictionary"]))
{ 
	SQLRun('UPDATE `#APDictionary` SET `entry` = "'.$_REQUEST["newentry"].'", `definition` = "'.$_REQUEST["newdefinition"].'"   WHERE `id` = '.$_REQUEST["wordid"].';');
	// $hint = ('UPDATE `#APDictionary` SET `entry` = "'.$_REQUEST["newentry"].'", `definition` = "'.$_REQUEST["newdefinition"].'"   WHERE `id` = '.$_REQUEST["wordid"].';');
	$hint = '{"definition":"'.$_REQUEST["newdefinition"].'", "entry":"'.$_REQUEST["newentry"].'"}';
}

//////////////////////////////////////


if (isset($_REQUEST["addnote"]))
{ 

	$NoteId = SQLRun('INSERT INTO `#APNotesText` (`Text`) VALUES ("'.$_REQUEST["notetext"].'");'); 
	
	
	if( $_REQUEST["wordids"] != "")
	{
		$WIDs = explode(",", $_REQUEST["wordids"]);
		foreach($WIDs as $wid)
		{
			if($wid != "")
			{
				SQLRun("INSERT INTO `#APNotesLocations` (`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `Author`) VALUES (".$NoteId.", ". $wid.", '', '".$_REQUEST["author"]."');"); 
			}
		}
	}
	else
	{
		$LIDs = explode(",", $_REQUEST["linecitations"]);
		foreach($LIDs as $lid)
		{

			if($lid != "")
			{
				if($_REQUEST["author"] == "C")
				{
					$FirstID = SQLQ('SELECT MIN(`id`)  FROM `#APAeneidText` WHERE CONCAT(`book`, ".", `lineNumber`) = '. $lid.'');
				}
				else
				{
					$FirstID = SQLQ('SELECT MIN(`id`)  FROM `#APDBGText` WHERE CONCAT(`book`, ".", `chapter`, ".", `lineNumber`) = '. $lid.'');
				}

				SQLRun ("INSERT INTO `#APNotesLocations` (`NoteId`, `AssociatedWordId`, `AssociatedLineCitation`, `Author`) VALUES (".$NoteId.", ".$FirstID." ,'". $lid."', '".$_REQUEST["author"]."');"); 
			}
		}

	}

	$hint = "Notes Added";
}

//////////////////////////////////////

$hint = trim($hint);

echo $hint == "" ? "No results" : $hint;  



?> 
