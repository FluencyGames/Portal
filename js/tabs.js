Tabs = function(options) {
	if (options.div === undefined) alert("div not given to Tabs instance");
	
	var div = options.div;
	var prefix = options.prefix || 'verify';
	//console.log(prefix);
	
	var $this = this;
	
	this.children = $(div).children();
	this.tabsDiv = $(div + " .tabs");
	this.currentTab = 0;
	this.numTabs = 0;
	this.fnSubmit = options.submit;
	
	var child, tab, cid = 0;
	for (var i = 0; i < this.children.length; ++i) {
		child = $(this.children[i]);
		if (child.attr('class') == 'tab-content') {
			if (cid == 0) child.addClass('selected');
			
			this.createTab(child.attr('data-title'), cid);
			child.attr('data-id', cid);
			++cid;
		}
	}
	
	this.numTabs = cid;
	
	var lastButton = $(".tab-content:last-of-type .next-tab-button");
	lastButton.text('Finish');
	
	$(".next-tab-button").click(function(e) {
		e.preventDefault();
		$this.nextTab();
	});
}

Tabs.prototype.createTab = function(title, id, div) {
	// Create the div, set title, and id
	var tab = $('<div class="tab"></div>');
	tab.text(title);
	tab.attr('data-id', id);
	
	// Should it be selected?
	if (id == 0) tab.addClass('selected');
	
	// Click function
	var $this = this;
	tab.click(function() {
		$this.switchTab(id);
	});
	
	// Add to DOM
	this.tabsDiv.append(tab);
}

Tabs.prototype.switchTab = function(n) {
	this.currentTab = n;
	
	// If this tab exists, switch to it!
	if (this.currentTab < this.children.length - 1) {
		for (var i = 0; i < this.children.length; ++i) {
			if (i == n)
				$('[data-id=' + i + ']').addClass('selected');
			else
				$('[data-id=' + i + ']').removeClass('selected');
		}
	} else {
		// Otherwise submit the form
		this.submit();
	}
}

Tabs.prototype.nextTab = function() {
	this.switchTab(this.currentTab + 1);
}

Tabs.prototype.submit = function() {
	var title;
	for (var i = 0; i < this.numTabs; ++i) {
		title = $('.tab[data-id=' + i + ']').text();
		
		if (!eval('verify' + title + '()')) {
			alert(title + "Form not completed.");
			return;
		}
	}
	
	if(this.fnSubmit != null)
	    this.fnSubmit.call();
	//sendForm();
}