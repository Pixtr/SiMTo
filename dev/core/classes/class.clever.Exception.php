<?php

class cleverExceptionCore extends Exception{
	//Stored instance of class object
	protected static $inst;
	
	public function __construct(){
		
	}
	
	public static function getInst()
	{
		if (!cleverException::$inst)
			cleverException::$inst = new cleverException();
		
		return cleverException::$inst;
	}
	
	public function handler($number,$message,$fatality = false){
		
	}
}
?>