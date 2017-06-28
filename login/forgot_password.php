<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::redirectUsersToHome();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		registerOnClick("#reset-password-form button", function() {
			var m = new LoadingModal();
			m.open( {title: "Sending Email",
					 refresh: 5000,
					 url: "reset_password"
					 });

			sendAjax({
				url: "php/ajax/login/forgot-password.php",
				data: {
					Email: $("[name=email]").val(),
				},
				success: function(result) {
					if (result['success']) {
						window.location = "reset_password";
					} else {
						alert(result['error']);
					}
					console.log(result);
				},
				error: function(result) {
				    if(result['status']==200) {
						window.location = "reset_password";
						console.log("forgot_password.php: Email successfully sent.");
					} else {
						alert('forgot_password.php: ' + result['error']);
						console.log(result);
					}
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
							Forgot Password
						</div>
						<div class="body">
							<form id="reset-password-form" method="POST">
								<!--<div class="form-error">Error: Wrong password</div>-->
								
								<div class='text-input' data-label='Email' data-name='email' data-placeholder></div>
								<div class="clear"></div>
								
								<input type="checkbox" id="remember" name="remember" />
								<button>Send Reset</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="../login">Login</a>
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