<?php
header("Access-Control-Allow-Origin: *");
require_once('license.php');

$mysql_server = "localhost"; //This is probably correct
$mysql_username = "root";    // Your MySQL username
$mysql_password = "root";        // Your MySQL Password
$mysql_table = "userlicenses"; // Your actual table to hold the data

//$mysql_username = "fluencyg_admin";    // Your MySQL username
//$mysql_password = "FGdata@";        // Your MySQL Password
//$mysql_table = "UserLicenses"; // Your actual table to hold the data

$mysql_database = "fluencyg_license";     // The name of your database

// Make a MySQL Connection no changes need to be made here
$con = mysql_connect($mysql_server,$mysql_username,$mysql_password) or die('1,Can not connect to server');
mysql_select_db($mysql_database) or die('1,Can not connect to database');

$lic = "";
$action = "NOP";
$appId = "";

if(isset($_REQUEST['action']))
	$action = $_REQUEST['action']; 

if(isset($_REQUEST['lic']))
	$lic = mysql_real_escape_string($_REQUEST['lic']);

if(isset($_REQUEST['appId']))
	$appId = mysql_real_escape_string($_REQUEST['appId']);
	
function create_contact($con, $mysql_table, $lic)
{
	$username = isset($_REQUEST['username'])?$_REQUEST['username']:"";
	$userlevel = isset($_REQUEST['userlevel'])?$_REQUEST['userlevel']:"";
	$email = isset($_REQUEST['email'])?$_REQUEST['email']:"";
	$groups = isset($_REQUEST['groups'])?$_REQUEST['groups']:"";
	$pwd = isset($_REQUEST['pword'])?$_REQUEST['pword']:"";

	$pwd = crypt($pwd, "Qr");
	
	if($username!="") {
		mysql_query("INSERT INTO Contacts" .
					"(license, UserType, Username, PWord, Email, Groups) " .
					"VALUES('$lic','$userlevel','$username', '$pwd', '$email', '$groups') ")
		or die("1," . mysql_error()); // Something went wrong
			
	}
	
	//echo "0"; //All is right with the world. YAY.
	mysql_close($con);

}

function verify_unique($con, $mysql_table)
{
	$table = isset($_REQUEST['table']) ? $_REQUEST['table'] : 'Contacts';
	$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : '';
	$value = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';
	
	$response = array('success' => true, 'error' => null );
	
	if(empty($field) || empty($value)) {
		$response['error'] = 'Unable to process, empty values.';
		$response['success'] = false;
	} else {
		$result = mysql_query("SELECT COUNT(*) FROM $table WHERE ($field = '$value')")
		    or die("1," . mysql_error());

		$row = mysql_fetch_array($result);
		if($row!=null && intval($row['COUNT(*)']) > 0) {
			$response['error'] = 'Duplicate ' . $field . 's found.';
			$response['success'] = false;
		}
	}
	
	echo json_encode($response);
	mysql_close($con);

}

function create_license($con, $mysql_table, $lic)  //declare function Part between () is all the variables you will need for this function.
{
 	$purchase_data = "";

	$type = $_REQUEST['userType'];
	$edu_inst = $_REQUEST['sys'];
	$users = $_REQUEST['users'];
	$prodId = $_REQUEST['products'];
	$dom_suffix = $_REQUEST['suffix'];
	$purchase_src = $_REQUEST['purchase'];
	
    if(isset($_REQUEST['pur_data'])) $purchase_data = $_REQUEST['pur_data'];
	
	$start_d = date_create_from_format( 'n/j/Y', $_REQUEST['start_date']);
	$end_d = date_create_from_format( 'n/j/Y', $_REQUEST['end_date']);
			
	$start = date_format($start_d, 'Y-m-d');
	$end = date_format($end_d, 'Y-m-d');
	
	mysql_query("INSERT INTO $mysql_table" .
				"(LicenseKey, Type, System, StartDate, EndDate, NumUsers, Products, DomainSuffix, PurchaseSource, PurchaseData) " .
				" VALUES('$lic','$type','$edu_inst', '$start', '$end','$users','$prodId','$dom_suffix', '$purchase_src', '$purchase_data') ")
	or die("1," . mysql_error()); // Something went wrong
	
	//echo "0"; //All is right with the world. YAY.
	mysql_close($con);
}

function delete_license($con, $mysql_table, $lic)
{
	mysql_query("DELETE FROM $mysql_table WHERE (LicenseKey='$lic')") or die("1," . mysql_error()); // Something went wrong

	echo "0"; //All is right with the world. YAY.
	mysql_close($con);

}

function request($con, $mysql_table)
{
	$type = $_REQUEST['type'];
	$edu_inst = $_REQUEST['sys'];
	$users = $_REQUEST['users'];
	$prodId = $_REQUEST['products'];
	$enddate = $_REQUEST['enddate'];
	$dom_suffix = $_REQUEST['suffix'];
	$purchase_src = $_REQUEST['purchase'];
	$pur_data_data = $_REQUEST['pur_data'];

	echo "1";
}

function update($con, $mysql_table, $lic)
{
    $sql_update = "";
	$what = "";
	$data = "";
	
 	if(isset($_REQUEST['what']))
  		$action = $_REQUEST['what']; 
  
  	if(isset($_REQUEST['data']))
  		$username = mysql_real_escape_string($_REQUEST['data']);	
 	
	if($what == "" || $data == "") {
		echo "1,No data Sent";
	} else {
  	  mysql_query("UPDATE $mysql_table SET " . $what . "='" .$data . "'  WHERE LicenseKey = '$lic'")
	  or die("1," . mysql_error());
	
	  echo "0";
	  mysql_close($con);
	}
}
// This function will pull account information
function validate($con, $mysql_table, $lic, $appId)
{
    $valid = FALSE;
	
	$result = mysql_query("SELECT Type, System, NumUsers, EndDate, Products, DomainSuffix FROM $mysql_table WHERE (LicenseKey = '$lic')")
	or die("1," . mysql_error());
	
	while($row = mysql_fetch_array($result)) {
	   $valid = true;
       $productId = intval($row['Products']);
       if($productId & intval($appId) == 0)
          echo "1,License does not include this product";
       else
       	  echo "0," . $row['DomainSuffix'] . "," . $row['EndDate'] . "," . $row['System'] . "," . $row['Type'] . "," .  $row['NumUsers'];
	}
	if(!$valid)
	  echo "1,License Key Not Found";

	mysql_close($con);
}

function generate_license()
{
	$usr = $_REQUEST['username'];
	$length = $_REQUEST['length'];
	$products = $_REQUEST['products'];
	$usr_type = $_REQUEST['userType'];
	
	$lic = license_make_key($usr, $length, $usr_type, $products);
	
	if(!empty($lic))
	    echo $lic;
	else 		 
	    echo "1,Unable to generate license: " . $lic;

}

// This function is used for nothing more than testing that the script and database are working. 
function connection_test($con, $mysql_table, $pname)
{
	$result = mysql_query("SELECT * FROM $mysql_table WHERE p_name = '$pname' ");
	while($row = mysql_fetch_array($result))
	if ($row <= 0)
		{
		echo "1,Could not comunicate with database. Double check your settings and ask your web host for help.";
		}
	else
		{
		echo "Connection test was OK!";
		}
}

// This determines which function to call based on the $f parameter passed in the URL.
switch($action)
{
	case 'create': create_license($con, $mysql_table, $lic); break;
	case 'contact': create_contact($con, $mysql_table, $lic); break;
	case 'validate': validate($con, $mysql_table, $lic, $appId); break;
	case 'verify': verify_unique($con, $mysql_table); break;
	case 'update': update($con, $mysql_table, $lic); break;
	case 'gen': generate_license(); break;
	case 'del': delete_license($con, $mysql_table, $lic); break;
	case 'NOP': break; // loading file
	default: echo "1,error in action code";
}

?>