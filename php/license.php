<?php
require('lic_constants.php');

function license_make_prefix($username)
{
	$_prefix = string_lettersdigits($username);
	
	if(strlen($_prefix)>10) {
	    $n = rand(0, strlen($_prefix)-10);
		$_prefix = substr($_prefix, $n, 10);
	} else {
	    $_prefix = str_pad($_prefix, 10, string_lettersdigits($username));
	}
	
	return $_prefix;
}



//
//  license_checksum_calc()
//
function license_checksum_calc($str)
{
	$cksum = 0;
	
	for($i=0; $i<strlen($str); $i++)
		$cksum += ord(substr($str, $i, 1));
	
	return $cksum % 36;
}

//
//  license_encrypt()
//
function license_encrypt($license)
{
 	GLOBAL $LETTERSDIGITS;
	GLOBAL $FGENCRYPTION;

	$encrypt = "";
	
	if(strlen($license)!=14) {
		echo "1, license_ecrypt: invalid license string: " . $license . "<br>";
	    return "";
	}

	$key = rand(0,strlen($FGENCRYPTION));
	$encrypt = substr($FGENCRYPTION, $key, 1);
	
	for($i=0;$i<strlen($license);$i++) {
	    $k = strpos($LETTERSDIGITS, substr($license,$i,1));
		if($k===FALSE)
		    return $encrypt;
		$iencrypt = ($key+1+$k+1+$i+1) % 36;
		$encrypt .= substr($FGENCRYPTION, $iencrypt, 1);
	}
	
	$cksum = license_checksum_calc($encrypt);
	//trace("license_encrypt", "cksum=" . strval($cksum));

	$encrypt .= substr($FGENCRYPTION, $cksum, 1);
	
	return $encrypt;
}


//
//  license_make_key()
//
function license_make_key($username, $type, $level, $products)
{
	$license = "";

	$license = license_make_prefix($username) . strval($type) . strval($level) . dec_to_hex(intval($products),2);
	
	trace("license_make_key", "license=" . $license);
	return license_encrypt($license);
}

?>

