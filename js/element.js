InfoStack = function(json) {
	var json = json || {};
	if (json instanceof Element) {
		var elem = json;
		var json = {};

		json.title = elem.getAttribute("data-title") || "";

		var date = elem.getAttribute("data-date");
		if (date != null) {
			var date_array = date.split(/(?:[\-\:]|\s)/);
			// console.log(date_array);
			switch (+date_array[1]) {
				case 1: json.top = "Jan"; break;
				case 2: json.top = "Feb"; break;
				case 3: json.top = "Mar"; break;
				case 4: json.top = "Apr"; break;
				case 5: json.top = "May"; break;
				case 6: json.top = "June"; break;
				case 7: json.top = "July"; break;
				case 8: json.top = "Aug"; break;
				case 9: json.top = "Sep"; break;
				case 10: json.top = "Oct"; break;
				case 11: json.top = "Nov"; break;
				case 12: json.top = "Dec"; break;
				default: break;
			}
			json.middle = date_array[2];
			json.bottom = date_array[0];
		} else {
			json.top = elem.getAttribute("data-top") || "";
			json.middle = elem.getAttribute("data-middle") || "";
			json.bottom = elem.getAttribute("data-bottom") || "";
		}
		// console.log(json);
		this.wrapper = elem;
	} else {
		var elem = document.createElement("div");
		elem.className = "info-stack";
		this.wrapper = elem;
	}

	var wrapper = this.wrapper;
	var children = new Array();
	for (var i = wrapper.children.length; i --; ) {
		children.push(wrapper.children[i]);
		wrapper.removeChild(wrapper.children[i]);
	}
	console.log(children);

	// Add children
	var elem = document.createElement("div");
	elem.className = "title";
	$(elem).text(json.title);
	wrapper.appendChild(elem);

	var elem = document.createElement("div");
	elem.className = "data-wrapper";
	wrapper.appendChild(elem);
	wrapper = elem;

	var elem = document.createElement("div");
	elem.className = "data";
	wrapper.appendChild(elem);
	wrapper = elem;

	if (json.top) {
		var elem = document.createElement("div");
		elem.className = "top";
		$(elem).text(json.top);
		wrapper.appendChild(elem);
	}

	if (json.middle) {
		var elem = document.createElement("div");
		elem.className = "middle";
		$(elem).text(json.middle);
		wrapper.appendChild(elem);
	}

	if (json.bottom) {
		var elem = document.createElement("div");
		elem.className = "bottom";
		$(elem).text(json.bottom);
		wrapper.appendChild(elem);
	}

	for (var i = children.length; i --; ) {
		this.wrapper.appendChild(children[i]);
	}
}

$(window).ready(function() {
	$(".info-stack").each(function() {
		new InfoStack(this);
	});
});