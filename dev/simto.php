<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Version: 0.9.1
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(basename($_SERVER['PHP_SELF']) == 'simto.php') die('Restricted area! You cannot load this page directly.');


	
	
class simtoCore{
	private $cClasses = array();
	private $pClasses = array();
		
	//Store created object
	protected static $instance;
		
		
	static public function getInst($dir)
	{
		if (!simtoCore::$instance)
			simtoCore::$instance = new simtoCore($dir);
			
		return simtoCore::$instance;
	}
		
		
	protected function __construct($dir = '')
	{
		//Starts sessions
		session_start();
		
		//Sets global variable for active project root
		define('PR_ROOT',$dir);
		
		//Sets global variable for CleverPCT root
		define('SIMTO_ROOT',dirname(__FILE__));
	}
	
	//Connect basic application to active project
	public function connect()
	{
		//Joints basic setting of application
		require_once(PR_ROOT.'/config/base.defs.php');
		require_once(SIMTO_ROOT.'/config/core.defs.php');
		require_once(SIMTO_ROOT.'/core/classes/class.simto.Autoloader.php');
		
		//Default class autoloader
		spl_autoload_register(array(simtoAutoloader::getInst(), 'load'));
			
		//Default error handling
		set_error_handler(array(simtoErrorCatcher::getInst(), 'handler'));
			
		//Default exception handling
		set_exception_handler(array(simtoException::getInst(), 'handler'));
	}
		
	
	
}

?>