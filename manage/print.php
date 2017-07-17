<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	
	$teacherOrStudent = $_GET['type'];
	
	$LNameStr = '';
	$numItems = 0;
	switch ($teacherOrStudent) {
		case 'teachers': {
			$LNameStr = 'LName';
			$orderBy = "ORDER BY {$LNameStr} ASC";
			$items = $user->getTeachers($orderBy);
			
			$numItems = count($items);
		} break;
		
		case 'students': {
			$LNameStr = 'Lname';
			$orderBy = "ORDER BY {$LNameStr} ASC";
			$items = $user->getStudents($orderBy);
			
			// Decode all the items
			$numItems = count($items);
			for ($i = 0; $i < $numItems; ++$i) {
				$items[$i]['Fname'] = User::decode($items[$i]['Fname']);
				$items[$i][$LNameStr] = User::decode($items[$i][$LNameStr]);
				$items[$i]['Username'] = User::decode($items[$i]['Username']);
			}
			
			// Now that they're decoded, we need to sort them
			$user->sortStudents($items);
		} break;
		
		default:
			die();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Print Test</title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/grid.css">
	<style type="text/css">
		html, body {
			padding: 0px;
			margin: 0px;
			font-family: 'Open Sans', sans-serif;
			font-size: 16px;
		}
		
		html {
			height: 100%;
			background-color: #222299;
		}
		
		body {
			margin: 0px auto;
			width: 670px;
			background-color: white;
			min-height: 100%;
		}
		
		@media all {
			.page-break	{ display: none; }
		}

		@media print {
			.page-break	{ display: block; page-break-before: always; }
		}
		
		.names {
			padding: 15px;
		}
		
		.names .row {
			height: 130px;
		}
		
		.names .col-xs-3 {
			word-wrap: break-word;
		}
	</style>
</head>
<body>
	<?php
		$i = 0;
		foreach ($items as $item) {
			if ($i % 7 == 0) {
	?>
	<div class="names">
	<?php
			}
	?>
		<div class="row">
			<div class="col-xs-3"><?php print($item[$LNameStr]); ?></div>
			<div class="col-xs-3"><?php print($item['Fname']); ?></div>
			<div class="col-xs-3"><?php print($item['Username']); ?></div>
			<div class="col-xs-3">FOUR</div>
		</div>
	<?php
			if ($i % 7 == 6) {
	?>
	</div>
	<div class="page-break"></div>
	<?php
			}
			++$i;
		}
	?>
</body>
</html>