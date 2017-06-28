var bulkDivID = 0;

$(window).ready(function() {
	// (Un)check all on bulk checkbox click
	$("#bulk-input").change(function() {
		$("input[type=checkbox]").prop('checked', this.checked);
	});
	
	// Update bulk checkbox checked status
	$("input[type=checkbox]").change(function() {
		$("#bulk-input").prop('checked', true);
		$("input[type=checkbox]").each(function() {
			if (!$(this).prop('checked')) {
				$("#bulk-input").prop('checked', false);
			}
		});
	});
	
	// Change all select values based on bulk value
	$("#bulk-product").change(function() {
		$("select").val($(this).val());
	});
	
	// Open settings iFrame
	$(".settings-toggle").click(function() {
		var id = $(this).attr("data-id");
		var prod = $('.student-settings[data-id="' + id + '"] select').val();
		var userList = $('.student-settings[data-id="' + id + '"] input[name="username"]').val();
		
		// Get user (list)
		if ((id == bulkDivID) && (!$("#bulk-input").prop('checked'))) {
			var userID = 0;
			userList = "";
			$("input[type=checkbox]:not(#bulk-input)").each(function() {
				if ($(this).prop('checked')) {
					userID = $(this).parent().parent().attr("data-id");
					userList += $('.student-settings[data-id="' + userID + '"] input[name="username"]').val();
					userList += ",";
				}
			});
			userList = userList.replace(/,+$/,'');
		}
		
		// 10-10-16 mse
		// gamemaker html cannot parse a parameter with an '=' sign in it, we 
		// replace the '=' with '-' and replace them back on the settings.html side
		userList = userList.replace(/=/g,'-');
		
		//var src = "http://fg/dev/portal/settings/index.html?";
		var src = "settings/index.html?";
		src += "key=" + getCookie("fluency_games_license");
		src += "&prod=" + prod;
		src += "&auth=1";
		src += "&user=" + userList;
		iFrameToggle(id, src, "options");
	});
	
	// Open progress iFrame
	$(".progress-toggle").click(function() {
		var id = $(this).attr("data-id");
		var prod = $('.student-settings[data-id="' + id + '"] select').val();
		var user = $('.student-settings[data-id="' + id + '"] input[name="username"]').val();
		
		// 10-10-16 mse
		// GM:HTML cannot correctly parse parameters with '=' 
		user = user.replace(/=/g,'-');
		
		var src = "progress/index.html?";
		src += "key=" + getCookie("fluency_games_license");
		src += "&user=" + user;
		src += "&prod=" + prod;
		src += "&auth=1";
		iFrameToggle(id, src, "progress");
	});
	
	// this is the reports page
	$(".reports-toggle").click(function() {
		var id = $(this).attr("data-id");
		var user = $('.student-settings[data-id="' + id + '"] input[name="username"]').val();
		var prod = $('.student-settings[data-id="' + id + '"] select').val();
		
		sendAjax({
			url: "php/ajax/manage/get-student.php",
			data: {
				lic: getCookie("fluency_games_license"),
				username: user,
				product: prod,
				auth: '1'
			},
			success: function(result) {
				if (result['success']) {
					window.open(getAbsoluteUrl('reports/reports.php'));
				} else {
					alert('Student not found (No settings set up?)');
					console.log(result);
				}
			},
			error: function(result) {
				alert('User not found in game information database (Check student settings).');
				console.log(result);
			},
		});		
	});	
	
	$(".reports-toggle-bulk").click(function() {
		var prod = $("#bulk-product").val();
		sendAjax({
			url: "php/ajax/manage/set-student.php",
			data: {
				id: '*',
				product: prod
			},
			success: function(result) {
				if (result['success']) {
					window.open(getAbsoluteUrl('reports/reports-bulk.php'));
				} else {
					alert(result['error']);
					console.log(result);
				}
			},
			error: function(result) {
				alert(result['responseText']);
				console.log(result);
			},
		});		
	});

	
});

function iFrameToggle(id, src, type) {
	var iframe = $('iframe[data-id="' + id + '"]');
	var create = true;
	if (iframe.length) {
		create = iframe.attr("data-type") != type;
		iframe.remove();
	}
	
	if (create) {
        var _http =  document.location.protocol == 'https:' ? 'https://' : 'http://';
		iframe = $('<iframe data-id="' + id + '" data-type="' + type + '"></iframe>');
		//iframe.attr("src", _http + location.hostname + "/playground/dev/portal/" + src);
		iframe.attr("src", _http + location.hostname + "/portal/" + src);
		iframe.insertAfter($(".student-settings[data-id=" + id + "]"));
	}
}