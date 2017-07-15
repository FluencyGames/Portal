<?php
	define('FLUENCY_GAMES_ADMIN', 0);
	define('EDUCATIONAL_ADMIN', 1);
	define('TEACHER', 2);
	define('TEACHER_ADMIN', 4);
	define('PARENT_GUARDIAN', 8);
	define('UNRESTRICTED', 15);

    ///////////////////////////////////////////////
    // 
    // changing root locations: These files need to updated
    //
    // init.php
    //      - domain global variable
    //      - documentroot global variable
    //
    // main.js
    //      - getAbsoluteUrl function
    //
    // student-settings.js
    //      - iFrameToggle function
    //
	
	//
	// for local install
	//
	//define('T_CONTACTS', 'contacts');
	//define('T_LICENSES', 'userlicenses');
	//define('T_GAMEDATA', 'gamedata');
	//define('T_STUDENTS', 'students');
	
	// 
	// for online install
	//
	define('T_CONTACTS', 'Contacts');
	define('T_LICENSES', 'UserLicenses');
	define('T_GAMEDATA', 'GameData');
	define('T_STUDENTS', 'students');
	
	$GLOBALS['config'] = array(
		'mysql' => array(
			'host' => '127.0.0.1',
			
			// for local install
			//'username' => 'root', // function_fluency
			//'password' => 'hatepit9',
			//'db' => 'fluencyg_license' // function_fluency
			
			// for on-line install
			'username' => 'fluencyg_admin', 
			'password' => 'FGdata@',
			'db' => 'fluencyg_license'
		),
		
		// for local install
		//'domain' => 'http://fg/',
		//'documentroot' => '/dev/portal/',
		//'shopfile' => 'shop-local.json',
		
		// for on-line install
		//'domain' => 'https://www.fluency-games.com/',
		'domain' => 'https://localhost',
		//'documentroot' => '/playground/dev/portal/',
		//'documentroot' => '/portal/',
		'documentroot' => '/dev/portal/',
		'shopfile' => 'shop-live.json',
		
		'mediaroot' => '/media/',
		'shoproot' => '/xcart/',

		'live' => false,
		'maintenance' => false,
		'version' => '1.1.060617 beta',
		
		'url' => array(
			
		),
		
		'email' => array(
			'host' => 'mail.fluency-games.com', // Can we use 127.0.0.1 here?
			'port' => 465,           // 587?
			'auth' => true,
			'smtp-security' => 'ssl',
			'smtp-user' => 'marty.esterman@fluency-games.com',
			'smtp-password' => 'Hatepit99',
			'Support' => array(
				'address' => 'support@fluency-games.com',
				'name' => 'Fluency Games Support',
			),
			'Fluency Games' => array(
				'address' => 'support@fluency-games.com',
				'name' => 'Fluency Games Support',
			),
			
		),
		
		'products' => array(
			0 => 'No Product',
			1 => 'Addition Blocks',
			2 => 'Multiplication Blocks',
			4 => 'Percent Bingo',
			8 => 'Subtraction Blocks',
			16 => 'Integer Blocks',
			255 => 'All Products'
		),
		
		'error' => array(
		    'result' => false,
			'id' => -1,
			'text' => 'no error',
			'link' => '',
		),
        
        'recaptcha' => array(
            'sitekey' => '6LcCZCQUAAAAABoofuC0KPNYZBVMUKHh89SRevMV',
            'secret' => '6LcCZCQUAAAAAPog2iTE5yM2QxIesbeOGoazjVSj'
        )
	);
?>