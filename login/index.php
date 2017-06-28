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
		registerOnClick("#login-form button", function() {
			sendAjax({
				url: "php/ajax/login/login.php",
				data: {
					username: $("[name=username]").val(),
					password: $("[name=password]").val(),
				},
				success: function(result) {
					if (result['success']) {
						window.location = "../home"; // TODO: Redo
					} else {
						console.log(result);
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
							Login
						</div>
						<div class="body">
							<form id="login-form" method="POST">
								<!--<div class="form-error">Error: Wrong password</div>-->
								
								<input type="text" name="username" placeholder="Username or email" />
								<input type="password" name="password" placeholder="Password" />
								<div class="clear"></div>
								
								<input type="checkbox" id="remember" name="remember" />
								<label for="remember"><span></span>Remember me</label>
								<button>Login</button>
								<div class="clear"></div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="body">
							<div style="text-align: right; font-weight: 200;">
								<a href="forgot_password">Forgot password?</a>
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