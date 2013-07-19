<?php

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');


class simtoAutoloader
{
	//Stored instance of class object
	protected static $inst;
	
	//Complete list of classes
	private $class_list = array();
	
	//List of folders where are classes stored
	public $class_folders = array();
	
	//Path to cached list of classes
	private $cache_list = '';
	
	
	//Load list of classes
	protected function __construct()
	{
		$this->cache_list = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'class.list.php';
		
		if (file_exists($this->cache_list))
			$this->class_list = include($this->cache_list);
		else
			$this->createList();
	}
	
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoAutoloader::$inst)
			simtoAutoloader::$inst = new simtoAutoloader();
	
		return simtoAutoloader::$inst;
	}
	
	
	//Load classes when they are needed
	public function load($classname)
	{
		if(!isset($this->class_list[$classname]) 
		|| (isset($this->class_list[$classname]) && !is_file($this->class_list[$classname])) 
		|| (isset($this->class_list[$classname.'Core']) && !is_file($this->class_list[$classname.'Core'])))
		{
			$this->createList();
		}
		
		//Check if it is core class or not
		if(substr($classname, -4) != 'Core')
		{
			//Check is class exists
			if (!isset($this->class_list[$classname]) || (isset($this->class_list[$classname]) && !is_file($this->class_list[$classname])))
			{	
				//Check if exists reflecting Core class
				if(isset($this->class_list[$classname.'Core']) && is_file($this->class_list[$classname.'Core']))
				{
					require($this->class_list[$classname.'Core']);
					
					//Virtualy create new class of Core class extension
					$temp_class = new ReflectionClass($classname.'Core');
					eval(($temp_class->isAbstract() ? 'abstract ' : '').'class '.$classname.' extends '.$classname.'Core {}');
				}
				else
				{
					//If class and Core class does not exists, throw and exception
					//throw new simtoException('Class '.$classname.' does not exists and do not have Core class.','simtoAutoloader_CLASS',true);
				}
			}
			else
			{
				//Load a required class
				require_once($this->class_list[$classname]);
			}
		}
		elseif(isset($this->class_list[$classname]) && is_file($this->class_list[$classname]))
		{
			//Load a required Core class
			require_once($this->class_list[$classname]);
		}
		else
		{
			//If Core class does not exists, throw and exception
			//throw new simtoException('Class '.$classname.' does not exists.','simtoAutoloader_CORECLASS',true);
		}
	}
	
	
	//Create virtual list of classes if original is missing
	protected function createList()
	{
		$classes_list = array();
		$dir_list = array();
		
		//Load list of all class folders for core
		$cdir_xml = simplexml_load_file(SIMTO_ROOT.DS.'config'.DS.'base.sett.xml');
		foreach($cdir_xml->class->dir_list->folder as $dir_path)
		{
			array_push($dir_list,$dir_path);
		}
		
		//Load list of all class folders for project
		if(PR_ID && PR_ID != 'core')
		{
			$pcdir_xml = simplexml_load_file(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'base.sett.xml');
			foreach($pcdir->class->dir_list->folder as $dir_path)
			{
				array_push($dir_list,$dir_path);
			}
		}
		
		//Look trought all folder on list and return list of classes merged into one array
		foreach($dir_list as $dir)
		{
			$classes_list = array_merge($classes_list,$this->listFromDir($dir));
		}
		ksort($classes_list);
		
		//Save list to file and cache it
		$this->cacheList($classes_list);
		
		//Load list to variable
		$this->class_list = $classes_list;
	}
	
	
	//Get all classes from folder
	protected function listFromDir($dir)
	{
		$classes = array();
		
		if(strlen($dir)>0)
		{
		$files = scandir($dir);
		foreach($files as $file)
		{
			if($file[0] != '.')
			{	
				if(is_dir($dir.$file))
				{
					$classes = array_merge($classes, $this->listFromDir($dir.$file.DS));
				}
				elseif (substr($file, -4) == '.php')
				{	
					$content = file_get_contents($dir.$file);
					$pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>[a-z][a-z0-9_]*(Core)?)'
							.'(\s+extends\s+[a-z][a-z0-9_]*)?(\s+implements\s+[a-z][a-z0-9_]*(\s*,\s*[a-z][a-z0-9_]*)*)?\s*\{#i';
					if (preg_match($pattern, $content, $m))
					{
						$classes[$m['classname']] = $dir.$file;
					}
				}
			}
		}
		}
		
		return $classes;
	}

	
	//Backup list of classes to cache
	protected function cacheList($data = array())
	{
		$content = '<?php return '.var_export($data,true).'; ?>';
		$content = stripslashes($content);
		
		$cache = $this->cache_list;
		if((file_exists($cache) && !is_writable($cache)) || !is_writable(dirname($cache)))
		{
			header('HTTP/1.1 503 temporarily overloaded');
			die($cache.' is not writable, please give write permissions (chmod 666) on this folder and file.');
		}
		else
		{
			// In order to be sure that this file is correctly written, a check is done on the file content
			$loop_protection = 0;
			do
			{
				
				$check = false;
				file_put_contents($cache, $content, LOCK_EX);
				if ($loop_protection++ > 10)
					break;
		
				// If the file content end with PHP tag, integrity of the file is ok
				if (preg_match('#\?>\s*$#', file_get_contents($cache)))
					$check = true;
			}
			while (!$check);
			
			if (!$check)
			{
				file_put_contents($cache, '<?php return array(); ?>', LOCK_EX);
				header('HTTP/1.1 503 temporarily overloaded');
				die('Your file '.$cache.' is corrupted. Please remove this file, a new one will be regenerated automatically.');
			}
		}
	}
}