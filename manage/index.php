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
							Overview
						</div>
						<div class="body">
							<a style="display: inline-block;" class="icon-user" data-toggle="tooltip" data-gravity="w" title="Username"></a> <?php echo $user->getDisplayUsername(); ?><br />
							<a style="display: inline-block;" class="icon-link" data-toggle="tooltip" data-gravity="w" title="Domain"></a> <?php echo $license['DomainSuffix']; ?><br />
							<a style="display: inline-block;" class="icon-key" data-toggle="tooltip" data-gravity="w" title="License Key"></a> <?php echo $license['LicenseKey']; ?><br />
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="card">
								<div class="head center">
									Teachers
								</div>
								<div class="body">
									<?php echo $user->getNumTeachers(); ?> Teachers
								</div>
								<a href="teachers" class="footer center bold">Manage</a>
							</div>
						</div>
						
						<div class="col-xs-12 col-md-6">
							<div class="card">
								<div class="head center">
									Students
								</div>
								<div class="body">
									<?php echo $user->getNumStudents(); ?> Students
								</div>
								<a href="students" class="footer center bold">Manage</a>
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