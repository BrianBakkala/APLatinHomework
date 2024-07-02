
function FilterDict(filterText)
{



	return latinAJAX("filter-dictionary",
		{
			level: '<?php echo Context::getLevel(); ?>',
			filter_text: filterText,
		}

		, function (r, t)
		{
			if (document.getElementById('filterdict').value == filterText)
			{
				document.getElementById('dictionary').innerHTML = Response;
				document.getElementById('searchResult').innerText = filterText;
			}
		}
	);

}

function GetWordInfo(clickedElement)
{
	WordElement = clickedElement;
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement;
	}

	window.open('WordInfo.php?level=<?php echo Context::getLevel(); ?>&wordid=' + WordElement.getAttribute('wordid'), '_blank');
}

function ConvertAntiAsterisks(input)
{

	// var temptext = input;
	// temptext = temptext.replace(/(^<i>)|(<\/i>$)/g, '');
	// temptext = temptext.replace(/<\/?highlight>/g, '');
	// temptext = temptext.replace(/<\/?i>/g, '*');




	// var temptext = input;
	// temptext = temptext.replace(/(^<i>)|(<\/i>$)/g, '');
	// temptext = temptext.replace(/<\/?highlight>/g, '');
	// temptext = temptext.replace(/<\/?i>/g, '*');



	return ConvertAntiItalicsToAsterisks(ConvertAntiBoldToAsterisks(input));
}

function ConvertAntiItalicsToAsterisks(input)
{
	var temptext = input;
	temptext = temptext.replace(/(^<i>)|(<\/i>$)/g, '');
	temptext = temptext.replace(/<\/?highlight>/g, '');
	temptext = temptext.replace(/<i>(.*?)<\/i>/g, '***$1***');
	temptext = temptext.replace(/<\/i>(.*?)<i>/g, '*$1*');

	return temptext;
}

function ConvertAntiBoldToAsterisks(input)
{
	var temptext = input;
	temptext = temptext.replace(/(^<b>)|(<\/b>$)/g, '');
	temptext = temptext.replace(/<\/?highlight>/g, '');
	temptext = temptext.replace(/<b>(.*?)<\/b>/g, '****$1****');
	temptext = temptext.replace(/<\/b>(.*?)<b>/g, '**$1**');

	return temptext;
}

function EditEntry(clickedElement)
{
	if (typeof (WordElement) != "undefined")
	{
		SaveEntry(WordElement);
	}
	WordElement = clickedElement;
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement;
	}

	if (WordElement.getAttribute('editing') != "true")
	{
		WordElement.setAttribute('editing', 'true');

		EntryStatic = WordElement.getElementsByTagName('entry')[0];
		DefStatic = WordElement.getElementsByTagName('definition')[0];

		WordElement.getElementsByClassName('editbutton')[0].style.display = "none";
		WordElement.getElementsByClassName('deletebutton')[0].style.display = "none";
		WordElement.getElementsByClassName('savebutton')[0].style.display = "";
		EntryStatic.style.display = "none";
		DefStatic.style.display = "none";

		EntryEle = document.createElement('input');
		EntryEle.setAttribute('type', "text");
		EntryEle.setAttribute('class', "editEntry");
		EntryEle.setAttribute('size', EntryStatic.innerHTML.length + 5);
		EntryEle.setAttribute('value', ConvertAntiAsterisks(EntryStatic.innerHTML));
		EntryEle.onfocusout = function (e)
		{
			console.log(e.srcElement);
		};

		DefinitionEle = document.createElement('input');
		DefinitionEle.setAttribute('type', "text");
		DefinitionEle.setAttribute('class', "editDef");
		DefinitionEle.setAttribute('size', DefStatic.innerHTML.length + 5);

		DefinitionEle.setAttribute('value', ConvertAntiAsterisks(DefStatic.innerHTML));

		DefinitionEle.focus();
		DefinitionElonfocusout = function (e)
		{
			console.log(e.srcElement);
		};

		SpinnerEle = document.createElement('div');
		SpinnerEle.setAttribute('class', "spinner");
		SpinnerEle.style.display = "none";

		WordElement.insertBefore(SpinnerEle, WordElement.lastChild);
		WordElement.insertBefore(DefinitionEle, WordElement.firstChild);
		WordElement.insertBefore(EntryEle, WordElement.firstChild);
	}
}

function SaveEntry(WordElement)
{

	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement;
	}

	if (WordElement.getAttribute('editing') == "true")
	{

		WordElement.setAttribute('editing', '');
		WordId = WordElement.getAttribute('wordid');

		NewEntry = WordElement.getElementsByClassName('editEntry')[0].value;
		NewDef = WordElement.getElementsByClassName('editDef')[0].value;
		NewDef = NewDef.replace(/\+/g, '%2B');

		OldEntry = WordElement.getElementsByTagName('entry')[0].innerHTML;
		OldEntry = ConvertAntiAsterisks(OldEntry);
		OldDef = WordElement.getElementsByTagName('definition')[0].innerHTML;
		OldDef = ConvertAntiAsterisks(OldDef);

		WordElement.getElementsByClassName('editEntry')[0].parentElement.removeChild(WordElement.getElementsByClassName('editEntry')[0]);
		WordElement.getElementsByClassName('editDef')[0].parentElement.removeChild(WordElement.getElementsByClassName('editDef')[0]);

		WordElement.getElementsByTagName('entry')[0].style.display = "";
		WordElement.getElementsByTagName('definition')[0].style.display = "";
		WordElement.getElementsByClassName('editbutton')[0].style.display = "";
		WordElement.getElementsByClassName('deletebutton')[0].style.display = "";
		WordElement.getElementsByClassName('savebutton')[0].style.display = "none";

		console.log(NewDef == OldDef);
		if (NewEntry != OldEntry || NewDef != OldDef)
		{

			SpinnerEle = WordElement.getElementsByClassName('spinner')[0];
			SpinnerEle.style.display = "";
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function ()
			{
				if (this.readyState == 4 && this.status == 200)
				{
					Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '');
					Response = JSON.parse(Response);
					WordElement.getElementsByTagName('entry')[0].innerText = Response['entry'];

					var temptext = Response['definition'];
					temptext = temptext.replace(/\*(.*?)\*/g, '</i>$1<i>');
					temptext = "<i>" + temptext + "</i>";
					WordElement.getElementsByTagName('definition')[0].innerHTML = temptext;


					var temptext = Response['entry'];
					temptext = temptext.replace(/\*\*(.*?)\*\*/g, '</b>$1<b>');
					temptext = "<b>" + temptext + "</b>";
					WordElement.getElementsByTagName('entry')[0].innerHTML = temptext;

					SpinnerEle.parentElement.removeChild(SpinnerEle);
				}
			};

			XMLURL = "AJAXAPL.php?update-dictionary=true&level=<?php echo Context::getLevel(); ?>&wordid=" + WordId + "&newdefinition=" + NewDef + "&newentry=" + NewEntry;
			xmlhttp.open("GET", XMLURL, true);
			xmlhttp.send();
			console.log(NewDef);
			// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL);
			// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;

		}

	}

}

function DeleteEntry(clickedElement)
{

	WordElement = clickedElement;
	while (WordElement.tagName.toLowerCase() != 'word')
	{
		WordElement = WordElement.parentElement;
	}
	if (confirm("Are you sure you would like to delete the entry \"" + WordElement.getElementsByTagName("entry")[0].innerText + "\"?"))
	{
		WordId = WordElement.getAttribute('wordid');
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function ()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				WordElement.parentElement.removeChild(WordElement);
			}
		};

		XMLURL = "AJAXAPL.php?delete-dictionary-entry=true&level=<?php echo Context::getLevel(); ?>&wordid=" + WordId;
		xmlhttp.open("GET", XMLURL, true);
		xmlhttp.send();
		console.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
		// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;
	}

}

