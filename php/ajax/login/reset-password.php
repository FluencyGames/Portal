<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = array('success' => false, 'error' => null);
	
	$db = Database::getInstance();
	$code = $_POST['Code'];
	
	// Get User and check validity
	$user = new User($_POST['Email']);
	if (!$user->isValid()) {
		$response['error'] = 'Email not linked to a user';
		echo json_encode($response);
		die();
	}
		
	// Get the row in the passwordreset table
	$query = 'SELECT * FROM passwordreset WHERE Email = ? AND Code = ?';
	if ($db->query($query, array($user->getColumn('Email'), $code))->count() == 0) {
		$response['error'] = "['code']))->count() == 0";
		echo json_encode($response);
		die();
	}
	
	// Make sure the column matches the current User
	$row = $db->firstResult();
	if ($row['Id'] != $user->getColumn('Id')) {
		$response['error'] = "$user->getColumn('Id')";
		echo json_encode($response);
		die();
	}
	
	// TODO: Compare the date
	$dateOld = false;
	if ($dateOld) {
		$response['error'] = 'Password reset requests expire after 24 hours.';
	} else {
		// Update the password
		$response = $user->updatePassword($_POST, false);
	}
	
	// Remove the row in the passwordreset
	$query = 'DELETE FROM passwordreset WHERE Code = ?';
	$db->query($query, array($code));
	
	// Return the response
	echo json_encode($response);
?>