<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = array('success' => true);
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$amount = $_POST['years'];
	
	$renewalDate = max(strtotime($license['EndDate']), time());
	$newEndDate = strtotime('+' . $amount . ' year', $renewalDate);
	$newEndDateStr = date('Y-m-d 00:00:00', $newEndDate);
	
	if ($renewalDate == time()) {
		// TODO: Perhaps reset start date as the new date? Then how do I know if it's renewal or not???
	}
	
	$data = array($newEndDateStr, $license['LicenseKey']);
	$query = "UPDATE ' . T_LICENSES . ' SET EndDate = ? WHERE LicenseKey = ?";
	$response['success'] = !Database::getInstance()->query($query, $data)->error();
	
	echo json_encode($response);
?>