/*
	this will hold all the updates to the subscription array
*/

function updateSubscriptionItem( element, unit, val=-9999 ) {
	if(val == -9999)
		val = element.parent().attr('data-value');
	
	if(val > 1) unit = unit + 's';
	
	if( val == 0)
	    element.parent().hide();
	else {
	    element.parent().show();
		$('#' + element.parent().attr('id')+' label').text( element.parent().attr('data-label') + ' (' + val + ' ' + unit + ')');
	}
}

function updateSubscriptionOrder() {
	var bDisplay = false;
	var val = 0;
	
	$("[data-name=product-order] input[type=checkbox]").each(function() {
		val = $(this).parent().attr('data-value');
		if( val!=0 )
			bDisplay = true;
	});		
		
	if( !bDisplay) 
		$("#subscription-order").hide();
	else {
		$("#subscription-order").show();
		
	    updateSubscriptionItem( $("#product-100 input[type=checkbox]"), 'year');
	    updateSubscriptionItem( $("#product-200	input[type=checkbox]"), 'seat');
		
		prod = parseInt( $("#product-300 input[type=checkbox]").parent().attr('data-value') );
		count = (prod & 1 ? 1 : 0) + 
				(prod & 2 ? 1 : 0) +
				(prod & 4 ? 1 : 0) +
				(prod & 8 ? 1 : 0) +
				(prod & 16 ? 1 : 0);
	    updateSubscriptionItem( $("#product-300 input[type=checkbox]"), 'product', count);
	}	
}

/* PRODUCT MODAL */

ProductModal = function() {
	this.modal.addClass('product-modal');
	
	var row = $('<div class="row"></div>');
	this.content.append(row);
	
	// Left column
	this.leftCol = $('<div class="col-xs-12 col-md-8"></div>');
	this.leftCol.append($('<img src="http://lorempixel.com/480/360/technics" />'));
	
	this.thumbnails = $('<div class="thumbnails"></div>');
	
	this.thumbnails.append($('<img src="http://lorempixel.com/211/120/cats" />'));
	this.thumbnails.append($('<img src="http://lorempixel.com/210/120/nature" />'));
	this.thumbnails.append($('<img src="http://lorempixel.com/212/120/fashion" />'));
	
	this.leftCol.append(this.thumbnails);
	
	// Right column
	this.rightCol = $('<div class="col-xs-12 col-md-4"></div>');
	this.productTitle = $('<div class="title">Title</div>');
	// TODO: Perhaps gather this information from an AJAX call? Yes I think so
	this.description = $('<div class="description">A really long run on sentence that also is a really, really long run on sentence that also is a really, really, really long run on sentence that also is a really, really, really, really long run on sentence that also is a really, really, really, really, really long run on sentence that also is a really, really, really, really, really, really long run on sentence.</div>');
	this.purchaseButton = $('<button>Purchase</button>');
	this.rightCol.append(this.productTitle, this.description, this.purchaseButton);
	
	row.append(this.leftCol, this.rightCol);

}

/*
ProductModal.prototype = new Modal();

ProductModal.prototype.insertContent = function(settings) {
	this.productTitle.text(settings.productTitle);
}

ProductModal = new ProductModal();

registerOnClick(".big-button", function(element) {
	//ProductModal.open({title: "Add Product", productTitle: element.data("modal-title")});
	subscriptionOrder['Products'] += 1;
	updateSubscription();
});
*/

// Extend License Modal
YearsModal = function() {
	this.modal.addClass('years-modal');
	
	var dropdown = $('<select id="years-select" class="big"><option value="1">1 Years</option><option value="2">2 Years</option><option value="3">3 Years</option><option value="4">4 Years</option><option value="5">5 Years</option><option value="6">6 Years</option><option value="7">7 Years</option><option value="8">8 Years</option><option value="9">9 Years</option><option value="10">10 Years</option></select>');
	
//	var button = $('<button onClick="extendLicenseAJAX();">Extend</button>');
	var button = $('<button onClick="extendLicense();">Extend</button>');

	this.content.append(dropdown);
	this.content.append(button);
}

YearsModal.prototype = new Modal();

YearsModal.prototype.insertContent = function(settings) {
		// do nothing
}

YearsModal = new YearsModal();

function extendLicenseAJAX() {
	sendAjax({
		url: "php/ajax/manage/extend-license.php",
		data: {
			years: $("#years-select").val()
		},
		success: function(result) {
			LoadingModal.update({title: "Success!", refresh: 1000});
		},
		error: function(result) {
			LoadingModal.update({title: "Failure", content: "The request failed. Please refresh and try again."});
		}
	});
	
	YearsModal.close();
	LoadingModal.open({title: "Sending..."});
}

function extendLicense() {
	var current = parseInt($("#product-100 input[type=checkbox]").parent().attr('data-value')) + parseInt( $("#years-select").val() );
	$("#product-100 input[type=checkbox]").parent().attr('data-value', current);
	YearsModal.close();
	updateSubscriptionOrder();
}


registerOnClick("#extend-years-button", function(element) {
	YearsModal.open({title: "Extend License"});
});



/* SEATS MODAL */

SeatsModal = function() {
	this.modal.addClass('seats-modal');
	
	var row = $('<div class="row"></div>');
	
	var col1 = $('<div class="col-xs-6"></div>');
	var col2 = $('<div class="col-xs-6"></div>');
	
	this.tenButton = $('<div class="big-button increase-seats" data-amount="10" data-icon="th-large">Add 10 Seats</div>');
	new BigButton(this.tenButton[0]);
	this.hundredButton = $('<div class="big-button increase-seats" data-amount="100" data-icon="th">Add 100 Seats</div>');
	new BigButton(this.hundredButton[0]);
	
	registerOnClick(".big-button.increase-seats", function(element) {
	    /*
			We will now send the information to x-cart with all updates at one
			time...x-cart will call-back to us with the changes to update the db
		*/
		
		/* increaseSeatsAJAX(element.attr("data-amount")); */
		var current = parseInt( $("#product-200 input[type=checkbox]").parent().attr('data-value')) + parseInt(element.attr("data-amount"));
		$("#product-200 input[type=checkbox]").parent().attr('data-value', current);
		updateSubscriptionOrder();
		SeatsModal.close();
	});
	
	col1.append(this.tenButton);
	col2.append(this.hundredButton);
	row.append(col1);
	row.append(col2);
	this.content.append(row);
}

SeatsModal.prototype = new Modal();

SeatsModal.prototype.insertContent = function(settings) {
	
}

SeatsModal = new SeatsModal();

function increaseSeatsAJAX(numSeats) {
	sendAjax({
		url: "php/ajax/manage/increase-seats.php",
		data: {
			amount: numSeats
		},
		success: function(result) {
			LoadingModal.update({title: "Success!", refresh: 1000});
		},
		error: function(result) {
			LoadingModal.update({title: "Failure", content: "The request failed. Please refresh and try again."});
		}
	});
	
	SeatsModal.close();
	LoadingModal.open({title: "Sending..."});
}

registerOnClick("#increase-seats-button", function(element) {
	SeatsModal.open({title: "Increase Seats"});
});



/* LOADING MODAL */
LoadingModal = new LoadingModal();

$(window).ready(function() {
	//$.getJSON(getAbsoluteUrl("media/products.json"), function(data) {
	//	populateProducts(data);
	//});
});

























