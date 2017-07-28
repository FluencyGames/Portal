<?php
	require_once(__DIR__ . "/../../classes/Database.class.php");
	require_once(__DIR__ . '/../../classes/Student.class.php');
	
	// Response
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		$response['error'] = 'Not logged in';
		echo json_encode($response);
		die();
	}
	
	if (!isset($_POST['product'])) {
		$response['error'] = 'No product sent';
		echo json_encode($response);
		die();
	}
	
	$db = Database::getInstance();
	$product = $_POST['product'];
	
	// Get the students
	$students = User::getCurrentUser()->getStudents();
	
	$response['students'] = array();
	foreach ($students as $student) {
		$id = $student['Id'];
		$s = new Student($student['Username'], $product);
		$response['students'][$id] = $s->getGameData();
	}
	
	$response['success'] = true;
	echo json_encode($response);
?>