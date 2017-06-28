<?php
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	// TODO: Check to make sure it's an email and not a username (if it is, prompt the user to enter the email maybe?)
	
	$response = array('success' => false, 'error' => 'User not found');
	
	$user = new User($_POST['Email']);
	if ($user->isValid()) {
		$response = $user->sendPasswordReset();
	}
	
	echo json_encode($response, JSON_NUMERIC_CHECK);
?>