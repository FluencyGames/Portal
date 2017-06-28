<?php
	require_once(__DIR__ . '/../../classes/Student.class.php');
	
	$response = array('success' => false, 'error' => null);
	$id = $_POST['id'];
	$product = $_POST['product'];
	Student::setCookies($id, $product);
	$response['success'] = true;
	echo json_encode($response);
?>
