<TITLE>Dictionary Parser</TITLE>

<form id = 'form'  onchange = "Parse()"  >

<textarea onchange = "Parse()"  onkeyup = "Parse()" rows = 10 cols = 80 id = 'textarea'></textarea>
<BR>

 
</form>
entry[tab]def[tab]rank[newline]
<BR>
<BR><BR><BR><BR> 

<textarea id = 'output' rows = 20 cols = 200></textarea>

<script>
function Parse()
{

//INSERT INTO `#APAeneidText` (`id`, `word`, `definitionId`, `book`, `lineNumber`, `secondaryDefId`) VALUES (NULL, 'canō', '0', '1', '1', '0');

RawText = document.getElementById('textarea').value

Lines = RawText.split('\n')
Lines = Lines.filter(x=>x!="")

OutputText = ""

for (l=0; l< Lines.length; l++)
{
	Entry = Lines[l].split('	')[0]
	Definition = Lines[l].split('	')[1]
	Rank = Lines[l].split('	')[2]
	Entry = Entry.replace(/'/g, "\\\'")
	Definition = Definition.replace(/'/g, "\\\'")
	Entry = Entry.replace(/;/g, "&#59;")
	Definition = Definition.replace(/;/g, "&#59;")

	OutputText+= "INSERT INTO `#APDictionary` (`entry`, `definition`, `rank`) VALUES ( '"+Entry+"', '"+Definition+"', '"+Rank+"') ON DUPLICATE KEY UPDATE `entry`= '"+Entry+"', `definition` = '"+Definition+"', `rank` = '"+Rank+"' ;";
	OutputText += "\n"


}
document.getElementById('output').value = OutputText


}


</script>