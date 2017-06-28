<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Shop.class.php");
	
	Element::redirectUsersToHome();
	
	$documentroot = Config::get('documentroot');
	$shoproot = Config::get('shoproot');
	$shop = Shop::getInstance();
	
	function outputProductArray($theShop) {
	    $str = "\n";
		$items = $theShop->getCatalog();
		foreach($items as $key => $item) {
		    $str .= "\t\t\t['{$key}', '{$item->{'Product-Id'} }', '{$item->{'Name'} }', {$item->{'Price'} }, {$item->{'data'} } ], \n";
		}
		echo  $str . "\t\t";
	}

	function outputProductList() {
		$list = Config::get('products');
		$str = "\n";
		foreach($list as $key => $value) {
			$str .= "\t\t\t['{$value}', $key], \n";
		}
		echo $str . "\t\t";
	}
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script src="<?php echo $documentroot; ?>js/tabs.js"></script>
	<script src="<?php echo $documentroot; ?>js/shop.js"></script>
	<script type="text/javascript">
		var schoolSubProductId = "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-WIDE'); ?>";
		var classSubProductId = "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-CLASSROOM'); ?>";
		var universalSubProductId = "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-CLASSUNIVERSAL'); ?>";
		var products = [<?php outputProductArray($shop); ?>];
		var productList = [<?php outputProductList(); ?>];

		function sendToCart() {
			var m = new LoadingModal();
            
            if(!$("#user-agreement [type=checkbox]").prop('checked')) {
                m.open( {title: "Submit Order",
                         content: "Please agree to terms and conditions before submitting your order.",
                         });
                return;
            }
            
			m.open( {title: "Please Wait",
					 content: "Adding Items to Cart.",
					 });
					 
            
            var o_order = {};
            var seats = $("[name=seats]").val();
		    var userEmail = $("[name=email]").val();
			var lic = ($("[name=purchaser]").val()==schoolSubProductId) ? 'LIC-' : 'PROD-';
            
            o_order['productList'] = products;
            o_order['subscription'] = $("[name=subscription]").val();
            o_order['modal'] = m;
            
            if(seats.length>0) {
                o_order['numSeats'] = parseInt(seats);
                o_order['skuSeats'] = getSKUForNumberOfSeats( products, seats);    // in shop.js
            }
            
            o_order['licenseType'] = lic;
            o_order['productCode'] = getProductValue();
            
            var orders = buildOrders(o_order);

            $.when.apply($, orders ).then( function() {
			    m.insertContent( {content: "You are being redirected to checkout.<p>Please do not close the browser."} );
				var timer = setInterval( function() {
					m.close();
				    clearInterval(timer);
                    window.location = "<?php echo $shoproot; ?>cart.php?target=checkout&action=update_profile&email="+userEmail+"&same_address=1";
				}, 1500);   // was 5000
			});

		}
        
		function removeAllProducts(which) {
			var e = document.getElementsByName(which);
			while(e[0].options.length>1) e[0].remove(1);
			e[0].selectedIndex = 0;
		}

		function addProduct(which, p, v) {
			var e = document.getElementsByName(which);
			var x = document.createElement("option");
			x.text = p;
			x.value = v;
			e[0].add(x);
		}		
 
		function updateCurrentProduct() {
            if(getProductValue()>0) {
                var e = document.getElementsByName("product")[0];
			    $("#current-products").html("Product: " + e.options[e.selectedIndex].text);			
            }
		}
        
        function calculateSeatsPrice(seats) {
            var total = 0.0;
            if(seats>0) {
                var ix = products.length-2;
                while(ix>0 && products[ix][0].startsWith("SEATS-")) {
                    if(seats > products[ix][4]) {
                        total = products[ix+1][3];
                        break;
                    }
                    
                    ix--;
                }
                // if we have broken out of the loop, then we are at < 100 seats
                total = products[ix+1][3];
            }
            
            return total;
        }
            
		function updateCurrentOrder() {
			var total = 0.0;

            //
            // re-initialize all descriptions and product lists
            //
			$("#current-subscription").html("");
			$("#current-products").html("");			
            
            //
            // School wide subscription -- user will need to select their product to license
            //
			if($("[name=purchaser]").val() == "2")
            {
                total += <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-WIDE'); ?>;		
				$("#current-subscription").html("Subscription: School ($" + <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-WIDE'); ?> + ")");
                
                // find out how much the seats cost
                var seats = $("[name=seats]").val();
                if(seats>0) {
                    var seatsTotal = calculateSeatsPrice(seats).toFixed(2);
                    $("#current-seats").html("Seats: " + (seats).toString() + " (@ $" + seatsTotal.toString() + " ea. = $" + (seatsTotal*seats).toFixed(2).toString() + ")");
                    total += seatsTotal * seats;                    
                }
                
			} 
            //
            // classroom subscription -- just show 'All Products' checked
            //
            else if($("[name=purchaser]").val() == "1")
            {
                if($("[name=subscription]").val() == classSubProductId) {
                    $("#current-subscription").html("Subscription: Classroom ($" + <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-CLASSROOM'); ?> + ")");
                    total += <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-CLASSROOM'); ?>;
                }
                if($("[name=subscription]").val() == universalSubProductId) {
                    $("#current-subscription").html("Subscription: Universal ($" + <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-CLASSUNIVERSAL'); ?> + ")");
                    total += <?php echo $shop->getProductPriceBySku('SUB-SCHOOL-CLASSUNIVERSAL'); ?>;		
                }

                //
                // handle the additional seats
                //
                var seats = $("[name=seats]").val();
                if(seats>0) {
                    var seatsTotal = calculateSeatsPrice(seats).toFixed(2);
                    $("#current-seats").html("Additional: " + (seats).toString() + " (@ $" + seatsTotal.toString() + " ea. = $" + (seatsTotal*seats).toFixed(2).toString() + ")");
                    total += seatsTotal*seats;
                }
            }
            
			//
            // Update total order amount for display
            //
			total = total.toFixed(2);    //fix floating point issues in javascript;
			//console.log("UpdateCurrentOrder: total = " + total.toString());
			
			if(total <= 0.0) {
				$("#current-order").hide();
			} else {
				$("#current-order").show();
				$("#current-order > .head").text("Current Order ($" + total.toString() + ")");
			}
            
            updateCurrentProduct();
			
		}        

		function getProductValue() {
			return $("[name=product]").val();
		}
		
		function verifyProducts() {
			return (getProductValue() > 0);
		}
		
		function verifySubscription() {
			var value = $("[name=subscription]").val();
			return (value == schoolSubProductId || value == classSubProductId || value == universalSubProductId);
		}

		function verifyPurchase() {
			var purchaser = $("[name=purchaser]").val();
			return (purchaser == "1" || purchaser == "2");
		}
		
		function verifyAccount() {
			// var username = $("[name=username]").val();  // no longer need username for signup
			var email = $("[name=email]").val();
			return (email.length > 0);
		}
        
		function verifySeats() {
			var seats = $("[name=seats]").val();
			var purchaser = $("[name=purchaser]").val();
            
            console.log("purchaser=" + purchaser + " seats="+seats);
            
            if(purchaser == "2")    // if we have schools, they must declare the number of seats
                return (parseInt(seats)>0);
            else {
                return true;
            }                   // if we have a classroom, they can have no addl seats
		}
		
		
		$(window).ready(function() {
            updateCurrentOrder();
            
			var tabs = new Tabs({
				div: "#tabs-div",
				prefix: 'verify',
                submit: sendToCart
			});
			
			$("[name=subscription]").change(function() {
                var type = $(this).val();
                removeAllProducts("product");
                if(type == schoolSubProductId || type == universalSubProductId) {
                    addProduct("product", "All Products", "255");
                } else {
                    productList.forEach(function(e) {
                        if(e[1]!=0 && e[1]!=255)
                            addProduct("product", e[0], e[1]);
                    });
                }
                
				updateCurrentOrder();
            });
            
			$("[name=purchaser]").change(function() {
					var type = $(this).val();
                    removeAllProducts("subscription");
                    removeAllProducts("product");
                
					if(type == "2") {
						// purchasing for a school
						//   get all products by default
                        $("#seats-note").hide();
                        addProduct("subscription", "Yearly", "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-WIDE'); ?>");
                        addProduct("product", "All Products", "255");
					} else if(type == "1") {
						// purchasing for a classroom
						//    need to pick specific products
                        $("#seats-note").show();
                        addProduct("subscription", "Yearly",    "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-CLASSROOM'); ?>");
                        addProduct("subscription", "Universal", "<?php echo $shop->getProductIdBySku('SUB-SCHOOL-CLASSUNIVERSAL'); ?>");
                        productList.forEach(function(e) {
                            if(e[1]!=0 && e[1]!=255)
                                addProduct("product", e[0], e[1]);
                        });
                        
					}
                
                    updateCurrentOrder();
                
				});
				
			//$("#btn-checkout").click(function() {
			//		sendToCart();
			//	});
            
			$("#btn-update-order").click(function() {
					updateCurrentOrder();
				});
					
            $("[data-name=seats]").change(function() {
                updateCurrentOrder();
            });
			
			$("[name=product]").change(function() {
				updateCurrentProduct();
				updateCurrentOrder();
			});
		});
	</script>
</head>
<body>
	<?php Element::header(); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-8 col-sm-push-2 col-md-6 col-md-push-3">
					<div class="card">
						<div class="head bold center">
							New School License
						</div>
						<form id="new-license-form" method="POST" class="no-margin">
							<div id="tabs-div" class="tabs-body">
								<div class="tabs"></div>

								<div class="tab-content" data-title="Purchase">
									<div class="clear"></div>
									<select name="purchaser">
										<option value="-1" disabled selected>I am ...</option>
										<option value="1">I am purchasing for a class</option>
										<option value="2">I am purchasing for a school or district</option>
									</select>									
								</div>
                                
								<div class="tab-content" data-title="Subscription">
									<select name="subscription">
										<option value="-1" disabled selected>Subscription Type</option>
										<option value="<?php echo $shop->getProductIdBySku('SUB-SCHOOL-CLASSROOM'); ?>">Yearly</option>
                                        <option value="<?php echo $shop->getProductIdBySku('SUB-SCHOOL-UNIVERSAL'); ?>">Universal</option>
									</select>
									
									<button class="next-tab-button">Next</button>
									<div class="clear"></div>
								</div>
                                
								<div class="tab-content" data-title="Products">
									<h2 style="text-align: center; margin-bottom: 12px;">Select Product</h2>
									<div class="clear"></div>
									<select name="product">
										<option value="0" disabled selected>Select product...</option>
									</select> 
                                    <button class="next-tab-button">Next</button>
									<div class="clear"></div>
								</div>
                                
								<div class="tab-content" data-title="Seats">
									<div class='text-input' data-label='Number of seats' data-name='seats' data-placeholder></div>
                                    <span id="seats-note">Classroom subscriptions are alloted 35 seats as part of the subscription. Enter any additional seats, if needed.</span><br/>
									<div class="clear"></div>										
									<button class="next-tab-button">Next</button>
									<div class="clear"></div>
								</div>

								<div class="tab-content" data-title="Account">
									
									<!-- <div class='text-input' data-label='Account username' data-name='username' data-placeholder></div> -->
									<div class='text-input' data-label='Account email' data-name='email' data-placeholder></div>
                                    <span>Once we have processed your order, you will recieve an email with your account details
									and login credentials.<br/><br/></span>                                    
                                    <div class="clear"></div>
                                    <div class="checkbox-input"
										 data-name="product"
										 data-value="0"
										 id="user-agreement"
										 data-label="I Agree to the Terms and Conditions"></div>                                    
                                    <div class="clear"></div>										
									<button id="submit-order-button" class="next-tab-button" id="btn-checkout">Next</button>
									<div class="clear"></div>
								</div>
							</div>
						</form>
					</div>
                    
					<div class="card" id="current-order">
						<div class="head bold center">
							Current Order
						</div>					
						<div class="body">
							<span id="current-subscription"></span><br/>
							<span id="current-products"></span><br/>
							<span id="current-seats"></span>
						</div>
					</div>
                    
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="../index">Return to login</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>