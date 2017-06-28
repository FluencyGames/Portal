<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess();
	
	$user = User::getCurrentUser();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		registerOnClick("#account-settings-form button", function() {
			var password1, password2;
			password1 = $("[name=newpassword1]").val();
			password2 = $("[name=newpassword2]").val();
			
			if (password1 != password2) {
				alert("Passwords do not match");
			} else {
				sendAjax({
					url: "php/ajax/settings/update-password.php",
					data: {
						CurrentPassword: $("[name=currentpassword]").val(),
						NewPassword: password1,
					},
					success: function(result) {
						console.log(result);
						if (result['success']) {
							alert("Password successfully updated");
							refresh('password');
						} else {
							alert(result['error']);
						}
					}
				});
			}
		});
	</script>
</head>
<body>
	<?php Element::header(2); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarSettings(2); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Change Password
						</div>
						<div class="body">
							<form id="account-settings-form" method="POST">
								<div class='text-input' data-label='Current Password' data-type='password' id='currentpassword' data-name='currentpassword'></div>
								<div class='text-input' data-label='New Password' data-type='password' id='newpassword1' data-name='newpassword1'></div>
								<div class='text-input' data-label='Repeat Password' data-type='password' id='newpassword2' data-name='newpassword2'></div>
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