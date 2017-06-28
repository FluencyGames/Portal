<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Student.class.php");

	$documentroot = Config::get('documentroot');
	$products = Config::get('products');
	
	$student = Student::getCurrentStudent();
	$user = User::getCurrentUser();
	$productId = $student->getCurrentProduct();
	
	$license = $user->getLicenseData();
	
	$teacherDomain = $license['DomainSuffix']; 
	$teacherID = $user->getColumn('Id');
	$groupName = $user->getColumn('Groups');
	
	if(!Student::isLoaded()) {
		echo 'Unable to load student data for ' . $student->getDisplayName();
		die();
	}
	
?>


<!DOCTYPE html>
<html>
<head>
	<?php Element::head("Fluency Games User Portal"); ?>

	<script src="<?php echo $documentroot; ?>js/math.js"></script>	
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>	
	<script src="<?php echo $documentroot; ?>js/Chart.js"></script>	
	<script src="<?php echo $documentroot; ?>js/Chart.min.js"></script>	
	<script src="<?php echo $documentroot; ?>js/moment.min.js"></script>
	<script src="<?php echo $documentroot; ?>js/reports/process-report.js"></script>
	<script src="<?php echo $documentroot; ?>js/reports/prepare-report.js"></script>
	
	<script type="text/javascript">
	
	var theStudent = {  reportId: "student-0",
						name:	 "<?php echo $student->getDisplayName(); ?>",
						product:  <?php echo $student->getCurrentProduct(); ?>,
						productName: "<?php echo $products[ $student->getCurrentProduct()]; ?>",
						games: [],
						progressData: [],
                        summary: {},
                        progress: {}
					};
	
	
	
	$(window).ready(function() {	
		var fileToLoad = "<?php echo $documentroot . Config::get('mediaroot') . 'reports/reports-product-' . $productId . '.htm'; ?>";
       
        processGameData( theStudent, '<?php echo $student->getGameData(); ?>');
        processProgressData(theStudent, '<?php echo $student->getProgressData(); ?>')
        
		$.ajax({url:fileToLoad, 
				context: document.body,
				success: function(response){
					reportHeader(true);
					reportBody(theStudent);
					$(".report-body > .container").html(response);		
			    	prepareReport(theStudent.reportId, theStudent['summary']);
					prepareReport(theStudent.reportId, theStudent['progress']);
					prepareGraphs(theStudent.reportId, theStudent);
					reportFooter();
				},
				error: {},
		});	
		
		
	});
	
	</script>	
	
</head>
<body>
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/print.css" >

<!--
	<div class="report-body" >
		<br/>
		<h1>STUDENT NAME</h1>
		<h2>PRODUCT NAME</h2>
		<div class="container" id="student"></div>
		<br/>
	</div>
-->


</body>
</html>