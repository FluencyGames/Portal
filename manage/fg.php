<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Shop.class.php");

	Element::restrictAccess(FLUENCY_GAMES_ADMIN);
	
	$user = User::getCurrentUser();
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
				<?php Element::sidebarAdmin(0); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Overview
						</div>
						<div class="body">
							<a style="display: inline-block;" data-toggle="tooltip" data-gravity="w" title="Username"></a>Nothing here yet...<br />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>