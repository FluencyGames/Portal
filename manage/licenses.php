<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	
	Element::restrictAccess(FLUENCY_GAMES_ADMIN);
	
	$search = "Licenses";
	
	$user = User::getCurrentUser();
	$licenses = $user->getLicenseInfo();
	
	$documentroot = Config::get('documentroot');
	
	function outputLicenseJSArray($licenses) {
		$str = "\n";
		foreach ($licenses as $license) {
			$str .= "\t\t\t{ LicenseKey: '{$license['LicenseKey']}', System: '{$license['System']}', StartDate: '{$license['StartDate']}', EndDate: '{$license['EndDate']}', NumUsers: '{$license['NumUsers']}', Products: '{$license['Products']}', DomainSuffix: '{$license['DomainSuffix']}' },\n";
		}
		echo $str . "\t\t";
	}
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>
	
	<script type="text/javascript">
		var licenses = [<?php outputLicenseJSArray($licenses); ?>];
		var inputKeys = [
		    'LicenseKey',
		    'System',
		    'DomainSuffix',
		    'StartDate',
		    'EndDate',
		    'NumUsers',
		    'Products',
		];
		
		function processLicenses(licData) {
			var curLic;
			for (var i = 0; i < licData.length; ++i) {
				curLic = licData[i];
				existingUsers.push(new User(
					['LicenseKey', 'System', 'DomainSuffix', 'StartDate', 'EndDate', 'NumUsers', 'Products' ],
					[curLic['LicenseKey'], curLic['System'], curLic['DomainSuffix'], curLic['StartDate'], curLic['EndDate'], curLic['NumUsers'], curLic['Products']  ],
					states.unedited
				));
			}
		}
	</script>
	<script src="<?php echo $documentroot; ?>js/manage/licenses.js"></script>
	
	<style type="text/css">
		.compact {
			width: 92px;
		}

		@media (min-width: 768px) {
			.years-modal {
				width: 500px;
			}

			.seats-modal {
				width: 500px;
			}

			.products-modal {
				width: 500px;
			}

		}
	</style>
	
</head>
<body>
	<?php Element::header(4); ?>
	<div class="body">
		<div class="container">
			<div class="row">
				<?php Element::sidebarAdmin(3); ?>

				<div id="existing-licenses-col" class="col-xs-12 col-sm-8 col-lg-6">
					<div class="card">
						<div class="head center">
							Current Licenses
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
						
						<div class="body" id="existing-body">
							<div id="existing-licenses" class="row">
								<div class="col-xs-12">
									<!--<div class="licenses-page-title">Current Licenses</div>-->
									<div class="row">
										<div class="col-xs-6 col-md-4">
											<div class="page-count" id="existing-page-count">Page 1 of 1</div>
										</div>
										<div class="col-xs-6 col-md-4 col-md-push-4">
											<select id="existing-licenses-count">
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
								
								<!-- Student slots will be added here in $documentroot/js/manage/license.js -->
								
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
								<div id='search' class='text-input' data-label='Search Licenes' data-name='username' data-placeholder></div>
							</form>
						</div>
					</div>-->