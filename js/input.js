// Reusable text input prototype
TextInput = function(json) {
	var json = json || {};
	var _this = this;

	if (json instanceof Element) {
		// Load JSON from wrapper element
		var elem = this.wrapper = json;
		json = {};
		$(elem).removeClass("text-input");
		$(elem).addClass("text-input-wrapper");
		json.id = elem.id;
		json.label = elem.getAttribute("data-label");
		json.name = elem.getAttribute("data-name");
		var elem_placeholder = elem.getAttribute("data-placeholder");
		json.placeholder = !(elem_placeholder === 'false' ||
			elem_placeholder == null);
		json.type = elem.getAttribute("data-type");
		json.value = elem.getAttribute("data-value");
	} else {
		// Create wrapper element
		var elem = this.wrapper = document.createElement("div");
		elem.className = "text-input-wrapper";
		elem.setAttribute("data-type", json.type || "text");
		if (json.id) {
			elem.id = json.id;
		}
	}

	if (!json.id) {
		this.wrapper.id = json.id = "text-field-" + (text_input_counter ++);
	}

	// Create children
	{	// Label
		var elem = this.label = document.createElement("label");
		elem.setAttribute("for", json.id + "-input");
		$(elem).text(json.label);
		if (json.placeholder) {
			$(elem).addClass("placeholder");
		}
		this.wrapper.appendChild(elem);
	}
	{	// Input
		var elem = this.input = document.createElement("input");
		elem.className = "text-input";
		elem.id = json.id + "-input";
		elem.value = json.value || "";
		elem.type = json.type || "text";
		elem.name = json.name || "";
		if (json.placeholder) {
			$(elem).on("input", function() {
				_this.label.style.display = _this.input.value ?
					"none": "";
			});
		}
		this.wrapper.appendChild(elem);
	}
	// Password show characters thing
	if (json.type == "password") {
		var elem = document.createElement("div");
		elem.className = "show-char-button";
		var input = this.input;
		$(elem).on("click", function() {
			input.type = (input.type == "password" ? "text" :
				"password");
		});
		this.wrapper.appendChild(elem);
	}
}

// Reusable checkbox prototype
CheckboxInput = function(json) {
	var json = json || {};
	var _this = this;
	
	if (json instanceof Element) {
		// Load JSON from wrapper element
		var elem = this.wrapper = json;
		json = {};
		$(elem).removeClass("checkbox-input");
		$(elem).addClass("checkbox-input-wrapper");
		json.id = elem.id;
		json.label = elem.getAttribute("data-label");
		json.name = elem.getAttribute("data-name");
		var elem_checked = elem.getAttribute("data-checked");
		json.checked = !(elem_checked === 'false' ||
			elem_checked == null);
	} else {
		// Create wrapper element
		var elem = this.wrapper = document.createElement("div");
		elem.className = "checkbox-input-wrapper";
		if (json.id) {
			elem.id = json.id;
		}
	}

	if (!json.id) {
		this.wrapper.id = json.id = "checkbox-" + (checkbox_input_counter ++);
	}

	// Create children
	{	// Checkbox
		var elem = this.input = document.createElement("input");
		elem.type = "checkbox";
		elem.id = json.id + "-input";
		elem.checked = !!json.checked;
		this.wrapper.appendChild(elem);
	}
	{	// Label
		var elem = this.label = document.createElement("label");
		elem.setAttribute("for", json.id + "-input");
		$(elem).text(json.label);
		this.wrapper.appendChild(elem);
	}
}

// Reusable big button prototype
BigButton = function(json) {
	var json = json || {};
	var _this = this;
	var attributes = new Array();

	if (json instanceof Element) {
		// Load JSON from wrapper element
		var elem = this.wrapper = json;
		json = {};
		var attributes = /^<div\s+([^>]*)>.*<\/div>/i.exec(elem.outerHTML)[1];
		attributes = attributes.split(/['"]\s+/);
		attributes[attributes.length - 1] =
			attributes[attributes.length - 1].replace(/['"]$/, '');
		$(elem).removeClass("big-button");
		$(elem).addClass("big-button-holder");
		json.id = elem.id;
		json.text = $(elem).text().trim();
		$(elem).empty();
		json.icon = elem.getAttribute("data-icon") || "blank";
		json.href = elem.getAttribute("data-href") || null;
		json.fileId = elem.getAttribute("data-file-id") || null;
		json.fileType = elem.getAttribute("data-file-type") || "";
	} else {
		// Create wrapper element
		var elem = this.wrapper = document.createElement("div");
		elem.className = "big-button-holder";
		json.icon = json.icon || "blank";
		if (json.id) {
			elem.id = json.id;
		}
	}

	// Create children
	var wrapper = this.wrapper;
	if (json.href) { // Link
		var elem = document.createElement("a");
		for (var i = attributes.length; i --; ) {
			var exec = /([a-z-]+)\s*=\s*['"](.*)/i.exec(
				attributes[i]);
			$(elem).attr(exec[1], exec[2]);
		}
		elem.href = json.href;
		wrapper.appendChild(elem);
		wrapper = elem;
	} else if (json.fileId) { // Form & Label (for file upload)
		var form = document.createElement("form");
		form.id = json.fileId + "-form";
		wrapper.appendChild(form);
		var elem = document.createElement("label");
		for (var i = attributes.length; i --; ) {
			var exec = /([a-z-]+)\s*=\s*['"](.*)/i.exec(
				attributes[i]);
			$(elem).attr(exec[1], exec[2]);
		}
		elem.setAttribute("for", json.fileId);
		form.appendChild(elem);
		wrapper = elem;
	} else { // Basic button
		var elem = document.createElement("div");
		for (var i = attributes.length; i --; ) {
			var exec = /([a-z-]+)\s*=\s*['"](.*)/i.exec(
				attributes[i]);
			$(elem).attr(exec[1], exec[2]);
		}
		wrapper.appendChild(elem);
		wrapper = elem;
	}
	{	// Content
		var elem = document.createElement("div");
		elem.className = "content";
		wrapper.appendChild(elem);
		var p = this.content = document.createElement("p");
		elem.appendChild(p);
	}
	if (json.fileId) { // File input
		var elem = document.createElement("input");
		elem.id = json.fileId;
		elem.type = "file";
		if (json.fileType) {
			elem.accept = json.fileType;
		}
		this.content.appendChild(elem);
	}
	{	// Icon
		var elem = document.createElement("span");
		elem.className = "icon-" + json.icon;
		this.content.appendChild(elem);
		// Text
		$(this.content).append(json.text);
	}
}


// Keep track of how many inputs exist without custom id's
text_input_counter = 0;
checkbox_input_counter = 0;

// Convert placeholder input elements into actual input elements
$(window).ready(function() {
	$(".text-input").each(function() {
		new TextInput(this);
	});
	$(".checkbox-input").each(function() {
		new CheckboxInput(this);
	});
	$(".big-button").each(function() {
		new BigButton(this);
	});
});