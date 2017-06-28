<?php
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
	
	// TODO: Add WHERE license = ? to every query!!
	
	$response = array('success' => false, 'error' => null, 'duplicates' => '');
	
	$db = Database::getInstance();
	$db->beginTransaction();
	
	$user = User::getCurrentUser();
	$license = $user->getColumn("license");
 	$k = 0;
	$duplicates = array();

	//
	// save added users first
	// check for duplicate usernames on this license
	// and remove
	//
	if (isset($_POST['added'])) {
	    $students = $_POST['added'];
		//$user->encodeFields($students, array('Username', 'First name', 'Last name'));
		
		//
		// check for duplicate names
		// 
		foreach ($students as $student) {
			$user->encodeFields($student, array('Username', 'First name', 'Last name'));
			$result = User::queryStudent($student['Username'], $license);
			if($result != null) {
				array_push($duplicates, $student['Username']);
				unset($students[$k]);
			} else {
			    $k++;
			}
		}

		//
		// Save students to database
		//
		$insertQuery = 'INSERT INTO ' . T_STUDENTS . ' (Username, Fname, Lname, LicenseKey, GroupName, Products) VALUES (?, ?, ?, ?, ?, ?)';
		foreach ($students as $student) {
			$user->encodeFields($student, array('Username', 'First name', 'Last name'));
			$product_code = intval($student['Products']);
			$db->query($insertQuery, array($student['Username'], $student['First name'], $student['Last name'], $license, $student['Group'], $product_code ));
			if ($db->error()) failure($db);
		}
	}

	//
	// update edited users
	//
	if (isset($_POST['edited'])) {
		$updateQuery = 'UPDATE ' . T_STUDENTS . ' SET Username = ?, Fname = ?, Lname = ?, GroupName = ? WHERE Id = ? AND LicenseKey = ?';
		$user->encodeFields($_POST['edited'], array('Username', 'First name', 'Last name'));
		
		foreach ($_POST['edited'] as $student) {
			$db->query($updateQuery, array($student['Username'], $student['First name'], $student['Last name'], $student['Group'], $student['Id'], $license));
			if ($db->error()) failure($db);
		}
	}
	
	if (isset($_POST['deleted'])) {
		//$strQuery = 'DELETE FROM str WHERE student = ?';
		$deleteQuery = 'DELETE FROM ' . T_STUDENTS . ' WHERE Id = ? AND LicenseKey = ?';
		$dataQuery = 'DELETE FROM ' . T_GAMEDATA . ' WHERE LicenseKey = ? and Username = ?';
		
		foreach ($_POST['deleted'] as $student) {
			//$db->query($strQuery, array($student['Id']));
			//if ($db->error()) failure($db);
			
			$db->query($deleteQuery, array($student['Id'], $license));
			if ($db->error()) failure($db);
			
			// added 12-10-15 mse delete user data from GameData database as well
			$db->query($dataQuery, array($license, $student['Username']));
			if ($db->error()) failure($db);
		}
	}
	
	//
	// Save to database
	//
	$db->commit();
	if ($db->error()) failure($db);

	$response['success'] = true;
	if(!empty($duplicates)) {
	    $str = '';
	    foreach($duplicates as $duplicate)
	    	$str = $str . 'Username already used: ' . $user->decode($duplicate) . ' Not added. \r\n';
		$response['duplicates'] = $str;
	}
	
	echo json_encode($response);
?>