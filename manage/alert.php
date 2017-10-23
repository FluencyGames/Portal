<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal", false); ?>
</head>
<body>
	<?php Element::header(1); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarManage(1); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Alert!
						</div>
						<div class="body">
							<p style="font-size: 20px; padding: 20px;">
							<?php
								$userType = $user->getColumn('UserType');
								$canUpgrade = (($userType & (EDUCATIONAL_ADMIN | TEACHER_ADMIN | PARENT_GUARDIAN)) > 0);
								if ($canUpgrade) {
							?>
								Your school's license has expired.</p><p style="font-size: 20px; padding: 20px; padding-top: 0px;">Please <a href="subscription">renew your license</a> to continue using the web portal and its features.
							<?php
								} else {
							?>
								Please let your administrator know that your license has expired, and needs to be renewed.
							<?php
								}
							?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>