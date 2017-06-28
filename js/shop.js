var g_modal;
var g_items=0;
var g_totalItems = 0;

function calculateSubscriptionEnd(start, type) {
	var day = start.getDate();
	var mo = start.getMonth();
	var yr = start.getFullYear();
    switch (type) {
	    case "0": mo++; break;      // home-monthly
		case "1": yr++; break;      // yearly (school default)
		case "9": yr+=100; break;   // universal
	}

    return new Date(yr, mo, day);
}


function getAjaxForOrder(p_id, amt) {

	return $.ajax( {
//				url: "http://fg/shop/xcart/cart.php",
				url: getAbsoluteUrl("/../xcart/cart.php"),
				async: false,
				data: {
					target: "cart",
					action: "add",
					product_id: p_id.toString(),
					amount: amt.toString(),
				},
				success: function(result) {
				    console.log("Order successfully added to cart.");
                    console.log("g_items = " + g_items + ", g_totalItems = " + g_totalItems);
                    g_modal.insertContent( {content: "Adding Products to Cart.<p>Item " + g_items.toString() + " of " + g_totalItems.toString() } );
                    g_items++;
				},
				error: function(result) {
					if(result["status"] != 200) {
						console.log("Error updating cart. Please try again: " + result["statusText"]);
						alert("Error updating cart. Please try again later. (" + result["statusText"] + ")");
					}
				},
			});
}

/*
*   NOTE: This function requires the products in productList (shop-live.json) to be in increasing order
*/
function getSKUForNumberOfSeats(products, seats) {
	var _id = -1;
	
	for(var i=0; i<products.length; i++) {
	    if( products[i][0].startsWith('SEATS-')) {
		    if( seats <= products[i][4]) {
			    _id = products[i][1];
				return _id;
			}
		}
	}
	
	return _id;
}

function calculateNumberOfItemsToCart(products, order) {
    var items = 0;
    var productCode = 0;
    
    if('subscription' in order || 'addSubscription' in order ) items++;
    if('skuSeats' in order) items++;
    
    if('productCode' in order ) {
        var licenseType = order['licenseType'];
        productCode = order['productCode'];
        if(productCode == 255)
            items++;
        else {
            for(i=0; i<products.length; i++) {
                if( products[i][0].startsWith( licenseType ))
                    if(productCode & products[i][4])
                        items++;
            }
        }
    }
    
    return items;
    
}

function buildOrders(theOrder) {
	
    var order = new Array();
    var type = 0;

	console.log(theOrder);

	var products = theOrder['productList'];
    
    //
    // calculate the number of times we will add something to our cart
    //
    if('modal' in theOrder) {
        g_modal = theOrder['modal'];
        g_totalItems = calculateNumberOfItemsToCart(products, theOrder);
        g_items = 1;
        console.log("g_totalItems = " + g_totalItems);
    }
    
	if(!('productList' in theOrder)) {
	    console.log('No products in list');
		console.log(theOrder);
	    return null;
	}
		
	
	/****
	*
	* Add Subscription type to x-cart
	* For schools/classes, this is yearly (either classroom or school district)
	* For home/personal, this is yearly or universal
	*****/
	if('subscription' in theOrder)
	{
        type = parseInt(theOrder['subscription']);
		order.push( getAjaxForOrder( theOrder['subscription'], 1));
	}
	
	if('addSubscription' in theOrder)
	{
	    //TODO: have to figure out what subscription model we are using...
        type = parseInt(theOrder['addSubscription']);
		order.push( getAjaxForOrder( theOrder['addSubscription'], 1));
	}

	
	/****
	*
	* Add # of Seats to x-cart
	*
	* search products[][] array in the data column for the number of seats desired
	* BUT Only the products with the SEATS-* SKU
	* Not used for personal/home licenses (Limit 6)
	*
	*****/
    if('skuSeats' in theOrder )
    {
        if('numSeats' in theOrder)
        {
            var seats = theOrder['numSeats'];
            var productId= theOrder['skuSeats'];
            if(seats>0 && productId!=-1) {
                order.push( getAjaxForOrder( productId, seats ));
            }
        }

        if('addSeats' in theOrder)
        {
            if(theOrder['addSeats'] > 0)
            {
                var seats = theOrder['addSeats'];
                var productId= theOrder['skuSeats'];
                if(seats>0 && productId!=-1) {
                    order.push( getAjaxForOrder( productId, seats ));
                }
            }

        }
    }

	/****
	*
	* Add products type to x-cart ONLY if we are not universal!
    *
    * If we have a universal subscription, don't individually add the products, we can add all the products from the admin
	*
	*****/
    if(type != 64 && type != 45 && type != 47)
    {
        if('productCode' in theOrder)
        {
            var licenseType = theOrder['licenseType'];
            var productCode = theOrder['productCode'];

            for(i=0; i<products.length; i++) {
                if( products[i][0].startsWith( licenseType )) {
                    if(productCode & products[i][4]) {
                        order.push( getAjaxForOrder(products[i][1], 1) );
                    }
                }
            }
        }

        if('addOnProduct' in theOrder) {
            var licenseType = theOrder['licenseType'];
            var productCode = theOrder['addOnProduct'];

            for(i=0; i<products.length; i++) {
                if( products[i][0].startsWith( licenseType )) {
                    if(productCode & products[i][4]) {
                        order.push( getAjaxForOrder(products[i][1], 1) );
                    }
                }
            }
        }
    }

	return order;
}


