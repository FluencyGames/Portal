<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Shop.class.php");
    //$recaptcha = new \ReCaptcha\ReCaptcha($secret);

	//Element::redirectUsersToHome();
	
	$documentroot = Config::get('documentroot');
	$shoproot = Config::get('shoproot');
	$shop = Shop::getInstance();

    // recatpcha variables
    //$siteKey = Config::get('recaptcha/sitekey');
    //$secret = Confgi::get('recaptcha/secret');

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
    <script src='https://www.google.com/recaptcha/api.js'></script>    
	<script src="<?php echo $documentroot; ?>js/tabs.js"></script>
	<script src="<?php echo $documentroot; ?>js/shop.js"></script>
	<script type="text/javascript">
		var yearlySubProductId = "<?php echo $shop->getProductIdBySku('SUB-HOME-YEARLY'); ?>";
		var universalSubProductId = "<?php echo $shop->getProductIdBySku('SUB-HOME-UNIVERSAL'); ?>";
		var products = [<?php outputProductArray($shop); ?>];
		var productList = [<?php outputProductList(); ?>];
				
		function addProductToOrder(label,price) {
			var html = $("#current-products").html();
			if(html==="") {
				$("#current-products").html("<br/>Products: " + label );
			} else {
				html += ", "+label;
				$("#current-products").html(html);
			}
		}
		
		function removeAllProducts() {
			var e = document.getElementsByName("product");
			while(e[0].options.length>1) e[0].remove(1);
			e[0].selectedIndex = 0;
		}

		function addProduct(p, v) {
			var e = document.getElementsByName("product");
			var x = document.createElement("option");
			x.text = p;
			x.value = v;
			e[0].add(x);
		}		
		
		function updateCurrentProduct() {
			var e = document.getElementsByName("product")[0];
			$("#current-products").html("Product: " + e.options[e.selectedIndex].text);			
		}
		
		function updateCurrentOrder() {
			var total = 0.0;

            //
            // re-initialize all descriptions and product lists
            //
			$("#current-subscription").html("");
			$("#current-products").html("");			
            removeAllProducts();
            
            //
            // universal subscription -- just show 'All Products' checked
            //
			if($("[name=subscription]").val() == universalSubProductId)
            {
				total += <?php echo $shop->getProductPriceBySku('SUB-HOME-UNIVERSAL'); ?>;		
				$("#current-subscription").html("Subscription: Universal ($" + <?php echo $shop->getProductPriceBySku('SUB-HOME-UNIVERSAL'); ?> + ")<br/>");
				addProduct("All Products", 255);
			} 
            
            //
            // yearly subscription -- user will need to select their product to license
            //
            if($("[name=subscription]").val() == yearlySubProductId)
            {
				total += <?php echo $shop->getProductPriceBySku('SUB-HOME-YEARLY'); ?>;
				$("#current-subscription").html("Subscription: Yearly ($" + <?php echo $shop->getProductPriceBySku('SUB-HOME-YEARLY'); ?> + ")<br/>");
				productList.forEach(function(e) {
					if(e[1]!=0 && e[1]!=255)
						addProduct(e[0], e[1]);
				});
            }
			

			//
            // Update total order amount for display
            //
			total = total.toFixed(2);    //fix floating point issues in javascript;
			console.log("UpdateCurrentOrder: total = " + total.toString());
			
			if(total <= 0.0) {
				$("#current-order").hide();
			} else {
				$("#current-order").show();
				$("#current-order > .head").text("Current Order ($" + total.toString() + ")");
			}
			
		}
		
		function sendToCart() {
            if($("#user-agreement [type=checkbox]").prop('checked'))
            {
                var userEmail = $("[name=email]").val();
                var m = new LoadingModal();
                m.open( {title: "Please Wait",
                         content: "Adding Items to Cart.",
                         });

                var orders = buildOrders( { productList: 	products,
                                            subscription:   $("[name=subscription]").val(),
                                            productCode:	getProductValue(),
                                            licenseType:    'LIC-',
                                            modal:          m
                                            });

                $.when.apply($, orders ).then( function() {
                    m.insertContent( {content: "Redirecting to checkout.<p>Please Do not close the browser."} );
                    var timer = setInterval( function() {
                        m.close();
                        clearInterval(timer);
                        window.location = "<?php echo $shoproot; ?>cart.php?target=checkout&action=update_profile&email="+userEmail+"&same_address=1";
                    }, 5000);
                });
            } 
            else
            {
                var m = new LoadingModal();
                m.open( {
                    title: "Submit Order",
                    content: "Please agree to terms and conditions before submitting your order.",
                });
            }
        }

		
		function getProductValue() {
			return $("[name=product]").val();
		}
		
		function verifyProducts() {
			return (getProductValue() > 0);
		}
		
		function verifySubscription() {
			var value = $("[name=subscription]").val();
			return ((value == yearlySubProductId) || (value == universalSubProductId));
		}
		
		function verifyAccount() {
			var email = $("[name=email]").val();
			return (email.length > 0); // TODO: Verify it's an email
		}
		
		$(window).ready(function() {
			updateCurrentOrder();
			
			var tabs = new Tabs({
				div: "#tabs-div",
				prefix: 'verify',
				submit: sendToCart
			});

			$("[name=subscription]").change(function() {
				updateCurrentOrder();
			});
			
			$("[name=product]").change(function() {
				updateCurrentProduct();
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
							New Home License
						</div>
						<form id="new-license-form" method="POST" class="no-margin">
							<div id="tabs-div" class="tabs-body">
								<div class="tabs"></div>
                                
								<div class="tab-content" data-title="Subscription">
									<h2 style="text-align: center; margin-bottom: 12px;">Select Subscription Type</h2>
									<select name="subscription">
										<option value="" disabled selected>Subscription Type</option>
										<option value="<?php echo $shop->getProductIdBySku('SUB-HOME-YEARLY'); ?>">Yearly</option>
										<option value="<?php echo $shop->getProductIdBySku('SUB-HOME-UNIVERSAL'); ?>">Universal</option>
									</select>
									<div class="clear"></div>
									<div id="subscription-desc"></div>
									<div class="clear"></div>
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
                                
								<div class="tab-content" data-title="Account">
									<h2 style="text-align: center; margin-bottom: 12px;">Enter Contact Email</h2>
									<div class='text-input' data-label='Account email' data-name='email' data-placeholder></div>
									<span>Once we have processed your order, you will recieve an email with your account details
									and login credentials.<br/><br/></span>
									<div class="clear"></div>
                                    <div class="checkbox-input"
										 data-name="product"
										 data-value="0"
										 id="user-agreement"
										 data-label="I Agree to the Terms and Conditions"></div>
                                    <div style="font-size: 10px;"><a href="https://www.fluency-games.com/terms-of-use/"  target="_blank">   Read Terms and conditions</a></div>
									<div class="clear"></div>
									<button id="submit-order-button" class="next-tab-button">Next</button>
									<div class="clear"></div>
								</div>
                                <!--
								<div class="tab-content" data-title="Verification">
									<h2 style="text-align: center; margin-bottom: 12px;">Verify User</h2>
									<div class="g-recaptcha" data-sitekey="6LcCZCQUAAAAABoofuC0KPNYZBVMUKHh89SRevMV"></div>
									<div class="clear"></div>
									<button class="next-tab-button">Next</button>
									<div class="clear"></div>
								</div>
                                -->
							</div>
						</form>
					</div>
					
					<div class="card" id="current-order">
						<div class="head bold center">
							Current Order
						</div>					
						<div class="body">
							<span id="current-subscription"></span>
							<span id="current-products"></span>
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