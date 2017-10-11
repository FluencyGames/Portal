<?php
	require_once(__DIR__ . '/../php/classes/Database.class.php');
	require_once(__DIR__ . '/../php/classes/Element.class.php');
	require_once(__DIR__ . '/../php/classes/User.class.php');
	require_once(__DIR__ . "/../php/classes/Shop.class.php");

	// TODO: Find a much better way to do this!
	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER_ADMIN | PARENT_GUARDIAN, 'manage');
	
	$user = User::getCurrentUser();
	$userType = User::getCurrentUser()->getColumn('UserType');
	$userEmail = $user->decode(User::getCurrentUser()->getColumn('Email'));

	$shoproot = Config::get('shoproot');
	$shop = Shop::getInstance();

	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$expired = time() > strtotime($license['EndDate']);
	
	$startDate = strtotime($license['StartDate']);
	$startDate = date('Y-m-d', $startDate);
	
	$endDate = strtotime($license['EndDate']);
	$endDate = date('Y-m-d', $endDate);
	
	$query = 'SELECT count(*) FROM students WHERE LicenseKey = ?';
	$result = Database::getInstance()->query($query, array($license['LicenseKey']))->firstResult();
	$numStudents = $result['count(*)'];

	function outputProductArray($theShop) {
	    $str = "\n";
		$items = $theShop->getCatalog();
		foreach($items as $key => $item) {
		    $str .= "\t\t\t['{$key}', '{$item->{'Product-Id'} }', '{$item->{'Name'} }', {$item->{'data'} } ], \n";
		}
		echo  $str . "\t\t";
	}

?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal", false); ?>
	<script src="<?php echo Config::get('documentroot'); ?>js/manage/subscription.js"></script>
	<script src="<?php echo $shoproot; ?>js/shop.js"></script>

	<style type="text/css">
		.compact {
			width: 92px;
		}
		
		@media (min-width: 768px) {
			.years-modal {
				width: 500px;
			}
			
			.seats-modal {
				width: 500px;
			}
		}
	</style>
	
	<script type="text/javascript">
		var currentProducts = <?php echo $license['Products']; ?>;
		var userType = <?php echo $userType ?>;
		var products = [<?php outputProductArray($shop); ?>];

		function sendToCart() {
		    var email	= "<?php echo $userEmail; ?>";
			var m = new LoadingModal();
			m.open( {title: "Please Wait",
					 content: "Adding Items to Cart.",
					 });

			var orders = buildOrders( { productList: 		products,
										addSubscription:	$("#product-100 input[type=checkbox]").parent().attr('data-value'),
			                            addSeats:			$("#product-200	input[type=checkbox]").parent().attr('data-value'),
										productCode:		getProductValue(),
										});

			$.when.apply($, orders ).then( function() {
			    m.insertContent( {content: "You will be redirected to checkout.<p>Do not close the browser."} );
				var timer = setInterval( function() {
					m.close();
				    clearInterval(timer);
					window.location = "<?php echo $shoproot; ?>cart.php?target=checkout&action=update_profile&email="+userEmail+"&same_address=1";
				}, 5000);
			});
		}

		function updateProducts() {

			//
			// Remove the products that the user
			// has already licensed
			//
			$("[data-name=product] input[type=checkbox]").each(function() {
				if (currentProducts & parseInt($(this).parent().attr('data-value')) ) {
					$(this).parent().hide();
				}
			});
			
			//
			// if the user has all products licensed,
			// then do not show the Add Products card
			//
			if(currentProducts == 255)
			    $("#products-card").hide();
			
		
		}
		
		$(window).ready(function() {
			updateSubscriptionOrder();
			updateProducts();
			
			if(userType == <?php echo PARENT_GUARDIAN ?> )   // home licenses cannot increase seats
			    $("#increase-seats-button").hide();
			
			$("#remove-item-button").click(function() {
				$("[data-name=product-order] input[type=checkbox]").each(function() {
					if( $(this).prop('checked')) {
					    $(this).parent().attr('data-value', 0);
						$(this).prop('checked', false);
					    $(this).parent().hide();
					}

				});
				
				updateSubscriptionOrder();
				
			});
			
			$("#add-products-button").click(function() {
				var products = 0;
				
				$("[data-name=product] input[type=checkbox]").each(function() {
					if( $(this).prop('checked')) {
						products += parseInt($(this).parent().attr('data-value'));
					}
				});
				
				$("#product-300 input[type=checkbox]").parent().attr('data-value', products);
				
				updateSubscriptionOrder(); 

			});
			
			$("#submit-order-button").click(function() {
					sendToCart();
				});


		});
	</script>
	
</head>
<body>
	<?php Element::header(3); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarManage(-1); ?>
				
				<div id="middle-column" class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							License: <span style="font-weight: 400;"><?php echo $license['LicenseKey']; ?></span>
						</div>
						<div class="body small">
							<div class="row">
								<div class="col-xs-6 col-sm-3 center">
									<div class="info-stack" data-title="Start Date" data-date="<?php echo $startDate ?>"></div>
								</div>
								<!-- <div class="col-xs-3">
									<div class="info-stack">
										<div class="title">Start Date</div>
										<div class="data-wrapper">
											<div class="data">
												<div class="top"><?php echo $startDate['month']; ?></div>
												<div class="middle"><?php echo $startDate['date']; ?></div>
												<div class="bottom"><?php echo $startDate['year']; ?></div>
											</div>
										</div>
									</div>
								</div> -->
								
								<div class="col-xs-6 col-sm-3 center<?php echo $expired ? ' error' : ''?>">
									<div class="info-stack" data-title="End Date" data-date="<?php echo $endDate; ?>"></div>
									<button id="extend-years-button" class="compact xs-margin-bottom"><?php echo $expired ? 'Renew' : 'Extend'; ?></button>
								</div>
								
								<div class="col-xs-6 col-sm-3 center">
									<div class="info-stack red" data-title="Available" data-middle="<?php echo $license['NumUsers']; ?>" data-bottom="Seats"></div>
									<button id="increase-seats-button" class="compact xs-margin-bottom">Increase</button>
								</div>
								
								<div class="col-xs-6 col-sm-3 center">
									<div class="info-stack" data-title="Active" data-middle="<?php echo $numStudents; ?>" data-bottom="Students"></div>
									<!-- <a href="students"><button class="compact xs-margin-bottom">Add</button></a> -->
								</div>
							</div>
						</div>
					</div>
					
					<div class="card" id="products-card">
						<div class="head center">
							Add Products
						</div>
						<div class="body">
							<div id="products-wrapper" class="row row-no-margin">

									<div class="checkbox-input"
										 data-name="product"
										 data-value="<?php echo $shop->getProductDataBySku('PROD-ADD-BLOCKS')?>"
										 id="product-1"
										 data-label="Addition Blocks"></div>

									<div class="checkbox-input"
										 data-name="product"
										 data-value="<?php echo $shop->getProductDataBySku('PROD-MX-BLOCKS')?>"
										 id="product-2"
										 data-label="Multiplication Blocks"></div>

									<div class="checkbox-input"
										 data-name="product"
										 data-value="<?php echo $shop->getProductDataBySku('PROD-PERCENT-BINGO')?>"
										 id="product-3"
										 data-label="Percent Bingo"></div>

									<div class="checkbox-input"
										 data-name="product"
										 data-value="<?php echo $shop->getProductDataBySku('PROD-SUB-BLOCKS')?>"
										 id="product-4"
										 data-label="Subtraction Blocks"></div>

									<div class="checkbox-input"
										 data-name="product"
										 data-value="<?php echo $shop->getProductDataBySku('PROD-INT-BLOCKS')?>"
										 id="product-5"
										 data-label="Integer Blocks"></div>
										 
									<div class="clear"></div>
									
								<button id="add-products-button" class="compact" style="float: right">Add</button>

							</div>
						</div>
					</div>
					
					<div id="subscription-order">
						<div class="card">
							<div class="head center">
								Order
							</div>
							<div class="body">
							    <div id="order-items-wrapper" class="row row-no-margin">
									<div class="checkbox-input"
										 data-name="product-order"
										 data-value="0"
										 id="product-100"
										 data-label="Extend License"></div>

									<div class="checkbox-input"
										 data-name="product-order"
										 data-value="0"
										 id="product-200"
										 data-label="Add Seats"></div>

									<div class="checkbox-input"
										 data-name="product-order"
										 data-value="0"
										 id="product-300"
										 data-label="Add Products"></div>

									<div class="clear"></div>
									<button id="remove-item-button" class="compact" style="float: right; margin-bottom: 12px;">Remove</button>
									<div class="clear"></div>
									<button id="submit-order-button" class="compact" style="float: right">Submit</button>

								</div>
							</div>
						</div>
					</div>

				</div>
				
				<div class="col-xs-12 col-sm-12 col-lg-3" sq-for-1200='overview,switch-license'>
					<div class="card" sq-id="overview" sq-order="1">
						<div class="head center">
							Overview
						</div>
						<div class="body">
							<a style="display: inline-block;" class="icon-user" data-toggle="tooltip" data-gravity="w" title="Username"></a> <?php echo $user->getDisplayUsername(); ?><br />
							<a style="display: inline-block;" class="icon-link" data-toggle="tooltip" data-gravity="w" title="Domain"></a> <?php echo $license['DomainSuffix']; ?><br />
							<a style="display: inline-block;" class="icon-key" data-toggle="tooltip" data-gravity="w" title="License Key"></a> <?php echo $license['LicenseKey']; ?><br />
						</div>
					</div>
					<?php Element::switchLicenseForm(2); ?>
				</div>
				
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>