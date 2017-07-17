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
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head('Fluency Games User Portal'); ?>
	
	<script src="<?php echo $documentroot; ?>js/math.js"></script>
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>
	
	<script type="text/javascript">
		//
	</script>
	<!-- <script src="<?php echo $documentroot; ?>js/manage/users.js"></script> -->
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
							<select id="product" class="right-aligned-products">
								<?php if($products & 0x01) { ?> <option value="1" >Addition Blocks</option> <?php } ?>
								<?php if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option> <?php } ?>
								<?php if($products & 0x04) { ?> <option value="4" >Percent Bingo</option> <?php } ?>
								<?php if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option> <?php } ?>
								<?php if($products & 0x10) { ?> <option value="16">Integer Blocks</option> <?php } ?>
                                <option value="128">Facts Assessment</option>
							</select>
						</div>
						<!--<div class="snapshot">
							<span class="name">Name (Games)</span>
							<?php
								for ($i = 0; $i < 2; ++$i) {
							?>
							<div class="scores-3-pair">
								<div class="score-title">Avg</div>
								<div class="score-title">Pts/<br/>Sec</div>
								<div class="score-title">Accu.</div>
							</div>
							<?php } ?>
						</div>-->
						<div class="snapshot">
							<div class="row">
								<div class="col-xs-3">
									<span class="name">Name (Games)</span>
								</div>
									<?php
										for ($i = 0; $i < 2; ++$i) {
									?>
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<div class="col-xs-4">
												<div class="score-title">Avg</div>
											</div>
											<div class="col-xs-4">
												<div class="score-title">Pts / Sec</div>
											</div>
											<div class="col-xs-4">
												<div class="score-title">Accu.</div>
											</div>
										</div>
									</div>
								</div>
									<?php } ?>
							</div>
						</div>
						<?php
							function createStudent($lname, $fname, $username, $id) {
								$name = $lname . ', ' . $fname;
						?>
						<div class="snapshot" data-id="<?php echo $id; ?>">
							<div class="row">
								<div class="col-xs-3">
									<span class="name"><?php echo $name; ?></span>
								</div>
								<input id="username-<?php echo $id; ?>" type="hidden" name="username" value="<?php echo $username; ?>" />
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<div class="col-xs-4">
												<div class="score-circle green"></div>
											</div>
											<div class="col-xs-4">
												<div class="score-circle red"></div>
											</div>
											<div class="col-xs-4">
												<div class="score-circle"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<div class="col-xs-4">
												<div class="score-circle"></div>
											</div>
											<div class="col-xs-4">
												<div class="score-circle red"></div>
											</div>
											<div class="col-xs-4">
												<div class="score-circle green"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--<div class="snapshot" data-id="<?php echo $id; ?>">
							<span class="name"><?php echo $name; ?></span>
							<input id="username-<?php echo $id; ?>" type="hidden" name="username" value="<?php echo $username; ?>" />
							<div class="scores-3-pair">
								<div class="score-circle"></div>
								<div class="score-circle"></div>
								<div class="score-circle"></div>
							</div>
							<div class="scores-3-pair">
								<div class="score-circle"></div>
								<div class="score-circle"></div>
								<div class="score-circle"></div>
							</div>
						</div>-->
						<?php
							}
							
							$students = $user->getStudents('ORDER BY Lname ASC, Fname ASC', '*', $groupName);
							foreach ($students as &$student) {
								$student['Lname'] = User::decode($student['Lname']);
								$student['Fname'] = User::decode($student['Fname']);
							}
							$user->sortStudents($students);
							foreach ($students as $s) {
								createStudent($s['Lname'], $s['Fname'], $s['Username'], $s['Id']);
							}
						?>
						<div class="footer center">
							End of students
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>