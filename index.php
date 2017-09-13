<?php
	require_once(__DIR__ . "/php/classes/Element.class.php");
	require_once(__DIR__ . "/php/classes/User.class.php");
	
	if (User::loggedIn())
		Element::redirectUsersToHome();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		registerOnClick("#login-form button", function() {
			sendAjax({
				url: "php/ajax/login/login.php",
				data: {
					username: $("[name=username]").val(),
					password: $("[name=password]").val(),
				},
				success: function(result) {
					if (result['success']) {
						window.location = "index";
					} else {
						alert(result['error']);
					}
				},
				error: function(result) {
					alert("Index.php: Line 27, " + result['responseText']);
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
							Login
						</div>
						<div class="body">
							<form id="login-form" method="POST">
								<!--<div class="form-error">Error: Wrong password</div>-->
								
								<div class='text-input' data-label='Username or email' data-name='username' data-placeholder></div>
								<div class='text-input' data-label='Password' data-name='password' data-type='password' data-placeholder></div>
								<div class="clear"></div>
								
								<!-- <input type="checkbox" id="remember" name="remember" />
								<label for="remember"><span></span>Remember me</label> -->
								<div class='checkbox-input' data-name='remember' id='remember' data-label='Remember me'></div>
								<button>Login</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="register/new_home_license">Don't have a license? (Home)</a> |
								<a href="register/new_school_license">Don't have a license? (School)</a>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="login/forgot_password">Forgot Password</a>
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