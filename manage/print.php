<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	
	$teacherOrStudent = $_GET['type'];
	
	$LNameStr = '';
	switch ($teacherOrStudent) {
		case 'teachers': {
			$LNameStr = 'LName';
			$orderBy = "ORDER BY {$LNameStr} ASC";
			$items = $user->getTeachers($orderBy);
		} break;
		
		case 'students': {
			$LNameStr = 'Lname';
			$orderBy = "ORDER BY {$LNameStr} ASC";
			$items = $user->getStudents($orderBy);
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
		$numItems = count($items);
		for ($i = 0; $i < $numItems; ++$i) {
			if ($i % 7 == 0) {
	?>
	<div class="names">
	<?php
			}
	?>
		<div class="row">
			<div class="col-xs-3"><?php print($items[$i][$LNameStr]); ?></div>
			<div class="col-xs-3"><?php print($items[$i]['Fname']); ?></div>
			<div class="col-xs-3"><?php print($items[$i]['Username']); ?></div>
			<div class="col-xs-3">FOUR</div>
		</div>
	<?php
			if ($i % 7 == 6) {
	?>
	</div>
	<div class="page-break"></div>
	<?php
			}
		}
	?>
</body>
</html>