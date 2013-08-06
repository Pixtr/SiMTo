<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
* Author: CleverOn Group
* Proprietary software license
* All rights reserved for CleverOn Group
*/

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

class simtoTranslatorCore implements simtoICore
{
	//Stored instance of class object
	protected static $inst;
	
	//Active language
	protected $active_lang;
	
	//Word category
	protected $word_cat = array();
	
	//Language dictionary
	protected $dictionary = array();
	
	
	public function __construct()
	{
		$this->setLang();
		$this->loadCache($this->active_lang);
	}
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoTranslator::$inst)
			simtoTranslator::$inst = new simtoTranslator();
	
		return simtoTranslator::$inst;
	}
	
	//Set active language
	public function setLang($lang = '')
	{
		if(empty($lang))
		{
			session_start();
			if(isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) && $this->inLang($_REQUEST['lang']))
				$this->active_lang = $_REQUEST['lang'];
			elseif(isset($_SESSION['lang']) && !empty($_SESSION['lang']) && $this->inLang($_SESSION['lang']))
				$this->active_lang = $_SESSION['lang'];
			elseif(isset($_COOKIE['lang']) && !empty($_COOKIE['lang']) && $this->inLang($_COOKIE['lang']))
				$this->active_lang = $_COOKIE['lang'];
			elseif($this->inLang($this->browserLang()))
				$this->active_lang = $this->browserLang();
			else 
				$this->active_lang = PR_LANG;
		}
		elseif(inLang($lang))
			$this->active_lang = $lang;
		else 
			$this->active_lang = PR_LANG;
	}
	
	//Find browser prefered language
	public function browserLang()
	{
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		return $lang;
	}
	
	//Check if language is in instaled languages
	public function inLang($lang)
	{
		$i = 0;
		$lang_xml = simplexml_load_file(SIMTO_ROOT.DS.'config'.DS.'base.sett.xml');
		foreach($lang_xml->languages->language->prefix as $prefix)
			if($prefix == $lang)
				$i = 1;
		
		if($i == 1)
			return true;
		else
			return false;
	}
	
	//Translates segments of text from dictionary
	public function translate($string, $id = '', $cat = '')
	{
		$this->createWord($string, debug_backtrace() , $cat);
	}
	
	
	
	//Translates segments of text from database
	public function translateDb($id)
	{
		
	}
	
	//Checks if word is in dictionary and returns it
	protected function inDc($id = '', $cat = '')
	{
		
	}
	
	//Loads cache dictionary to variable
	protected function loadCache($lang)
	{
		$dictionary = array();
		$cache = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'dictionary.'.$lang.'.php';
		
		if(file_exists($cache))
			include($cache);
		elseif($this->createCache($lang))
			include($cache);
		else 
			return false;
		
		$this->dictionary = $dictionary;
		return true;
	}
	
	
	//Creates cache dictionary file
	protected function createCache($lang)
	{
		$cache = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'dictionary.'.$lang.'.php';
		
		$dictionary = array();
		
		$files = $this->fileList(PR_ROOT.DS.'langs'.DS.$lang.DS);
		foreach($files as $file)
		{
			if(file_exists($file))
				include($file);
		}
		
		$this->dictionary = $dictionary;
	}
	
	//Returns list of files in folder
	protected function fileList($dir)
	{
		$list = array();
		$files = scandir($dir);
		foreach($files as $file)
		{
			if(is_dir($dir.$file))
				$list = array_merge($list,$this->fileList($dir.$file));
			elseif(substr($file, -4) == '.php')
				$list = array_push($list,$dir.$file);
		}
		
		return $list;
	}
	
	//Adds dictionary sheet to cache dictionary
	protected function addDc($lang,$file)
	{
		
	}
	
	//Creates dictionary sheet for category and language
	protected function createDc($lang, $dictionary)
	{
		
	}
	
	//Create new word in dictionary
	protected function createWord($string, $conditions = array(), $cat = '')
	{
		$lastcon = end($conditions);
		$file_path = $lastcon['file'];
		$line_num = $lastcon['line'];
		$id = hash('crc32',$line_num).hash('crc32',$string);
		
		if(empty($this->word_cat))
			$this->word_cat = array('other');
		
		$cats = '';
		if(!empty($cat))
			$cats = explode('/',$cat);
		
		$word_cat = $this->word_cat;
		array_push($word_cat,$cats);
		
		$dictionary = array();
		foreach($word_cat as $key)
			$dictionary =& $dictionary[$key];
		
		$dictionary[$id] = $string;
	}
	
	//Sets word category
	public function wordCategory($string)
	{
		$cats = explode('/',$string);
		$this->word_cat = $cats;
	}
	
}

?>