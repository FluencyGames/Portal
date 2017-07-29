var edited = false;

var fullWidth = true; // 1-15-16 mse
var newUsers = [];
var newSlots = [];
//var newOffset = 0;
var existingUsers = [];
var existingSlots = [];
var existingOffset = 0;
var existingCount = 25; // Change this for the default

var newSlotPool = [];
var existingSlotPool = [];

var states = {
	added: "added",
	deleted: "deleted",
	edited: "edited",
	unedited: "unedited"
};

$(window).ready(function() {

	//
	// search field in nav bar
	//
	$("#search-input").on("keyup keydown", function() {
		var term = $("#search-input").val();

		var length = existingSlots.length;
		for (var i = 0; i < length; ++i) {
			existingSlots[i].hide(term);
		}

		length = newUsers.length;
		for (var j = 0; j < length; ++j) {
			newUsers[j].hide(term);
		}
	});

	$("#search-cancel").on("click", function() {
		var term = '';

		var length = existingSlots.length;
		for (var i = 0; i < length; ++i) {
			existingSlots[i].hide(term);
		}

		length = newUsers.length;
		for (var j = 0; j < length; ++j) {
			newUsers[j].hide(term);
		}
        $("#search-input").val('');
	});
	
	// Make add users button work
	
	//
	// currentGroup is the group name of the teacher
	// productCode is the encoded products available for this license
	// both are defined in rosters.php
	//
	$("#add-user-button").click(function() {
		var user = new User(
			inputKeys,
			(userType == "teacher") ? ['', '', '', ''] : ['', '', '', currentGroup, productCode ],
			states.added
		);
		
		//collapseAll('new');
		createSlots(newSlots.length + 1, "new");
		newSlots[newSlots.length - 1].loadUser(user);
		newSlots[newSlots.length - 1].expand();
		newUsers.push(user);
	});
	
	// Make global buttons work
	linkGlobalButtons();
	
	// Make dropdowns work
	linkDropdowns();
	
	// Make first/prev/next/last buttons work
	linkButtons();
	
	// Get those users
	sendAjax({
		url: "php/ajax/manage/get-users.php",
		data: {
			requesting: userType + 's',
			groupName: groupName,
		},
		success: function(result) {
			console.log(result);
			if (result['success']) {
				sortUsers(result['users']);
				processUsers(result['users']);
				loadUsersIntoSlots('existing', existingCount, 0);
				updatePageCount('existing');
			} else {
				alert("users.js, line 100: "+result['responseText']);
			}
		},
		error: function(result) {
			alert("users.js, line 105: "+result['responseText']);
		},
	});
});

function createSlots(num, type) {
	var numSlotsOnPage = ((type == "new") ? newSlots : existingSlots).length
	var numToCreate = num - numSlotsOnPage;
	var slot;
	for (var i = 0; i < numToCreate; ++i) {
		if (type == "new") {
			$("#" + type + "-body").css("display", "block");
			if (newSlotPool.length > 0) {
				slot = newSlotPool.pop();
				slot.addToDOM();
			} else
				slot = new Slot(type);
			newSlots.push(slot);
		} else {
			$("#" + type + "-body").css("display", "block");
			if (existingSlotPool.length > 0) {
				slot = existingSlotPool.pop();
				slot.addToDOM();
			} else
				slot = new Slot(type);
			existingSlots.push(slot);
		}
	}
	
	// Reverse count because we're also removing them from the array
	for (var j = numSlotsOnPage - 1; j >= num; --j) {
		if (type == "new") {
			slot = newSlots.pop();
			slot.recycle();
		} else {
			slot = existingSlots.pop();
			slot.recycle();
		}
	}
	
	updatePageCount(type);
}

function loadUsersIntoSlots(type, num, offset) {
	if (offset === undefined) offset = 0;
	
	// Ensure there are enough slots to load the users into!
	createSlots(num, type);
	
	for (var s = 0, u = offset; s < num; ++s, ++u) {
		if (type == "new") {
			if (newUsers.length > u)
				newSlots[s].loadUser(newUsers[u]);
			else
				newSlots[s].loadUser(null);
		} else {
			if (existingUsers.length > u)
				existingSlots[s].loadUser(existingUsers[u]);
			else
				existingSlots[s].loadUser(null);
		}
	}
}

function expandAll(type) {
	if (type === undefined) {
		expandAll("new");
		expandAll("existing");
	}
	
	var numSlots = ((type == "new") ? newSlots : existingSlots).length;
	for (var i = 0; i < numSlots; ++i) {
		((type == "new") ? newSlots : existingSlots)[i].expand();
	}
}

function collapseAll(type) {
	if (type === undefined) {
		collapseAll("new");
		collapseAll("existing");
	}
	
	var numSlots = ((type == "new") ? newSlots : existingSlots).length;
	for (var i = 0; i < numSlots; ++i) {
		((type == "new") ? newSlots : existingSlots)[i].collapse();
	}
}

function toggleHalfWidth() {
	fullWidth = !fullWidth;
	
	var numSlots;
	
	numSlots = newSlots.length;
	for (var i = 0; i < numSlots; ++i) {
		newSlots[i].fixWidth();
	}
	
	numSlots = existingSlots.length;
	for (var i = 0; i < numSlots; ++i) {
		existingSlots[i].fixWidth();
	}
}

function getPageCount(numUsers, usersPerPage) {
	var pages = Math.floor(numUsers / usersPerPage);
	if (numUsers % usersPerPage == 0) --pages;
	return pages;
}

function updatePageCount(type) {
	var element = $("#" + type + "-page-count");
	var page = 1;
	var totalPages = 1;
	
	if (type == "new") {
		
	} else {
		page = existingOffset / existingCount + 1;
		totalPages = getPageCount(existingUsers.length, existingCount) + 1;
	}
	
	if( totalPages == 0)
	    element.text("No Records Found");
 	else
		element.text("Page " + page + " of " + totalPages);
		
	//
	// if we have only 1 page, then hide the
	// navigation buttons
	//
	updateButtons(totalPages <= 1);
}

function updateButtons(hide) {
	// display/hide navigation button if less than number of uses being displayed
	if(hide) {
	    $(".existing-first-page").hide();
		$(".existing-prev-page").hide();
		$(".existing-next-page").hide();
		$(".existing-last-page").hide();
	} else {
	    $(".existing-first-page").show();
		$(".existing-prev-page").show();
		$(".existing-next-page").show();
		$(".existing-last-page").show();
	}

}

function linkGlobalButtons() {
	// Has to be inside lambda functions! Trust me!
	$("#expand-all").click(function() {
		expandAll()
	});
	
	$("#collapse-all").click(function() {
		collapseAll()
	});
	
	$("#toggle-width").click(function() {
		toggleHalfWidth()
	});
	
	$(".save-users-button").click(function() {
		sendUsers();
	});
}

function linkDropdowns() {
	$("#existing-users-count").change(function() {
		existingOffset = 0;
		existingCount = parseInt(this.value);
		loadUsersIntoSlots('existing', existingCount, existingOffset);
	});
}

function linkButtons() {
	// Existing user page buttons
	$(".existing-first-page").click(function() {
		existingOffset = 0;
		loadUsersIntoSlots("existing", existingCount, existingOffset);
		
		collapseAll("existing");
		updatePageCount("existing");
	});
	
	$(".existing-prev-page").click(function() {
		if (existingOffset > 0) {
			existingOffset -= existingCount;
			loadUsersIntoSlots("existing", existingCount, existingOffset);
		}
		
		collapseAll("existing");
		updatePageCount("existing");
	});
	
	$(".existing-next-page").click(function() {
		if (existingOffset < getPageCount(existingUsers.length, existingCount) * existingCount) {
			existingOffset += existingCount;
			loadUsersIntoSlots("existing", existingCount, existingOffset);
		}
		
		collapseAll("existing");
		updatePageCount("existing");
	});
	
	$(".existing-last-page").click(function() {
		existingOffset = getPageCount(existingUsers.length, existingCount) * existingCount;
		loadUsersIntoSlots("existing", existingCount, existingOffset);
		
		collapseAll("existing");
		updatePageCount("existing");
	});
}

function sendUsers() {
	var added = [];
	var edited = [];
	var deleted = [];

	for (var i = 0; i < existingUsers.length; ++i) {
		switch (existingUsers[i].getState()) {
			case states.unedited: // TODO: Remove this!!
			    break;
			case states.edited:
				edited.push(existingUsers[i].info);
				break;
			case states.deleted:
				deleted.push(existingUsers[i].info);
				break;
		}
	}
	
	for (var i = 0; i < newUsers.length; ++i) {
		switch (newUsers[i].getState()) {
			case states.added:
				added.push(newUsers[i].info);
				break;
			case states.deleted:
				break;
		}
	}
	

	sendAjax({
		url: "php/ajax/manage/update-" + userType + "s.php",
		data: {
			added: added,
			edited: edited,
			deleted: deleted
		},
		success: function(result) {
			if (result['success']) {
				var m = new LoadingModal();
				m.open({title: "Saving...", refresh: 1000 });
				if(result['duplicates']!='')
				    alert("You have some duplicate records: " + result['duplicates']);
			} else {
				alert('users.js, line:365' + result['error']);
			}
		},
		error: function(result) {
			alert("users.js, line 369" + result['responseText']);
			console.log(result);
		},
	});

}

Slot = function(type) {
	var $this = this;
	this.user = null;
	
	parentCol = "#" + type + "-" + userType + "s";
	
	// Element stuff
	var col = $('<div class="col-xs-12 ' + userType + '-holder"></div>');
	this.col = col;
	this.fixWidth();
	
	this.type = type;
	this.id = ((type == "new") ? newSlots : existingSlots).length;
	this.editable = $('<div id="' + type + '-slot-' + this.id + '" class="account-editable ' + userType + '"></div>');
	
	// Head
	var head = $('<div class="head center"></div>');
	head.addClass("head center");
	this.headTitle = $('<span></span>');
	head.html(this.headTitle);
	this.editable.append(head);
	//this.updateTitle();
	
	// Buttons
	var buttons = $('<span class="buttons"></span>');
	head.append(buttons);
	
	this.expandButton = $('<span data-toggle="tooltip" title="Expand" class="icon-button icon-plus-circled"></span>');
	buttons.append(this.expandButton);
	
	this.deleteButton = $('<span data-toggle="tooltip" title="Delete user" class="icon-button icon-trash-empty"></span>');
	buttons.append(this.deleteButton);
	
	this.undoButton = $('<span data-toggle="tooltip" title="Undo delete user" class="icon-button icon-undo hidden"></span>');
	buttons.append(this.undoButton);
	this.undoButton = this.undoButton;
	
	this.mergeButton = null;
	/*if (this.conflict != null) {
		this.mergeButton = $('<span data-toggle="tooltip" title="Merge into existing ' + userType + '" class="icon-button icon-left-circle"></span>');
		buttons.prepend(this.mergeButton);
		this.mergeButton.tipsy({gravity: 's'});
	}*/
	
	// Body
	var body = $('<div class="body small"></div>');
	this.body = body;
	
	//
	// Append this new div block to page
	//
	this.editable.append(body);
	col.append(this.editable);
	this.addToDOM();
	
	//
	// Create text input fields
	// do NOT include 'products' field
	//
	var k=0;
	for (var i = 0; i < inputKeys.length-1; ++i) {
	    if( userType == 'student' && inputKeys[i] == 'Group') {
		    var select = $('<div class="select-input-wrapper"></div>');
			var label = $('<label for = "sel-field-'+k+'">Group</label>');
			select.append(label);
			
		    var selInput = $('<select class="select-input" id="sel-field-'+k+'" name="' + inputKeys[i] + '"></select>');
			for(var j=0; j < teachers.length; j++) {
			    if(teachers[j].group!='') {
					var o = $('<option value = "'+teachers[j].group + '">' + teachers[j].group + '</option>');
					selInput.append(o);
				}
			}
			k++;
		    selInput.attr("data-info-index", this.id);
		    selInput.attr("data-info-type", this.type);

			select.append(selInput);
			body.append(select);
		} else {
			var textInput = $('<div class="text-input"></div>');
			textInput.attr("data-label", inputKeys[i]);
			textInput.attr("data-name", inputKeys[i]);
			textInput.attr("data-value", "");
			body.append(textInput);
		}
	}

	if(userType == "student") {
		var loginAs = $('<div id="login-as"></div>');
		body.append(loginAs);
		loginAs.html('Login: ');
	}
	
	//
	// bind data values to text input controls
	//	
	var t;
	var $i = 0;
	
	$.each($(".text-input:not([id])"), function() {
		t = new TextInput(this);
		
		// save the username control element
		if(inputKeys[$i] == "Username") {
			$this.usernameInput = $(t.input);
		}
		
		// Bind the field name to the control element
		$(t.input).attr("data-info-id", inputKeys[$i++]);
		
		// User changes value in any field, copy the new data to the user data structure
		$(t.input).on('input', function(e) {
			var inputKey = $(this).attr("data-info-id");
			$this.user.info[inputKey] = $(this).val().trim().replace(/\s/g,'');
            if(inputKey=='First name' || inputKey == 'Last name')
			     $this.updateUsername();
			$this.updateTitle();
			$this.updateLoginAs();
			if ($this.getState() == states.unedited) {
				$this.overwriteState(states.edited);
			}
		});
	});
	
	
    $.each($(".select-input"), function() {
		$(this).on('change', function() {
			var ix = $(this).attr("data-info-index");
			var slots = $(this).attr("data-info-type") == 'existing' ? existingSlots : newSlots;
			console.log(slots[ix]);
			slots[ix].user.info['Group'] = $(this).val();
			slots[ix].updateLoginAs();
			if (slots[ix].getState() == states.unedited)
				slots[ix].overwriteState(states.edited);
		});
	});
	
	body.append($('<div class="clear"></div>'));
	
	this.collapse();
}

Slot.prototype.loadUser = function(user) {
	this.user = user;
	
	if (user != null) {
		this.editable.removeClass("empty");

		if(userType == 'teacher') {
			for (var i = 0; i < inputKeys.length; ++i)
				$("#" + this.type + "-slot-" + this.id + " input[name='" + inputKeys[i] + "']").val(user.info[inputKeys[i]]);
		} else {
			for (var i = 0; i < inputKeys.length; ++i) {
			    if(inputKeys[i] != 'Group')
					$("#" + this.type + "-slot-" + this.id + " input[name='" + inputKeys[i] + "']").val(user.info[inputKeys[i]]);
				else {
				    $("#" + this.type + "-slot-" + this.id + " select[name='" + inputKeys[i] + "']").val(user.info[inputKeys[i]]);
				}
			}
			if(this.user.info['Username']!='')
				$("#" + this.type + "-slot-" + this.id + " input[name='Username']").attr("disabled","true");
		}
		
		this.updateUsername();
		this.updateLoginAs();
	} else {
		this.editable.addClass("empty");
		
		for (var i = 0; i < inputKeys.length; ++i)
			$("#" + this.type + "-slot-" + this.id + " input[name='" + inputKeys[i] + "']").val('');
	}
	
	this.updateTitle();
	
	// Make sure the delete state is correct
	var userState = this.getState();
	
	if ((userState == null) && (this.deleted)) {
		this.toggleDelete(false);
	}
	
	if ((userState == states.deleted) && (!this.deleted)) {
		this.toggleDelete(false);
	}
	
	if ((userState != states.deleted) && (this.deleted)) {
		this.toggleDelete(false);
	}
}

Slot.prototype.addToDOM = function() {
	var $this = this;
	
	$("#" + this.type + "-page-buttons-bottom").before(this.col);
	
	// Add tooltips
	this.expandButton.tipsy({gravity: 's'});
	this.deleteButton.tipsy({gravity: 's'});
	this.undoButton.tipsy({gravity: 's'});
	
	// Methods
	registerOnClick(this.expandButton, function() {
		$this.toggle();
	});
	
	registerOnClick(this.deleteButton, function() {
		$this.toggleDelete();
	});
	
	/*if (mergeButton != null) {
		registerOnClick(this.mergeButton, function() {
			$this.merge();
		});
	}*/
}

Slot.prototype.recycle = function() {
	// MAKE SURE IT's BEEN REMOVED FROM THE ARRAY BEFORE RECYCLING!
	if (((this.type == "new") ? newSlots : existingSlots).indexOf(this) > -1)
		alert("Error: recycle was called before the Slot was removed from the array");
	
	// Insert null data to the inputs
	this.loadUser(null);
	
	// Add to a slot pool
	((this.type == "new") ? newSlotPool : existingSlotPool).push(this);
	
	// Remove from DOM
	this.col.remove();
	
	// TODO: Remove tipsy
	$(".tipsy").remove();
}

Slot.prototype.collapse = function() {
	if (this.expanded)
		this.expandButton.tipsy(true).updateTitle("Expand");
	this.expanded = false;
	this.body.addClass("collapsed");
	this.expandButton.addClass("icon-plus-circled");
	this.expandButton.removeClass("icon-minus-circled");
}

Slot.prototype.expand = function() {
	if (this.user == null) return;
	
	if (!this.expanded)
		this.expandButton.tipsy(true).updateTitle("Collapse");
	this.expanded = true;
	this.body.removeClass("collapsed");
	this.expandButton.removeClass("icon-plus-circled");
	this.expandButton.addClass("icon-minus-circled");
}

Slot.prototype.toggle = function() {
	if (this.expanded)
		this.collapse();
	else
		this.expand();
}

Slot.prototype.fixWidth = function() {
	if (!fullWidth)  // 1-15-16 mse
		this.col.addClass("col-md-6");
	else
		this.col.removeClass("col-md-6");
}

Slot.prototype.getState = function() {
	if (this.user != null)
		return this.user.getState();
	return null;
}

Slot.prototype.overwriteState = function(newState) {
	if (this.user != null)
		this.user.overwriteState(newState);
}

Slot.prototype.restoreState = function() {
	if (this.user != null)
		this.user.restoreState();
}

Slot.prototype.toggleDelete = function(changeUserState) {
	if (changeUserState === undefined) changeUserState = true;
	
	if (this.type == "new") {
		newSlots.splice(newSlots.indexOf(this), 1);
		this.overwriteState(states.deleted); // Make sure the user is marked as deleted
		this.recycle();
		return;
	}
	
	this.deleted = !this.deleted;
	
	this.editable.toggleClass("deleted");
	this.collapse();
	
	if (this.deleted) {
		this.expandButton.addClass("hidden");
		this.deleteButton.removeClass("icon-cancel-circle");
		this.deleteButton.addClass("icon-undo");
		this.deleteButton.tipsy(true).updateTitle("Undo delete");
		
		if (changeUserState)
			this.overwriteState(states.deleted);
		
		if (this.mergeButton != null) this.mergeButton.addClass("hidden");
	} else {
		this.expandButton.removeClass("hidden");
		this.deleteButton.addClass("icon-trash-empty");
		this.deleteButton.removeClass("icon-undo");
		this.deleteButton.tipsy(true).updateTitle("Delete user");
		
		if (changeUserState)
			this.restoreState();
		
		if (this.mergeButton != null) this.mergeButton.removeClass("hidden");
	}
}

Slot.prototype.updateTitle = function() {
	if (this.user != null)
		this.headTitle.text(this.user.info['Last name'] + ', ' + this.user.info['First name']);
	else
		this.headTitle.text('');
}

Slot.prototype.updateUsername = function() {
	//
	// Teachers types need to have full email as their username, so no special processing is needed.
	// Student types we can auto-generate based on first/last name
	//

	if(userType == "student") {
        console.log("UpdateUsername");
        
        var username = '';
		if (this.user.originalInfo['Username'] == '')
			username = this.user.info['First name'].trim().toLowerCase().charAt(0) + this.user.info['Last name'].trim().toLowerCase();
        else
			username = this.user.info['Username'].split('@')[0];
            
       if(username.length>0)
            this.usernameInput.val(username);
            
        console.log(this.user.info);
	}
    
}

Slot.prototype.updateLoginAs = function() {
	if(userType == "student") {
		var username = this.usernameInput.val();
        this.user.info['Username'] = username.trim().replace(/\s/g,'') + '@' + this.user.info['Group'] + '.' + domain;

        console.log("UpdateLoginAs: user=" + this.user.info['Username']);
        
        if(username.length>0)
		  $("#" + this.type + "-slot-" + this.id + " #login-as").html('Login: ' + this.user.info['Username']);
	}
    
}

Slot.prototype.hide = function(term) {
	if (term == "") {
		this.editable.removeClass("hidden");
		return;
	}

	var hidden = true;

	var re = new RegExp(term, 'i');

	$.each(this.user.info, function(index, value) {
		if (value.search(re) == 0)
			hidden = false;
	});

	if ((this.user.info["First name"] + " " + this.user.info["Last name"]).search(re) == 0)
		hidden = false;

	if (hidden) {
		this.editable.addClass("hidden");
	} else {
		this.editable.removeClass("hidden");
		this.collapse();
	}
}

User = function(guideline, t, initialState) {
	// Store the user's info
	this.originalInfo = {};
	this.info = {};
	for (var i = 0; i < guideline.length; ++i) {
		guideline[i] = guideline[i].trim().replace(/^"|"$/g,'');
		if ((t[i] === undefined) || (t[i] == null))
			t[i] = '';
		else
			t[i] = t[i].trim().replace(/^"|"$/g,'');
		this.originalInfo[guideline[i]] = t[i];
		this.info[guideline[i]] = t[i];
	}
	
	
	this.state = initialState;
	this.prevState = null;
}

User.prototype.getState = function() {
	return this.state;
}

User.prototype.overwriteState = function(newState) {
	if (newState == states.edited) {
		if (!edited) {
			edited = true;
			$(".save-body").css("display", "block");
		}
	}
	
	this.prevState = this.state;
	this.state = newState;
}

User.prototype.restoreState = function() {
	this.state = this.prevState;
	this.prevState = null;
}

