<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(TEACHER | TEACHER_ADMIN, 'settings');
	
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	$aLicenses = $user->getLicenses();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$defaults = $user->getTeacherOptions();
	
	$ranges = array(
		1 => $user->getRanges('AddRanges'),
		2 => $user->getRanges('MultRanges'),
		4 => $user->getRanges('PercRanges'),
	);
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">

	function getProductName(id) {
		var name = 'product';
		switch (parseInt(id)) {
			case 1:
				name = 'AddRanges';
				break;
			case 2:
				name = 'MultRanges';
				break;
			case 4:
				name = 'PercRanges';
				break;
		}
		return name;
	}
	
	function sendSettings(data) {
		sendAjax({
			url: "php/ajax/settings/update-account.php",
			data: data,
			success: function(result) {
				console.log('ay');
			}
		});
	}
	
	registerOnClick('#teacher-defaults-form button', function() {
		data = {
			settings: true,
			DefaultPage: $('[name=default-page]').val(),
			DefaultProduct: $('[name=default-product]').val(),
		};
		
		sendSettings(data);
	});
	
	registerOnClick('#teacher-ranges-form button', function() {
		data = {
			settings: true,
		};
		
		$('.product-ranges').each(function(key, value) {
			var id = value.id.split('-')[2];
			var accmin = $('[name=acc-min-' + id + ']').val();
			var accmax = $('[name=acc-max-' + id + ']').val();
			var ppsmin = $('[name=pps-min-' + id + ']').val();
			var ppsmax = $('[name=pps-max-' + id + ']').val();
			
			var range = ((accmin) | (accmax << 8) | (ppsmin << 16) | (ppsmax << 24));
			data[getProductName(id)] = range;
		});
		
		sendSettings(data);
	});
	
	registerOnChange('#modify-product-input', function(e) {
		$('.product-ranges').hide();
		
		var productSelected = $(e).val();
		$('#ranges-product-' + productSelected).show();
	});
	
	</script>
	
</head>
<body>
	<?php Element::header(2); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarSettings(3); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Defaults
						</div>
						<div class="body">
							<form id="teacher-defaults-form" class="teacher-options-form" method="POST">
								<div class="select-input" data-label="Default Page" data-name="default-page" data-value="<?php echo $defaults['page']; ?>">
									<option value="index">Overview</option>
									<option value="rosters">Manage Rosters</option>
									<option value="students">Manage Students</option>
									<option value="snapshot">Student Snapshot</option>
								</div>
								
								<div class="select-input" data-label="Default Product" data-name="default-product" data-value="<?php echo $defaults['product']; ?>">
									<?php Element::productSelectInput($products); ?>
								</div>
								
								<button>Update</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="head center">
							Performance Ranges
						</div>
						<div class="body">
							<form id="teacher-ranges-form" class="teacher-options-form" method="POST">
								<div class="select-input" id="modify-product" data-value="1">
									<?php Element::productSelectInput($products); ?>
								</div>
								
								<!-- Need to put these in -->
								<?php
									for ($i = 0; $i < count($ranges); ++$i) {
										$productID = pow(2, $i);
								?>
								<div id="ranges-product-<?php echo $productID; ?>" <?php if ($i != 0) { echo 'style="display: none;"'; } ?> class="row product-ranges">
									<div class="col-xs-6">
										<div class="text-input required" data-label="Accuracy Min" data-name="acc-min-<?php echo $productID; ?>" data-value="<?php echo $ranges[$productID]['accmin']; ?>"></div>
									</div>
									<div class="col-xs-6">
										<div class="text-input required" data-label="Accuracy Max" data-name="acc-max-<?php echo $productID; ?>" data-value="<?php echo $ranges[$productID]['accmax']; ?>"></div>
									</div>
									<div class="col-xs-6">
										<div class="text-input required" data-label="Points Per Second Min" data-name="pps-min-<?php echo $productID; ?>" data-value="<?php echo $ranges[$productID]['ppsmin']; ?>"></div>
									</div>
									<div class="col-xs-6">
										<div class="text-input required" data-label="Points Per Second Max" data-name="pps-max-<?php echo $productID; ?>" data-value="<?php echo $ranges[$productID]['ppsmax']; ?>"></div>
									</div>
								</div>
								<?php
									}
								?>
								
								<button>Update</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>