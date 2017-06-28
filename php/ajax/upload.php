<?php
	require_once(__DIR__ . '/../classes/FileUploader.class.php');
	require_once(__DIR__ . '/../classes/User.class.php');
	
	$response = array('success' => false, 'error' => null);
	
	if (!User::loggedIn()) {
		echo json_encode($response, JSON_NUMERIC_CHECK);
		die();
	}
	
	$user = User::getCurrentUser();
	$fileUploader = new FileUploader($_POST['file-id']);
	
	if (($fileUploader->isValidFile()) && ($fileUploader->errors() == null)) {
		// By setting part of the filename in the directory, the FileUploader will automatically give it a random filename, which is then prefixed by the end of what you supplied to setDirectory
		$fileUploader->setDirectory('uploads/temp/' . $user->getColumn('Id') . '_');
		$fileUploader->upload();
		
		$response['error'] = $fileUploader->errors();
		if ($response['error'] == null) {
			$response['success'] = true;
			$response['filename'] = $fileUploader->getFilename();
		}
	} else {
		$response['error'] = $fileUploader->errors();
	}
	
	echo json_encode($response, JSON_NUMERIC_CHECK);
	
	/*
	require_once('../classes/FileUploader.class.php");
	require_once("../classes/User.class.php");
	require_once("../classes/UserService.class.php");
	
	$userService = UserService::getInstance();
	$user = $userService->getCurrentUser();
	$fileUploader = new FileUploader($_GET["file-id"]);
	
	$success = false;
	$error = null;
	/*
	if (($user->isValid()) && ($userService->checkCookies()))
	{
		if (($fileUploader->isValidFile()) && ($fileUploader->errors() == null))
		{
			$fileUploader->setDirectory("media/temp/" . $user->getAttribute("id") . "_");
			$fileUploader->upload();
			$success = true;
		}
		else
			$error = $fileUploader->errors();
	}
	else
		$error = "Unexpected error. Please refresh and try again";
	
	echo json_encode(array('success' => $success,
						   'error' => $error,
						   'url' => $fileUploader->getFilename()));*/
?>