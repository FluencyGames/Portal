<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");

	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER | TEACHER_ADMIN, 'manage');
	
	$search = "Students";
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$teachers = $user->getTeachers();
	$groupName = $user->getColumn('Groups');
	
	$documentroot = Config::get('documentroot');
	$template = $documentroot . 'media/csv/Fluency Games Student Template.csv';
	
	function outputTeachersJSArray($teachers) {
		$str = "\n";
		foreach ($teachers as $teacher) {
			//$str .= "{ id: {$teacher['Id']}, firstname: '{$teacher['Fname']}', lastname: '{$teacher['LName']}', username: '{$teacher['Username']}' },\n\t\t\t";
			$str .= "\t\t\t{ id: {$teacher['Id']}, firstname: '{$teacher['Fname']}', lastname: '{$teacher['LName']}', username: '{$teacher['Username']}', group: '{$teacher['Groups']}' },\n";
		}
		echo $str . "\t\t";
	}
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		var groupName = '<?php echo $groupName; ?>';
		var productCode = '<?php echo $products; ?>';
		var userType = "student";
		var userLevel = <?php echo $user->getColumn('UserType'); ?>;
		var domain = "<?php echo $license['DomainSuffix']; ?>";
		var currentGroup = "<?php echo $groupName; ?>"
		var teachers = [<?php outputTeachersJSArray($teachers); ?>];
		var inputKeys = [
			"First name",
			"Last name",
			"Username",
			"Group",
			"Products"
		];

		function sortUsers(userData) {
			userData.sort( function(a, b) {
				var A = a['Lname'];
				var B = b['Lname'];
				if( A < B )
					res = -1;
				else if( A > B )
					res =  1;
				else {
					A = a['Fname'];
					B = b['Fname'];
					if( A < B )
						res = -1;
					else if( A > B )
						res = 1;
					else
						res = 0;
				}
				return res;
			});
		}
				
		function processUsers(userData) {
			var curUser;
			for (var i = 0; i < userData.length; ++i) {
				curUser = userData[i];
				existingUsers.push(new User(
					['Id', 'First name', 'Last name', 'Username', 'Group', 'Products' ],
					[curUser['Id'], curUser['Fname'], curUser['Lname'], curUser['Username'], curUser['GroupName'], curUser['Products'] ],
					states.unedited
				));
			}
		}
		
		function processCSV(filename) {
		    console.log("processCSV:");
			sendAjax({
				url: "uploads/temp/" + getCookie("fluency_games_id") + "_" + filename,
				datatype: "text",
				success: function(result) {
					students = result.split("\n");
					console.log("# of records:"+students.length);
					
					// we assume the field is first name, last name, teacher
					var iFName=1, iLName=0;
					
					// we assume no headers
					var header = 0;
					
					// get the first row of data to check for header row
					var guideline = students[0].split(",");
					
					// check for a header field
					if(guideline[0].search(/lname|lastname|last name|fname|firstname|first name/i)>=0)
						header = 1;
					
					// check if the lastname field is first, if it is, then switch the 
					// firstname and lastname field indicies
					if(header && (guideline[0].search(/fname|first|first name/i)>=0)) {
						iLName = 1;
						iFName = 0;
					} 
					
					// we assume that there is a header field									
					var length = students.length - header;
					for (var i = header; i < length; i++) {
						var student = students[i].split(",");
						var group = currentGroup;
						if(student.length > 2)
						    group = student[2];
						
						// add the new user 
						// username will be created by updateUsername()
						newUsers.push(new User(inputKeys,
										[student[iFName], student[iLName], '', group, productCode],
										states.added));
					}
					
					collapseAll('new');
					collapseAll('existing');
					loadUsersIntoSlots('new', length, newSlots.length);
				},
				error: function(result) {
					alert("rosters.php, Line 111: " + result['error']);
					console.log(result);
				}
			});
		}		
		
		
		registerOnChange("#student-csv-upload", function() {
			if ($('#student-csv-upload')[0].files.length == 0) {
				alert("No file uploaded");
			} else {
				sendAjaxFile({
					url: "php/ajax/upload.php",
					files: [
						'student-csv-upload',
					],
					success: function(result) {
						if (result['success']) {
							// window.location = "home";
							processCSV(result['filename']);
						} else {
							alert("rosters.php, Line 131" + result['error']);
							console.log(result);
						}
					},
					error: function(result) {
						alert("rosters.php, Line 136: Error: " + result['error']);
						console.log(result);
					},
				});
			}
			
			// Reset the form
			$("#student-csv-upload").val("");
		});
		
	</script>
	<script src="<?php echo $documentroot; ?>js/manage/users.js"></script>
</head>
<body>
	<?php Element::header(4); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarManage(4); ?>

				<div id="existing-students-col" class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Available Students
							<span class="buttons">
								<span id="expand-all" data-toggle="tooltip" title="Expand all" class="icon-button icon-plus-1"></span>
								<span id="collapse-all" data-toggle="tooltip" title="Collapse all" class="icon-button icon-minus-1"></span>
								<span id="toggle-width" data-toggle="tooltip" title="Half width" class="icon-button icon-resize-small-1"></span>
							</buttons>
						</div>
						
						<div class="body no-padding-bottom save-body">
							<div class="row">
								<div class="col-xs-12">
									<div class="save-users-button">Save Changes</div>
								</div>
							</div>
						</div>
						
						<div class="body no-padding-bottom">
							<div class="row">
								<div class="col-xs-12">
									<div id="add-user-button">Add Student...</div>
								</div>
							</div>
						</div>
						
						<div class="body" id="new-body">
							<div id="new-students" class="row">
								<div class="col-xs-12">
									<!--<div class="user-page-title">New students</div>-->
									<!--<div class="row">
										<div class="col-xs-12 col-md-6">
											<div class="page-count" id="new-page-count">Page 1 of 1</div>
										</div>
										<div class="col-xs-12 col-md-6">
											<select id="new-users-count">
												<option value="25">25 / Page</option>
												<option value="50">50 / Page</option>
												<option value="100">100 / Page</option>
												<option value="250">250 / Page</option>
											</select>
										</div>
									</div>-->
								</div>
								<!--<div id="new-page-buttons-top">
									<div class="col-xs-6 col-md-3">
										<div class="page-button new-prev-page">Prev Page</div>
									</div>
									<div class="col-xs-6 col-md-3">
										<div class="page-button new-next-page">Next Page</div>
									</div>
									<div class="col-xs-6 col-md-3">
										<div class="page-button new-first-page">First Page</div>
									</div>
									<div class="col-xs-6 col-md-3">
										<div class="page-button new-last-page">Last Page</div>
									</div>
								</div>-->
								<div id="new-page-buttons-bottom">
									<div class="clear"></div>
								</div>
							</div>
						</div>
						
						<div class="body" id="existing-body">
							<div id="existing-students" class="row">
								<div class="col-xs-12">
									<!--<div class="user-page-title">Existing students</div>-->
									<div class="row">
										<div class="col-xs-6 col-md-4">
											<div class="page-count" id="existing-page-count">Page 1 of 1</div>
										</div>
										<div class="col-xs-6 col-md-4 col-md-push-4">
											<select id="existing-users-count">
												<option value="5">5 / Page</option>
												<option value="25">25 / Page</option>
												<option value="50">50 / Page</option>
												<option value="100">100 / Page</option>
												<!--<option value="250">250 / Page</option>-->
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-3">
											<div class="page-button existing-first-page icon-to-start"></div>
										</div>
										<div class="col-xs-3">
											<div class="page-button existing-prev-page icon-fast-bw"></div>
										</div>
										<div class="col-xs-3">
											<div class="page-button existing-next-page icon-fast-fw"></div>
										</div>
										<div class="col-xs-3">
											<div class="page-button existing-last-page icon-to-end"></div>
										</div>
									</div>
								</div>
								
								<!-- Student slots will be added here in $documentroot/js/manage/users.js -->
								
								<div id="existing-page-buttons-bottom">
									<div class="clear"></div>
									<div class="col-xs-3">
										<div class="page-button existing-first-page icon-to-start"></div>
									</div>
									<div class="col-xs-3">
										<div class="page-button existing-prev-page icon-fast-bw"></div>
									</div>
									<div class="col-xs-3">
										<div class="page-button existing-next-page icon-fast-fw"></div>
									</div>
									<div class="col-xs-3">
										<div class="page-button existing-last-page icon-to-end"></div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="body save-body">
							<div class="row">
								<div class="col-xs-12">
									<div class="save-users-button">Save Changes</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-8 col-md-12 col-lg-3">
					<div class="card">
						<div class="head center">
							Utilities
						</div>
						<div class="body" style="font-size: 20px;">
							<div>
								<a href="print?type=students">
									<span class="icon-print"></span> Print Usernames
								</a>
							</div>
							<!--<div class="big-button" data-icon="upload" data-file-id="student-csv-upload" data-file-type=".csv">Upload CSV</div>-->
							<div>
								<a href="<?php echo $template; ?>">
									<span class="icon-download"></span> Download Template
								</a>
							</div>
							<!--<div id="student-uploading" class="uploading">
								<div class="info-wrapper">
									<div class="info">
										<span class="icon-cw"></span>
										<p>Uploading...</p>
									</div>
								</div>
							</div>-->
						</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-12 col-lg-3" sq-for-1200='help-card'>
					<div class="card" sq-id='help-card'>
						<div class="head center">
							Help
						</div>
						<div class="body">
							<div class="dropdown">
								<a class="title">Do I need the template?</a>
								<div class="content">
									<?php if($user->getColumn('UserType') == EDUCATIONAL_ADMIN) { ?>
										You do not have to use the template, but the system will only read a <b>CSV(Comma-separated values)</b> file with the fields:<br/><br/>
										<i>First Name, Last Name, Group Name.</i><br/><br/>
										The <i>Group Name</i> used to automatically assign a student to teacher or class group.
									<?php } else { ?>
										You do not have to use the template, but the system will only read a <b>CSV (Comma-separated values)</b> file with the fields:<br/>
										<i>First Name, Last Name</i><br/><br/>
									<?php } ?>
								</div>
							</div>
							<div class="dropdown">
								<a class="title">How do I make a CSV file?</a>
								<div class="content">
									Have your student names listed in a spreadsheet and <b>'Export'</b> or <b>'Save As...'</b> a *.CSV file<br/><b>OR</b><br/>
									Type each field (ie, first name, last name) separated by a comma, one name per line.<br/><b>OR</b><br/>
									Ask your administrator to send you a <b>*.CSV</b> file of the students in your class.
								</div>
							</div>
							<div class="dropdown">
								<a class="title">The data isn't right!</a>
								<div class="content">
									The site will assume that the data is in a certain order if your file does not a header.<br/>
									The CSV file must also be a <b>plain TEXT file</b>; <i>not</i> a spreadsheet or word processor file (as in an EXCEL or WORD document).
								</div>
							</div>
							<div class="dropdown">
								<a class="title">Help me!</a>
								<div class="content">
									If you are still having difficulties, please click on the link below:<br/>
									<a href="http://www.fluency-games.com/contactus/">Contact Fluency Games</a>
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
