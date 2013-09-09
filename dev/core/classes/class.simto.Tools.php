<?php

//Page restriction
if(!defined('PR')) die('Restricted area! You cannot load this page directly.');

//Most common functions used in application
class simtoToolsCore{
	//Stored instance of class object
	protected static $inst;
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoTools::$inst)
			simtoTools::$inst = new simtoTools();
	
		return simtoTools::$inst;
	}
	
	//Nesting function for arrayToString
	protected static function arrayBuild($array,$first)
	{
		$string = '';
		foreach($array as $key => $value)
		{
			$first_key = '';
			if(!is_numeric($key))
				$key = "'".$key."'";
			$first_key .= $first."[".$key."]";
			
			if(is_array($value))
				$first_key = simtoTools::arrayBuild($value,$first_key);
			else
				$first_key .= " = '".$value."';\n";
			
			$string .= $first_key;
		}
		return $string;
	}
	
	//Return string from array
	public static function arrayToString($array, $varname = 'array', $format = 'build')
	{
		$string = '';
		switch ($format)
		{
			default:
			case 'build':
				foreach($array as $key => $value)
				{
					if(!is_numeric($key))
						$key = "'".$key."'";
					$first_key = '$'.$varname."[".$key."]";
					if(is_array($value))
						$first_key = simtoTools::arrayBuild($value,$first_key);
					else
						$first_key .= " = '".$value."';\n";
					
					$string .= $first_key;
				}
			break;
			
			case 'export':
				$string = var_export($array,true);
				$string = stripslashes($string);
			break;
			
			case 'seril':
				$string = serialize($array);
			break;
		}
		return $string;
	}
	
	//Escapes every aphostrophe in string
	public static function escapeAp($string)
	{
		$string = preg_replace("/'/","\'",$string);
		return $string;
	}
	
	//Escapes every quotes in string
	public static function escapeQu($string)
	{
		$string = preg_replace('/"/','\"',$string);
		return $string;
	}
	
	//Clears array but leaves keys intacted
	public static function clearArr($array)
	{
		foreach($array as $key => $value)
			if(is_array($value))
				$array[$key] = simtoTools::clearArr($value);
			else 
				$array[$key] = '';
			
		return $array;
	}

	//Prepares file path creating all needed folders
	public static function preparePath($path)
	{
		$path = str_replace("\\",DS,$path);
		$path = str_replace('/',DS,$path);
		
		$ds = strrpos($path,DS,-1);
		$dir_path = substr($path,0, $ds-strlen($path)+1);
		
		$dir_test = '';
		$i = 0;
		$test = 0;
		$ds = strpos($dir_path,DS,0);
		
		$dir_test .= substr($dir_path,0,$ds+1);
		$dir_path = substr($dir_path,$ds+1);
		
		do
		{			
			$error = '';
			if(file_exists($dir_test))
			{
				if(empty($dir_path))
					$test = 1;
				$ds = strpos($dir_path,DS,0);
				$dir_test .= substr($dir_path,0,$ds+1);
				$dir_path = substr($dir_path,$ds+1);
				
			}
			else
			{
				if(!mkdir($dir_test))
					//TODO: exception
					$error = $dir_test;
			}
			
			$i++;
		}
		while($test < 1 && $i<20);
		
		if(empty($error))
			return $path;
		else 
			return false;
	}

	//Saves data to file by creating it or rewriting it
	public static function saveToFile($file,$data){
		simtoTools::preparePath($file);
		
		if($handler = fopen($file, 'c'))
		{
			$start_time = microtime();
			
			do
			{
				$locker = flock($handler, LOCK_EX);
				if(!$locker) usleep(round(rand(0, 100)*1000));
			}while ((!$locker) && ((microtime()-$start_time) < 1));
			
			if($locker)
			{
				ftruncate($handler,0);
				$fw = fwrite($handler,$data);
				flock($handler, LOCK_UN);
				fclose($handler);
				
				if($fw === false)
					return false;
			}
			else 
			{
				fclose($handler);
				return false;
			}
		}
		else
			return false;
		
		return true;
	}	

}