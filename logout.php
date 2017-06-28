<?php
	require_once(__DIR__ . "/php/classes/Config.class.php");
	require_once(__DIR__ . "/php/classes/User.class.php");
	require_once(__DIR__ . "/php/classes/Student.class.php");
	
	User::logout();
	Student::logout();
	
	header('location: ' . Config::get('documentroot'));
?>