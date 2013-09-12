<?php

//Page restriction
if(!defined('PR')) die('Restricted area! You cannot load this page directly.');

class simtoExceptionCore extends Exception{
	//Stored instance of class object
	protected static $inst;
	
	//XML object with cached exceptions
	protected $list;
	protected $cache_path = '';			//Path to cache file
	protected $folder_path = '';		//Path to folder with exception files
	
	//All exceptions prepared for print
	public $res_show = array();		//What show to users
	public $res_admin = array();	//What send to administrator
	
	public function __construct(){
		$this->cache_path = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'cache'.DS.'exceptions.list.xml';
		$this->folder_path = SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'exceptions'.DS;
		
		if(!file_exists($this->cache_path))
			$this->createCache();
		
		$this->loadCache();
	}
	
	public static function getInst()
	{
		if (!simtoException::$inst)
			simtoException::$inst = new simtoException();
		
		return simtoException::$inst;
	}
	
	//Handles all trown exceptions
	public function handler($code)
	{
		$string = $code->getMessage();
		$ds = (strpos($string,'|') < 1) ? strlen($string) : strpos($string,'|');
		
		$message = substr($string,0,$ds);
		$message = preg_replace('/^ */','',$message);
		$message = preg_replace('/ *$/','',$message);
		
		$string = substr($string,$ds + 1,strlen($string));
		
		$ds = (strpos($string,'|') < 1) ? strlen($string) : strpos($string,'|');
		
		$id = substr($string,0,$ds);
		$id = preg_replace('/^ */','',$id);
		$id = preg_replace('/ *$/','',$id);
		
		$cat = substr($string,$ds + 1,strlen($string));
		$cat = preg_replace('/^ */','',$cat);
		$cat = preg_replace('/ *$/','',$cat);
		
		$variables = array();
		if(preg_match('/\;\#(?<id>[A-Za-z][a-z0-9_]*?)\#/', $message, $m))
			foreach($m['id'] as $tag)
			{
				$pattern = '/\;\#'.$tag.'\#(?<var>[A-Za-z][a-z0-9_]*?)\#'.$tag.'\#\;/';
				if(preg_match($pattern, $message, $n))
				{
					$variables[$tag] = $n['var'][0];
					$message = preg_replace($pattern, ';#'.$tag.'#;',$message);
				}
			}
				
		if(empty($id))
		{
			$conditions = $code->getTrace();
			$lastcon = end($conditions);
			
			$file_path = $lastcon['file'];
			$line_num = $lastcon['line'];
			$id = hash('crc32',$line_num).hash('crc32',$message);
			
			$this->addException($id, $cat, $message);
			
			$file_content = file_get_contents($file_path);
			$file_lines = explode("\n",$file_content);
				
			$cat_var = '';
			if(!empty($cat))
			{
				$cat_var = ",'".simtoTools::escapeAp($cat)."'";
			}
				
			$file_lines[$line_num-1] = preg_replace("/\bsimtoException\((.*)\)[;|\.]/","simtoException('".simtoTools::escapeAp($message)."','".simtoTools::escapeAp($id)."'".$cat_var.");",$file_lines[$line_num-1]);
			$file_content = implode("\n",$file_lines);
				
			simtoTools::saveToFile($file_path,$file_content);
		}
		
		$conditions = array('c' => 'exceptions/exception[@langid="'.$id.'" and @langcat="'.$cat.'"]', 'r' => 'n');
		$exception = $this->list->find($conditions)->item(0);
		$translate = simtoTranslator::getInst()->translateXML($exception,'Exceptions');
		
		foreach($variables as $tag => $var)
			$translate = preg_replace('/\;\#'.$tag.'\#\;/',$var,$translate);
		
		$translate = preg_replace('/\;\#[A-Za-z][a-z0-9_]*\#\;/','',$translate);
		
		if($this->list->nodeValue('@show',true,$exception) == '1')
			$this->res_show[] = $translate;
		
		if($this->list->nodeValue('@admin',true,$exception) == '1')
			$this->res_admin[] = $translate;
		
		if($this->list->nodeValue('@fatal',true,$exception) == '1')
			die($translate);
	}
	
	//Adds new exception to file
	//If exception does not exists it will create it
	protected function addException($id,$cat,$message = '',$settings = array())
	{
		$ds = (strpos($cat,'/') < 1) ? strlen($cat) : strpos($cat,'/');
		
		$filename = substr($cat,0,$ds);
		$filename = simtoTools::prepareFileName($filename,true);
		$file = new simtoXML(SIMTO_ROOT.DS.'projects'.DS.PR_ID.DS.'exceptions'.DS.$filename.'.xml');
		
		$conditions = array('c' => 'exceptions/exception[@langid="'.$id.'" and @langcat="'.$cat.'"]', 'r' => 'b');
		if($file->find($conditions))
		{
			$conditions = array_merge($conditions, array('r' => 'n'));
			$start = $file->find($conditions);
			$file->editNode('',$message,$start);
			
			if(!empty($settings['show']) && ($settings['show'] == '1' || $settings['show'] == '0'))
				$file->setAttr('','show='.$settings['show'],$start);
			
			if(!empty($settings['admin']) && ($settings['admin'] == '1' || $settings['admin'] == '0'))
				$file->setAttr('','admin='.$settings['admin'],$start);
			
			if(!empty($settings['fatal']) && ($settings['fatal'] == '1' || $settings['fatal'] == '0'))
				$file->setAttr('','fatal='.$settings['fatal'],$start);
		}
		else 
		{
			$start = $file->addNode('exceptions/exception',$message);
			$settings = array_merge(array('show' => '1', 'admin' => '1', 'fatal' => '0'),$settings);
			$file->setAttr('',array('langid' => $id, 'langcat' => $cat, 'show' => $settings['show'], 'admin' => $settings['admin'], 'fatal' => $settings['fatal']),$start);
		}
		
		$file->save();
		$this->createCache();
		$this->loadCache();
	}
	
	//Creates cached list of exceptions
	protected function createCache()
	{
		$cache = new simtoXML($this->cache_path);
		$cache->clear();
		$cache->addNode('exceptions');
		
		$files = $this->fileList($this->folder_path);
		
		foreach($files as $file)
		{
			$buff = new simtoXML($file);
			$nodes = $buff->selectNode('exception','root',true);
			
			if(!empty($nodes))
				foreach($nodes as $node)
					$cache->importNode($node,'','root');
			
			$buff = null;
		}
		
		$cache->save();
		$cache = null;
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
				elseif(substr($file, -4) == '.xml')
				{
					$element = $dir.$file;
					array_push($list,$element);
				}
			}
		}
	
		return $list;
	}
	
	//Loads cached list of exceptions
	protected function loadCache()
	{
		$this->list = new simtoXML($this->cache_path);
	}

	//Returns results of exeptions witch should be shown to users
	public function showOut()
	{
		return $this->res_show;
	}
	
	//Returns results f exceptions which should be send to administrator
	public function adminOut()
	{
		return $this->res_admin;
	}
}
?>