

function InitializeWords()
{
	words = document.getElementsByTagName('word');

	for (i = 0; i < words.length; i++)
	{
		words[i].onclick = function ()
		{
			this.toggleAttribute("reveal");
		};
		// words[i].onmouseover = function()
		// {
		// 	this.setAttribute("preview", ("true"))
		// }
		// words[i].onmouseout = function()
		// {
		// 	this.setAttribute("preview", ("false"))
		// }

		words[i].ontouchstart = function ()
		{
			this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"));

			for (i = 0; i < words.length; i++)
			{
				words[i].onclick = function () { };
				words[i].onmouseover = function () { };
				words[i].onmouseout = function () { };

				words[i].ontouchstart = function ()
				{
					this.setAttribute("reveal", (this.getAttribute("reveal") == "true" ? "false" : "true"));
				};

			}

		};
	}
}
function ToggleNotes(element)
{
	const CurrentStatus = (document.getElementsByTagName("wrapper")[0].getAttribute("shownotes") == "true");
	const NewStatus = !CurrentStatus;

	document.getElementsByTagName("wrapper")[0].setAttribute("shownotes", NewStatus.toString());

	element.innerHTML = "Notes: <b>" + (NewStatus ? "on" : "off") + "</b>";

}

function ToggleMacrons(element)
{
	const CurrentStatus = (document.getElementsByTagName("wrapper")[0].getAttribute("showmacrons") == "true");
	const NewStatus = !CurrentStatus;

	document.getElementsByTagName("wrapper")[0].setAttribute("showmacrons", NewStatus.toString());

	console.log(element);
	element.innerHTML = "Macrons: <b>" + (NewStatus ? "on" : "off") + "</b>";

}

function CheckSSE()
{
	//non-IE/Edge Functionality
	if (typeof (EventSource) !== "undefined")
	{
		var Level = CONTEXT.level;
		var source = new EventSource("TestModeSSE.php?level=" + Level + "&timestampupdate=true");
		Recheck = null;
		const StatusOnLoad = (CONTEXT.test_status ? "1" : "0");

		source.onmessage = function (event)
		{
			SSEResponse = JSON.parse(event.data.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, ''));
			if (Recheck == null)
			{
				Recheck = SSEResponse[0]["TestMode" + Level];
			}

			if (SSEResponse[0]["TestMode" + Level] != Recheck || StatusOnLoad != Recheck)
			{
				document.getElementsByTagName('html')[0].innerHTML = "";
				source.onmessage = function (event) { };
				source.close();
				// location.reload();
			}
		};
	}


}

function ScrollToWord(wordId)
{

	if (document.getElementById("" + wordId))
	{
		const yOffset = -200;
		newY = document.getElementById("" + wordId).getBoundingClientRect().top + window.pageYOffset + yOffset;
		window.scrollTo({ top: newY, behavior: 'smooth' });
		document.getElementById("" + wordId).setAttribute('reveal', "true");
		history.pushState({ state: wordId }, "State 1", "#" + wordId);
		history.pushState({ state: wordId }, "State 2", window.location.href.split("#")[0]);
	}


}

function scrollToNote(noteId)
{
	boundingele = document.getElementsByTagName('notes')[0];
	correctNote = [...document.getElementsByTagName('note')].filter(x => x.getAttribute('noteid') == ("" + noteId))[0];

	if (correctNote)
	{
		if ((correctNote.getBoundingClientRect().top < 0 || correctNote.getBoundingClientRect().top > (window.innerHeight - 50)))
		{
			const yOffset = -100;
			newY = correctNote.getBoundingClientRect().top + boundingele.scrollTop + yOffset;

			boundingele.scrollTo({ top: newY, behavior: 'smooth' });
		}
	}

}

function setDifficulty(occurenceThreshold)
{

	document.querySelectorAll('word').forEach(function (word)
	{
		word.toggleAttribute('reveal', (+word.getAttribute('frequency')) <= (+occurenceThreshold));
	});
}

function getHomeworkDueDate()
{
	getAssignmentFromDocument(ASSIGNMENT_ID).then(function (assignmentData)
	{
		const dueDateEle = document.getElementById('dueDate');
		dueDateEle.innerText = assignmentData[1];
		dueDateEle.style.color = "inherit";
	}
	);
}



function ResetNoteHighlights()
{
	noteElements = document.getElementsByTagName('note');

	for (var n = 0; n < noteElements.length; n++)
	{
		noteElements[n].removeAttribute('highlighted');
	}

}

function HighlightNotes(hoveredElement)
{
	var ThereIsAHighlightedWord = false;

	noteElements = document.getElementsByTagName('note');

	for (var n = 0; n < noteElements.length; n++)
	{
		if (noteElements[n].getAttribute('associatedwords').split(",").indexOf(hoveredElement.getAttribute('wordid')) != -1)
		{
			noteElements[n].setAttribute('highlighted', "true");
			scrollToNote((+noteElements[n].getAttribute('noteid')));
			ThereIsAHighlightedWord = true;
		}
		else
		{
			noteElements[n].setAttribute('highlighted', "false");
		}
	}

	if (!ThereIsAHighlightedWord)
	{
		ResetNoteHighlights();
	}
}

function SetupNoteHighlights()
{
	wordElements = document.getElementsByTagName('word');

	for (var w = 0; w < wordElements.length; w++)
	{
		wordElements[w].onmouseover = function ()
		{
			HighlightNotes(this);
		};

		wordElements[w].onmouseout = function ()
		{
			ResetNoteHighlights();
		};
	}

}

function ToggleQuote(clickedElement)
{
	var Pele = clickedElement.parentElement;
	if (Pele.getAttribute('expanded') != "true")
	{
		Pele.setAttribute('expanded', 'true');
	}
	else
	{
		Pele.setAttribute('expanded', 'false');
	}
}