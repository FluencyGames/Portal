<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . '/../../classes/Student.class.php');
	
	$response = array('success' => false, 'error' => null);
	
	$db = Database::getInstance();
	
	$lic = $_POST['lic'];
	$user = $_POST['username'];
	$product = $_POST['product'];
	
	$student = new Student($user, $product);
	
	// Get User and check validity
	if (!$student->isValid()) {
		$response['success'] = false;
		$response['error'] = 'User not found for selected product.';	
		echo json_encode($response);
		die();
	}

	// Set as our active student for access through the system
	$student->setCurrentStudent();
	
	$response['success'] = true;
	echo json_encode($response);
?>
