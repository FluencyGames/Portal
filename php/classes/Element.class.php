<?php
	require_once(__DIR__ . "/Config.class.php");
	require_once(__DIR__ . "/User.class.php");
	
	class Element {
		public static function redirectUsersToHome() {
			//
			// 12-21-15 mse updated to redirect logged in user to 
			// specific home page
			//
			$user = User::getCurrentUser();
			$userType = $user->getColumn('UserType');
			$loc = '';
				
			if (User::loggedIn()) {
				switch($userType) {
					case FLUENCY_GAMES_ADMIN:
						$loc = 'manage/fg';
						break;
											
					default: 
						$loc = 'home';
						break;
						
					case EDUCATIONAL_ADMIN: 
						$loc = 'manage/teachers';
						break; 
						
					case TEACHER: 
					case TEACHER_ADMIN: 
					case PARENT_GUARDIAN: 
						$page = $user->getHomePage();
						$loc = "manage/{$page}";
						break;
				}
			} else {
				User::logout();
			}
			
			header('location: ' . Config::get('documentroot') . $loc);
			die();
		}			

		public static function redirectUsersToSetup() {
			header('location: ' . Config::get('documentroot') . 'settings/index.php');
		}

		public static function restrictAccess($allowedLevel = UNRESTRICTED, $location = '') {
			$userType = User::getCurrentUser()->getColumn('UserType');

			if($location != 'settings' && $location != 'landing') {
				
				if($userType == EDUCATIONAL_ADMIN || $userType == TEACHER_ADMIN) {
					$lic = User::getCurrentUser()->getLicenseData();
					if(empty($lic['DomainSuffix'])) {
					    Element::redirectUsersToSetup();
						die();
					}
				}

				if($userType == TEACHER_ADMIN || $userType == TEACHER || $userType == PARENT_GUARDIAN) {
				    if(User::getCurrentUser()->getColumn('Groups')=='' || User::getCurrentUser()->getColumn('Groups')==null ) {
					    Element::redirectUsersToSetup();
						die();
					}
				}
			}
			
			if ( ($allowedLevel == UNRESTRICTED || $allowedLevel == FLUENCY_GAMES_ADMIN) && $userType==FLUENCY_GAMES_ADMIN) {
			    return;
			}
			
			if (($userType & $allowedLevel) == 0) {
				header('location: ' . Config::get('documentroot') . $location);
			}
			
			if (!User::loggedIn()) {
				Element::redirectUsersToHome();
				die();
			}
		}
		
		public static function pageURL() {
			$pageURL = 'http';
			if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on")) {
				$pageURL .= "s";
			}
			$pageURL .= "://";
			
			if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}
		
		public static function head($title, $checkForExpired = true) {
			// This is where we're checking the expired license for now
			if ($checkForExpired && User::loggedIn()) {
				$user = User::getCurrentUser();
				$userType = $user->getColumn('UserType');
				$license = $user->getLicenseData();
                if($userType != FLUENCY_GAMES_ADMIN) {
                    if (time() > strtotime($license['EndDate'])) {
                        /*$canUpgrade = (($userType & (EDUCATIONAL_ADMIN | TEACHER_ADMIN | PARENT_GUARDIAN)) > 0);
                        if ($canUpgrade) {
                            header('location: ' . Config::get('documentroot') . 'manage/subscription');
                            die();
                        } else {*/
                            header('location: ' . Config::get('documentroot') . 'manage/alert');
                            die();
                        //}
                    }
                }   
			}
			
			// TODO: Google Analytics
			// TODO: Remove Open Sans not using
			$documentroot = Config::get('documentroot');
			$jquery = Config::get('live') ? 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js' : ($documentroot . 'js/jquery.min.js');
			?>
	<title><?php echo $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,700,300,600,800,400' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/grid.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/keyframes.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/styles.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/tipsy.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/fontello.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/animation.css"><!--[if IE 7]><link rel="stylesheet" href="<?php echo $documentroot; ?>css/fontello-ie7.css"><![endif]-->

	<script src="<?php echo $jquery; ?>"></script>
	<script src="<?php echo $documentroot; ?>js/main.js"></script>
	<script src="<?php echo $documentroot; ?>js/modal.js"></script>
	<script src="<?php echo $documentroot; ?>js/input.js"></script>
	<script src="<?php echo $documentroot; ?>js/jquery.tipsy.js"></script>
	<script src="<?php echo $documentroot; ?>js/navbar.js"></script>
	<script src="<?php echo $documentroot; ?>js/element.js"></script>
	<script src="<?php echo $documentroot; ?>js/sequence.js"></script>
	<script>
	Sequence.setBreakpoints(768, 992, 1200);
	$(document).ready(function(){
		//$('[data-toggle="tooltip"][data-html="true"]').tipsy({html: true, gravity: 's'});
		
		$('[data-toggle="tooltip"][data-gravity="w"]').tipsy({/*fade: true, */gravity: 'w'});
		$('[data-toggle="tooltip"][data-gravity="n"]').tipsy({/*fade: true, */gravity: 'n'});
		$('[data-toggle="tooltip"][data-gravity="e"]').tipsy({/*fade: true, */gravity: 'e'});
		$('[data-toggle="tooltip"]').tipsy({/*fade: true, */gravity: 's'});
		
	});
	</script>
			<?php
		}
		
		public static function header($n = 0) {
			$documentroot = Config::get('documentroot');
			$page = '&page=' . self::pageURL();
			?>
	<div id="header">
			<div class="login-types">
			<?php if(!Config::get('live')) { ?>
				Quick Dev Login: 
				<a href="<?php echo $documentroot . 'login-as?level=' . FLUENCY_GAMES_ADMIN .
					$page; ?>">FG Admin</a>
				<a href="<?php echo $documentroot . 'login-as?level=' . EDUCATIONAL_ADMIN .
					$page; ?>">Edu Admin</a>
				<a href="<?php echo $documentroot . 'login-as?level=' . TEACHER .
					$page; ?>">Teacher</a>
				<a href="<?php echo $documentroot . 'login-as?level=' . TEACHER_ADMIN .
					$page; ?>">Teacher Admin</a>
				<a href="<?php echo $documentroot . 'login-as?level=' . PARENT_GUARDIAN .
					$page; ?>">Parent</a>
			<?php } ?>
			<br />
			<br />
			<br />
			<br />
			<?php
				$user = User::getCurrentUser();
				if ($user->isValid())
					echo 'Logged in as ' . $user->getDisplayUsername() . ' | <a href="' . $documentroot . 'login-as">Home</a>';
				else
					echo 'Not Logged in.'
			?>
		</div>
		<div class="container">
			<a href="<?php echo $documentroot; ?>home">
				<div id="logo"></div>
			</a>
		</div>
		<div id="navbar">
			<?php
				// TODO: Show different navbar based on logged in status
				if (User::loggedIn())
				{
			?>
			<div class="tiny-title">
				<span class="icon-menu"></span>
				<span class="title"></span>
			</div>
			<div class="overlay"></div>
			<?php
				}
			?>
			<div class="container">
			<?php
				// TODO: Show different navbar based on logged in status
				if (User::loggedIn())
				{
					$userType = User::getCurrentUser()->getColumn('UserType');
			?>
				<span class="icon-menu"></span>
				<ul>
					<div class="left">
						<a href="<?php echo $documentroot; ?>manage/index">
							<li<?php echo ($n == 1) ? ' class="selected"' : ''; ?>>
								<span class="icon-home"></span>Home
							</li>
						</a>
						<?php
							if($userType & EDUCATIONAL_ADMIN) {
						?>
							<a href="<?php echo $documentroot; ?>manage/teachers">
								<li<?php echo ($n == 3) ? ' class="selected"' : ''; ?>>
									<span class="icon-user"></span>Teachers
								</li>
							</a>
						<?php
							}
						?>
						<?php
							if($userType & EDUCATIONAL_ADMIN || $userType & TEACHER || $userType & TEACHER_ADMIN || $userType & PARENT_GUARDIAN) {
						?>
						<a href="<?php echo $documentroot; ?>manage/rosters">
							<li<?php echo ($n == 4) ? ' class="selected"' : ''; ?>>
								<span class="icon-th-list"></span>Rosters
							</li>
						</a>
						<?php
							}
						?>
						<?php
							if($userType & TEACHER || $userType & TEACHER_ADMIN || $userType & PARENT_GUARDIAN) {
						?>
							<a href="<?php echo $documentroot; ?>manage/students">
								<li<?php echo ($n == 5) ? ' class="selected"' : ''; ?>>
									<span class="icon-users"></span>Students
								</li>
							</a>
						<?php
							}
						?>
						
					</div>
					<div class="right">
						<?php
							global $search;
							if ($search) {
						?>
						<input type="text" style="width: auto; padding: 3px 5px;" id="search-input" placeholder="Search <?php echo $search; ?>" />
						<a style="display: inline-block; padding: 0px 3px; font-size: 15px">
							<span class="icon-arrows-cw" id="search-cancel" data-toggle="tooltip" title="Reset"></span>
						</a>
						<?php
							}	
						?>
						<a href="<?php echo $documentroot; ?>settings/index.php">
							<li<?php echo ($n == 2) ? ' class="selected"' : ''; ?>>
								<span class="icon-cog"></span>Settings
							</li>
						</a>
						<a href="<?php echo $documentroot; ?>logout">
							<li<?php /* This is definitely not correct echo ($n == 5) ? ' class="selected"' : '';*/ ?>>
								<span class="icon-logout"></span>Log out
							</li>
						</a>
					</div>
				</ul>
			<?php
				}
			?>
			</div>
		</div>
	</div>
			<?php
		}

		// 12-21-15 mse
		// change to level checking bit flags instead of absolute levels
		// allows use to have multiple levels per user
		//		
		private static function sidebarItem($url, $title, $n, $i, $level, $maxLevel = null) {
//			if ((User::getCurrentUser()->getColumn('UserType') <= $level) && (User::getCurrentUser()->getColumn('UserType') >= $maxLevel)) {
			$usr = User::getCurrentUser()->getColumn('UserType');
			if ( $usr == FLUENCY_GAMES_ADMIN || ($usr & $level) ) {
				?>
							<a href="<?php echo $url; ?>">
								<li<?php echo ($n == $i) ? ' class="selected"' : ''; ?>><?php echo $title; ?><span class="icon-right-open"></span></li>
							</a>
				<?php
			}
		}

		private static function sidebarAdminItem($url, $title, $n, $i) {
				?>
					<a href="<?php echo $url; ?>">
						<li<?php echo ($n == $i) ? ' class="selected"' : ''; ?>><?php echo $title; ?><span class="icon-right-open"></span></li>
					</a>
				<?php

		}

		public static function sidebarManage($n) {
			?>
				<div class="col-xs-12 col-sm-4 col-lg-3" sq-for='help-card,overview,switch-license'>
					<div class="card sidebar center-xs" sq-order="0">
						<div class="head">
							Navigation
						</div>
						<ul>
							<?php
								$i = 0;
								self::sidebarItem('../manage/index', 'Home', $n, ++$i, EDUCATIONAL_ADMIN | TEACHER_ADMIN );
								self::sidebarItem('subscription', 'Manage Subscription', $n, ++$i, EDUCATIONAL_ADMIN | TEACHER_ADMIN | PARENT_GUARDIAN );
								self::sidebarItem('teachers', 'Manage Teachers', $n, ++$i, EDUCATIONAL_ADMIN);
								self::sidebarItem('rosters', 'Manage Rosters', $n, ++$i, TEACHER | TEACHER_ADMIN | EDUCATIONAL_ADMIN);
								self::sidebarItem('students', 'Manage Students', $n, ++$i, TEACHER | TEACHER_ADMIN | PARENT_GUARDIAN);
								self::sidebarItem('snapshot', 'Student Snapshot', $n, ++$i, EDUCATIONAL_ADMIN | TEACHER | TEACHER_ADMIN | PARENT_GUARDIAN);
							?>
						</ul>
					</div>
				</div>
			<?php
		}
		
		public static function sidebarSettings($n) {
			?>
				<div class="col-xs-12 col-sm-4 col-lg-3">
					<div class="card sidebar center-xs">
						<div class="head">
							Navigation
						</div>
						<ul>
							<?php
								$i = 0;
								self::sidebarItem('../settings/index', 'General', $n, ++$i, UNRESTRICTED);
								self::sidebarItem('password', 'Password', $n, ++$i, UNRESTRICTED);
								self::sidebarItem('teacher-options', 'Teacher Options', $n, ++$i, TEACHER | TEACHER_ADMIN);
							?>
						</ul>
					</div>
				</div>
			<?php
		}
		
		public static function sidebarAdmin($n) {
			?>
				<div class="col-xs-12 col-sm-4 col-lg-3">
					<div class="card sidebar center-xs">
						<div class="head">
							Navigation
						</div>
						<ul>
							<?php
								$i = 0;
								self::sidebarAdminItem('../manage/fg', 'Overview', $n, ++$i);
								self::sidebarAdminItem('create', 'Create License', $n, ++$i);
								self::sidebarAdminItem('licenses', 'Manage Licenses', $n, ++$i);
							?>
						</ul>
					</div>
				</div>
			<?php
		}
		
		public static function switchLicenseForm($order) {
			// Don't show the form if they only have one license...
			if (count(User::getCurrentUser()->getLicenses()) <= 1)
				return;
			?>
					<div class="card" sq-id="switch-license" sq-order="<?php echo $order; ?>">
						<div class="head center">
							Switch License
						</div>
						<div class="body">
							<select id="license-select">
								<?php
									$user = User::getCurrentUser();
									$licenses = $user->getLicenses();
									$i = 0;
									foreach ($licenses as $license)
									{
										$currentLicense = $user->getLicenseData();
										if ($currentLicense['LicenseKey'] == $license['license']) {
								?>
								<option value="<?php echo $i++; ?>" selected="selected"><?php echo $license['license']; ?></option>
								<?php
										} else {
								?>
								<option value="<?php echo $i++; ?>"><?php echo $license['license']; ?></option>
								<?php
										}
									}
								?>
							</select>
							<div id="license-loading" class="uploading">
								<div class="info-wrapper">
									<div class="info">
										<span class="icon-cw"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						registerOnChange("#license-select", function(e) {
							license = $(e).val();
							$("#license-loading").addClass("active");
							sendAjax({
								url: "php/ajax/switch-license",
								data: {
									license: license
								},
								success: function(result) {
									if (result['success']) {
										refresh('');
									} else {
										alert(result['error']);
									}
								},
								error: function(result) {
									alert("AN ERROR?!");
									console.log(result);
								},
							});
						});
					</script>
			<?php
		}
		
		// TODO: If col == col-xs-12 then show extended version perhaps
		public static function footer() {
			?>
	<div class="footer">
		<div class="container">
			<span class="left">
				Site Created by <a target="_BLANK" href="http://www.brethudson.com">Bret Hudson</a>. &copy; <?php echo date("Y"); ?> Fluency Games. All rights reserved.
			</span>
			<span class="right">
				<a href="http://fluency-games.com/privacy-policy/">Privacy Policy</a> | 
				<a href="http://fluency-games.com/terms-of-use/">Terms of Use</a> | 
				<a href="http://fluency-games.com/contactus/">Contact Us</a> |
				<?php echo ' ' . Config::get('version'); ?>
			</span>
			<div class="clear"></div>
		</div>
	</div>

			<?php
		}
		
		public static function PrintHeader($showPrintButton) {
			?>
			<div class="header">
				<div class="container">
					<br/>
					<?php if($showPrintButton) { ?>
						<style type="text/css" >
							@media print { #print-button { display:none; visibility: hidden; } }
						</style>
						<div align="right">
							<input id="print-button" type="button" value="Print This Page" onClick="window.print();" />
						</div>
					<?php } ?>
				</div>
				<div class="container">
					<span class="center">
						<h1>Student Progress Report</h1>
					</span>
					<div class="clear"></div>
				</div>
			</div>

			<?php
		}
		
		public static function PrintFooter() {
			?>
			<div class="footer">
				<div class="container">
					<span class="left">
						Report Generated on <?php echo date("l, F dS, Y"); ?>.
					</span>
					<div class="clear"></div>
				</div>
			</div>

			<?php
		}
		
		public static function productSelectInput($products) {
			if($products & 0x01) { ?> <option value="1" >Addition Blocks</option> <?php }
			if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option> <?php }
			if($products & 0x04) { ?> <option value="4" >Percent Bingo</option> <?php }
			if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option> <?php }
			if($products & 0x10) { ?> <option value="16">Integer Blocks</option> <?php }
			?> <option value="128">Facts Assessment</option> <?php
		}
		
	}

?>