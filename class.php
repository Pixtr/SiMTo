<?php
class test
{
	static $cat;
	
	static function translate($string)
	{
		$backtrace = debug_backtrace();
		print_r( $backtrace );
		echo $string.'<br>'."\n";
		echo test::$cat.'<br>'."\n";
	}
	
	static function cat($string)
	{
		test::$cat = $string;
	}
}

?>