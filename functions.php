<?php
include_once('class.php');

$tr = new test();

function t($string)
{
	global $tr;
	//$backtrace = debug_backtrace();
	//print_r( $backtrace );
	
	//test::translate($string);
	$tr->translate($string);
}

function cat($string)
{
	global $tr;
	//test::cat($string);
	$tr->cat($string);
}

?>