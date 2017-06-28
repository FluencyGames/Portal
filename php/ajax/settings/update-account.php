<?php
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$user = User::getCurrentUser();
	if ($user->isValid()) {
		$response = $user->updateAccount($_POST);
	} else
		$response = array('success' => false, 'error' => null);
	echo json_encode($response, JSON_NUMERIC_CHECK);
?>