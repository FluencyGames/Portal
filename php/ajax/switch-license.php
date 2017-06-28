<?php
	require_once(__DIR__ . '/../classes/User.class.php');
	
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		echo json_encode($response);
		die();
	}
	
	$response['success'] = User::getCurrentUser()->switchLicense($_POST['license']);
	
	echo json_encode($response);
?>