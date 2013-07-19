<?php

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

class simtoExceptionCore extends Exception{
	//Stored instance of class object
	protected static $inst;
	
	public function __construct(){
		
	}
	
	public static function getInst()
	{
		if (!simtoException::$inst)
			simtoException::$inst = new simtoException();
		
		return simtoException::$inst;
	}
	
	public function handler($number,$message,$fatality = false){
		
	}
}
?>