<?php
	require_once(__DIR__ . "/../php/classes/Database.class.php");
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	$code = null;
	if (isset($_GET['code']))
		$code = $_GET['code'];
	
	if ($code) {
		$query = 'SELECT * FROM passwordreset WHERE Code = ?';
		$validCode = Database::getInstance()->query($query, array($_GET['code']))->count() > 0;
	}
	
	Element::redirectUsersToHome();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		<?php if (($code) && ($validCode)) { ?>
		registerOnClick("#reset-password-form button", function() {
			var password1, password2;
			password1 = $("[name=newpassword1]").val();
			password2 = $("[name=newpassword2]").val();
			
			if (password1 != password2) {
				alert("Passwords do not match");
			} else {
				sendAjax({
					url: "php/ajax/login/reset-password.php",
					data: {
						Email: $("[name=email]").val(),
						NewPassword: password1,
						Code: '<?php echo $_GET['code']; ?>'
					},
					success: function(result) {
						if (result['success']) {
						    alert('Password successfully changed.');
							window.location = "../index";
						} else {
							alert(result['error']);
						}
					},
					error: function(result) {
						alert(result['error']);
						console.log(result);
					},
				});
			}
		});
		<?php } ?>
	</script>
</head>
<body>
	<?php Element::header(); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-8 col-sm-push-2 col-md-6 col-md-push-3">
					<div class="card">
						<?php if ($code == null) { ?>
						<div class="head bold center">
							Reset Password
						</div>
						<div class="body">
							<p>Please enter the code you received in the email below</p>
							<form method="GET">
								<!--<div class="form-error">Error: Incorrect password</div>-->
								
								<div class='text-input' data-label='Code' data-name='code' data-placeholder></div>
								<div class="clear"></div>
								
								<button>Continue</button>
								<div class="clear"></div>
							</form>
						</div>
						<?php } else if (!$validCode) { ?>
						<div class="head bold center">
							Reset Password
						</div>
						<div class="body">
							<p>That password reset code is not longer valid.<br>Please return to the home page to request a new code.</p>
						</div>
						<?php } else { ?>
						<div class="head bold center">
							Reset Password
						</div>
						<div class="body">
							<form id="reset-password-form" method="POST">
								<!--<div class="form-error">Error: Wrong password</div>-->
								
								<div class='text-input' data-label='Email' data-name='email' data-placeholder></div>
								<div class='text-input' data-label='New password' data-name='newpassword1' data-type='password' data-placeholder></div>
								<div class='text-input' data-label='Repeat password' data-name='newpassword2' data-type='password' data-placeholder></div>
								<div class="clear"></div>
								
								<button>Reset</button>
								<div class="clear"></div>
							</form>
						</div>
						<?php } ?>
					</div>
					
					<div class="card">
						<div class="body">
							<div style="text-align: center; font-weight: 600;">
								<a href="../index">Home</a>
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