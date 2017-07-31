<?php
	require_once(__DIR__ . '/php/classes/Element.class.php');
	require_once(__DIR__ . '/php/classes/User.class.php');
	
	if (isset($_GET['level'])) {
		$data = array();
		
		User::logout();
		switch ($_GET['level']) {
				case FLUENCY_GAMES_ADMIN:
					$data['username'] = 'FluencyGames';
					$data['password'] = '';
					break;
				case EDUCATIONAL_ADMIN:
					$data['username'] = 'Admin';
					$data['password'] = '';
					break;
				case TEACHER:
					//$data['username'] = 'bhudson';
					$data['username'] = 'BBaker@cobbk12.com';
					$data['password'] = 'hatepit9';
					break;
				case TEACHER_ADMIN:
					$data['username'] = 'AGarcia@cobbk12.com';
					$data['password'] = '';
					break;
				case PARENT_GUARDIAN:
					$data['username'] = 'aalda';
					$data['password'] = '';
					break;
		}
		
		$result = User::login($data);
		print_r($result);
	}
	
	if (isset($_GET['page']))
		header('location: ' . $_GET['page']);
	else
		Element::redirectUsersToHome();
?>