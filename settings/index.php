<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(UNRESTRICTED, 'settings');
	
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	$aLicenses = $user->getLicenses();
	$license = $user->getLicenseData();

?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	<script type="text/javascript">

	registerOnClick("#parent-setup-form button", function() {
		var group = $("#parent-setup-form [name=group]").val().toLowerCase().trim().replace(/\s/g,'');
	    valid = isUniqueGroupName( group );
				
		if(valid) {
			sendAjax({
				url: "php/ajax/settings/update-account.php",
				data: {
				    License: '<?php echo $user->getColumn('license'); ?>',
					Username: $("#account-setup-form [name=username]").val(),
					Groups: group
				},
				success: function(result) {
					console.log(result);
					if (result['success']) {
						alert("Settings Updated.");
						refresh('index');
					} else {
						alert(result['error']);
					}
				}
			});
		}
	});
	
	registerOnClick("#school-setup-form button", function() {
	    valid = true;
	    usrLevel = <?php echo $user->getColumn('UserType'); ?>;
		
		if(usrLevel == <?php echo EDUCATIONAL_ADMIN ?> || usrLevel == <?php echo TEACHER_ADMIN ?>) {
			var domain_suffix = $("#school-setup-form [name=domainsuffix]").val().trim().replace(/\s/g,'');
		    valid = isUniqueDomainSuffix(domain_suffix);
			if(valid) {
			    $.when( $.ajax( {
		 					url: "../php/ajax/settings/update-license.php",
							type: "POST",
		 					data: {
		 						DomainSuffix: domain_suffix
		 					}
		 				})).then( function() {  // success
							alert("Settings Updated.");
							refresh('index');
						}, function() {
						    alert("Settings Failed to Update");
							valid = false;
						});
			}
		}
	});
	
	registerOnClick("#group-setup-form button", function() {
	    valid = true;
	    usrLevel = <?php echo $user->getColumn('UserType'); ?>;
	
		if(usrLevel == <?php echo TEACHER_ADMIN ?> || usrLevel == <?php echo TEACHER ?>) {
			var group = $("#group-setup-form [name=group]").val().trim().replace(/\s/g,'');
			valid = isUniqueGroupName(group);
			if(valid) {
			    $.when( $.ajax( {
							url: "../php/ajax/settings/update-account.php",
							type: "POST",
							data: {
							    License: '<?php echo $user->getColumn('license'); ?>',
								Username: $("[name=username]").val(),
								Groups: group
							},
						})).then( function() {
							alert("Settings Updated.");
							refresh('index');
						}, function() {
						    valid = false;
						    alert("Settings Failed to Update");
						});
			}
		}
	});
	
	registerOnClick("#account-settings-form button", function() {
		sendAjax({
			url: "php/ajax/settings/update-user.php",
			data: {
				Username: $("[name=username]").val(),
				Fname: $("[name=firstname]").val(),
				LName: $("[name=lastname]").val(),
				Email: $("[name=email]").val(),
				Phone: $("[name=phone]").val(),
			},
			success: function(result) {
				console.log(result);
				if (result['success']) {
					alert("Settings Updated.");
				} else {
					alert(result['error']);
				}
			}
		});
	});	
	
	$(window).ready(function() {
	    $(".required.text-input-wrapper").each( function() {
			 if($(this).attr('data-value') != '') {
			 	$("#" + $(this).attr('id') + "-input").attr('disabled','disabled');
			}
		});
	});
	
	</script>
	
</head>
<body>
	<?php Element::header(2); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarSettings(1); ?>
				<div class="col-xs-12 col-sm-8 col-lg-6">
				
					<!-- Parent Form for Group Name -->
					<?php if( $user->getColumn('UserType')==PARENT_GUARDIAN && $user->getColumn('Groups')=='') 
						{
					?>
						<div class="card" id="parent-setup">
							<div class="head center">
								Account: No Group Set
							</div>
							<div class="body">
	
								<form id="parent-setup-form" method="POST">
									<span>Please enter a name that will uniquely identify your home group. For example, 'SmithFamily'.
									<br><br>Login names for your users in your group will be 'childname@SmithFamily'
									<br><br></span>
									<div class="text-input required" data-label="Group" data-name="group" data-value="<?php echo $user->getColumn("Groups") ?>"></div>
									<button>Update</button>
									<div class="clear"></div>
								</form>
							</div>
						</div>
					<?php 
						}
					?>
					
					<!-- School Form for School Domain -->
					<?php if(  ($user->getColumn('UserType')==TEACHER_ADMIN || $user->getColumn('UserType')==EDUCATIONAL_ADMIN) && $license['DomainSuffix']=='')
							{
					?>
						<div class="card" id = "school-setup">
							<div class="head center">
								Account: No School Domain Set
							</div>
							<div  class="body">
								<form id="school-setup-form" method="POST">
									<span>Enter a name that will uniquely identify your school. For example, 'SmithElem'.
									<br><br>Login names for your users in your group will be 'childname@groupname.SmithElem'
									<br>A common practice is to use the school mascot or nickname.<br><br></span>
									<div class="text-input required" data-label="School Domain" data-name="domainsuffix" data-value="<?php echo $license["DomainSuffix"]; ?>"></div>
									<button>Update</button>
									<div class="clear"></div>
								</form>
							</div>
						</div>
					<?php
						} else if(($user->getColumn('UserType')==TEACHER_ADMIN || $user->getColumn('UserType')== TEACHER) && $user->getColumn('Groups')=='') {
					?>
						<div class="card" id="group-setup" >
							<div class="head center">
								Account: No Group Defined
							</div>
							<div class="body" >
								<form id="group-setup-form" method="POST">
									<span>Enter a name that will uniquely identify your class group. For example, 'JonesClass'.
									<br><br>Login names for your users in your group will be 'childname@JonesClass.schoolname'
									<br>A common practice is to use a classroom mascot or nickname.<br><br></span>
									<div class="text-input required" data-label="Group" data-name="group" data-value="<?php echo $user->getColumn("Groups") ?>"></div>
									<button>Update</button>
									<div class="clear"></div>
								</form>
							</div>
						</div>
					<?php
						} else {
					?>
						<div class="card">
							<div class="head center">
								General
							</div>
							<div class="body">
								<form id="account-settings-form" method="POST">
									<?php
										function setting($title, $name, $content) {
									?>
											<div class="text-input" data-label="<?php echo $title; ?>" data-name="<?php echo $name; ?>" data-value="<?php echo $content; ?>"></div>
									<?php
										}
										setting("Username", "username", $user->decode($user->getColumn("Username")));
										setting("First name", "firstname", $user->decode($user->getColumn("Fname")));
										setting("Last name", "lastname", $user->decode($user->getColumn("LName")));
										setting("Email", "email", $user->decode($user->getColumn("Email")));
										setting("Phone", "phone", $user->decode($user->getColumn("Phone")));
									?>
									
									<button>Update</button>
									<div class="clear"></div>
								</form>
							</div>
						</div>
					<?php
						}
					?>	
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>