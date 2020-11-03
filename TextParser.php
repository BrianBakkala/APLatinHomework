<form id = 'form'  onchange = "Parse()"  >

<textarea onchange = "Parse()"  onkeyup = "Parse()" rows = 10 cols = 80 id = 'textarea'></textarea>
<BR>
Text<select id = 'author'  onchange = "Parse()"  >
<option >^Latin4InCatilinamText</option>
<option selected>#APAeneidText</option>
<option >#APDBGText</option>
</select>
<BR>
Book<input min = 0 max = 20 onchange = "Parse()"  id = 'book' type = 'number' value = 0>
<BR>
Chapter<input min = 0 max = 10000 onchange = "Parse()"  id = 'chapter' type = 'number' value = 0>
<BR> 
Line Number Start<input min = 0 max = 10000 onchange = "Parse()"  id = 'line' type = 'number' value = 1>
<input type="reset" value="Reset"   onclick = 'document.getElementById("output").style.display = "none"' >
</form>

<BR>
<TITLE>Text Parser</TITLE>

<BR><BR><BR><BR> 

<textarea style = 'display:none;' id = 'output' rows = 20 cols = 100></textarea>

<script>
function Parse()
{

//INSERT INTO `#APAeneidText` (`id`, `word`, `definitionId`, `book`, `lineNumber`, `secondaryDefId`) VALUES (NULL, 'canō', '0', '1', '1', '0');

RawText = document.getElementById('textarea').value

Lines = RawText.split('\n')
Lines = Lines.filter(x=>x!="")

OutputText = ""

LineNumberDetected = false;
i=0
StartLineNumber = 1;

while (!LineNumberDetected && i < Lines.length)
{
	if((/\d+/g).test(Lines[i]))
	{
		StartLineNumber = (+(Lines[i]).match(/\d+/g)[0]-i)
		document.getElementById('line').value = StartLineNumber
		LineNumberDetected = true;
	}
	i++
}

for (l=0; l< Lines.length; l++)
{
	Words = Lines[l].split(' ')

	for (w= 0; w < Words.length; w++)
	{
		if(Words[w].length >1 && !parseInt(Words[w]))
		{
			LineStart = document.getElementById('line').value
			BookNumber = document.getElementById('book').value
			ChapterNumber = document.getElementById('chapter').value
			Author = document.getElementById('author').value
			Words[w] = Words[w].replace(/'/g, '"') 
			Words[w] = Words[w].replace(/\d/g, '') 


			OutputText += "INSERT INTO `"+Author+"` (`word`,  `book`,  `chapter`, `lineNumber`, `definitionId`, `secondaryDefId`  ) VALUES ( '"+Words[w]+"', '"+BookNumber+"', '"+ChapterNumber+"', '"+(+LineStart+l)+"', 0, -1 );"
			OutputText += "\n"
		}

	}


}


document.getElementById('output').value = OutputText
if(document.getElementById('chapter').value == 0 || document.getElementById('book').value == 0 || document.getElementById('chapter').value == 0)
{
	document.getElementById('output').style.display = "none"
}
else
{
	document.getElementById('output').style.display = ""
}


}


</script>