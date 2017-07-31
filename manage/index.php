<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER_ADMIN);
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
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
							Notifications
						</div>
						<div class="body">
							<a style="display: inline-block;" class="icon-user" data-toggle="tooltip" data-gravity="w" title="Username"></a> <?php echo $user->getDisplayUsername(); ?><br />
							<a style="display: inline-block;" class="icon-link" data-toggle="tooltip" data-gravity="w" title="Domain"></a> <?php echo $license['DomainSuffix']; ?><br />
							<a style="display: inline-block;" class="icon-key" data-toggle="tooltip" data-gravity="w" title="License Key"></a> <?php echo $license['LicenseKey']; ?><br />
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="card">
								<div class="head center">
									RENAME
								</div>
								<div class="row">
									<div class="col-xs-12 col-md-6">
										<div class="big-button" data-href="#" data-icon="download">Manage Roster</div>
									</div>
									<div class="col-xs-12 col-md-6">
										<div class="big-button" data-href="#" data-icon="download">Manage Students</div>
									</div>
									<div class="col-xs-12 col-md-push-3 col-md-6">
										<div class="big-button" data-href="#" data-icon="camera">Snapshot</div>
									</div>
								</div>
							</div>
							<!--<div class="card">
								<div class="head center">
									???
								</div>
								<div class="body">
									<div class="row row-no-margin">
										
									</div>
								</div>
								<a href="teachers" class="footer center bold">Manage</a>
							</div>-->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>