<?php

//Page restriction
if(!defined('PR')) die('Restricted area! You cannot load this page directly.');

class simtoExceptionCore extends Exception{
	//Stored instance of class object
	protected static $inst;
	
	//XML object with cached exceptions
	protected $list;
	
	//All exceptions prepared for print
	public $result = array();
	
	public function __construct(){
		$this->list = new simtoXML(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'exceptions.list.php');
	}
	
	public static function getInst()
	{
		if (!simtoException::$inst)
			simtoException::$inst = new simtoException();
		
		return simtoException::$inst;
	}
	
	public function handler($message,$id = '',$cat = ''){
		if(empty($id))
		{
			
		}
		else
		{
			
			
		}
		
	}
	
	//Creates cached list of exceptions
	protected function createCache()
	{
		
	}
	
}
?>