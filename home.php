<?php
	require_once(__DIR__ . "/php/classes/Config.class.php");
	require_once(__DIR__ . "/php/classes/Element.class.php");
	require_once(__DIR__ . "/php/classes/User.class.php");
	
	Element::restrictAccess();
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	
	function showNotifications() {
		$notifications = User::getCurrentUser()->getNotifications();
		
		if ($notifications == null) {
		?>
								No unread notifications
		<?php
		} else {
			foreach ($notifications as $key => $value) {
		?>
								<a href="#"><span class="<?php echo $key; ?>"></span> <?php echo $value; ?></a>
		<?php 
			}
		}
	}
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
				<div class="col-xs-12 col-sm-4 col-lg-3">
					<div class="card sidebar">
						<div class="head">
							User Info
						</div>
						<div class="body">
							<div class="notifications">
								<a class="icon-user" data-toggle="tooltip" data-gravity="w" title="Username"></a> <?php echo $user->getDisplayUsername(); ?><br />
								<a class="icon-link" data-toggle="tooltip" data-gravity="w" title="Domain"></a> <?php echo $license['DomainSuffix']; ?><br />
								<a class="icon-lock" data-toggle="tooltip" data-gravity="w" title="License Key"></a> <?php echo $license['LicenseKey']; ?><br />
							</div>
						</div>
					</div>
					<div class="card sidebar">
						<div class="head">
							Notifications
						</div>
						<div class="body">
							<div class="notifications">
								<?php
									showNotifications();
								?>
							</div>
						</div>
					</div>
					<!--
					<div class="card sidebar">
						<div class="head">
							Quick Navigation
						</div>
						<ul>
							<a href="manage/teachers">
								<li><span class="icon-paw"></span> Add/Manage Teachers</li>
							</a>
							<a href="#">
								<li><span class="icon-chart-bar"></span> Page 2</li>
							</a>
							<a href="#">
								<li><span class="icon-calendar"></span> Page 3</li>
							</a>
							<a href="#">
								<li><span class="icon-basket"></span> Page 4</li>
							</a>
						</ul>
					</div>
					-->
				</div>
				<div class="col-xs-12 col-sm-8 col-lg-9">
					<div class="card">
						<div class="head center">
							Overview
						</div>
						<div class="body">
							Body text
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>