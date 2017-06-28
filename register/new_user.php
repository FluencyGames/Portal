<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::redirectUsersToHome();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games Admin"); ?>
	<script type="text/javascript">
		registerOnClick("#new-user-form button", function() {
			// TODO: Do a password check (make sure it's not blank or w/e)
			sendAjax({
				url: "php/ajax/register/new-user.php",
				data: {
					licenseKey: $("[name=licenseKey]").val(),
					password: '',
					//password: $("[name=password]").val(),
				},
				success: function(result) {
					if (result['success']) {
						window.location = getAbsoluteUrl("settings");
					} else {
						alert(result['error']);
					}
				},
				error: function(result) {
					alert("AN ERROR?!");
					console.log(result);
				},
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
							New User
						</div>
						<div class="body">
							<form id="new-user-form" method="POST">
								<div class='text-input' data-label='Please enter your license key' data-name='licenseKey' data-placeholder></div>
								<!--<div class='text-input' data-label='Enter a password' data-name='password' data-type='password' data-placeholder></div>-->
								<div class="clear"></div>
								
								<button>Register</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="new_home_license">Don't have a license? (Home)</a> | 
								<a href="new_school_license">Don't have a license? (School)</a>
							</div>
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