<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(TEACHER | TEACHER_ADMIN, 'settings');
	
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	$aLicenses = $user->getLicenses();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$defaultPage = 'index';
	$defaultProduct = 1;
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">

	registerOnClick("#parent-setup-form button", function() {
		var group = $("#parent-setup-form [name=group]").val().toLowerCase().trim().replace(/\s/g,'');
	    valid = isUniqueGroupName( group );
				
		if(valid) {
			sendAjax({
				url: "php/ajax/settings/update-account.php",
				data: {
				    License: '<?php echo $user->getColumn('license'); ?>',
					Username: $("#account-setup-form [name=username]").val(),
					Groups: group
				},
				success: function(result) {
					console.log(result);
					if (result['success']) {
						alert("Settings Updated.");
						refresh('index');
					} else {
						alert(result['error']);
					}
				}
			});
		}
	});
	
	registerOnClick("#school-setup-form button", function() {
	    valid = true;
	    usrLevel = <?php echo $user->getColumn('UserType'); ?>;
		
		if(usrLevel == <?php echo EDUCATIONAL_ADMIN ?> || usrLevel == <?php echo TEACHER_ADMIN ?>) {
			var domain_suffix = $("#school-setup-form [name=domainsuffix]").val().trim().replace(/\s/g,'');
		    valid = isUniqueDomainSuffix(domain_suffix);
			if(valid) {
			    $.when( $.ajax( {
		 					url: "../php/ajax/settings/update-license.php",
							type: "POST",
		 					data: {
		 						DomainSuffix: domain_suffix
		 					}
		 				})).then( function() {  // success
							alert("Settings Updated.");
							refresh('index');
						}, function() {
						    alert("Settings Failed to Update");
							valid = false;
						});
			}
		}
	});
	
	registerOnClick("#group-setup-form button", function() {
	    valid = true;
	    usrLevel = <?php echo $user->getColumn('UserType'); ?>;
	
		if(usrLevel == <?php echo TEACHER_ADMIN ?> || usrLevel == <?php echo TEACHER ?>) {
			var group = $("#group-setup-form [name=group]").val().trim().replace(/\s/g,'');
			valid = isUniqueGroupName(group);
			if(valid) {
			    $.when( $.ajax( {
							url: "../php/ajax/settings/update-account.php",
							type: "POST",
							data: {
							    License: '<?php echo $user->getColumn('license'); ?>',
								Username: $("[name=username]").val(),
								Groups: group
							},
						})).then( function() {
							alert("Settings Updated.");
							refresh('index');
						}, function() {
						    valid = false;
						    alert("Settings Failed to Update");
						});
			}
		}
	});
	
	registerOnClick("#account-settings-form button", function() {
		sendAjax({
			url: "php/ajax/settings/update-user.php",
			data: {
				Username: $("[name=username]").val(),
				Fname: $("[name=firstname]").val(),
				LName: $("[name=lastname]").val(),
				Email: $("[name=email]").val(),
				Phone: $("[name=phone]").val(),
			},
			success: function(result) {
				console.log(result);
				if (result['success']) {
					alert("Settings Updated.");
				} else {
					alert(result['error']);
				}
			}
		});
	});	
	
	$(window).ready(function() {
	    $(".required.text-input-wrapper").each( function() {
			 if($(this).attr('data-value') != '') {
			 	$("#" + $(this).attr('id') + "-input").attr('disabled','disabled');
			}
		});
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
							Teacher Options
						</div>
						<div class="body">
							<form id="account-settings-form" method="POST">
								<div class="select-input" data-label="Default Page" data-name="default-page" data-value="<?php echo $defaultPage; ?>">
									<option value="index">Overview</option>
									<option value="rosters">Manage Rosters</option>
									<option value="students">Manage Students</option>
									<option value="snapshot">Student Snapshot</option>
								</div>
								
								<div class="select-input" data-label="Default Product" data-name="default-product" data-value="<?php echo $defaultProduct; ?>">
									<?php Element::productSelectInput($products); ?>
								</div>
								
								<hr />
								
								<div style="text-align: center; margin: 18px 0px 6px;">Adjust Performance Ranges</div>
								
								<div class="select-input" data-name="default-product" data-value="1">
									<?php Element::productSelectInput($products); ?>
								</div>
								
								<!-- Need to put these in -->
								
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