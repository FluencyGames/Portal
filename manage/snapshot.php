<?php
	require_once(__DIR__ . '/../php/classes/Config.class.php');
	require_once(__DIR__ . '/../php/classes/Element.class.php');
	require_once(__DIR__ . '/../php/classes/User.class.php');
	
	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER | TEACHER_ADMIN, 'manage');
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$documentroot = Config::get('documentroot');
	
	$teacherDomain = $license['DomainSuffix']; 
	$teacherID = $user->getColumn('Id');
	$groupName = $user->getColumn('Groups');
	
	$students = $user->getStudents('ORDER BY Lname ASC, Fname ASC');
	
	$defaults = $user->getTeacherOptions();
	
	$ranges[1] = $user->getRanges('AddRanges');
	$ranges[2] = $user->getRanges('MultRanges');
	$ranges[4] = $user->getRanges('PercRanges');
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head('Fluency Games User Portal'); ?>
	
	<script src="<?php echo $documentroot; ?>js/math.js"></script>
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>
	<script src="<?php echo $documentroot; ?>js/reports/process-report.js"></script>
	<script src="<?php echo $documentroot; ?>js/reports/prepare-report.js"></script>
	<script type="text/javascript">
		ranges = <?php echo json_encode($ranges); ?>;
	</script>
	<script src="<?php echo $documentroot; ?>js/manage/snapshot.js"></script>
</head>
<body>
	<?php Element::header(5); ?>
	<div class="body">
		<div class="container">
			
			<div class="row">
				<?php Element::sidebarManage(6); ?>
				<div class="col-xs-12 col-sm-8 col-lg-9">
					
					<div class="card">
						<div class="head center">
							<?php echo $user->getFullname() . ' (Group: ' . $user->getColumn('Groups') . ')'; ?>
						</div>
						
						<div class="snapshot product" data-id="0">
							<span class="name">Select Product:</span>
							<input type="hidden" name="username" value="<?php echo '*.' . $groupName . '.' . $teacherDomain; ?>" />
							
							<div class="select-input right-aligned-products" id="product" data-name="product" data-value="<?php echo $defaults['product']; ?>">
								<?php Element::productSelectInput($products); ?>
							</div>
							
							
							<!--<select id="product-old" class="right-aligned-products">
								<?php Element::productSelectInput($products); ?>
							</select>-->
						</div>
						
						<div class="snapshot">
							<div class="row">
								<div class="col-xs-3">
									<span class="name" style="height: 50px !important; line-height: 60px;">Name (Games)</span>
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Summary
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Last Game
								</div>
									<?php
										for ($i = 0; $i < 2; ++$i) {
									?>
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<!--<div class="col-xs-4">
												<div class="score-title" data-toggle="tooltip"title="Average">Avg</div>
											</div>-->
											<div class="col-xs-6">
												<div class="score-title" data-toggle="tooltip" title="Points Per Second">PPS</div>
											</div>
											<div class="col-xs-6">
												<div class="score-title" data-toggle="tooltip" title="Accuracy">Acc</div>
											</div>
										</div>
									</div>
								</div>
									<?php } ?>
							</div>
						</div>
						<div id="snapshots"></div>
						<div class="footer center">
							End of students
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
	<script type="text/javascript">
		<?php
			foreach ($students as $student) {
				$json = json_encode($student);
				echo "createSnapshot($json);" . PHP_EOL;
			}
		?>
		loadSnapshots(<?php echo $defaults['product']; ?>);
		
		registerOnChange('#product-input', function(e) {
			var product = parseInt($(e).val());
			loadSnapshots(product);
		});
	</script>
</body>
</html>