<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . '/../../classes/Shop.class.php');
	require_once(__DIR__ . '/../../license.php');
	require_once(__DIR__ . '/../../services/PHPMailer/PHPMailerAutoload.php');

	// license types
	define('HOME', 0);
	define('SCHOOL', 1);
	
	// subscription types
	define('YEARLY', 1);
	define('UNIVERSAL', 9);
	
	// user type
	define('USERTYPE_ADMIN', 1);
	define('USERTYPE_TEACHER', 2);
	define('USERTYPE_TEACHER_ADMIN', 4);
	define('USERTYPE_HOME', 8);
	
	$useHtml=true;
	
	// Init
	$response = array('success' => false, 'error' => null, 'post' => $_POST);
	$db = Database::getInstance();
	$shop = Shop::getInstance();
	
	// Parse things
	$licenseType = -1;
	$subscription = -1;
	$productValue = 0;
	$seats = 0;
	$acct_username = '';
	$acct_type = '';
	$acct_email = '';
	$schoolName = '';
	$groupName = '';

	if(isset($_POST['licenseType'])) 	$licenseType = intval($_POST['licenseType']);
	if(isset($_POST['subscription'])) 	$subscription = intval($_POST['subscription']);
	if(isset($_POST['productValue'])) 	$productValue = intval($_POST['productValue']);
	if(isset($_POST['numSeats']))		$seats = intval($_POST['numSeats']);
	if(isset($_POST['username'])) 		$acct_username = $_POST['username'];
	if(isset($_POST['adminType'])) 		$adminType = intval($_POST['adminType']);
	if(isset($_POST['email'])) 			$acct_email = $_POST['email'];
	if(isset($_POST['group']))			$groupName = $_POST['group'];
	if(isset($_POST['schoolName'])) 	$schoolName = $_POST['schoolName'];
	if(isset($_POST['adminType'])) 		$adminType = intval($_POST['adminType']);

	// Create the license key
	$licenseKey = license_make_key($acct_username, $subscription, $licenseType, $productValue);
	
	// Get the system name
	$system = ($licenseType == HOME) ? 'HOME' : $schoolName;
	
	// Dates
	$startDate = time();
	$endDate = $startDate;
	if ($subscription == YEARLY)
		$endDate = strtotime('+365 days', $startDate);
	else if ($licenseType == UNIVERSAL)
		$endDate = strtotime('+100 year', $startDate);
	
	// Date strings
	$startDateStr = date('Y-m-d 00:00:00', $startDate);
	$endDateStr = date('Y-m-d 00:00:00', $endDate);
	
	// Number of seats
	$seats = ($licenseType == HOME) ? 6 : numSeats;
	
	// Domain suffix
	$domainSuffix = '';
	
	// Purchase info
	$purchaseSource = 'FGPortal';
	$purchaseData = '';

	//
	// add seats to order
	//
	if($licenseType == 'SCHOOL' && $seats > 0) {
		$prodName = '';
		if($seats > 1000) 		$prodName = 'SEATS-1K-PLUS';
		else if($seats>=500) 	$prodName = 'SEATS-1K';
		else if($seats>=250)    $prodName = 'SEATS-500';
		else if($seats>=100)    $prodName = 'SEATS-250';
		else                    $prodName = 'SEATS-99';
	}
	
	echo json_encode($response);
?>