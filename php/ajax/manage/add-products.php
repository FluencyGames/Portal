<?php
	require_once(__DIR__ . '/../../classes/Emailer.class.php');
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		echo json_encode($response);
		die();
	}
	
	$user = User::getCurrentUser();
	
	$to = array(
		$user->getEmailArray()
	);
	
	$data = array(
		'fullname' => $user->getFullname()
	);
	
	$response['success'] = Emailer::getInstance()->send('add-product', $to, $data);
	
	echo json_encode($response)
?>