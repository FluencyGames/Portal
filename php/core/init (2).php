<?php
	define('FLUENCY_GAMES_ADMIN', 0);
	define('EDUCATIONAL_ADMIN', 1);
	define('TEACHER', 2);
	define('TEACHER_ADMIN', 3);
	define('PARENT_GUARDIAN', 4);
	
	$GLOBALS['config'] = array(
		'mysql' => array(
			'host' => '127.0.0.1',
			'username' => 'fluencyg_admin', // function_fluency
			//'password' => 'uTTS2FnKSerPV4VG',
			'password' => 'hatepit9',
			'db' => 'fluencyg_license' // function_fluency
		),
		
		'domain' => 'http://localhost', // http://www.brethudson.com
		'documentroot' => '/playground/dev/portal/',
		
		'live' => false,
		'maintenance' => false,
		
		'url' => array(
			
		),
		
		'email' => array(
			'host' => 'localhost', // Can we use 127.0.0.1 here?
			'port' => 25,
			'auth' => false,
			
			'from' => array(
				'Fluency Games' => array(
					'email' => 'support@fluency-games.com',
					'password' => 'password',
				),
			),
		),
	);
?>