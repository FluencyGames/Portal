Modal = function() {
	var $this = this;
	
	// Base elements
	this.overlay = $('<div id="overlay"></div>');
	this.modal = $('<div class="card modal"></div>');
	this.head = $('<div class="head center bold"></div>');
	this.title = $('<span>Title</span>');
	this.button = $('<span class="close-button icon-cancel"></span>');
	this.content = $('<div class="body"></div>');
	this.url = '';
	
	this.modal.hide();
	this.modal.append(this.head);
	this.head.append(this.title);
	this.head.append(this.button);
	this.modal.append(this.content);
	
	$(document).ready(function() {
		$("body").append($this.overlay, $this.modal);
		
		$this.overlay.bind('click', function() {
			$this.close();
		});
		
		$this.button.bind('click', function() {
			$this.close();
		});
		
		$(document).keyup(function(event) {
			if (event.which == 27) {
				$this.close();
			}
		});
	});
};

Modal.prototype.insertContent = function(settings) {
	
}

Modal.prototype.open = function(settings) {
	this.overlay.addClass("visible");
	this.modal.show();
	
	if (settings === undefined)
		settings = new Object();
	
	this.update(settings);
	
	// Add binding
	$(window).bind('resize.modal', function() {
		$this.center();
	});
	
	setScrollEnabled(false);
};

Modal.prototype.update = function(settings) {
	
	// Insert content
	this.title.text(settings.title || "Title");
	this.insertContent(settings);
	
	var $this = this;
	
	// Center
	this.center();
	
	// Center
	this.center();
}

Modal.prototype.close = function() {
	this.overlay.removeClass("visible");
	this.modal.hide();
	
	$(window).unbind('resize.modal');
	
	setScrollEnabled(true);
};

Modal.prototype.center = function() {
	var top, left;
	
	top = Math.max($(window).height() - this.modal.outerHeight(), 0) * 0.5;
	left = Math.max($(window).width() - this.modal.outerWidth(), 0) * 0.5;
	
	this.modal.css({
		top: top,// + $(window).scrollTop(),
		left: left// + $(window).scrollLeft()
	});
};

LoadingModal = function() {
	this.modal.addClass('loading-modal');
}

LoadingModal.prototype = new Modal();

LoadingModal.prototype.insertContent = function(settings) {
	this.content.html(settings.content || "");
	var refreshTimeout = settings.refresh || 0;
	var refreshUrl = settings.url || '';
	
	if (refreshTimeout > 0) {
		this.content.html('Please <a onClick="refresh();">click here</a> if the page does not automatically refresh after 5 seconds');
		setTimeout(function() {
			refresh(refreshUrl);
		}, refreshTimeout);
	}
}

// { text, classes, link, click, html5 }
Alert = function(a) {
	if (a === undefined) a = new Object();
	
	this.type = a.type || "notification"; // button or link
	
	this.div = $('<div class="alert"></div>');
	if (a.classes != undefined) this.div.addClass(a.classes);
	
	this.text = $('<span></span>');
	this.text.html(a.text || "Text")
	this.div.append(this.text);
	
	$(".alerts").prepend(this.div);
	
	var $this = this;
	this.method = function() { $this.close(); };
	
	if (a.click != undefined) {
		this.method = a.click;
	}
	
	if (a.link != undefined) {
		this.method = function() { window.location = a.link; }
		this.div.addClass("link");
	}
	
	this.div.bind("click", this.method);
}

Alert.prototype.close = function() {
	this.div.remove();
}