<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(EDUCATIONAL_ADMIN, 'manage');
	
	$search = "Teachers";
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	
	$documentroot = Config::get('documentroot');
	$template = $documentroot . 'media/csv/Fluency Games Teacher Template.csv';
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">
		var groupName = null;
		var userType = "teacher";
		var domain = "<?php echo $license['DomainSuffix']; ?>";
		var inputKeys = [
			"First name",
			"Last name",
			"Group",
			"Username",
			"Unused"    // students have an additional field
		];
		
		function sortUsers(userData) {
			userData.sort( function(a, b) {
				var A = a['LName'];
				var B = b['LName'];
				res = (A > B) - (A < B);
				if (res == 0) {
					A = a['Fname'];
					B = b['Fname'];
					res = (A > B) - (A < B);
				}
				return res;
			});
		}
				
		function processUsers(userData) {
			var curUser;
			console.log(userData);
			for (var i = 0; i < userData.length; ++i) {
				curUser = userData[i];
				existingUsers.push(new User(
					['Id', 'First name', 'Last name', 'Group', 'Username'],
					[curUser['Id'], curUser['Fname'], curUser['LName'], curUser['Groups'], curUser['Username']],
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
					teachers = result.split("\n");
					console.log("# of records:"+teachers.length);

					// we assume the field is first name, last name, teacher
					var iFName=1, iLName=0;

					// we assume no headers
					var header = 0;

					// get the first row of data to check for header row
					var guideline = teachers[0].split(",");
					
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
					var length = teachers.length - header;
					for (var i = header; i < length; i++) {
						var teacher = teachers[i].split(",");
						var group = '';
						var usr = '';
						
						// pull out the username/email
						if(teacher.length > 2)
						    usr = teacher[2];
							
						// pull out the groupname, or create one
						if(teacher.length > 3)
						    group = teacher[3];
						else
						    group = teacher[iFName].charAt(0)+teacher[iLName];
							
						// add the new user
						// username will be created by updateUsername()
						newUsers.push(new User(inputKeys,
										[teacher[iFName], teacher[iLName], group, usr],
										states.added));
					}

					collapseAll('new');
					collapseAll('existing');
					loadUsersIntoSlots('new', length, newSlots.length);
				},
				error: function(result) {
					alert("teachers.php, Line 101: " + result['error']);
					console.log(result);
				}
			});
		}


		registerOnChange("#teacher-csv-upload", function() {
			if ($('#teacher-csv-upload')[0].files.length == 0) {
				alert("No file uploaded");
			} else {
				sendAjaxFile({
					url: "php/ajax/upload.php",
					files: [
						'teacher-csv-upload',
					],
					success: function(result) {
						if (result['success']) {
							// window.location = "home";
							processCSV(result['filename']);
						} else {
							alert(result['error']);
						}
					},
					error: function(result) {
						alert("teachers.php, Line 136: Error: " + result['error']);
						console.log(result);
					},
				});
			}

			// Reset the form
			$("#teacher-csv-upload").val("");
		});
		
	</script>
	<script src="<?php echo $documentroot; ?>js/manage/users.js"></script>
</head>
<body>
	<?php Element::header(3); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarManage(3); ?>

				<div id="existing-teachers-col" class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Available Teachers
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
									<div id="add-user-button">Add Teacher...</div>
								</div>
							</div>
						</div>
						
						<div class="body" id="new-body">
							<div id="new-teachers" class="row">
								<div class="col-xs-12">
									<!--<div class="user-page-title">New teachers</div>-->
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
									<!--<div class="col-xs-6 col-md-3">
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
									</div>-->
								</div>
							</div>
						</div>
						
						<div class="body" id="existing-body">
							<div id="existing-teachers" class="row">
								<div class="col-xs-12">
									<!--<div class="user-page-title">Existing teachers</div>-->
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
								
								<!-- teacher slots will be added here in $documentroot/js/manage/users.js -->
								
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
								<a href="print?type=teachers">
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
									You do not have to use the template, but the system will only read a <b>CSV (Comma-separated values)</b> file with the fields:<br/>
									<i>First Name, Last Name, Username or Email, Group Name.</i><br/>
									The <i>Group Name</i> is used to automatically assign students to a teacher or class group.
								</div>
							</div>
							<div class="dropdown">
								<a class="title">How do I make a CSV file?</a>
								<div class="content">
									There are two ways to make the <b>CSV (Comma-separated values)</b> file:<br/>
									Have the teacher names listed in a spreadsheet and <b>'Export'</b> or <b>'Save As...'</b> a *.CSV file<br/><b>OR</b><br/>
									Simply type the values, with each field (ie, first name, last name) separated by a comma.
								</div>
							</div>
							<div class="dropdown">
								<a class="title">The data isn't right</a>
								<div class="content">
									If you did not use a header, then the site will assume that the data is in a certain order.<br/>
									The file must also be a <b>plain TEXT file</b>; <i>not</i> a spreadsheet or word processor file (as in an EXCEL or WORD document).
								</div>
							</div>
							<div class="dropdown">
								<a class="title">Help me!</a>
								<div class="content">
									If you a still having difficulties, please click below:<br/>
									<a href="http://www.fluency-games.com/contactus/">Contact Fluency Games</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<a style="width: 100%; height: 100%; display: block; cursor: pointer;" onClick="sendUsers();">
					<div class="col-xs-12 col-md-0">
						<div class="card">
							<div class="head center">
								<h2>Save Changes</a></h2>
							</div>
						</div>
					</div>
				</a>
				
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>

<!--<div class="card">
						<div class="head center">
							Search
						</div>
						<div class="body">
							<form>
								<div id='search' class='text-input' data-label='Search Teachers' data-name='username' data-placeholder></div>
							</form>
						</div>
					</div>-->