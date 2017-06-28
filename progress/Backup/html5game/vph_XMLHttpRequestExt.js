

var xhr = null;
var isCreated = false;
var isOpen = false;
var percentComplete = 0;
var verbose = false;

function debugLog(str)
{
	if(verbose==true) {
		console.log(str);
	}
}

function updateProgress(oEvent)
{
	if(oEvent.lengthComputable)
		percentComplete = 100 * oEvent.loaded/oEvent.total;
	else
	    percentComplete = -1;
}

function xhr_init()
{
	xhr=null;
	isCreated = false;
	isOpen = false;
	percentComplete = 0;
	verbose = false;
}

function xhr_destroy() { if(isCreated) { delete xhr; isCreated = false; } }

function xhr_create()
{
	debugLog("XHR: XMLHttpRequest Object Created");
	xhr = new XMLHttpRequest();
	isCreated = true;
	return xhr;
}

function xhr_open( method, url )
{
	if(!isCreated)
	    xhr_create();
	debugLog("XHR: XMLHttpRequest Object Opened:" + url);
	xhr.addEventListener("progress", updateProgress, false);
	xhr.open( method, url, true);
	isOpen = true;
	return true;
}

function xhr_setCredentials( setCredentials )
{
  if(isCreated && isOpen) {
    xhr.withCredentials = setCredentials;
  }
} 

function xhr_setHeader( header, value )
{
	if(isCreated && isOpen) {
		xhr.setRequestHeader( header, value);
	}
}

function xhr_send()
{
	if(isCreated && isOpen) {
		debugLog("XHR: XMLHttpRequest command sent");
		xhr.send();
	}
}

function xhr_sendData( str )
{
	if(isCreated && isOpen) {
		debugLog("XHR: XMLHttpRequest Data sent:" + str);
		xhr.send(str);
	}
}


function xhr_sendFile( strFilename )
{

}

function xhr_getResponse()
{
	var resp="";
	
	debugLog("XHR: getResponse(): "+xhr.readyState + " (" + xhr.status + ")" );
    if (xhr.readyState == 4 && xhr.status == 200) {
		debugLog("XHR: XMLHttpRequest Response: "+xhr.responseText );
        resp = xhr.responseText;
    }

	return resp;
}

function xhr_getState()
{
	debugLog("XHR: getState(): "+xhr.readyState + " (" + xhr.status + ")" );
	if(isCreated && isOpen) {
	    return xhr.readyState;
	}
	return -1;
}

function xhr_getResponseStatus()
{
	debugLog("XHR: getResponseStatus(): "+xhr.readyState + " (" + xhr.status + ")" );

	if(isCreated && isOpen) {
	    if(xhr.readyState == 4)
		    return xhr.status;
	}
	return -1;
}

function xhr_getPercentCompleted()
{
	return percentComplete;
}