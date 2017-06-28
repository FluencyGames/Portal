<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Student.class.php");

	Element::restrictAccess(TEACHER | TEACHER_ADMIN | PARENT_GUARDIAN, 'manage');
	
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
	<?php Element::head("Fluency Games User Portal"); ?>
	
	<script src="<?php echo $documentroot; ?>js/math.js"></script>	
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>	
	<script src="<?php echo $documentroot; ?>js/reports/process-report.js"></script>		
	<script src="<?php echo $documentroot; ?>js/manage/student-settings.js"></script>
	
	<style type="text/css">
		.checkbox-input-wrapper {
			display: inline-block;
		}
	</style>
	
</head>
<body>
	<?php Element::header(5); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarManage(5); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							<?php echo $user->getFullname() . ' (Group: ' . $user->getColumn('Groups') . ')'; ?>
						</div>
						<div class="student-settings bulk" data-id="0">
							<div class='checkbox-input' data-name='remember' id='bulk'></div>
							<span class="name">Bulk Actions</span>
							<input type="hidden" name="username" value="<?php echo '*.' . $groupName . '.' . $teacherDomain; ?>" />
							<select id="bulk-product">
								<?php if($products & 0x01) { ?> <option value="1" >Addition Blocks</option> <?php } ?>
								<?php if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option> <?php } ?>
								<?php if($products & 0x04) { ?> <option value="4" >Percent Bingo</option> <?php } ?>
								<?php if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option> <?php } ?>
								<?php if($products & 0x10) { ?> <option value="16">Integer Blocks</option> <?php } ?>
                                <option value = "128">Facts Assessment</option>
							</select>
							<div class="links">
								<a class="settings-toggle" data-id="0" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Settings">
									<span class="icon-cog"></span>
								</a>
								<a class="progress-toggle" data-id="0" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Progress">
									<span class="icon-chart-line"></span>
								</a>
								<a class="reports-toggle-bulk" data-id="0" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Reports">
									<span class="icon-doc-text-inv"></span>
								</a>
								<!--
								<a class="message-toggle" data-id="0" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Message">
									<span class="icon-comment"></span>
								</a>
								-->
							</div>
						</div>
						<?php
							function createStudent($lname, $fname, $username, $id, $products) {
						?>
						<div class="student-settings" data-id="<?php echo $id; ?>">
							<div class="checkbox-input" data-name="selected" id="selected-<?php echo $id; ?>"></div>
							<span class="name"><?php echo $lname; ?>, <?php echo $fname; ?></span>
							<input id="username-<?php echo $id; ?>" type="hidden" name="username" value="<?php echo $username; ?>" />
							<select data-id="<?php echo $id; ?>">
								<?php if($products & 0x01) { ?> <option value="1" >Addition Blocks</option>  <?php } ?>
								<?php if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option>  <?php } ?>
								<?php if($products & 0x04) { ?> <option value="4" >Percent Bingo</option>  <?php } ?>
								<?php if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option>  <?php } ?>
								<?php if($products & 0x10) { ?> <option value="16">Integer Blocks</option>  <?php } ?>
                                <option value = "128">Facts Assessment</option>
							</select> 
							<div class="links">
								<a class="settings-toggle" data-id="<?php echo $id; ?>" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Settings">
									<span class="icon-cog"></span>
								</a>
								<a class="progress-toggle" data-id="<?php echo $id; ?>" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Progress">
									<span class="icon-chart-line"></span>
								</a>
								<a class="reports-toggle" data-id="<?php echo $id; ?>" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Reports">
									<span class="icon-doc-text-inv"></span>
								</a>
								<!--							
								<a class="message-toggle" data-id="<?php echo $id; ?>" class="icon-lock" data-toggle="tooltip" data-gravity="s" title="Message">
									<span class="icon-comment"></span>
								</a>
								-->
							</div>
						</div>
						<?php
							}
							
							$students = $user->getStudents('ORDER BY Lname ASC, Fname ASC', '*', $groupName);
							foreach ($students as &$student) {
								$student['Lname'] = User::decode($student['Lname']);
								$student['Fname'] = User::decode($student['Fname']);
							}
							$user->sortStudents($students);
							foreach ($students as $s) {
								createStudent($s['Lname'], $s['Fname'], $s['Username'], $s['Id'], $s['Products']);
								//createStudent(user::decode($student['Lname']), user::decode($student['Fname']), $student['Username'], $student['Id'], $student['Products']);
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