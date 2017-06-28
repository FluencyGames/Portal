<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Shop.class.php");
	require_once(__DIR__ . "/../php/fg/lic.php");
	
	Element::restrictAccess(FLUENCY_GAMES_ADMIN, 'home');

	$documentroot = Config::get('documentroot');
	$shoproot = Config::get('shoproot');
	$shop = Shop::getInstance();
	
	function processLicenses($data) {}   // placeholder
	
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
	<?php Element::head("Fluency Games User Portal"); ?>
	<script src="<?php echo $documentroot; ?>js/tabs.js"></script>
	<script src="<?php echo $documentroot; ?>js/shop.js"></script>
	<script src="<?php echo $documentroot; ?>js/manage/licenses.js"></script>
	<script type="text/javascript">
		var products = [<?php outputProductArray($shop); ?>];
		var keys = [];
		var licenseNo = '';
		var startDate = new Date();
		var endDate;
		var purData = [];

function emailLicense() {
	return 	$.ajax({
			url: "../php/ajax/login/new-license.php",
			data: {
				license: licenseNo,
				userEmail: $("[name=email]").val(),
				userName: $("[name=pub-username").val(),
				password: $("[name=pub-password").val(),
				users:  $("[name=purchaser]").val()=='1'?'6':$("[name=seats]").val(),
				startDate: dateFormat(startDate),
				endDate: dateFormat(endDate),
				subType: ($("[name=subscription]").val()=='1')?'Yearly':'Universal',
				seats:  $("[name=purchaser]").val()=='<?php echo PARENT_GUARDIAN ?>'?'6':$("[name=seats]").val(),
			},
			success: function(result) {
				console.log(result);
			},
			error: function(result) {
				alert("licenses.js (Line 295):" + result['error']);
			},
		});

}

function createLicense() {
	return $.ajax({
				url: "../php/fg/lic.php",
				async: false,
				data: {
				    type: "GET",
				    action: "gen",
					userType: $("[name=purchaser]").val(), // 1 = school
					products: String(getProductValue()),
					length: $("[name=subscription]").val(),
					username: $("[name=username]").val(),
				},
				success: function(result) {
					console.log(result);
				},
				error: function(result) {
					alert("licenses.js (Line 316):" + result['error']);
				},
			});
}

function publishContacts() {

	return $.ajax({
				url: "../php/fg/lic.php",
				async: false,
				data: {
				    action: "contact",
					lic: licenseNo,
					username: $("[name=pub-username]").val(),
					pword: $("[name=pub-password").val(),
					email: $("[name=pub-email]").val(),
					userlevel: $("[name=purchaser]").val(), // 1 = school
					groups: ''
				},
				success: function(result) {
					console.log(result);
				},
				error: function(result) {
					alert("Licenses.js [Line 337]: "+result['error']);
				}
			});
}

function publishLicense() {

	return $.ajax({
				url: "../php/fg/lic.php",
				async: false,
				data: {
				    action: "create",
					lic: licenseNo,
					userName: $("[name=pub-username]").val(),
					email: $("[name=pub-email]").val(),
					userType: $("[name=purchaser]").val(), // 1 = school
					sys: $("[name=purchaser]").val()=='<?php echo PARENT_GUARDIAN ?>'?'HOME':'SCHOOL',
					suffix: '',
					products: String(getProductValue()),
					users: $("[name=pub-seats]").val(),
					start_date: dateFormat(startDate),
					end_date: dateFormat(endDate),
					purchase: 'FG_Portal',
					pur_data: purchaseData(),
				},
				success: function(result) {
					console.log(result);
				},
				error: function(result) {
					alert("Licenses.js [Line 268]: "+result['error']);
				}
			});

}
		
		function sendForm() {

		    $.when( createLicense() ).then( function( ret ) {
				licenseNo = $.trim(ret);
				endDate = calculateSubscriptionEnd(startDate, $("[name=subscription]").val());
				updatePublishLicense();
			});
		}
		

		function purchaseData() {
		    return JSON.stringify( {'OrderNo': '',
									'data':     {
					                    'Date': 	startDate,
					                    'User':     $("[name=pub-username]").val(),
										'Email':    $("[name=pub-email]").val(),
										'Products': String(getProductValue()),
										'Seats': 	$("[name=pub-seats]").val(),
										'SeatSKU': 	getSKUForNumberOfSeats(products, parseInt(purData['Seats'])),
										'Length': 	$("[name=subscription]").val(),
										'userType': $("[name=purchaser]").val(),
									},
							});
		}
		
		function updatePublishLicense() {
		    $("[name=pub-license]").val(licenseNo);
		    $("[name=pub-username]").val($("[name=username]").val());
		    $("[name=pub-email]").val($("[name=email]").val());
		    $("[name=pub-start-date]").val( dateFormat(startDate));
		    $("[name=pub-end-date]").val( dateFormat(endDate));
		    $("[name=pub-seats]").val($("[name=purchaser]").val()=='<?php echo PARENT_GUARDIAN ?>'?'6':$("[name=seats]").val());
		    $("[name=pub-code]").val(getProductValue());
			$("[name=pub-password]").val(createTempPassword());
			$("#publish-license-form").show();
		}
		
		function getProductValue() {
			var productValue = 0;
			
			$("[data-name=product] input[type=checkbox]").each(function() {
				if ($(this).prop('checked'))
					productValue += parseInt($(this).parent().attr('data-value'));
			});
			
			if (productValue > 255)
				productValue = 255;
			
			return productValue;
		}
		
		function verifyProducts() {
			return (getProductValue() > 0);
		}
		
		function verifySubscription() {
			var value = $("[name=subscription]").val();
			return (value == "0") || (value == "1") || (value == "9");
		}

		function verifyPurchase() {
			var value = $("[name=purchaser]").val();
			return (value != "");
		}

		function verifyAccount() {
			var username = $("[name=username]").val();
			var email = $("[name=email]").val();
			return (username.length > 0) && (email.length > 0);
		}
		
		$(window).ready(function() {
			$("#publish-license-form").hide();
			
			var tabs = new Tabs({
				div: "#tabs-div",
				prefix: 'verify',
				submit: sendForm
			});

			//
			// subscription type (Home/School) drop down list
			//
			$("[name=purchaser]").change(function() {
					var type = $(this).val();
					if(type == "<?php echo PARENT_GUARDIAN ?>")
						$("[data-name=seats]").hide();
					else
						$("[data-name=seats]").show();
				});

			$("[name=subscription]").change(function() {
					var type = $("[name=purchaser]").val();
					var length = $(this).val();

					//
					// school/district or universal (home) subscriptions get all products
					//
					if(type == "3" || type == "2") {
						$("#all-products-input").prop('checked', true);
						$("[data-name=product] input[type=checkbox]:not(#all-products-input)").each(function() {
							$(this).prop('checked', true);
							$(this).parent().hide();
						});
					} else {
						$("#all-products-input").prop('checked', false);
						$("[data-name=product] input[type=checkbox]:not(#all-products-input)").each(function() {
							$(this).prop('checked', false);
							$(this).parent().show();
						});
					}
				});

			$("#publish-license-button").click(function() {
					valid = isUniqueUserName($("[name=pub-username]").val());
					
					if(valid) {
						var m = new LoadingModal();
						m.open( {title: "Please Wait",
								 content: "Publishing License and E-mailing User.",
								 });

						$.when( publishLicense(), publishContacts(), emailLicense() ).then( function() {
						    m.close();
							alert("License Published, Email Sent. Transferring to Update Order Status.");
							//refresh('<?php echo $shoproot ?>admin.php?target=order_list');
						},
						function() {
							m.close();
							alert("Publish failed.");
						});
					}
			});
					
			$("#all-products-input").click(function() {
				var checked = $(this).prop('checked');
				$("[data-name=product] input[type=checkbox]").each(function() {
					$(this).prop('checked', checked);
				});
			});
			
			$("[data-name=product] input[type=checkbox]:not(#all-products-input)").click(function() {
				var allSelected = true;
				$("[data-name=product] input[type=checkbox]:not(#all-products-input)").each(function() {
					if (!$(this).prop('checked')) {
						allSelected = false;
					}
				});
				
				$("#all-products-input").prop('checked', allSelected);
			});
		});
	</script>
</head>
<body>
	<?php Element::header(); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarAdmin(2); ?>
				
				<div id="middle column" class="col-xs-12 col-sm-8 col-lg-6">
					<div id="create-license-form">
						<div class="card">
							<div class="head bold center">
								Create License
							</div>

							<div id="new-license-form" method="POST" class="no-margin">
								<div id="tabs-div" class="tabs-body">
									<div class="tabs"></div>

									<div class="tab-content" data-title="Purchase">
										<h2 style="text-align: center; margin-bottom: 12px;">Select License Type</h2>
										<div class="clear"></div>
										<select name="purchaser">
											<option value="" disabled selected>I am creating...</option>
											<option value="<?php echo PARENT_GUARDIAN ?>">I am creating a HOME license</option>
											<option value="<?php echo TEACHER_ADMIN ?>">I am creating a CLASSROOM license</option>
											<option value="<?php echo EDUCATIONAL_ADMIN ?>">I am creating a SCHOOL or DISTRICT license</option>
										</select>
									</div>

									<div class="tab-content" data-title="Subscription">
										<select id="sub-products" name="subscription">
											<option value="" disabled selected>Subscription Type</option>
											<option value="0">Monthly (HOME)</option>
											<option value="1">Yearly (HOME)</option>
											<option value="9">Universal (HOME)</option>
											<option value="1">Yearly (SCHOOL)</option>
										</select>

										<button class="next-tab-button">Next</button>
										<div class="clear"></div>
									</div>

									<div class="tab-content" data-title="Products">
										<h2 style="text-align: center; margin-bottom: 12px;">Select Products</h2>
										<div class="clear"></div>

										<div class="checkbox-input"
											 data-name="product"
											 data-value="255"
											 id="all-products"
											 data-label="All Products"></div>

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


										<button class="next-tab-button">Next</button>
										<div class="clear"></div>
									</div>

									<div class="tab-content" data-title="Account">
										<div class='text-input' data-label='Username' data-name='username' data-placeholder></div>
										<div class='text-input' data-label='Account email' data-name='email' data-placeholder></div>
										<div class='text-input' data-label='Number of seats (min. 10)' data-name='seats' data-placeholder></div>
										<div class="clear"></div>
										<button class="next-tab-button" id="btn-purchaser">Next</button>
										<div class="clear"></div>
									</div>
								</div>
							</div> <!-- </form> -->
						</div>
					</div>

					<div id="publish-license-form">
						<div class="card">
							<div class="head bold center">
								Publish License
							</div>
							<div class="body row row-no-margin">
							    <div class="text-input col-md-6" data-label="License #" data-name="pub-license" data-value=""></div>
							    <div class="text-input col-md-6" data-label="Email" data-name="pub-email" data-value=""></div>
								<div class="clear"></div>
							    <div class="text-input col-md-6" data-label="Username" data-name="pub-username" data-value=""></div>
							    <div class="text-input col-md-6" data-label="Password" data-name="pub-password" data-value=""></div>
							    <div class="text-input col-md-3" data-label="Starting" data-name="pub-start-date" data-value=""></div>
							    <div class="text-input col-md-3" data-label="Ending" data-name="pub-end-date" data-value=""></div>
							    <div class="text-input col-md-3" data-label="Seats" data-name="pub-seats" data-value=""></div>
							    <div class="text-input col-md-3" data-label="Products" data-name="pub-code" data-value=""></div>
								<div class="clear"></div>
								<button id="cancel-license-button" class="compact" style="float: right; margin-bottom: 12px;">Cancel</button>
								<div class="clear"></div>
								<button id="publish-license-button" class="compact" style="float: right">Publish</button>
							</div>
						</div>
					</div>

				</div> <!-- middle column -->
				
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>