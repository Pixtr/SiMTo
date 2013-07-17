<?php
/* 2013 CleverPCT - Clever Project Control Tool
 * 
 * 
 * @author: clever-sites.net
 * @license: Proprietary software license
 */

//Page restriction
if(basename($_SERVER['PHP_SELF']) == 'clever.Core.php') die('Restricted area! You cannot load this page directly.');


	
	
class cleverCore{
	private $cClasses = array();
	private $pClasses = array();
		
	//Store created object
	protected static $instance;
		
		
	static public function getInst($dir)
	{
		if (!cleverCore::$instance)
			cleverCore::$instance = new cleverCore($dir);
			
		return cleverCore::$instance;
	}
		
		
	protected function __construct($dir = '')
	{
		//Starts sessions
		session_start();
		
		//Sets global variable for active project root
		define('PR_ROOT',$dir);
		
		//Sets global variable for CleverPCT root
		define('CPCT_ROOT',dirname(__FILE__));
	}
	
	//Connect basic application to active project
	public function connect()
	{
		//Joints basic setting of application
		require_once(PR_ROOT.'/core/base.defs.php');
		require_once(CPCT_ROOT.'/core/core.defs.php');
		require_once(CPCT_ROOT.'/core/classes/class.clever.Autoloader.php');
		
		//Default class autoloader
		spl_autoload_register(array(cleverAutoloader::getInst(), 'load'));
			
		//Default error handling
		set_error_handler(array(cleverErrorCatcher::getInst(), 'handler'));
			
		//Default exception handling
		set_exception_handler(array(cleverException::getInst(), 'handler'));
	}
		
	
	
}

?>