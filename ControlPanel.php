

<style>
.testModeCheckers
{
	font-family: "Crimson Text";

	padding: 10px;
	border-radius:200px;
	border: 2px solid black;
	display:inline-block;
	text-align:center;
	cursor:pointer;
}

.classtitle
{
	font-weight:bold
}

.testModeCheckers[activated="true"]
{
	background-color:lightblue;
}

.testingNotification
{
	display:none;
	z-index:-100000;
}

.testModeCheckers[activated="true"] .testingNotification
{
	display:block;
	font-variant:small-caps;
}

.cb
{
	display:none;
}

#wrapper
{
	display:inline-grid;
	row-gap: 20px;
}

hw
{
	display:inline-grid;
	column-gap:10px;
	row-gap:10px;
	border: 2px solid black;
	cursor:pointer;
}

hw[suggested="true"]
{
	background-color:lightgreen;
}

hw[suggested="true"][rd="-1"]
{
	display:none;
}

hwtitle
{
	display:block;
	font-weight:bold;
}

hwduedate
{
	font-style:italic;
}

</style>

<body onload = 'GetRotationDaysJSON(); InitializeCalendarGAPI();'>
<?php>

require_once ('GoogleClassroom/APLGSI.php');  
require_once ( 'FontStyles.php');
require_once ( 'GenerateNotesandVocab.php'); 


$context = new Context; 
$levArray = array_keys($context::LevelDictDB);

$statuses = SQLQuarry('SELECT * FROM `Control Panel` ')[0];

echo "<div id = 'wrapper'>";

foreach($levArray as $level)
{
	if($statuses['TestMode'.$level] == "1")
	{
		$checkedclause = " checked ";
		$activatedclause = "true";
	}
	else
	{
		$checkedclause = "";
		$activatedclause = "false";
	}
	echo "<div  onclick = 'ToggleTestMode(this)' class = 'testModeCheckers' activated = '".$activatedclause."'>"; 
	echo "<input ".$checkedclause." class = 'cb' type = 'checkbox' level = '".$level."' id = 'testModeCheckbox".$level."'>";
	echo "<span class = 'classtitle'  >";
	echo "Test Mode - ".$level."";
	echo "</span>";
	echo "<BR>";
	echo "<span class = 'testingNotification'>";
	echo "testing";
	echo "</span>";
	echo "</div>";
}
echo "</div>";

//  $context->GetTestStatus())





?>



<script>
const CLIENT_ID = '448443480105-krbg7mnhjqd7s4kdevurs1dtffe1uf1t.apps.googleusercontent.com';
const API_KEY = 'AIzaSyCN9ZxUhMb9zQW7rK4ZSaP1S4NJ7EKc_es';
const DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest", "https://www.googleapis.com/discovery/v1/apis/classroom/v1/rest"];
const SCOPES = (["https://www.googleapis.com/auth/calendar.events", "https://www.googleapis.com/auth/calendar.readonly", "https://www.googleapis.com/auth/classroom.topics.readonly",  "https://www.googleapis.com/auth/classroom.coursework.students"].join(" "));

const GoogleClassroomCourseName = "AP Latin E12";

function ToggleTestMode(clickedElement)
{
	
	checkElement =  clickedElement.getElementsByClassName('cb')[0]
	checkElement.checked = !checkElement.checked

	var Level = checkElement.getAttribute('level')
	var newval = checkElement.checked ? "1" : "0";


	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function()
	{
		if (this.readyState == 4 && this.status == 200)
		{
			var Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
			clickedElement.setAttribute('activated', checkElement.checked)
		}
	};

	XMLURL = "AJAXAPL.php?toggletestmode=true&newval="+newval+"&level="+Level;
	xmlhttp.open("GET", XMLURL, true);
	xmlhttp.send();
	// cnsole.log(window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/" + XMLURL);
	// return window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/"  + XMLURL;

}

function GetRotationDaysJSON()
{

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				document.getElementById('RotationDaysJSON').innerText = Response
			}
		};
		xmlhttp.open("GET", "https://lighthouse.csamsacs.org/Photon/Public/RotationDays.php", true);
				xmlhttp.send();

}


function InitializeCalendarGAPI()
{
	gapi.load('client:auth2', function()
	{
		gapi.client.init(
		{
			apiKey: API_KEY,
			clientId: CLIENT_ID,
			discoveryDocs: DISCOVERY_DOCS,
			scope: SCOPES
			
		}).then(function()
		{
			// PullGoogleClassroomCalendars();
			FindClassroomCourse(GoogleClassroomCourseName);

		}, function(error)
		{
			return new Promise(function(resolve, reject)
			{
				reject();
			});
		});

	})

}

function SignInWithCheck()
{
	(gapi.auth2.getAuthInstance().signIn())
	.then(
		function()
		{
			InitializeCalendarGAPI() 
		}
	);
}

function PullGoogleClassroomCalendars()
{

	gapi.client.calendar.calendarList.list().then(function(response)
	{
		ClassroomJSON = {};
		var answer = response.result.items;

		answer = answer.sort(function(el1, el2)
		{
			return el1["summary"] == el2["summary"] ? 0 : (el1["summary"] < el2["summary"] ? -1 : 1);
		});

		Object.keys(answer).forEach(function(k)
		{
			console.log(answer[k])
		})

	});

}

function FindClassroomCourse(CourseName)
{
	gapi.client.classroom.courses.list(
		{
			pageSize: 100
		})
		.then(function(response)
		{
			var courses = response.result.courses;
			if (courses.length > 0)
			{
				for (i = 0; i < courses.length; i++)
				{
					var course = courses[i];
					if (course.name == CourseName)
					{
						COURSEID = course.id;
						GetClasswork(course.id);
						GetTopics(course.id);
					}
				}
			}
		});
}

function GetClasswork(idNum)
{
	gapi.client.classroom.courses.courseWork.list({courseId:idNum})
	.then(function(response)
		{
			var rez = response.result.courseWork
			rez = rez.filter(x => RegExp('^HW').test(x.title));
			rez = rez.sort(function (a,b)
			{
				a = +(a.title.substring(2))
				b = +(b.title.substring(2))

				return Math.sign(a-b);

			});
			
			rez= rez.slice(Math.max(rez.length - 3, 0))
			
			DisplayCurrentHW(rez)
			
			}
		)

}

function DisplayCurrentHW(HWArray)
{
	var HWNumsArray = [];
	for (var a = 0; a < HWArray.length; a++)
	{
		HWNumsArray.push(+(HWArray[a].title.substring(2)))

		tempHW = document.createElement('hw');
		tempHW.innerHTML = "<hwtitle>" + HWArray[a]['title'] + "</hwtitle>" 
		tempHW.innerHTML += "<hwduedate>" + HWArray[a]['dueDate']['month'] + "/" + HWArray[a]['dueDate']['day'] + "/" + HWArray[a]['dueDate']['year'] +  "</hwduedate>" 
		tempHW.innerHTML += "<hwdescription>" + HWArray[a]['description'].substring(0, HWArray[a]['description'].indexOf("\n"))+ "</hwdescription>" 
		document.getElementById('hwAssigned').appendChild(tempHW)
	}
	var LatestNumber = HWNumsArray.pop()
	SuggestHW(HWArray, LatestNumber)
}

function SuggestHW(HWArray, LatestNumber)
{
	if (LatestNumber == null)
	{
		LatestNumber = 0;
	}

	SpreadsheetDocID = "1CKcfxPCIV2Kz7b7QAbhK6JJ5kroxVdZoreGDXvngjS8"

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				Response = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '')
				SheetData = (JSON.parse(Response).feed.entry)
				
				sd = 0;
				
				var tempRDJSON = JSON.parse(document.getElementById('RotationDaysJSON').innerText)

				
				while ( sd < SheetData.length )
				{
					
					if((SheetData[sd].title["$t"]).startsWith("A") && +(SheetData[sd].content['$t'].substring(1)) > LatestNumber )
					{
						var tempHWNum = (+(SheetData[sd].content['$t'].substring(1)))
						var tempHWbookName = (SheetData[sd].content['$t'].substring(0,1)) == "V" ? "A" : "DBG"
						var tempDays = +(SheetData[sd+1]['gs$cell'].numericValue) -25568
						var tempD =  new Date ((  tempDays *1000*60*60*24))
						var tempDate = tempD.getFullYear() + "-"+ ("00"+(tempD.getMonth()+1)).slice(-2)+ "-"+ ("00"+tempD.getDate()).slice(-2)
						var tempHWDueDate =  SheetData[sd+1].content['$t']
						var tempHWCitation = tempHWbookName+ " "+ SheetData[sd+4].content['$t']
						var tempUnit =   SheetData[sd+3].content['$t'].substring(SheetData[sd+3].content['$t'].length-1)
						var tempRD =   ((tempRDJSON[tempDate] != undefined) ? tempRDJSON[tempDate].RD : -1)
						var temptimestamp = {
							"-1":"00:00",
							"2":"07:55",
							"4":"07:55",
							"6":"13:35",
							"8":"13:35",
						}[tempRD]+ ":00";


						tempHW = document.createElement('hw');
						tempHW.onclick = function (){CreateAPLatinHWAssignment(this)};
						tempHW.setAttribute('suggested', 'true')
						tempHW.setAttribute('datestamp', tempDate)
						tempHW.setAttribute('timestamp', temptimestamp)
						tempHW.setAttribute('citation', tempHWCitation)
						tempHW.setAttribute('hwnum', tempHWNum)
						tempHW.setAttribute('unit', tempUnit)
						tempHW.setAttribute('rd', tempRD)
						tempHW.innerHTML = "<hwtitle>HW" + (+(SheetData[sd].content['$t'].substring(1))) + " ["+tempUnit+"] </hwtitle>" 
						tempHW.innerHTML += "<hwduedate>" + tempD.toLocaleDateString(undefined) + "</hwduedate>" 
						tempHW.innerHTML += "<hwdescription>" + tempHWCitation + "</hwdescription>" 
						document.getElementById('hwAssigned').appendChild(tempHW)


					}
					sd++
				}
			}
		};
		xmlhttp.open("GET", "https://spreadsheets.google.com/feeds/cells/" + SpreadsheetDocID + "/1/public/values?alt=json", true);
		
		xmlhttp.send();


}

function GetTopics(ClassId)
{
	gapi.client.classroom.courses.topics.list({courseId:ClassId})
	.then( function(r) { 
		
		teTOPICS = (r.result.topic)
		TOPICS = {};

		for (t=0; t< teTOPICS.length; t++)
		{
			TOPICS[+(teTOPICS[t].name.substring(teTOPICS[t].name.indexOf(" "), teTOPICS[t].name.lastIndexOf(" ")))] = teTOPICS[t].topicId
		}		
	});
}

function CreateAPLatinHWAssignment(clickedElement)
{
	var citeit = clickedElement.getAttribute('citation')
	var numbah = clickedElement.getAttribute('hwnum')
	var dateee = clickedElement.getAttribute('datestamp')
	var timeee = clickedElement.getAttribute('timestamp')
	var united = clickedElement.getAttribute('unit')

	var dateobj = (new Date(dateee + " " + timeee + ""))
	
	var description = citeit+`
	http://aplatin.altervista.org/HomeworkViewer.php?hw=`+numbah+`
	https://docs.google.com/spreadsheets/d/1CKcfxPCIV2Kz7b7QAbhK6JJ5kroxVdZoreGDXvngjS8`

	//Test Class Id =========    "46640113054"

	gapi.client.classroom.courses.courseWork.create({
		
		//AP
		courseId:COURSEID,
		topicId:TOPICS[united],

		// Test Class
		// courseId:"46640113054",


		workType: "ASSIGNMENT",
		state: "PUBLISHED",
		title:("HW"+numbah),
		description:description,
		dueDate:{
				"year": (+(dateee.split('-')[0])),
				"month": (+(dateee.split('-')[1])),
				"day": (+(dateee.split('-')[2]))
				},
		dueTime:{
				"hours": dateobj.getUTCHours(),
				"minutes": dateobj.getUTCMinutes(),
				"seconds": 0,
				"nanos": 0
				},
		maxPoints:100

		}).then(function(r)
		
		{
			if(r.status == 200)
			{
				clickedElement.removeAttribute('suggested')
			}

		});
}


</script>

<script async defer src="https://apis.google.com/js/api.js"></script>



<json style = 'display:none;' id = 'RotationDaysJSON'>
</json>



<div id= 'hwWrapper'>
	<div id = 'hwAssigned'>
	</div>
	<div id = 'hwSuggested'>
	</div>
</div>

<BR>
<BR>
<BR>
<span onclick = 'signOut()'>signout</span>
<div data-containertype= "button" style = ' '  onclick = 'SignInWithCheck()' >Link with Google API</div>