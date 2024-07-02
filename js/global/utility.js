
function latinAJAX(destination, params, callbackFunction = null, method = "POST")
{

    const xmlhttp = new XMLHttpRequest();

    return new Promise(function (resolve, reject)
    {
        xmlhttp.onreadystatechange = function ()
        {
            if (this.readyState == 4)
            {
                if (this.status == 200)
                {
                    var resp = this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, '').trim();
                    const respTime = new Date().getTime() - ajaxTime;

                    try
                    {
                        resp = JSON.parse(resp);
                    }
                    catch (e)
                    { };

                    if (callbackFunction)
                    {
                        callbackFunction(
                            {
                                "r": resp,
                                "t": respTime
                            });
                    }
                    resolve(
                        {
                            "r": resp,
                            "t": respTime
                        });
                    return {
                        "r": resp,
                        "t": respTime
                    };

                }
                else if (this.status == 504)
                {
                    console.error(this.responseText.replace(/(\r\n\t|\n|\r\t)/gm, " ").replace(/^\s+|\s+$/gm, ''));
                }
                else if (this.status == 401)
                {
                    // HandleUnauthorizedAJAXRequest();
                }
            }
        };

        const XMLParams = new URLSearchParams(params);
        const XMLURL = "AJAXAPL.php" + "?" + destination + '=true&' + (XMLParams.entries().next().value[0] + "=" + XMLParams.entries().next().value[1]);

        xmlhttp.open(method, XMLURL + "", true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        const ajaxTime = new Date().getTime();
        xmlhttp.send(XMLParams.toString());

    });
}
