<?php
	// 12-30-15 mse
	// changed updating/adding 'Email' for teacher to 'group'
	
	// 8-21-16 mse
	// added encoding to teacher names
	
	require_once(__DIR__ . '/../../classes/Config.class.php');
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	function failure($db) {
		$db->rollback();
		$err = $db->errorInfo();
		$response['success'] = false;
		$response['error'] = $err[2] . ' (' . $err[1] . ')'; //'An unknown error occurred, please try again. If the problem persists, try refreshing the page.';
		echo json_encode($response);
		die();
	}
	

	$response = array('success' => false, 'error' => null, 'duplicates' => '');
	
	$db = Database::getInstance();
	$db->beginTransaction();
	
	$user = User::getCurrentUser();
	$license = $user->getColumn("license");
	
	if (isset($_POST['added'])) {
		$insertQuery = 'INSERT INTO ' . T_CONTACTS . ' (UserType, Username, Fname, LName, Groups, license) VALUES (2, ?, ?, ?, ?, ?)';
//		$user->encodeFields($_POST['added'], array('Username', 'First name', 'Last name'));
		
		foreach ($_POST['added'] as $teacher) {
			$user->encodeFields($teacher, array('Username', 'First name', 'Last name'));
			$db->query($insertQuery, array($teacher['Username'], $teacher['First name'], $teacher['Last name'], $teacher['Group'], $license));
			if ($db->error())
				failure($db);
		}
	}

	if (isset($_POST['edited'])) {
		$updateQuery = 'UPDATE ' . T_CONTACTS . ' SET Username = ?, Fname = ?, LName = ?, Groups = ? WHERE Id = ? AND license = ?';
//		$user->encodeFields($_POST['edited'], array('Username', 'First name', 'Last name'));
		
		foreach ($_POST['edited'] as $teacher) {
			$user->encodeFields($teacher, array('Username', 'First name', 'Last name'));
			$db->query($updateQuery, array($teacher['Username'], $teacher['First name'], $teacher['Last name'], $teacher['Group'], $teacher['Id'], $license));
			if ($db->error())
				failure($db);
		}
	}

	if (isset($_POST['deleted'])) {
		// TODO: Also disassociate students (?) - ask Marty
		
		$deleteQuery = 'DELETE FROM ' . T_CONTACTS . ' WHERE Id = ? AND license = ?';
		
		foreach ($_POST['deleted'] as $teacher) {
			$db->query($deleteQuery, array($teacher['Id'], $license));
			if ($db->error())
				failure($db);
		}
	}

	$db->commit();
	
	$response['success'] = true;
	
	echo json_encode($response);
?>