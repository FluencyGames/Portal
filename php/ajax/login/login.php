<?php
	require_once(__DIR__ . '/../../classes/User.class.php');
	
	$response = User::login($_POST);
	echo json_encode($response, JSON_NUMERIC_CHECK);
?>