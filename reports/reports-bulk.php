<?php
	require_once(__DIR__ . "/../php/classes/Element.class.php");
	require_once(__DIR__ . "/../php/classes/Config.class.php");
	require_once(__DIR__ . "/../php/classes/User.class.php");
	require_once(__DIR__ . "/../php/classes/Student.class.php");
	
	$documentroot = Config::get('documentroot');
	$products = Config::get('products');

	$product = Student::getCookie('product');
		
	$user = User::getCurrentUser();
	
	$license = $user->getLicenseData();
	
	$teacherDomain = $license['DomainSuffix']; 
	$teacherID = $user->getColumn('Id');
	$groupName = $user->getColumn('Groups');	
	
	function getProductList($productList) {
		$str = '';
		foreach($productList as $key => $product) {
			$str = $str . '"' . $key . '": "' . $product . '", ';
		}
		return '{ ' . $str . ' }';
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
	<script src="<?php echo $documentroot; ?>js/reports/process-report.js"></script>
	<script src="<?php echo $documentroot; ?>js/reports/prepare-report.js"></script>
	
	<script type="text/javascript">
	
	var productList = <?php echo getProductList($products); ?>;
    var fileToLoad = "<?php echo $documentroot . Config::get('mediaroot') . 'reports/reports-product-' . $product . '.htm'; ?>";

	function processStudents(students) {
		console.log("processStudents");
		
		if(students==null)
			return;
			
		Object.keys(students).forEach(function(key, index) {
			var student = { };
			student['reportId'] = "student-"+index.toString();
			student['name'] = students[key]['Lname'] + ',' +students[key]['Fname'];
			student['product'] = parseInt(students[key]['Product']);
			student['productName'] = productList[ students[key]['Product'] ];
            
			processGameData( student, students[key]['TrackingData']);
			processProgressData( student, students[key]['ProgressData']);
			
			var showPrintButton = (index==0);
			
			//
			// Print header: Separator for reports
			// Show Print Button for first report only
			//
			reportHeader(showPrintButton);
			reportBody(student);
			
			//
			// Load in the Report HTML text for a specific report type from
			// print-report-#.htm (where # is the product id number)
			// then prepare the report with student data
			// and create the student graphs
			//
			$("#" + student.reportId).load(fileToLoad,'',function(responseTxt, statusTxt, xhr) {
				console.log(student);
				if(statusTxt == "success") {
			    	prepareReport(student.reportId, student.summary);
					prepareReport(student.reportId, student.progress);
					prepareGraphs(student.reportId, student);
				}
			    if(statusTxt == "error")
			        alert("Error: " + xhr.status + ": " + xhr.statusText);
			});
			
			reportFooter();
		
			
		});
		
	}
	
	$(window).ready(function() {	

		//
		// get the student the list of users first
		//	
		sendAjax({
			url: "php/ajax/manage/get-student-bulk.php",
			data: {
				lic: "<?php echo $user->getColumn('license'); ?>",
				groupName: "<?php echo $groupName; ?>",
				product: "<?php echo $product; ?>"
			},
			success: function(result) {
				console.log(result);
				if (result['success']) {
					processStudents(result['students']);
				} else {
					alert("reports-bulk.php, line 100: "+result['responseText']);
				}
			},
			error: function(result) {
				alert("reports-bulk.php, line 105: "+result['responseText']);
			},
		});			
		
		//
		// remove place holder text 
		//
		var e = document.getElementById("remove-this");
		document.getElementsByTagName('body')[0].removeChild(e);
		
	});
	
	</script>	
	
</head>
<body>
	<link rel="stylesheet" type="text/css" href="<?php echo $documentroot; ?>css/print.css" >
	
	<div id="remove-this">
		Generating Reports...
    </div>
</body>
</html>