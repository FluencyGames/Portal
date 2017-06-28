var edited = false;

var fullWidth = true; // 1-15-16 mse
//var newLicenses = [];
//var newSlots = [];
//var newOffset = 0;
var existingUsers = [];
var existingSlots = [];
var existingOffset = 0;
var existingCount = 25; // Change this for the default

//var newSlotPool = [];
var existingSlotPool = [];

var states = {
	added: "added",
	deleted: "deleted",
	edited: "edited",
	unedited: "unedited"
};

/******
*
*   These are the dialogs used for extending the license/adding products
*
*******/

///////////////////////////// Extend License Modal ////////////////////////////////

updateLicenseModal = function() {
	this.modal.addClass('years-modal');
	var dropdown = $('<select id="years-select" class="big"><option value="100">Unlimited</option><option value="1">1 Year</option><option value="2">2 Years</option><option value="3">3 Years</option><option value="4">4 Years</option><option value="5">5 Years</option><option value="6">6 Years</option><option value="7">7 Years</option><option value="8">8 Years</option><option value="9">9 Years</option><option value="10">10 Years</option></select>');
	var button = $('<button onClick="updateLicense();">Extend</button>');
	this.content.append(dropdown);
	this.content.append(button);
	this.elementId = -1;
}

updateLicenseModal.prototype = new Modal();

updateLicenseModal.prototype.insertContent = function(settings) {
    console.log("updateLicenseModal.insertContent:");
	this.elementId = settings.elementId;
    console.log(this.elementId);
}

updateLicenseModal = new updateLicenseModal();

function updateLicense() {
	// take value from drop down and add the years to the current date value
    console.log("updateLicense())");
    console.log(updateLicenseModal.elementId);
	var amt = parseInt( $("#years-select").val());
	if(updateLicenseModal.elementId != -1) {
		var e = $("#existing-slot-" + updateLicenseModal.elementId + " input[name='EndDate']");
		var cur = e.val();
		var dt = new Date( cur );
		dt.setFullYear( dt.getFullYear() + amt );
		e.val(dateFormat(dt));
		e.trigger('change');
	}
	updateLicenseModal.close();

}

///////////////////////////// Add Seats Modal ////////////////////////////////

updateSeatsModal = function() {
	this.modal.addClass('seats-modal');
	var dropdown = $('<select id="seats-select" class="big"><option value="10">10 Seats</option><option value="25">25 Seats</option><option value="50">50 Seats</option><option value="100">100 Seats</option><option value="200">200 Seats</option><option value="300">300 Seats</option><option value="500">500 Seats</option><option value="1000">1000 Seats</option></select>');
	var button = $('<button onClick="updateSeats();">Add Seats</button>');
	this.content.append(dropdown);
	this.content.append(button);
	this.elementId = -1;
}

updateSeatsModal.prototype = new Modal();

updateSeatsModal.prototype.insertContent = function(settings) {
	this.elementId = settings.elementId;
    console.log("updateSeatsModal.insertContent:");
    console.log(updateLicenseModal.elementId);
}

updateSeatsModal = new updateSeatsModal();

function updateSeats() {
	// take value from drop down and add the years to the current date value
    console.log("updateSeats()");
    console.log(updateSeatsModal.elementId);
	var amt = parseInt( $("#seats-select").val());
	if(updateSeatsModal.elementId != -1) {
		var e = $("#existing-slot-" + updateSeatsModal.elementId + " input[name='NumUsers']");
		amt = amt + parseInt(e.val());
		e.val(amt);
		e.trigger('change');
	}
	updateSeatsModal.close();

}


///////////////////////////// Add Products Modal ////////////////////////////////

updateProductsModal = function() {
	this.modal.addClass('products-modal');
	var dropdown = $('<select id="products-select" class="big"><option value="255">All Products</option><option value="1">Addition Blocks</option><option value="2">Multiplication Blocks</option><option value="4">Percent Bingo</option><option value="8">Subtraction Blocks</option><option value="16">Integer Blocks</option>');
	var button = $('<button onClick="updateProducts();">Add Products</button>');
	this.content.append(dropdown);
	this.content.append(button);
	this.elementId = -1;
}

updateProductsModal.prototype = new Modal();

updateProductsModal.prototype.insertContent = function(settings) {
	this.elementId = settings.elementId;
    console.log("updateProductsModal:");
    console.log(this.elementId);
}

updateProductsModal = new updateProductsModal();

function updateProducts() {
	// take value from drop down and add the years to the current date value
    console.log("updateProduct())");
    console.log(updateProductsModal.elementId);
	var amt = parseInt( $("#products-select").val());
	if(updateProductsModal.elementId != -1) {
		var e = $("#existing-slot-" + updateProductsModal.elementId + " input[name='Products']");
		if(amt != 255)
			amt = amt & parseInt(e.val());
		e.val(amt);
		e.trigger('change');
	}
	updateProductsModal.close();

}

/******************* End Update Dialogs ***************/

$(window).ready(function() {
	//
	// search field in nav bar
	//
	$("#search-input").on("keyup keydown", function() {
	//$("#search-execute").on("click", function() {
		var term = $("#search-input").val();

 		var length = existingSlots.length;
		for (var i = 0; i < length; ++i) {
			existingSlots[i].hide(term);
		}
	});

	$("#search-cancel").on("click", function() {
		var term = '';

		var length = existingSlots.length;
		for (var i = 0; i < length; ++i) {
			existingSlots[i].hide(term);
		}
        $("#search-input").val('');
	});
	
	// Make global buttons work
	linkGlobalButtons();
	
	// Make dropdowns work
	linkDropdowns();
	
	// Make first/prev/next/last buttons work
	linkButtons();
	
	// Get those users
	sendAjax({
		url: "php/ajax/manage/get-licenses.php",
		data: {
			requesting: 'Licenses',
		},
		success: function(result) {
			if (result['success']) {
				processLicenses(result['licenses']);
				loadIntoSlots(existingCount);
			} else {
				alert(result['error']);
			}
		},
		error: function(result) {
			alert("Error: " + result['error']);
			console.log(result);
		},
	});
});

function createTempPassword(len=8) {
	var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ0123456789'
	var special = '!@#$%^&_+=';
	var hasSpecial = false;
	var pwd = '';
	while( pwd.length < len) {
	    r = Math.floor(Math.random() * (chars.length + 12));
		if( r < chars.length)
		    pwd += chars.substr(r, 1);
		else {
		    r = Math.floor(Math.random() * (special.length));
		    pwd += special.substr(r, 1);
		}
	}
	
	if(pwd.search(/[!@#$%^&_+=]/)==-1)
	    pwd += special.substr(Math.floor(Math.random() * (special.length)), 1);

	return pwd;
}

function sendLicenses() {
	var added = [];
	var edited = [];
	var deleted = [];

	for (var i = 0; i < existingUsers.length; ++i) {
		switch (existingUsers[i].getState()) {
			case states.unedited: // TODO: Remove this!!
			case states.edited:
				edited.push(existingUsers[i].info);
				break;
			case states.deleted:
				deleted.push(existingUsers[i].info);
				break;
		}
	}
	
	sendAjax({
		url: "php/ajax/manage/update-licenses.php",
		data: {
			added: added,
			edited: edited,
			deleted: deleted
		},
		success: function(result) {
			if (result['success']) {
				var m = new LoadingModal();
				m.open({title: 'Saving...',	refresh: 1000 });
			} else {
				alert(result['error']);
			}
		},
		error: function(result) {
			alert("Error:" + result['error'] + " (Licenses.js, line 215)");
			console.log(result);
		},
	});
}

function createSlots(num, type) {
	var numSlotsOnPage = existingSlots.length;
	var numToCreate = num - numSlotsOnPage;
	var slot;
	for (var i = 0; i < numToCreate; ++i) {
		$("#existing-body").css("display", "block");
		if (existingSlotPool.length > 0) {
			slot = existingSlotPool.pop();
			slot.addToDOM();
		} else
			slot = new Slot(type);
		existingSlots.push(slot);
	}
	
	// Reverse count because we're also removing them from the array
	for (var j = numSlotsOnPage - 1; j >= num; --j) {
		slot = existingSlots.pop();
		slot.recycle();
	}
	
	updatePageCount(type);
}

function loadIntoSlots(num,offset) {
	if(offset === undefined) offset = 0;
	
	// Ensure there are enough slots to load the users into!
	createSlots(num);
	
	for (var s = 0, u = offset; s < num; ++s, ++u) {
		if (existingUsers.length > u)
			existingSlots[s].loadUser(existingUsers[u]);
		else
			existingSlots[s].loadUser(null);
	}
}

function expandAll() {
	var numSlots = existingSlots.length;
	for (var i = 0; i < numSlots; ++i) {
		existingSlots[i].expand();
	}
}

function collapseAll() {
	var numSlots = existingSlots.length;
	for (var i = 0; i < numSlots; ++i) {
		existingSlots[i].collapse();
	}
}

function toggleHalfWidth() {
	fullWidth = !fullWidth;
	
	var numSlots;
	
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
	var element = $("#existing-page-count");
	var page = 1;
	var totalPages = 1;
	
	page = existingOffset / existingCount + 1;
	totalPages = getPageCount(existingUsers.length, existingCount) + 1;
	
	if( totalPages == 0)
	    element.text("No Records Found");
 	else
		element.text("Page " + page + " of " + totalPages);
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
		sendLicenses();
	});
}

function linkDropdowns() {
	$("#existing-licenses-count").change(function() {
		existingOffset = 0;
		existingCount = parseInt(this.value);
		loadIntoSlots(existingCount);
	});
}

function linkButtons() {
	// Existing user page buttons
	$(".existing-first-page").click(function() {
		existingOffset = 0;
		loadIntoSlots(existingCount, existingOffset);
		collapseAll();
		updatePageCount();
	});
	
	$(".existing-prev-page").click(function() {
		if (existingOffset > 0) {
			existingOffset -= existingCount;
			loadIntoSlots(existingCount, existingOffset);
		}
		collapseAll();
		updatePageCount();
	});
	
	$(".existing-next-page").click(function() {
		if (existingOffset < getPageCount(existingUsers.length, existingCount) * existingCount) {
			existingOffset += existingCount;
			loadIntoSlots(existingCount, existingOffset);
		}
		collapseAll();
		updatePageCount();
	});
	
	$(".existing-last-page").click(function() {
		existingOffset = getPageCount(existingUsers.length, existingCount) * existingCount;
		loadIntoSlots(existingCount, existingOffset);
		collapseAll();
		updatePageCount();
	});
}

function sendLicenses() {
	var added = [];
	var edited = [];
	var deleted = [];
	
	//if(!isValidDate()) {
	//
	//}
	
	for (var i = 0; i < existingUsers.length; ++i) {
		switch (existingUsers[i].getState()) {
			case states.unedited: // TODO: Remove this!!
			case states.edited:
				edited.push(existingUsers[i].info);
				break;
			case states.deleted:
				deleted.push(existingUsers[i].info);
				break;
		}
	}
	
	
	sendAjax({
		url: "php/ajax/manage/update-licenses.php",
		data: {
			added: added,
			edited: edited,
			deleted: deleted
		},
		success: function(result) {
			if (result['success']) {
				//var m = new LoadingModal();
				//m.open({title: "Loading..."/*, refresh: 1000*/});
				alert("Licenses updated.");
			} else {
				alert(result['error']);
			}
		},
		error: function(result) {
			alert("Error: "+result['error'] + " (License.js, line403)");
			console.log(result);
		},
	});
}

Slot = function(type) {
	var $this = this;
	this.user = null;
    this.locked = true;
	
	parentCol = "#existing-licenses";
	
	// Element stuff
	var col = $('<div class="col-xs-12 licenses-holder"></div>');
	this.col = col;
	this.fixWidth();
	
	this.type = type;
	this.id = existingSlots.length;
	this.editable = $('<div id="existing-slot-' + this.id + '" class="account-editable license"></div>');
	
	// Head
	var head = $('<div class="head center"></div>');
	head.addClass("head center");
    
	this.headTitle = $('<span></span>');
	head.html(this.headTitle);
	this.editable.append(head);
    
	//this.updateTitle();
	
	// Attention (Change to Checkmark, Attention, Red)
	/*var attn = false;
	if (attn) {
		var notificationIcon = $('<span data-toggle="tooltip" class="icon-attention"></span>');
		notificationIcon.attr("title", "Duplicate email");
		head.append(notificationIcon);
		notificationIcon.tipsy({gravity: 's'});
	}*/
	
	// Buttons
	var buttons = $('<span class="buttons"></span>');
	head.append(buttons);
	
	this.expandButton = $('<span data-toggle="tooltip" title="Expand" class="icon-button icon-plus-circled"></span>');
	buttons.append(this.expandButton);
	
	this.lockButton = $('<span data-toggle="tooltip" title="Unlock for Editing" class="icon-button icon-lock"></span>');
	buttons.append(this.lockButton);

    this.loginButton = $('<span data-toggle="tooltip" title="Login" class="icon-button icon-login"></span>');
	buttons.append(this.loginButton);
    
	this.deleteButton = $('<span data-toggle="tooltip" title="Delete user" class="icon-button icon-cancel-circle"></span>');
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
	
	// Append to page
	this.editable.append(body);
	col.append(this.editable);
	this.addToDOM();
	
	// Create input
	for (var i = 1; i < inputKeys.length; i++) {
		var textInput = $('<div class="text-input"></div>');
		textInput.attr("data-label", inputKeys[i]);
		textInput.attr("data-name", inputKeys[i]);
		textInput.attr("data-value", "");
		body.append(textInput);
	}
	
	var t;
	var $i = 1;
	
	$.each($(".text-input:not([id])"), function() {
		t = new TextInput(this);
        
        // change 5-4-17 mse
        // intially all edit fields are disabled
        //
        $(t.input).attr("disabled", "disabled");  
        
        // original code
		//if( inputKeys[$i] == 'System' || inputKeys[$i] == 'StartDate')
		//	$(t.input).attr("disabled", "disabled");  // these fields should not be changed
        			
		$(t.input).on('change', function(e) {
		    console.log('Change event triggered');
			var j = $(this).attr("data-info-id");
			if($(this).attr("data-info-id")=='EndDate')
			    $this.user.info[j] = __date( new Date($(this).val()));
			else
				$this.user.info[j] = $(this).val();
			if ($this.getState() == states.unedited) {
				$this.overwriteState(states.edited);
				// Todo: add purchase info to purchase info field, date, what has been updated...
			}
		});
        
		if( inputKeys[$i] == 'EndDate') { 
		    var eId = $this.id;
			$(t.input).on('click' , function(e) {
                updateLicenseModal.open({title: "Extend License", elementId: eId });
			});
		}
		if( inputKeys[$i] == 'NumUsers') { // these fields should not be changed
		    var eId = $this.id;
			$(t.input).on('click' , function(e) {
                updateSeatsModal.open({title: "Add Seats", elementId: eId });
			});
		}
		if( inputKeys[$i] == 'Products') { // these fields should not be changed
		    var eId = $this.id;
			$(t.input).on('click' , function(e) {
                updateProductsModal.open({title: "Add Products", elementId: eId });
			});
		}
        
		$(t.input).attr("data-info-id", inputKeys[$i++]);
        
	});
	
	body.append($('<div class="clear"></div>'));
	
	this.collapse();
}

Slot.prototype.loadUser = function(user) {
	this.user = user;
	
	if (user != null) {
		this.editable.removeClass("empty");
		
		for (var i = 0; i < inputKeys.length; ++i) {
		    var v = user.info[inputKeys[i]];
			if( inputKeys[i] == 'StartDate' || inputKeys[i] == 'EndDate') {
				var dt = new Date( v.replace(/\s/,'T') );
				v = dateFormat( dt );
			}
			$("#existing-slot-" + this.id + " input[name='" + inputKeys[i] + "']").val(v);
		}
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
	
	$("#existing-page-buttons-bottom").before(this.col);
	
	// Add tooltips
	this.expandButton.tipsy({gravity: 's'});
	this.deleteButton.tipsy({gravity: 's'});
	this.undoButton.tipsy({gravity: 's'});
    this.loginButton.tipsy({gravity: 's'});
    this.lockButton.tipsy({gravity: 's'});
	
	// Methods
	registerOnClick(this.expandButton, function() {
		$this.toggle();
	});
	
	registerOnClick(this.deleteButton, function() {
		$this.toggleDelete();
	});
    
    registerOnClick(this.lockButton, function() {
        $this.toggleLock();
    });

    registerOnClick(this.loginButton, function() {
        $this.loginAsSuperUser();
    });
    
	/*if (mergeButton != null) {
		registerOnClick(this.mergeButton, function() {
			$this.merge();
		});
	}*/
}

Slot.prototype.recycle = function() {
	// MAKE SURE IT's BEEN REMOVED FROM THE ARRAY BEFORE RECYCLING!
	if (existingSlots.indexOf(this) > -1)
		alert("Error: recycle was called before the Slot was removed from the array");
	
	// Insert null data to the inputs
	this.loadUser(null);
	
	// Add to a slot pool
	existingSlotPool.push(this);
	
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
		this.deleteButton.addClass("icon-cancel-circle");
		this.deleteButton.removeClass("icon-undo");
		this.deleteButton.tipsy(true).updateTitle("Delete user");
		
		if (changeUserState)
			this.restoreState();
		
		if (this.mergeButton != null) this.mergeButton.removeClass("hidden");
	}
}

Slot.prototype.updateTitle = function() {
	if (this.user != null)
		this.headTitle.text(this.user.info['DomainSuffix'] + ' (' + this.user.info['LicenseKey'] + ')');
	else
		this.headTitle.text('');
}

Slot.prototype.updateUsername = function() {
	// don't do anything for license key, it should be disabled
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

	if ( (this.user.info["DomainSuffix"]).search(re)==0 || (this.user.info["LicenseKey"]).search(re) == 0)
		hidden = false;

	if (hidden) {
		this.editable.addClass("hidden");
	} else {
		this.editable.removeClass("hidden");
		this.collapse();
	}
}

Slot.prototype.toggleLock = function(lock) {
    var eid = this.editable[0].id;
    
    if(this.locked) {
        this.locked = false;
        this.lockButton.removeClass("icon-lock");
        this.lockButton.addClass("icon-lock-open");
		this.lockButton.tipsy(true).updateTitle("Lock Editing");
        
        $("#" + eid + " .text-input").each(function() {
            if( $(this).attr('name') != 'System' && $(this).attr('name') != 'StartDate') // these fields should not be changed
                $(this).removeAttr("disabled");  
        });
        
    } else {
        this.locked = true;
        this.lockButton.removeClass("icon-lock-open");
        this.lockButton.addClass("icon-lock");
		this.lockButton.tipsy(true).updateTitle("Unlock for Editing");
        
        //$.each($(".text-input:not([id])"), function() {
        $("#" + eid + " .text-input").each(function() {
            if( $(this).attr('name') != 'System' && $(this).attr('name') != 'StartDate') // these fields should not be changed
                $(this).attr("disabled","disabled");  
        });    
    }
}

Slot.prototype.loginAsSuperUser = function() {
    console.log("loginAsSuperUser");
    console.log(this);
    
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

