<?php
	require_once(__DIR__ . "/../../classes/Config.class.php");
	require_once(__DIR__ . "/../../classes/Element.class.php");
	require_once(__DIR__ . "/../../classes/User.class.php");
	
	// Response
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		echo json_encode($response);
		die();
	}
	
	// Get the current user
	$user = User::getCurrentUser();
	$response['users'] = array();
	
	if ($_POST['requesting'] == 'teachers') {
		$response['users'] = $user->getTeachers('ORDER BY LName ASC, Fname ASC', 'Id, Fname, LName, Groups, Username');
		foreach( $response['users'] as &$teacher) {
			$user->decodeFields($teacher, array('Fname', 'LName', 'Groups', 'Username'));
		}
	}
	else if ($_POST['requesting'] == 'students') {
		$response['users'] = $user->getStudents('ORDER BY Lname ASC, Fname ASC', 'Id, Fname, Lname, GroupName, Username, Products', $_POST['groupName']);;
		foreach( $response['users'] as &$student) {
			$user->decodeFields($student, array('Fname', 'Lname', 'Username'));
		}
	}
	
	$response['success'] = true;
	echo json_encode($response);
?>