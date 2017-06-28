<?php
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	echo json_encode(User::createFromLicense($_POST['licenseKey']));
?>