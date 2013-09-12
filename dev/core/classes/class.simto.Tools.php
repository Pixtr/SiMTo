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

	//Prepares filename replacing wrong letters with dot
	public static function prepareFileName($filename,$lowered = false)
	{
		//Replace accent characters, forien languages
		$search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô',
						'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 
						'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 
						'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 
						'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 
						'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 
						'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 
						'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 
						'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 
						'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 
						'ǿ');
		$replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O',
						 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e',
						 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a',
						 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E',
						 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 
						 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N',
						 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S',
						 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
						 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u',
						 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O',
						 'o');
		$filename = str_replace($search, $replace, $filename);
		
		//Replace common characters
		$search = array('&', '£', '$');
		$replace = array('', '', '');
		$filename= str_replace($search, $replace, $filename);
		
		//Remove - for spaces and union characters
		$find = array(' ', '&', '\r\n', '\n', '+', ',', '//');
		$filename = str_replace($find, '.', $filename);
		
		//Delete and replace rest of special chars
		$find = array('/[^A-Za-z0-9_\-]/');
		$replace = array('.');
		$filename = preg_replace($find, $replace, $filename);
		
		//Replace more than one dot with one dot
		$filename = preg_replace('/\.{2,}/', '.', $filename);
		
		//Replace dot on start of string
		$filename = preg_replace('/^\./', '', $filename);
		
		//Replace dot on end of string
		$filename = preg_replace('/\.$/', '', $filename);
		
		if($lowered)
			$filename = strtolower($filename);
		
		return $filename;
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