<?php
	require_once(__DIR__ . '/../../classes/Database.class.php');
	
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
	
 	//
	// update edited users
	//
	if (isset($_POST['edited'])) {
		$updateQuery = 'UPDATE ' . T_LICENSES . ' SET EndDate = ?, NumUsers = ?, Products = ? WHERE LicenseKey = ?';
		foreach ($_POST['edited'] as $licData) {
			$db->query($updateQuery, array($licData['EndDate'], $licData['NumUsers'], $licData['Products'], $licData['LicenseKey']));
			if ($db->error()) failure($db);
		}
	}
	
	if (isset($_POST['deleted'])) {
		$deleteQuery = 'DELETE FROM ' . T_LICENSES . ' WHERE LicenseKey = ?';
		//$dataQuery = 'DELETE FROM gamedata WHERE LicenseKey = ? and Username = ?'; // should we delete from contents as well?
		
		foreach ($_POST['deleted'] as $licData) {
			//$db->query($strQuery, array($student['Id']));
			//if ($db->error()) failure($db);
			
			$db->query($deleteQuery, array($licData['LicenseKey']));
			if ($db->error()) failure($db);
		}
	}
	
	$db->commit();
	if ($db->error()) failure($db);

	$response['success'] = true;
	echo json_encode($response);
?>