<?php
	require_once(__DIR__ . "/../../classes/Config.class.php");
	require_once(__DIR__ . "/../../classes/Element.class.php");
	require_once(__DIR__ . "/../../classes/User.class.php");
	
	// Response
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		echo json_encode($response);
		die();
	}
	
	// Get the current user
	$user = User::getCurrentUser();
	
	$response['licenses'] = array();
	
	if ($_POST['requesting'] == 'Licenses')
		$response['licenses'] = $user->getLicenseInfo('ORDER BY LicenseKey ASC');
	else if ($_POST['requesting'] == 'LicenseKey')
		$response['licenses'] = $user->getLicenseInfo('ORDER BY LicenseKey ASC', $_POST['LicenseKey']);
	
	$response['success'] = true;
	
	echo json_encode($response);
?>