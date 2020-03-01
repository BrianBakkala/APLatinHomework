<TITLE>Dictionary</TITLE>


<style> 
	html {
		text-align: center;
		font-family: "Palatino Linotype";
	}

	#filterdict {
		font-size: 3em;
		font-family: inherit;
		text-align: center;
		padding: 10px;
	}

	highlight {
		background-color: cornsilk;
	}

	word {
		display: block;
		text-align: left;
		font-size: x-large;
		border-bottom: 1px solid lightgray;
		padding-bottom: 2px;
		cursor: default;
	}

	attestations {
		display: block;
		font-size: 0;
	}

	word[reveal="true"]:not([editing="true"]) attestations {

		font-size: inherit;
	}

	word[used-in-ap="true"] {
		/* background-color:lightblue; */
	}

	definition,
	.editDef {
		font-style: italic;
		font-family: inherit;
		font-size: inherit;
		padding-left: 5px;
	}

	entry,
	.editEntry {
		font-weight: bold;
		font-family: inherit;
		font-size: inherit;
		padding-left: 5px;
	}

	.editDef,
	.editEntry {
		padding: 10px;
		background-color: lightyellow;
	}

	.deletebutton {
		display: none;
	}

	.editbutton,
	.infobutton,
	.deletebutton {
		position: relative;
		height: 24px;
		top: 4px;
		opacity: 0;
	}

	word:hover .editbutton, word:hover .infobutton  {
		cursor: pointer;
		padding-left: 18px;
		opacity: 1;
	}

	word:hover .deletebutton {
		padding-left: 10px;
		cursor: pointer;
		opacity: 1;
	}

	.savebutton {
		cursor: pointer;
		position: relative;
		height: 38px;
		top: 10px;
		padding-left: 10px;
	}

	.spinner {
		display: inline-block;
		position: relative;
		top: 1px;
		left: 1px;
		border: 3px solid rgba(0, 0, 0, 0);
		border-radius: 50%;
		border-top: 3px solid black;
		width: 13px;
		height: 15px;
		-webkit-animation: spin .75s linear infinite;
		animation: spin .75s linear infinite;
	}

	.spinnerbig {
		display: inline-block;
		position: relative;
		top: 1px;
		left: 1px;
		border: 10px solid rgba(0, 0, 0, 0);
		border-radius: 100%;
		border-top: 10px solid black;
		width: 5em;
		height: 5em;
		-webkit-animation: spin 1s linear infinite;
		animation: spin 1s linear infinite;
	}

	/* Safari */
	@-webkit-keyframes spin {
		0% {
			-webkit-transform: rotate(0deg);
		}

		100% {
			-webkit-transform: rotate(360deg);
		}
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}



	attestation {
		position: relative;
		display: inline-block;
		border-bottom: 1px dotted black;
	}

	/* Tooltip text */
	attestation attline {
		visibility: hidden;
		white-space: nowrap;
		background-color: #555;
		color: #fff;
		text-align: center;
		padding: 5px;
		border-radius: 6px;

		position: absolute;
		z-index: 1;
		bottom: 125%;
		left: 50%;
		margin-left: -60px;

		opacity: 0;
		transition: opacity 0.3s;
	}

	attestation:hover attline {
		visibility: visible;
		opacity: 1;
	}

</style>

<CENTER><BR><BR>
<body  onload = 'FilterDict(document.getElementById("filterdict").value);'>
<input placeholder = 'Search...' value = '<?php if (isset($_GET['word'])){echo $_GET['word'];}?>'   onkeyup = 'FilterDict(this.value);' type = "text" id = 'filterdict'><BR><BR>
<div style = 'display:none;' id = 'searchResult'></div>
<dictionary id = 'dictionary'>
</dictionary>

<script>
function FilterDict(filterText)
{
	if (document.getElementById('dictionary').getElementsByClassName('spinnerbig').length == 0)
	{
		document.getElementById('dictionary').innerHTML = "<BR><BR><BR><DIV class = 'spinnerbig'><DIV>"
	}

	if (typeof(xmlhttp) != "undefined")
	{
		xmlhttp.abort()
	}

	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function()
	{
		if (this.readyState == 4 && this.status == 200)
		{
			Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
			if (document.getElementById('filterdict').value == filterText && document.getElementById('searchResult').innerText != filterText)
			{
				document.getElementById('dictionary').innerHTML = Response;
				document.getElementById('searchResult').innerText = filterText;
			}
		}
	};

	XMLURL = "AJAXAPL.php?filterdictionary=true&filtertext=" + filterText;
	xmlhttp.open("GET", XMLURL, true);
	xmlhttp.send();
	// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL);
	// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;

}

function GetWordInfo(clickedElement)
{
	WordElement = clickedElement
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}

	window.open( 'WordViewer.php?wordid=' + WordElement.getAttribute('wordid'), '_blank');
}

function EditEntry(clickedElement)
{
	if (typeof(WordElement) != "undefined")
	{
		SaveEntry(WordElement)
	}
	WordElement = clickedElement
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}

	if (WordElement.getAttribute('editing') != "true")
	{
		WordElement.setAttribute('editing', 'true')

		EntryStatic = WordElement.getElementsByTagName('entry')[0]
		DefStatic = WordElement.getElementsByTagName('definition')[0]

		WordElement.getElementsByClassName('editbutton')[0].style.display = "none"
		WordElement.getElementsByClassName('deletebutton')[0].style.display = "none"
		WordElement.getElementsByClassName('savebutton')[0].style.display = ""
		EntryStatic.style.display = "none"
		DefStatic.style.display = "none"

		EntryEle = document.createElement('input')
		EntryEle.setAttribute('type', "text")
		EntryEle.setAttribute('class', "editEntry")
		EntryEle.setAttribute('size', EntryStatic.innerText.length + 5)
		EntryEle.setAttribute('value', EntryStatic.innerText)
		EntryEle.onfocusout = function(e)
		{
			console.log(e.srcElement)
		}

		DefinitionEle = document.createElement('input')
		DefinitionEle.setAttribute('type', "text")
		DefinitionEle.setAttribute('class', "editDef")
		DefinitionEle.setAttribute('size', DefStatic.innerText.length + 5)
		DefinitionEle.setAttribute('value', DefStatic.innerText)
		DefinitionEle.focus()
		DefinitionElonfocusout = function(e)
		{
			console.log(e.srcElement)
		}

		SpinnerEle = document.createElement('div')
		SpinnerEle.setAttribute('class', "spinner")
		SpinnerEle.style.display = "none"

		WordElement.insertBefore(SpinnerEle, WordElement.lastChild)
		WordElement.insertBefore(DefinitionEle, WordElement.firstChild)
		WordElement.insertBefore(EntryEle, WordElement.firstChild)
	}
}

function SaveEntry(WordElement)
{

	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}

	if (WordElement.getAttribute('editing') == "true")
	{

		WordElement.setAttribute('editing', '')
		WordId = WordElement.getAttribute('wordid')

		NewEntry = WordElement.getElementsByClassName('editEntry')[0].value
		NewDef = WordElement.getElementsByClassName('editDef')[0].value
		NewDef = NewDef.replace(/\+/g, '%2B')

		OldEntry = WordElement.getElementsByTagName('entry')[0].innerText
		OldDef = WordElement.getElementsByTagName('definition')[0].innerText

		WordElement.getElementsByClassName('editEntry')[0].parentElement.removeChild(WordElement.getElementsByClassName('editEntry')[0])
		WordElement.getElementsByClassName('editDef')[0].parentElement.removeChild(WordElement.getElementsByClassName('editDef')[0])

		WordElement.getElementsByTagName('entry')[0].style.display = ""
		WordElement.getElementsByTagName('definition')[0].style.display = ""
		WordElement.getElementsByClassName('editbutton')[0].style.display = ""
		WordElement.getElementsByClassName('deletebutton')[0].style.display = ""
		WordElement.getElementsByClassName('savebutton')[0].style.display = "none"

		if (NewEntry != OldEntry || NewDef != OldDef)
		{

			SpinnerEle = WordElement.getElementsByClassName('spinner')[0]
			SpinnerEle.style.display = ""
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function()
			{
				if (this.readyState == 4 && this.status == 200)
				{
					Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
					Response = JSON.parse(Response)
					WordElement.getElementsByTagName('entry')[0].innerText = Response['entry']
					WordElement.getElementsByTagName('definition')[0].innerText = Response['definition']
					SpinnerEle.parentElement.removeChild(SpinnerEle)
				}
			};

			XMLURL = "AJAXAPL.php?updatedictionary=true&wordid=" + WordId + "&newdefinition=" + NewDef + "&newentry=" + NewEntry;
			xmlhttp.open("GET", XMLURL, true);
			xmlhttp.send();
			console.log(NewDef)
			// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL);
			// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;

		}

	}

}

function DeleteEntry(clickedElement)
{

	WordElement = clickedElement
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement
	}
	if (confirm("Are you sure you would like to delete the entry \"" + WordElement.getElementsByTagName("entry")[0].innerText + "\"?"))
	{
		WordId = WordElement.getAttribute('wordid')
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				WordElement.parentElement.removeChild(WordElement)
			}
		};

		XMLURL = "AJAXAPL.php?deletedictionaryentry=true&wordid=" + WordId;
		xmlhttp.open("GET", XMLURL, true);
		xmlhttp.send();
		console.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
		// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;
	}

}

</script>
