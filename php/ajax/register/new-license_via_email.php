<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
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

	// LicenseKey

	// Insert license
	//$query = 'INSERT INTO userlicenses (LicenseKey, Type, System, StartDate, EndDate, NumUsers, Products, DomainSuffix, PurchaseSource, PurchaseData) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	//$licenseData = array($licenseKey, $_POST['licenseType'], $system, $startDateStr, $endDateStr, $seats, $_POST['productValue'], $domainSuffix, $purchaseSource, $purchaseData);
	//$result = $db->query($query, $licenseData)->result();
	
	// Insert contact
	//$query = 'INSERT INTO contacts (license, Username, UserType, Groups, Email) VALUES (?, ?, ?, ?, ?)';
	//$contactData = array($licenseKey, $_POST['username'], $userType, $_POST['group'], $_POST['email']);
	//$result = $db->query($query, $contactData)->result();
	
	$mail = new PHPMailer;
	$config = $GLOBALS['config'];
	$email = $config['email'];
	$from = $email['support'];
	
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = $email['host'];						  // Specify main and backup SMTP servers
	$mail->SMTPAuth = $email['auth'];                     // Enable SMTP authentication
	$mail->Username = $email['smtp-user'];                // SMTP username
	$mail->Password = $email['smtp-password'];            // SMTP password
	$mail->SMTPSecure = $email['smtp-security'];          // Enable TLS encryption, `ssl` also accepted
	$mail->Port = $email['port'];                         // TCP port to connect to

	$mail->setFrom($acct_email, 'Fluency Games Subscription Request');
	$mail->addAddress($from['address']);     				// Add a recipient
	
	$mail->isHTML(true);                                  	// Set email format to HTML

	$mail->Subject = 'License Purchase';
	
	$body =  '<h3><b>Purchase Request made on ' . date('D m-d-Y h:i A', time()) . '</b></h3>';
	if($licenseType==HOME) 		$body = $body . '<p>License Type: HOME</p>'; else $body = $body .'<p>License Type: SCHOOL</p>';
	if($licenseType==SCHOOL) 	$body = $body . '<p>School System: ' . $schoolName . '</p>';
	if($subscription==YEARLY) 	$body = $body . '<p>Subscription Type: YEARLY</p>'; else $body = $body .'<p>Subscription Type: UNIVERSAL</p>';
	$body = $body . '<p>Product Code: ' . $productValue . '</p>';
	$body = $body . '<p>Username: ' . $acct_username . '</p>';
	$body = $body . '<p>Email: ' . $acct_email . '</p>';
	$body = $body . '<p>Account Type: ' . $acct_type . '</p>';
	$body = $body . '<p>License Key : ' . $licenseKey . '</p>';
	$body = $body . '<p>Start date: ' . $startDateStr . '</p>';
	$body = $body . '<p>End date: ' . $endDateStr . '</p>';
	$body = $body . '<p>Seats: ' . $seats . '</p>';

	//$mail->MsgHTML($body);
	$mail->Body = $body;
	$mail->AltBody = $body;
	
	if(!$mail->send()) {
	    //echo 'Message could not be sent.';
	    //echo 'Mailer Error: ' . $mail->ErrorInfo;
		$response['success'] = false;
		$response['error'] = $mail->ErrorInfo;
	} else {
	    //echo 'Message has been sent';
		$response['success'] = true;
	}
	
	echo json_encode($response);
?>