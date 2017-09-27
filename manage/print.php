<?php
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	$documentroot = Config::get('documentroot');
	
	$user = User::getCurrentUser();
	
	$teacherOrStudent = $_GET['type'];
	$link = ($teacherOrStudent == 'students') ? 'rosters' : 'teachers';
	
	$LNameStr = '';
	$numItems = 0;
	switch ($teacherOrStudent) {
		case 'teachers': {
			$LNameStr = 'LName';
			$orderBy = "ORDER BY {$LNameStr} ASC, Fname ASC";
			$items = $user->getTeachers($orderBy);
			
			$numItems = count($items);
		} break;
		
		case 'students': {
			$LNameStr = 'Lname';
			$orderBy = "ORDER BY {$LNameStr} ASC, Fname ASC";
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
	<title>Fluency Games | Print <?php echo ucfirst($teacherOrStudent); ?></title>
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
			background-color: white;
		}
		
		body {
			margin: 0px auto;
			width: 670px;
			background-color: white;
			min-height: 100%;
			word-wrap: break-word;
		}
		
		button {
			margin: 20px auto;
			display: block;
			padding: 9px 32px;
			font-size: 13px;
			font-weight: 600;
			font-family: 'Open Sans', sans-serif;
			background-color: #7E3E97;
			border: 0px;
			outline: 0px;
			color: #F8F8F8;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			text-transform: uppercase;
			-webkit-transition: background-color 0.2s;
			transition: background-color 0.2s;
			border-bottom: 3px solid #3E0961;
		}

		form button {
			margin-bottom: 12px;
		}

		button:hover {
			background-color: #C753F4;
			border-bottom: 3px solid #8E4EA7;
		}
		
		@media all {
			.page-break	{ display: none; }
		}

		@media print {
			#return-button {
				display: none;
			}
			
			.page-break	{ display: block; page-break-before: always; }
			
			.page-break:last-child {
				page-break-before: auto;
				page-break-after: auto;	
			}
			
			.names {
				padding: 15px;
			}
			
			.names .row {
				height: 130px;
			}
		}
	</style>
	<script type="text/javascript">
		window.onload = function() { window.print(); }
	</script>
</head>
<body>
	<a id="return-button" href="<?php echo $link; ?>">
		<button>RETURN TO <?php echo strtoupper($link); ?></button>
	</a>
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
			<div class="col-xs-6"><?php print($item['Username']); ?></div>
			<!--<div class="col-xs-3">FOUR</div>-->
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