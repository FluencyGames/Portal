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
</head>
<body>
	<?php Element::header(4); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-push-1">
					<div class="card">
						<div class="head center">
							Graph Selection
						</div>
						<div class="body">
							<div class="row row-no-margin">
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="user">Overall</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-user"></span>
													<br />Overall
												</p>
											</div>
										</div>
									</div> -->
								</div>
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="user-plus">Correct Answers</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-user-plus"></span>
													<br />Correct Answers
												</p>
											</div>
										</div>
									</div> -->
								</div>
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="user-times">Incorrect Answers</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-user-times"></span>
													<br />Incorrect Answers
												</p>
											</div>
										</div>
									</div> -->
								</div>
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="col-xs-12 col-sm-10 col-sm-push-1">
					<div class="card">
						<div class="head center">
							Second Selection
						</div>
						<div class="body">
							<div class="row row-no-margin">
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="shuffle">Shuffle</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-shuffle"></span>
													<br />Shuffle
												</p>
											</div>
										</div>
									</div> -->
								</div>
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="laptop">Laptop</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-laptop"></span>
													<br />Laptop
												</p>
											</div>
										</div>
									</div> -->
								</div>
								<div class="col-xs-6 col-md-4">
									<div class="big-button" data-icon="beaker">Science</div>
									<!-- <div class="big-button-holder">
										<div class="big-button">
											<div class="content">
												<p>
													<span class="icon-beaker"></span>
													<br />Science
												</p>
											</div>
										</div>
									</div> -->
								</div>
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