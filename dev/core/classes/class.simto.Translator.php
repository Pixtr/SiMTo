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
	
	//Static language dictionaries
	protected $dictionary = array();		//Active language dictionary
	protected $def_dictionary = array();	//Default language dictionary
	protected $simto_dictionary = array();	//Simto dictionary
	
	//Name of dynamic dictionary table
	protected $dic_table = 'tab_dictionary';
	
	//List of wrong ids in mysql dictionary table
	protected $wrong_id = array();
	
	
	//Sets active language
	public function __construct()
	{
		$this->setLang();
		if(!$this->dictionary = $this->loadCache($this->active_lang))
			$this->dictionary = array();
		if(!$this->def_dictionary = $this->loadCache(PR_LANG))
			$this->def_dictionary = array();
		if(!$this->simto_dictionary = $this->loadCache('simto'))
			$this->simto_dictionary = array();
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
		elseif($this->inLang($lang))
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
			if((string)$prefix == (string)$lang)
				$i = 1;
		
		if($i == 1)
			return true;
		else
			return false;
	}
	
	///////////////////////////////
	//Static dictionary handeling//
	///////////////////////////////
	
	//Translates segments of text from dictionary
	public function translate($string, $id = '', $cat = '')
	{
		$variables = array();
		if(preg_match('/\;\#(?<id>[A-Za-z][a-z0-9_]*?)\#/', $string, $m))
			foreach($m['id'] as $tag)
			{
				$pattern = '/\;\#'.$tag.'\#(?<var>[A-Za-z][a-z0-9_]*?)\#'.$tag.'\#\;/';
				if(preg_match($pattern, $string, $n))
				{
					$variables[$tag] = $n['var'][0];
					$string = preg_replace($pattern, ';#'.$tag.'#;',$string);
				}
			}
	
		if(empty($id))
			$id = $this->createWord($string, debug_backtrace() , $cat);
		
		$translate = $string;
		if(!$this->inDc($id, $this->addCategory($cat), 'simto'))
		{
			if(!empty($id))
				$this->addWord($string, $id, $cat);
		}
		elseif($active = $this->inDc($id, $this->addCategory($cat), 'active'))
			$translate = $active;
		elseif($default = $this->inDc($id, $this->addCategory($cat), 'default'))
			$translate = $default;
			
		foreach($variables as $tag => $var)
			$translate = preg_replace('/\;\#'.$tag.'\#\;/',$var,$translate);
		
		$translate = preg_replace('/\;\#[A-Za-z][a-z0-9_]*\#\;/','',$translate);
		
		return $translate;
	}

	//Translates segments of text from dictionary
	public function translateXML($node,$cat = '')
	{
		if(get_class($node) != 'DOMElement')
			return false;
		
		if($node->hasAttribute('langid'))
		{
			$translate = $node->textContent;
			
			$langid = $node->getAttribute('langid');
			if(empty($langid))
			{
				$langcat = $node->getAttribute('langcat');
				if(!empty($cat))
					$langcat = $cat.'/'.$langcat;
				
				if(empty($langcat))
					$langcat = 'OtherXML';
				
				$id = hash('crc32',$translate).hash('crc32',rand());
				$this->addWord($translate, $id, $langcat);
				$node->setAttribute('langid', $id);
			}
			else 
			{
				$langcat = $node->getAttribute('langcat');
				if(!empty($cat))
					$langcat = $cat.'/'.$langcat;
				
				if(empty($langcat))
					$langcat = 'OtherXML';
				
				if(!$this->inDc($langid, $langcat, 'simto'))
					$this->addWord($translate, $langid, $langcat);
				elseif($active = $this->inDc($langid, $langcat, 'active'))
					$translate = $active;
				elseif($default = $this->inDc($langid, $langcat, 'default'))
					$translate = $default;
			}
			return $translate;
		}
		else 
			return $node->textContent;
	}

	//Checks if word is in dictionary and returns it
	protected function inDc($id = '', $cat = '',$dic = 'simto')
	{
		$word_cat = $cat;
		
		switch ($dic)
		{
			default:
			case 'simto':
				$array = $this->simto_dictionary;
			break;
			
			case 'active':
				$array = $this->dictionary;
			break;
			
			case 'default':
				$array = $this->def_dictionary;
			break;
		}
		
		foreach($word_cat as $key)
			if(isset($array[$key]))
				$array = $array[$key];
		
		if(isset($array[$id]))
			return $array[$id];
		else
			return false;
	}
	
	//Creates dictionary sheet for category and language
	protected function createDc($lang, $dictionary)
	{
		ksort($dictionary);
		
		$string = '<?php'."\n".'return ';
		$string .= simtoTools::arrayToString($dictionary, '', 'export');
		$string .= '?>';
		
		$file_name = key($dictionary);
		$file_name = simtoTools::prepareFileName($file_name,true);
		$file_path = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'languages'.DS.$lang.DS.$file_name.'.php';
		
		return simtoTools::saveToFile($file_path,$string);
	}
	
	//Creates new dictionary file copying simto dictionary
	protected function addDc($lang, $filename)
	{
		$simto_path = SIMTO_ROOT.DS.'projects'.DS.'PR_ID'.DS.'languages'.DS.'simto'.DS.$filename;
		$lang_path = SIMTO_ROOT.DS.'projects'.DS.'PR_ID'.DS.'languages'.DS.$lang.DS.$filename;
		
		$dictionary = array();
		$dic = array();
		$dic = @include($simto_path);
		$dictionary = array_replace_recursive($dictionary,$dic);
		$simto_dc = $dictionary;
		$simto_dc = simtoTools::clearArr($simto_dc);
		
		$dictionary = array();
		$dic = array();
		$dic = @include($lang_path);
		$dictionary = array_replace_recursive($dictionary,$dic);
		$lang_dc = $dictionary;
		
		$dictionary = array_replace_recursive($simto_dc,$lang_dc);
		return $this->createDc($lang, $dictionary);
	}
	
	//Create new word in dictionary
	protected function createWord($string, $conditions = array(), $cat = '')
	{
		$lastcon = end($conditions);
		$file_path = $lastcon['file'];
		$line_num = $lastcon['line'];
		$id = hash('crc32',$line_num).hash('crc32',$string);
		
		$word_cat = $this->addCategory($cat);
		$dc_name = current($word_cat);
		
		$new_word = array();
		$current =& $new_word;
		foreach($word_cat as $key)
			$current =& $current[$key];
		
		$current[$id] = $string;
		
		$dc_file = simtoTools::preparePath(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'languages'.DS.'simto'.DS.$dc_name.'.php');
		$dictionary = array(); $dic = array();
		if(file_exists($dc_file))
		{
			$dic = include($dc_file);
			$dictionary = array_replace_recursive($dictionary,$dic);
		}
		
		$dictionary = array_replace_recursive($dictionary,$new_word);
		
		//TODO: najít správny file v případě templatování!!!
		if($this->createDc('simto', $dictionary))
		{
			$file_content = file_get_contents($file_path);
			$file_lines = explode("\n",$file_content);
			
			$cat_var = '';
			if(!empty($cat))
			{
				$cat_var = ",'".simtoTools::escapeAp($cat)."'";
			}
			
			$file_lines[$line_num-1] = preg_replace("/\bt\((.*)\)[;|\.]/","t('".simtoTools::escapeAp($string)."','".simtoTools::escapeAp($id)."'".$cat_var.");",$file_lines[$line_num-1]);
			$file_content = implode("\n",$file_lines);
			
			simtoTools::saveToFile($file_path,$file_content);
			
			$this->createCache('simto');
			$this->simto_dictionary = $this->loadCache('simto');
			
			return $id;
		}
		else
			return false;
	}
	
	//Adds word to dictionary if id already exists
	protected function addWord($string, $id, $cat = '')
	{
		$word_cat = $this->addCategory($cat);
		$dc_name = current($word_cat);
		
		$new_word = array();
		$current =& $new_word;
		foreach($word_cat as $key)
			$current =& $current[$key];
		
		$current[$id] = $string;
		
		$dc_file = simtoTools::preparePath(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'languages'.DS.'simto'.DS.$dc_name.'.php');
		$dictionary = array();
		if(file_exists($dc_file))
		{
			$dic = include($dc_file);
			$dictionary = array_replace_recursive($dictionary,$dic);
		}
		
		$dictionary = array_replace_recursive($dictionary,$new_word);
		if($this->createDc('simto', $dictionary))
		{
			$this->createCache('simto');
			$this->simto_dictionary = $this->loadCache('simto');
			return true;
		}
		else
			return false;
	}
	
	//Sets word category
	public function wordCategory($string)
	{
		$cats = explode('/',$string);
		$this->word_cat = $cats;
	}
	
	//Adds category and returns whole list
	protected function addCategory($cat = '')
	{
		$cats = array();
		if(!empty($cat))
			$cats = explode('/',$cat);
		
		$word_cat = $this->word_cat;
		$word_cat = array_merge($word_cat,$cats);
		
		if(empty($word_cat))
			$word_cat = array('Other');
		
		return $word_cat;
	}

	//Loads cache dictionary to variable
	protected function loadCache($lang)
	{
		$dictionary = array();
		$cache = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'dictionary.'.$lang.'.php';
		
		if(file_exists($cache))
			$dictionary = include($cache);
		elseif($this->createCache($lang))
			$dictionary = include($cache);
		else 
			//TODO: exception
			return false;
		
		return $dictionary;
	}
	
	//Creates cache dictionary file
	protected function createCache($lang)
	{
		$dictionary = array();
		
		$files = $this->fileList(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'languages'.DS.$lang.DS);

		foreach($files as $file)
			if(file_exists($file))
			{
				$dic = include($file);
				$dictionary = array_replace_recursive($dictionary,$dic);
			}
		
		$string = '<?php return '."\n";
		$string .= simtoTools::arrayToString($dictionary,'', 'export');
		$string .= '; ?>';
		
		$cache = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'dictionary.'.$lang.'.php';
		
		if(!simtoTools::saveToFile($cache,$string))
			//TODO: exception
			return false;
		
		return true;
	}
	
	//Returns list of files in folder
	protected function fileList($dir)
	{
		$list = array();
		$new = array();
		$files = scandir($dir);
		foreach($files as $file)
		{
			if($file[0] != '.')
			{
				if(is_dir($dir.$file))
				{
					$new = $this->fileList($dir.$file.DS);
					$list = array_merge($list,$new);
				}
				elseif(substr($file, -4) == '.php')
				{
					$element = $dir.$file;
					array_push($list,$element);
				}
			}
		}
		
		return $list;
	}
	
	//////////////////////////////////////
	//Dynamic mysql dictionary handeling//
	//////////////////////////////////////
	
	//Translates segments of text from database
	public function translateDb($id)
	{
		if(strpos($id,'#T') == 0)
		{
			simtoDbaser::getInst()->select($this->dic_table,'t_id,string,lang',array('where' => 't_id = "'.$id.'"'));
	
			if(simtoDbaser::getInst()->numOut() > 0)
			{
				$rows = simtoDbaser::getInst()->resOut();
				foreach($rows as $row)
					if($row['lang'] == $this->active_lang)
						$aclang = $row['string'];
					elseif($row['lang'] == PR_LANG)
						$deflang = $row['string'];
					elseif($row['lang'] == 'simto')
						$simlang = $row['string'];
					else 
						$lastlang = $row['string'];
					
				if(isset($aclang) && !empty($aclang))
					return $aclang;
				elseif(isset($deflang) && !empty($deflang))
					return $deflang;
				elseif(isset($simlang) && !empty($simlang))
					return $simlang;
				elseif(isset($lastlang) && !empty($lastlang))
					//TODO: exception about missing simto string
					return $lastlang;
				else 
					//TODO: exception
					return '';
			}
			else
			{
				//TODO: exception
				return '';
			}
		}
		else
			return $id;
	}

	//Check languages for dual language
	public function simtoLang($lang)
	{
		
	}
	
	
	public function copyLang($new,$lang = 'simto')
	{
		
	}
	
	public function deleteLang()
	{
		
	}
	
	//Creates new id and row in dictionary table
	public function createDbWord($string,$path = '',$html = true)
	{
		$string = simtoDbaser::getInst()->htmlStrip(simtoDbaser::getInst()->safeData($string),$html);
		$string = simtoTools::escapeQu($string);
		$row = array('string' => '"'.$string.'"', 'lang' => '"simto"', 'path' => '"'.$path.'"');
		$row = simtoDbaser::getInst()->htmlStrip($row,true);
		$row = simtoDbaser::getInst()->safeData($row);
		
		if(simtoDbaser::getInst()->insert('tab_dictionary',$row))
		{
			$id = simtoDbaser::getInst()->lastId();
			$tid = '#T'.$id;
			
			$row = array('t_id' => '"'.$tid.'"');
			$options = array('where' => 'id = '.$id);
			if(simtoDbaser::getInst()->update($this->dic_table,$options,$row))
				return $tid;
			else
			{
				$this->deleteDbWord($tid);
				return $string;
			}
		}
		else 
			return $string;
	} 

	//Copys word to another language
	public function addDbWord($id,$lang,$string,$html = true)
	{
		simtoDbaser::getInst()->select($this->dic_table,'*',array('where' => 't_id = "'.$id.'"'));
		
		if(simtoDbaser::getInst()->numOut() > 0)
		{
			$string = simtoDbaser::getInst()->htmlStrip(simtoDbaser::getInst()->safeData($string),$html);
			$string = simtoTools::escapeQu($string);
			$rows = simtoDbaser::getInst()->resOut();
			$insert = false;
			
			foreach($rows as $row)
				if($row['lang'] == $lang)
				{
					$new_row = array('string' => '"'.$string.'"');
					if(simtoDbaser::getInst()->update($this->dic_table,array('where' => 'id = '.$row['id']),$new_row))
						return true;
					else 
						return false;
				}
				elseif($row['lang'] == 'simto')
				{
					$new_row = array('t_id' => '"'.$row['t_id'].'"','string' => '"'.$string.'"','lang' => '"'.$lang.'"','path' => '"'.$row['path'].'"');
					$insert = true;
				}
			
			if($insert)
				if(simtoDbaser::getInst()->insert($this->dic_table,$new_row))
					return true;
				else
					return false;
		}
		else
			return false;
	}

	//Destroys word in dictionary table
	public function deleteDbWord($id)
	{
		$options = array('where' => 't_id = "'.$id.'"');
		if(simtoDbaser::getInst()->delete($this->dic_table,$options))
			return true;
		else 
			return false;
	}

	//Checks if id is in dictionary table
	public function inDcDb($id,$lang = '')
	{
		if(!empty($lang))
			$lang = ' AND lang = "'.$lang.'"';
		
		$options = array('where' => 't_id = "'.$id.'"'.$lang);
		simtoDbaser::getInst()->select($this->dic_table, 'id,t_id,lang',$options);
		$result = simtoDbaser::getInst()->resOut();
		if(!empty($result))
			return true;
		else 
			return false;
	}

	//Checks if every id in dictionary table is in other tables 
	public function repairDcDb()
	{
		//Checks ids from dictionary in other tables via path
		simtoDbaser::getInst()->select($this->dic_table,'id,t_id,path',array('where' => 'path != ""'));
		if(simtoDbaser::getInst()->numOut() > 0)
		{
			$rows = simtoDbaser::getInst()->resOut();
			foreach($rows as $row)
			{
				$path = explode('/',$row['path']);
				$table = $path[0];
				$column = $path[1];
				
				simtoDbaser::getInst()->select($table,$column,array('where' => $column.' = "'.$row['t_id'].'"'));
				if(simtoDbaser::getInst()->numOut() < 1)
					simtoDbaser::getInst()->update($this->dic_table,array('where' => 'id = '.$row['id']),array('path' => '""'));
			}	
		}
		
		//Checks if some items have empty path column
		simtoDbaser::getInst()->select($this->dic_table,'id,t_id,path',array('where' => 'path = ""'));
		if(simtoDbaser::getInst()->numOut > 1)
		{
			$rows = simtoDbaser::getInst()->resOut();
			foreach($rows as $row)
			{
				$path = '';
				simtoDbaser::getInst()->execudeSQL('SHOW TABLES;');		//Gets every table in database
				$tables = simtoDbaser::getInst()->resOut();
				foreach($tables as $table)
				{
					$table = $table[0];
					if($table == $this->dic_table)
						continue;
					
					simtoDbaser::getInst()->execudeSQL('SHOW COLUMNS FROM '.$table.';');	//Gets every column in table
					$columns = simtoDbaser::getInst()->resOut();
					foreach($columns as $column)
					{
						$column = $column[0];
						simtoDbaser::getInst()->select($table,$column,array('where' => $column.' = "'.$row['t_id'].'"'));
						if(simtoDbaser::getInst()->numOut() > 0)
						{
							$path = $table.'/'.$column;
							break;
						}	
					}
					if(!empty($path))
						break;
				}
				
				if(empty($path))
					$path = 'MIT'; //Missing In Table
				
				simtoDbaser::getInst()->update($this->dic_table,array('where' => 'id = '.$row['id']),array('path' => '"'.$path.'"'));
			}
		}
		
		//Fill class array with wrong ids witch have no path
		simtoDbaser::getInst()->select($this->dic_table,'*',array('where' => 'path = "MIT"'));
		if(simtoDbaser::getInst()->numOut() > 0)
		{
			$rows = simtoDbaser::getInst()->resOut();
			
			$this->wrong_id = array();
			foreach($rows as $row)
				//Array in format Wrong_id[t_id][lang] = string
				$this->wrong_id[$row['t_id']][$row['lang']] = $row['string'];
		}
		else 
			$this->wrong_id = array();  //Spits empty array
		
		return true;
	}
	
	//Deletes all wrong ids from dictionary table
	public function deleteWrongId()
	{
		if(!empty($this->wrong_id))
		{
			foreach($this->wrong_id as $key => $val)
			{
				$this->deleteDbWord($key);
			}
		}
		return true;
	}
	
	//Return list of wrong ids
	public function wrongIdOut()
	{
		return $this->wrong_id;
	}
}
?>