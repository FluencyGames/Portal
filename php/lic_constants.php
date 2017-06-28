<?php
global $LETTERSDIGITS;
global $FGENCRYPTION;
global $DEBUG;

$LETTERSDIGITS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
$FGENCRYPTION =  "V7U3S5GMJ9K40Q261PCIX8YEOBHWLRDZTFAN";
$DEBUG=0;

function trace($fn, $dbg)
{
	if($GLOBALS['DEBUG'])
		echo $fn . " : " . $dbg . "<br>";
}

function string_lettersdigits($string)
{
 	GLOBAL $LETTERSDIGITS;
		
	$str = strtoupper($string);
	$len = strlen($string);
	$new_str = "";
	
	for($i=0; $i<$len; $i++) {
		$letter = substr($str, $i, 1);
		if(stripos($LETTERSDIGITS, $letter)!==FALSE) {
		    $new_str .= $letter;
		}
	}
	
	return $new_str;
}

function dec_to_hex($num, $places)
{
	return strtoupper(str_pad(dechex($num), $places, '0', STR_PAD_LEFT));
}

?>
