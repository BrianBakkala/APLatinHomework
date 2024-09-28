

function initializeWords()
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

function toggleNotes(element)
{
	document.querySelector('wrapper').toggleAttribute('show-notes');

}

function toggleMacrons(element)
{
	document.querySelector('wrapper').toggleAttribute('show-macrons');

}

function scrollToWord(wordId)
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

function resetNoteHighlights()
{
	noteElements = document.getElementsByTagName('note');

	for (var n = 0; n < noteElements.length; n++)
	{
		noteElements[n].removeAttribute('highlighted');
	}

}

function highlightNotes(hoveredElement)
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
		resetNoteHighlights();
	}
}

function setupNoteHighlights()
{
	wordElements = document.getElementsByTagName('word');

	for (var w = 0; w < wordElements.length; w++)
	{
		wordElements[w].onmouseover = function ()
		{
			highlightNotes(this);
		};

		wordElements[w].onmouseout = function ()
		{
			resetNoteHighlights();
		};
	}

} 