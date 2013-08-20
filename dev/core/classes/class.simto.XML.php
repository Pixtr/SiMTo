<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
* Author: CleverOn Group
* Proprietary software license
* All rights reserved for CleverOn Group
*/

//Page restriction
//if(!PR) die('Restricted area! You cannot load this page directly.');

//This class will load project settings and return needed variables
class simtoXMLCore //implements simtoICore
{
	//Stored instance of class object
	protected static $inst;
	
	//DOM XML object
	public $xml;
	
	//Path of xml file
	protected $filepath;
	
	//Cached node for global editing
	protected $cnode;
	
	public function __construct($file = '')
	{
		$this->filepath = $file;
		
		if(file_exists($file) || !empty($file))
		{
			$this->xml = new DOMDocument();
			$this->xml->load($file);
		}
		else 
			$this->xml = new DOMDocument('1.0', 'UTF-8');
	}
	
	//Get instance object from class
	public static function getInst($file)
	{
		if (!simtoXML::$inst)
			simtoXML::$inst = new simtoXML($file);
	
		return simtoXML::$inst;
	}
	
	//Changes file path
	public function filePath($path)
	{
		$this->filepath = $path;
	}
	
	//Loads XML source into DOM object
	public function loadXML($source)
	{
		$this->xml->loadXML($source);
	}
	
	//Returns searched node content
	public function find($options = array())
	{
		$options = array_merge(array(
				's' =>	'',		//node where xpath Starts looking
				'c'	=>	'',		//Conditions for search (path)
				'w'	=>	'',		//path to What you looking for
				'r'	=>	't'		//type of Result (t = first result in text, a = all results in array, n = nodelist, c = cached node)
		),$options);
		
		$xpath = new DOMXPath($this->xml);
		$query = '';
		$start = '';
		$result = '';
		
		if(!empty($options['s']))
			$start = $this->xml->getElementsByTagName($options['s'])->item(0);
		
		if(!empty($options['c']))
			$query .= $options['c'];
		
		if(!empty($options['w']))
		{
			if(!empty($query))
				$query .= '/'.$options['w'];
			else 
				$query = $options['w'];
		}	
		
		if(empty($query) && !empty($start))
			$resnode = $start;
		elseif(!empty($query) && empty($start))
			$resnode = $xpath->query($query);
		elseif(!empty($query) && !empty($start))
			$resnode = $xpath->query($query,$start);
		else 
			$resnode = $this->xml->createTextNode('');
		
		if($resnode->length > 0)
		switch($options['r'])
		{
			default:
			case 't':
				$result = $resnode->item(0)->textContent;	
			break;
			
			case 'a':
				foreach($resnode as $node)
					$result[] = $node->nodeValue;
			break;
			
			case 'n':
				$result = $resnode;
			break;
			
			case 'c':
				$this->clearCnode();
				$this->cnode = $resnode;
				$result = true;
			break;
		}
		
		return $result;
	}
	
	//Finds node within another node and returns it value
	public function nodeValue($tagname,$first = true,$node = '')
	{
		$tags = explode('/',$tagname);
		if(!is_object($node) && is_object($this->cnode))
			$node = $this->cnode;
		elseif(!is_object($node))
			return false;
		
		$result = '';
		if($first)
		{
			$fnode = $node->item(0);
			foreach($tags as $tag)
			{
				if(!is_object($fnode))
					break;
				
				if(strpos($tag,'@') > -1)
				{
					$tag = str_replace('@','',$tag);
					$fnode = $fnode->getAttribute($tag);
				}
				else
					$fnode = $fnode->getElementsByTagName($tag)->item(0);
				
			}
			if(is_object($fnode))
				$result = $fnode->textContent;
			else 
				$result = $fnode;
		}
		else
		{
			$i = 0;
			foreach($node as $n)
			{
				$a = 0;
				foreach($tags as $tag)
				{	
					if($a > 0)
						$n = $n->item(0);
					
					if(!is_object($n))
						break;
					
					if(strpos($tag,'@') > -1)
					{
						$tag = str_replace('@','',$tag);
						$n = $n->getAttribute($tag);
					}
					else 
						$n = $n->getElementsByTagName($tag);
					$a++;
				}
				
				if(is_object($n))
					foreach($n as $value)
						$result[$i][] = $value->textContent;
				else 
					$result[$i][] = $n;
				$i++;
			}
		}
		
		return $result;
	} 
	
	//Unsets cnode variable
	public function clearCnode()
	{
		unset($this->cnode);
	}
	
	
	public function add()
	{
		
	}
	
	
	public function edit()
	{
		
	}
	
	
	public function delete()
	{
		
	}
	
	
	public function save()
	{
		
	}
	
	
	public function create()
	{
		
	}
}
?>