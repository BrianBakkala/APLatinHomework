async function ajaxAjacis(destination, params, callbackFunction = null, method = "POST")
{
    const xmlhttp = new XMLHttpRequest();
    const ajaxTime = new Date().getTime();

    return new Promise(function (resolve, reject)
    {
        xmlhttp.onreadystatechange = function ()
        {
            if (this.readyState == 4)
            {
                if (this.status == 200)
                {
                    let resp = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '').trim();
                    const respTime = new Date().getTime() - ajaxTime;

                    try { resp = JSON.parse(resp); } catch (e) { }

                    const result = {
                        "r": resp,
                        "t": respTime
                    };

                    if (callbackFunction)
                    {
                        callbackFunction(result);
                    }

                    resolve(result);


                } else if (this.status == 504)
                {
                    console.error(this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, ''));
                    reject(new Error('Request timeout'));
                } else if (this.status == 401)
                {
                    // HandleUnauthorizedAJAXRequest();
                    reject(new Error('Unauthorized request'));
                } else
                {
                    reject(new Error(`Request failed with status ${this.status}`));
                }
            }
        };

        const XMLParams = new URLSearchParams(params);

        //relative path
        let XMLURL = "AJAXAPL.php" + "?" + destination + '=true&';
        if (XMLParams.entries().next().value)
        {
            XMLURL += (XMLParams.entries().next().value[0] + "=" + XMLParams.entries().next().value[1]);
        }

        //absolute path
        if (destination.startsWith('http:') || destination.startsWith('https:'))
        {
            XMLURL = destination;
        }

        xmlhttp.open(method, XMLURL, true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(XMLParams.toString());
    });
}

async function getAssignmentFromDocument(assignmentNumber)
{
    const result = await ajaxAjacis(DOCUMENT.url, {}, null, "GET");

    const data = result.r.values;

    const relevants = data.filter(r => r[0].slice(1) == assignmentNumber);

    if (relevants.length > 0)
    {
        return relevants[0];
    }
    else
    {
        throw new Error('Assignment not found');
    }
}


function toggleQuote(clickedElement)
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