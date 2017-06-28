<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = array('success' => true);
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$amount = $_POST['amount'];
	
	$data = array($license['NumUsers'] + $amount, $license['LicenseKey']);
	$query = "UPDATE ' . T_LICENSES . ' SET NumUsers = ? WHERE LicenseKey = ?";
	$response['success'] = !Database::getInstance()->query($query, $data)->error();
	
	echo json_encode($response);
?>