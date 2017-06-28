<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	require_once(__DIR__ . "/../../classes/User.class.php");
	require_once(__DIR__ . '/../../classes/Student.class.php');
	
	function student_sort($a, $b) {
		$res=0;
		$A = $a['Lname'];
		$B = $b['Lname'];
		if($A<$B)		$res = -1;
		else if($A>$B)	$res =  1;
		else {
			$A = $a['Fname'];
			$B = $b['Fname'];
			if($A<$B)		$res = -1;
			else if($A>$B )	$res = 1;
			else			$res = 0;
		}
		return $res;
	}
	
	$response = array('success' => false, 'error' => null, 'students' => array() );
	
	$db = Database::getInstance();
	
	if (!User::loggedIn()) {
		$response['error'] = "Not logged in.";
		echo json_encode($response);
		die();
	}
		
	$lic = $_POST['lic'];
	$group = $_POST['groupName'];
	$product = $_POST['product'];
	
	$query = "SELECT Username FROM " . T_GAMEDATA . " WHERE (LicenseKey = ? AND GroupName = ? AND Product = ?)";
	$data = array($lic, $group, $product);
	
	$result = Database::getInstance()->query($query, $data)->result();
//	if (count($result) > 0) $response['students'] = $result;
	if (count($result) > 0) {
		foreach($result as $student) {
			$s = new Student($student['Username'], $product);
			$a = array();
			$a['Username'] = $s->getDisplayUsername();  //$student['Username'];
			$a['Lname'] = $s->getLastName();
			$a['Fname'] = $s->getFirstName();
			$a['Product'] = $product;
			$a['TrackingData'] = $s->getGameData();
			$a['ProgressData'] = $s->getProgressData();
			$response['students'][$student['Username']] = $a;
		}
	}
	
	// sort our results in alphabetical order
	uasort($response['students'],"student_sort");
	
	// no active student yet, just have a large list of them
	Student::logout();
	
	$response['success'] = true;
	echo json_encode($response);
?>
