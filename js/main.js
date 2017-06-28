// When the window is ready
$(window).ready(function() {
	// Dropdown stuffs
	$(".dropdown").click(function() {
		$(this).toggleClass("expand");
	});
	
	// Alert stuff
	var alerts = $('<div class="alerts"></div>');
	$("body").append(alerts);
	
	// Chrome button fix
	$(".head .buttons").append($('<div class="button-fix"></div>'));
});

// TODO: Rewrite this temporary function (taken from http://stackoverflow.com/questions/10730362/get-cookie-by-name)
function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function getAbsoluteUrl(url) {
    var _http =  document.location.protocol == 'https:' ? 'https://' : 'http://';
	//return (url != null ? !/^(https?\:\/\/|\/)/i.test(url) ? _http + location.hostname + "/playground/dev/portal/" + url : url : "");
	return (url != null ? !/^(https?\:\/\/|\/)/i.test(url) ? _http + location.hostname + "/portal/" + url : url : "");
}

function prepareAjax(settings) {
	var url = getAbsoluteUrl(settings["url"]);
	//console.log("sendAjax: " . url );

	success = function(response) {
		if ((!response["success"]) && (response["error"] == null))
			response["error"] = "An unknown error occurred, please refresh and try again.";
		if (settings["success"] != undefined)
			settings["success"](response);
	}

	error = function(response) {
		// TODO: extend this a bit
		if (settings["error"] != undefined)
			settings["error"](response);
	}

	return $.ajax({
		url: url,
		dataType: settings["datatype"] || "json",
		type: settings["type"] || "POST",
		data: settings["data"],
		async: settings["async"] || true,
		success: success,
		error: error,
	});

}

// TODO: Consider possibly overriding the default success/error to react to the ajax file's success/error cases
function sendAjax(settings) {
	url = getAbsoluteUrl(settings["url"]);
	
	success = function(response) {
		if ((!response["success"]) && (response["error"] == null))
			response["error"] = "An unknown error occurred, please refresh and try again.";
		if (settings["success"] != undefined)
			settings["success"](response);
	}
	
	error = function(response) {
		// TODO: extend this a bit
		if (settings["error"] != undefined)
			settings["error"](response);
	}
	
	$.ajax({
		url: url,
		dataType: settings["datatype"] || "json",
		type: settings["type"] || "POST",
		data: settings["data"],
		success: success,
		error: error,
	});
}

/// TODO: make this much more general...
function dateFormat(theDate) {
	// getMonth() method 0=Janurary, 1=Feb, etc
	return theDate.getMonth()+1 + '/' + theDate.getDate() + '/' + theDate.getFullYear();
}

function __date(theDate) {
	return theDate.getFullYear().toString() + '-' + (theDate.getMonth()+1).toString() + '-' + theDate.getDate().toString() + '+00:00:00';
}

function isValidDate(theDate) {
}

function isUniqueGroupName( val ) {
	_isUnique = false;
	response = [];

	$.ajax({
	    url: "../php/fg/lic.php",
		async: false,
		type: "GET",
		datatype: "json",
		data:  {
			action: 'verify',
			table:	'Contacts',
			field:	'Groups',
			value:	val
		},
		success: function(result) {
		    response = JSON.parse(result);
			_isUnique = response['success'];
			if(!_isUnique)
			    alert('That name has already been taken by another user. Please enter a different Group Name.');
		},
		error: function(result) {
		    response = JSON.parse(result);
		    alert('Edit Settings: ' + response['error']);
		}

	});

	return _isUnique;
}

function isUniqueDomainSuffix( val ) {
	_isUnique = false;
	response = [];

	$.ajax({
	    url: "../php/fg/lic.php",
		async: false,
		type: "GET",
		datatype: "json",
		data:  {
			action: 'verify',
			table:	'UserLicenses',
			field:	'DomainSuffix',
			value:	val
		},
		success: function(result) {
		    response = JSON.parse(result);
			_isUnique = response['success'];
			if(!_isUnique)
			    alert('That name has already been taken by another user. Please enter a different School Name.');
		},
		error: function(result) {
		    response = JSON.parse(result);
		    alert('Edit Settings: ' + response['error']);
		}

	});

	return _isUnique;
}

function isUniqueUserName( val ) {
	_isUnique = false;
	response = [];
	
	$.ajax({
	    url: "../php/fg/lic.php",
		async: false,
		type: "GET",
		datatype: "json",
		data:  {
			action: 'verify',
			field:	'Username',
			value:	val
		},
		success: function(result) {
		    console.log(result);
		    response = JSON.parse(result);
			_isUnique = response['success'];
			if(!_isUnique)
			    alert('That name has already been taken by another user. Please enter a different User Name.');
		},
		error: function(result) {
		    response = JSON.parse(result);
		    alert('main.js (Line 184): ' + response['error']);
		}

	});

	return _isUnique;
}

function refresh(url='') {
	if(url === '' || typeof(url)==='undefined')
		location.reload();
	else
		location.href = url;
}

function sendAjaxFile(settings) {
	// Set up the url
	url = getAbsoluteUrl(settings["url"]);
	
	// Success and error reporting
	success = function(response) {
		if ((!response["success"]) && (response["error"] == null))
			response["error"] = "An unknown error occurred, please refresh and try again.";
		if (settings["success"] != undefined)
			settings["success"](response);
	}
	
	error = function(response) {
		// TODO: extend this a bit
		if (settings["error"] != undefined)
			settings["error"](response);
	}
	
	// FormData	
//	var data = new FormData(document.getElementById("teacher-csv-upload-form"));  // mse
	var data = new FormData();
	
	// Append files
	for (var f in settings["files"]) {
		var filename = settings["files"][f];
		// TODO: Be able to upload multiple files
		data.append(filename, $('#' + filename)[0].files[0]);
		data.append('file-id', filename);
	}
	
	// Append data
	for (var d in settings["data"]) {
		data.append(d, settings["data"][d]);
	}
	
	// Send AJAX
	$.ajax({
		url: url,
		dataType: settings["datatype"] || "json",
		data: data,
		processData: false,
		type: settings["type"] || "POST",
		contentType: false,
		beforeSend: function(x) {
			if ((x) && (x.overrideMimeType)) {
				x.overrideMimeType("multipart/form-data");
			}
		},
		mimeType: "multipart/form-data",
		success: success,
		error: error,
	});
}

function registerOnClick(element, handler) {
	$(window).ready(function(e) {
		$(element).click(function(e) {
			e.preventDefault();
			handler($(this));
		});
	});
}

function registerOnChange(element, handler) {
	$(window).ready(function(e) {
		$(element).change(function(e) {
			e.preventDefault();
			handler($(this));
		});
	});
}

function setScrollEnabled(enabled) {
	if (enabled) {
		var top = $("body").position().top;
		$("body").css("position", "static");
		$("body").scrollTop(-top);
		
		$(window).bind("scroll", fixNavbar);
	} else {
		$("body").css("top", -$("body").scrollTop() + "px");
		$("body").css("position", "fixed");
		
		$(window).unbind("scroll", fixNavbar);
	}
}