<?php
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = array('success' => false, 'error' => 'User not found');
	$user = User::getCurrentUser();
	$response = $user->sendLicenseToNewUser($_REQUEST['userEmail'],$_REQUEST);
	echo json_encode($response, JSON_NUMERIC_CHECK);
?>