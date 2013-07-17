<?php

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

class cleverErrorCatcherCore implements cleverICore
{
	//Stored instance of class object
	protected static $inst;
	
	//Path to folder with stored error logs
	protected $errorlog_folder = '';
	
	//Prefix for errorlog files
	CONST errpx = 'clerrlog_';
	
	
	//Set basic of error reporting
	protected function __construct()
	{
		//Block standart php displaying
		ini_set('display_errors', 1);
		
		//Find and set error log folder where are all errors stored
		if(PR_ID && PR_ID != 'core')
			$logs_xml = simplexml_load_file(CPCT_ROOT.DS.'projects'.DS.PR_ID.DS.'base.sett.xml');
		else
			$logs_xml = simplexml_load_file(CPCT_ROOT.DS.'core'.DS.'base.sett.xml');
		
		$this->errorlog_folder = $logs_xml->errorlog[0];
		
		//Set error level
		switch(PR_ERRLVL)
		{
			case '0':
				//Turn off all error reporting
				error_reporting(0);
			break;
			
			case '1':
			default:
				//Report all PHP errors
				error_reporting(E_ALL);
			break;
			
			case '2':
				//To report uninitialized variables or catch variable name misspellings
				error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
			break;
			
			case '3':
				// Report simple running errors
				error_reporting(E_ERROR | E_WARNING | E_PARSE);
			break;
			
			case '4':
				// Report only fatal errors
				error_reporting(E_ERROR);
			break;
		}
	}
	
	
	//Get instance object from class
	public static function getInst()
	{
		if (!cleverErrorCatcher::$inst)
			cleverErrorCatcher::$inst = new cleverErrorCatcher();
	
		return cleverErrorCatcher::$inst;
	}
	
	
	//Handle the error
	public function handler($errno, $errstr, $errfile, $errline)
	{
		
		//TODO: Vytvořit vlastní zobrazování chyb pomocí layoutů
		//TODO: Nastavení úrovně zobrazení pro uživatele
		
		
		//Error displaying
		if(PR_ERRSHOW)
			ini_set('display_errors', 1);
		else
			ini_set('display_errors', 0);
		
		
		//TODO: Ukládání chyb do externího souboru
		
		
		
		//TODO: Oznámení o chybě super administrátorovi
		
	}
	
	//Generate message to show user
	protected function displayToUser($errno)
	{
		$error_display = array();
		switch(PR_UERRLVL)
		{
			case '0':
			default:
				//Don't show anything to user
				$error_display['msg'] = '';
				$error_display['show'] = false;
				break;
					
			case '1':
				//Show all PHP errors to user (only for debugging!)
				$error_display['msg'] = 'Error occured: '.$errstr;
				$error_display['show'] = true;
				break;
					
			case '2':
				//To report uninitialized variables or catch variable name misspellings
				switch($errno)
				{
					case E_ERROR :
						$error_display['show'] = true;
						break;
							
					case E_WARNING :
						$error_display['show'] = true;
						break;
							
					case E_PARSE :
						$error_display['show'] = true;
						break;
							
					case E_NOTICE :
						$error_display['show'] = true;
						break;
				}
				error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
				break;
					
			case '3':
				// Report simple running errors
				error_reporting(E_ERROR | E_WARNING | E_PARSE);
				break;
					
			case '4':
				// Report only fatal errors
				error_reporting(E_ERROR);
				break;
		}
	}
}
?>