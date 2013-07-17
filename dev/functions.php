<?php
	/* Clever Project Control Tool
 	 * Basic functions crucial for right function of application
	 * Author: clever-sites.net
	 */

	//Page restriction//////////////////////////////////////////////
	if (basename($_SERVER['PHP_SELF']) == 'functions.php') {
		die('Restricted area! You cannot load this page directly.');
	};
	////////////////////////////////////////////////////////////////
	
	
	//Load definitions
	if(file_exists($file = '/core/defs.php')){
		include_once($file);
	}else{
		header('Location: error.php?custom=102');
		die;
	}
	
	//Load error handeling
	if(file_exists($file = '/core/errors.php')){
		include_once($file);
	}else{
		header('Location: error.php?custom=103');
		die;
	}
	
	//Load array of application plugins
	if(file_exists($file = CPCT_ROOT.'/core/plugs.cfg')){
		$cpct_plugs = unserialize($file);
	}else{
		header('Location: error.php?custom=4');
		die;
	}

	//Autoload classes with help of array
	function __autoload($class_name){
		global $cpct_plugs;
		global $project_plugs;
		
		if(isset($cpct_plugs[$class_name])){
			if(file_exists($cpct_plugs[$class_name])){
				include_once($cpct_plugs[$class_name]);
			}else{
				throw new Exception('Core class file is missing('.$class_name.').');
			}
		}elseif(isset($project_plugs[$class_name])){
			if(file_exists($project_plugs[$class_name])){
				include_once($project_plugs[$class_name]);
			}else{
				throw new Exception('Project class file is missing('.$class_name.').');
			}
		}else{
			throw new Exception('Class '.$class_name.' is missing from plugin list.');
		}
	}

?>