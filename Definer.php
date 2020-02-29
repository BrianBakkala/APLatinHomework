<TITLE>Definer</TITLE>

<style>
	line::before
	{
		content:attr(num);
		font-size:x-large;
		vertical-align:middle;
		text-align:center;
		padding:10px
	}


	word  
	{
		text-align: center;
		border: 1px solid black;
		display:inline-block;
		background-color:lightpink;
	}

	word[selected="true"]
	{
		background-color:lightgreen;
	}

	word span
	{
		display:inline-block;
		font-size:xx-large;
		padding:10px;
	}

	word definition
	{
		padding:5px;
		display:block; 
		font-style: italic;
	}


	word wrapper
	{
		padding:5px;
		display:block; 
	}


	line
	{
		padding-bottom:50px;
		display:block;
	}



</style>

<?php

require_once('SQLConnection.php');

if(isset($_GET['author']))
{
	if($_GET['author'] == 'C')
	{
		$Author =  	"DBG";
	}
	else
	{
		$Author =  	"Aeneid";
	}
	
}
else
{
	$Author =  	"Aeneid";
}

$Book =  (int) $_GET['book'];
if(isset($_GET['chapter']))
{
	$Chapter = $_GET['chapter'];
	$ChapterClause = ' AND  `chapter` =  '.$Chapter .' ';
}
else
{
	if($Author ==  	"DBG")
	{
		$Chapter = 1;
		$ChapterClause = ' AND  `chapter` =  '.$Chapter .' ';
	}
	else
	{
		$Chapter = null;
		$ChapterClause = " ";
	}

}
$LineStart =  (int) $_GET['line'];
if(isset($_GET['lineend']))
{
	$LineEnd =  (int) $_GET['lineend'];
}
else
{
	$LineEnd = $LineStart + 4;
}

$Text = SQLQuarry('SELECT `id`, `word`, `definitionId`, `book`, `chapter`, `lineNumber`, `secondaryDefId` FROM `#AP'.$Author.'Text` WHERE  `book` =  '.$Book .' '.$ChapterClause.' AND  `lineNumber` >= '. $LineStart.' AND `lineNumber` <= '. $LineEnd.' ORDER BY `id` ');
$Dictionary = SQLQuarry('SELECT `id`, `entry`, `definition` FROM `#APDictionary`');
$DictionaryJSONText = "";

$DictionaryJSONText .= "{";
	foreach ($Dictionary as $entry)
	{
	$DictionaryJSONText .= '"'. $entry['entry'] .'"';
	$DictionaryJSONText .= ":";
	$DictionaryJSONText .= "{";
	
	$DictionaryJSONText .= '"definition":'; 
	$DictionaryJSONText .= '"'. $entry['definition'] .'"';
	$DictionaryJSONText .= ',';
	
	$DictionaryJSONText .= '"id":'; 
	$DictionaryJSONText .= '"'. $entry['id'] .'"';
	$DictionaryJSONText .= ',';
	
	$DictionaryJSONText .= '"entry":'; 
	$DictionaryJSONText .= '"'. $entry['entry'] .'"';
	
	$DictionaryJSONText .= "},";
	}
	$DictionaryJSONText .= '"—":{"definition":"—","id":"0","entry":"—"}';
	$DictionaryJSONText .= "}";

	$DictionaryJSONText = preg_replace("/'/", "&#39;", $DictionaryJSONText);  
$DictionaryJSON = json_encode($DictionaryJSONText);




echo "<line num = ".$LineStart .">";
foreach ($Text as $word)
{
	if(isset($CurrentLine))
	{
		if($CurrentLine != $word['lineNumber'])
		{
			echo "</line><line num = ".$word['lineNumber'] .">";
		}
	}
	$CurrentLine = $word['lineNumber'];

	$defintionInfo = SQLQuarry('SELECT   `definition`, `entry`, `id` FROM `#APDictionary` WHERE `id` = '.  $word['definitionId'] )[0];  

	echo "<word wordid = ".$word['id'] ."  ";

	if($defintionInfo['entry'] != "—")
	{
		echo " selected = 'true' ";
	}
	echo "";


	echo " >";

	echo "<span>";
 
	$displayWord = mb_ereg_replace("(que|ne|ve|cum)[.!;,]?$","<u>\\1</u>", $word['word']);
	echo $displayWord ;

	echo "</span>";



	//var_dump($defintionInfo);
	
	echo "<definition>";
	echo $defintionInfo['definition'];

		if($word['secondaryDefId'] != -1)
		{
			$defintionInfo2 = SQLQuarry('SELECT   `definition`, `entry`, `id` FROM `#APDictionary` WHERE `id` = '.  $word['secondaryDefId'] )[0];  
			echo " | ";
			echo $defintionInfo2['definition'];
	
		}
	echo "</definition>"; 
	
	$Noclitics =$word['word'];
	$Noclitics = mb_ereg_replace("[^A-Za-zāēīōūӯӯĀĒĪŌŪȲ]","",$Noclitics);
	if(strlen($Noclitics) > 3)
	{
		$Noclitics = mb_ereg_replace("(que$|ne$|ve$|cum$)","",$Noclitics);
	}

	echo "<wrapper>";
	echo "<select onchange = 'SaveDefintionToWord(this)' class = 'definitionDD' wordid = '".$word['id']."'  word = '".$Noclitics."' >";
			echo "<option value =  " . $word['definitionId'] . ">";
			echo $defintionInfo['entry'];
			echo "</option>";
	echo "</select>";
	
	echo "<select onchange = 'SaveDefintionToWord(this)'  class = 'definitionDD2' wordid = '".$word['id']."'  word = '".$Noclitics."' >";
		echo "<option value =  " . $word['secondaryDefId'] . ">";
		if($word['secondaryDefId'] != -1)
		{ 
			echo $defintionInfo2['entry'];
		}
		echo "</option>";
	echo "</select>";
	echo "</wrapper>";
	
	echo "</word>";


	echo " ";
}
echo "</line>";










?>

<body onload = 'PopulateDefDDs()'>


<script>

Dictionary = JSON.parse('<?php echo $DictionaryJSONText;?>');


function PopulateDefDDs()
{
	AllDDs = document.getElementsByClassName('definitionDD')
	for (dd = 0; dd < AllDDs.length; dd++)
	{

		Options = []
		Options.push(["—", 0])

		maxlength = AllDDs[dd].getAttribute('word').length

		for (wordlength = maxlength; wordlength > (maxlength - 6); wordlength--)
		{
			if (wordlength > 0)
			{
				NewOptions = []
				for (let e in Dictionary)
				{
					if (Dictionary[e]['entry'].split(/( \| |, | ?\(|\) ?| \(.*?\) )/).map(y => y.replace(/\w*\. ?/,"")).filter(x => x != "" && x != ") "  && x != ")" && x != "(" && x != " (" && x != " | " && x != ", ").filter(x => StemCheck(x, AllDDs[dd].getAttribute('word'), wordlength)).length >= 1)
					{
						if (Options.concat(NewOptions).filter(x => x[0] == Dictionary[e]['entry'] && x[1] == Dictionary[e]['id']).length == 0)
						{
							NewOptions.push([Dictionary[e]['entry'], Dictionary[e]['id']])
						}
					}
				}



				NewOptions = NewOptions.sort(function(a, b)
				{
					a = a[0]
					b = b[0]

					Conversion = 
					{
						"-":"-",
						
				
						"ā":"a", "ē":"e", "ī":"i", "ō":"o", "ū":"u", "ӯ":"y", 
						"Ā":"a", "Ē":"e", "Ī":"i", "Ō":"o", "Ū":"u", "Ȳ":"y", 
						"a":"a", "b":"b", "c":"c", "d":"d", "e":"e", "f":"f", "g":"g", "h":"h", "i":"i", "j":"j", "k":"k", "l":"l", "m":"m", "n":"n", "o":"o", "p":"p", "q":"q", "r":"r", "s":"s", "t":"t", "u":"u", "v":"v", "w":"w", "x":"x", "y":"y", "z":"z", 
						"A":"a", "B":"b", "C":"c", "D":"d", "E":"e", "F":"f", "G":"g", "H":"h", "I":"i", "J":"j", "K":"k", "L":"l", "M":"m", "N":"n", "O":"o", "P":"p", "Q":"q", "R":"r", "S":"s", "T":"t", "U":"u", "V":"v", "W":"w", "X":"x", "Y":"y", "Z":"z" 
					}

					modifieda = a.split("").map(x => Conversion[x]).join("")
					modifiedb = b.split("").map(x => Conversion[x]).join("")

					if (modifieda < modifiedb)
					{
						return -1;
					}
					if (modifieda > modifiedb)
					{
						return 1;
					}
					else
					{
						return 0;
					}

				})
				Options = Options.concat(NewOptions)

				if (wordlength != 1)
				{
					if (Options[Options.length - 1][0] != "—")
					{
						Options.push(["—", 0])
					}
				}


			}
		}

		for (o = 0; o < Options.length; o++)
		{
			O = document.createElement('option')
			O.innerText = Options[o][0]
			O.value = Options[o][1]
			AllDDs[dd].appendChild(O)
		}

	}

	AllDDs2 = document.getElementsByClassName('definitionDD2')

	Options = []
	DropDown = AllDDs2[dd]
	for (let e in Dictionary)
	{
		if (Dictionary[e]['entry'][0] == "-")
		{
			Options.push([Dictionary[e]['entry'], Dictionary[e]['id']])
		}
	}

	for (dd = 0; dd < AllDDs2.length; dd++)
	{
		O = document.createElement('option')
		O.value = "-1"
		O.innerText = ""
		AllDDs2[dd].appendChild(O)

		for (o = 0; o < Options.length; o++)
		{
			O = document.createElement('option')
			O.innerText = Options[o][0]
			O.value = Options[o][1]
			AllDDs2[dd].appendChild(O)
		}

	}

}

function SaveDefintionToWord(dropdownSelected)
{

	WordElement = dropdownSelected
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement =  WordElement.parentElement
	}
	WordId = WordElement.getAttribute('wordid')
	DefId1 = (WordElement.getElementsByClassName('definitionDD')[0].value)
	DefId2 = (WordElement.getElementsByClassName('definitionDD2')[0].value)
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function()
	{
		if (this.readyState == 4 && this.status == 200)
		{
			Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')

			if(Response != "—")
			{
				WordElement.setAttribute('selected', "true") 
			}
			else
			{
				WordElement.setAttribute('selected', "false") 
			}
			WordElement.getElementsByTagName('definition')[0].innerText = Response
		}
	};
	
	XMLURL = "AJAXAPL.php?updatedefinition=true&authortext=<?php echo $Author;?>&wordid=" +  WordId + "&def1=" + DefId1 + "&def2=" + DefId2  ;
	xmlhttp.open("GET", XMLURL, true);
	xmlhttp.send();
	// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL);
	// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;

}


function GetEntries(word)
{
	Entries = []


	for( let e in Dictionary )
	{
	 
			for (pp = 0; pp<e.split(", ").length; pp++)
			{
				if(StemCheck(e[pp],word,1) && Entries.indexOf([Dictionary[e]['entry'], Dictionary[e]['id']] ) != -1) 
				{
					Entries.push([Dictionary[e]['entry'], Dictionary[e]['id'] ])
				}
			}
	 
	}





	return Entries;
}

//            StemCheck("Ītalia", "Ītaliam", 2)

function StemCheck(a,b,length)
{
	DontBother = ['m.', 'n.', 'f.', 'm.pl.', 'f.pl.', 'n.pl.', 'c.', ]
	if(DontBother.indexOf(a) != -1 || DontBother.indexOf(b) != -1 )
	{
		return false
	}   


	Conversion = 
	{
		"-":"-",
		
		"ā":"a", "ē":"e", "ī":"i", "ō":"o", "ū":"u", "ӯ":"y", 
		"Ā":"a", "Ē":"e", "Ī":"i", "Ō":"o", "Ū":"u", "Ȳ":"y", 
		"a":"a", "b":"b", "c":"c", "d":"d", "e":"e", "f":"f", "g":"g", "h":"h", "i":"i", "j":"j", "k":"k", "l":"l", "m":"m", "n":"n", "o":"o", "p":"p", "q":"q", "r":"r", "s":"s", "t":"t", "u":"u", "v":"v", "w":"w", "x":"x", "y":"y", "z":"z", 
		"A":"a", "B":"b", "C":"c", "D":"d", "E":"e", "F":"f", "G":"g", "H":"h", "I":"i", "J":"j", "K":"k", "L":"l", "M":"m", "N":"n", "O":"o", "P":"p", "Q":"q", "R":"r", "S":"s", "T":"t", "U":"u", "V":"v", "W":"w", "X":"x", "Y":"y", "Z":"z" 
	}
	
	
	if(a && b && a.length>0 && b.length>0)
	{
		modifieda=a.split("").map(x=>Conversion[x]).join("")
		modifiedb=b.split("").map(x=>Conversion[x]).join("")
		
		modifieda=modifieda.slice(0,length)
		modifiedb=modifiedb.slice(0,length)
		
		

		return modifieda === modifiedb





	}
	else
	{
		return false
	}
}
</script>



<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>