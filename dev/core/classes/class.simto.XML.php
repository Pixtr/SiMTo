<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
* Author: CleverOn Group
* Proprietary software license
* All rights reserved for CleverOn Group
*/

//Page restriction
if(!defined('PR')) die('Restricted area! You cannot load this page directly.');

//This class will load project settings and return needed variables
class simtoXMLCore
{
	//Stored instance of class object
	protected static $inst;
	
	//DOM XML object
	protected $xml;
	
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
			$this->xml->formatOutput = true;
			$this->xml->preserveWhiteSpace = false;
			$this->xml->load($file);
		}
		else
		{
			$this->xml = new DOMDocument('1.0', 'UTF-8');
			$this->xml->formatOutput = true;
			$this->xml->preserveWhiteSpace = false;
		}
	}
	
	public function __destruct()
	{
		//TODO: uložení rozdělaného xml
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
				's' =>	'',		//name of node where xpath Starts looking
				'c'	=>	'',		//Conditions for search (path)
				'w'	=>	'',		//path to What you looking for
				'r'	=>	't'		//type of Result (t = first result in text, a = all results in array, n = nodelist, 
		),$options);			//c = cached node, b = true if node exists and false if not)
		
		$xpath = new DOMXPath($this->xml);
		$query = '';
		$start = '';
		$result = '';
		
		//Prepares path for searching
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
		
		//Searches for result
		if(empty($query) && !empty($start))
			$resnode = $start;
		elseif(!empty($query) && empty($start))
			$resnode = $xpath->query($query);
		elseif(!empty($query) && !empty($start))
			$resnode = $xpath->query($query,$start);
		else 
			$resnode = $this->xml->createTextNode('');
		
		//Prepares result
		switch($options['r'])
		{
			default:
			case 't':	//String result
				if($resnode->length > 0)
					$result = simtoTranslator::getInst()->translateXML($resnode->item(0));	
			break;
			
			case 'a':	//Array result
				if($resnode->length > 0)
					foreach($resnode as $node)
						$result[] = $node->nodeValue;
			break;
			
			case 'n':	//Nodelist result
					$result = $resnode;
			break;
			
			case 'c':	//Global cached nodelist result
				$this->clearCnode();
				$this->cnode = $resnode;
				$result = true;
			break;
			
			case 'b':
				if($resnode->length > 0)
					$result = true;
				else
					$result = false;
			break;
		}
		
		return $result;
	}
	
	//Finds node within another node and returns it value
	//Tagname could be path to right node like node1/node2/node3 and so on
	//Tagname can also be attribute name but must have @ before name like node1/@attribute1
	//Variable first mean that function will return string of a first node value
	//If variable first is false function will return array of nodes value
	//You can use specific nodelist if you parse it trougth variable node or use cached nodelist from function find if you leave it empty 
	public function nodeValue($tagname,$first = true,$node = '')
	{
		//Prepare path to right node
		$tags = explode('/',$tagname);
		
		//Make sure it uses right nodelist
		if(!is_object($node) && is_object($this->cnode))
			$node = $this->cnode;
		elseif(!is_object($node))
			$node = $this->xml->documentElement;
		
		$result = '';
		if($first)		//If you want only first value 
		{
			if(get_class($node) != 'DOMElement')
				$fnode = $node->item(0);
			else 
				$fnode = $node;
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
			if(get_class($fnode) == 'DOMElement')
				$result = simtoTranslator::getInst()->translateXML($fnode);
			else 
				$result = $fnode;
		}
		else		//If you want array of all values
		{
			$i = 0;
			if(get_class($node) == 'DOMElement')
				$fnode[] = $node;
			else 
				$fnode = $node;
			
			foreach($fnode as $n)
			{
				echo 'tady';
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
						$result[$i][] = simtoTranslator::getInst()->translateXML($value);
				else 
					$result[$i][] = $n;
				$i++;
			}			//Result is two dimensional array in format array('0' => array('0' => 'value1','1' => 'value2'....))
		}
		
		return $result;
	} 
	
	//Unsets cnode variable
	public function clearCnode()
	{
		$this->cnode = '';
	}
	
	//Adds single node to xml document
	//Path is xpath to place where node should be created, last item of path is name of node
	//Start is specific node from which should path be calculated, finds root element if leaved empty
	//Returns created node
	public function addNode($path,$data = '',$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(empty($path))
			return false;
		else
			$tags = explode('/',$path);
		$nodename = array_pop($tags);
		
		$root = $this->selectNode($tags, $root);
		
		$newnode = $this->xml->createElement($nodename,$data);
		return $root->appendChild($newnode);
		
	}

	//Edits single node of xml document
	//Path is xpath to node, last item of path is name of node
	//Start is specific node from which should path be calculated, finds root element if leaved empty
	//It creates the node in case node don't exist
	public function editNode($path,$data = '',$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
	
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		$nodename = array_pop($tags);
	
		$root = $this->selectNode($tags, $root);
	
		$node = $root->getElementsByTagName($nodename)->item(0);
		if(empty($node))
		{
			$newnode = $this->xml->createElement($nodename,$data);
			$root->appendChild($newnode);
		}
		else
			$node->nodeValue = $data;
	}

	//Moves node from one location to another
	public function moveNode($node,$path,$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(get_class($node) != 'DOMElement')
			//TODO: exception
			return false;
		
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		
		$root = $this->selectNode($tags, $root);
		$root->appendChild($node);
	}
	
	//Imports node from another xml document
	public function importNode($node,$path,$start = '')
	{
		if(get_class($node) != 'DOMElement')
			//TODO: exception
			return false;
		
		$imported = $this->xml->importNode($node, true);
		
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		
		$root = $this->selectNode($tags, $root);
		$root->appendChild($imported);
	}
	
	//Creates or sets attribute to node
	//Variable attr could be string in format name=value or array of attributes for node
	public function setAttr($path,$attr,$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		
		$root = $this->selectNode($tags, $root);
		if(!is_array($attr))
		{
			$key = substr($attr,0,strpos($attr,'='));
			$value = substr($attr,strpos($attr,'=')+1);
			$attr = array($key => $value);
		}
		
		foreach($attr as $key => $value)
		{
			if($root->hasAttribute($key))
				$root->setAttribute($key,$value);
			else 
			{
				$attribute = $this->xml->createAttribute($key);
				$attribute->value = $value;
				$root->appendChild($attribute);
			}
		}	
	}

	//Removes whole node from XML document and returns removed node
	public function deleteNode($path,$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		
		$node = $this->selectNode($tags, $root);
		$parent = $node->parentNode;
		return $parent->removeChild($node);
	} 
	
	//Removes attribute from node and returns value of removed attribute
	public function deleteAttr($path,$start = '')
	{
		if(empty($start) || get_class($start) != 'DOMElement')
			$root = $this->xml->documentElement;
		else
			$root = $start;
		
		if(empty($path))
			$tags = array();
		else
			$tags = explode('/',$path);
		$attname = array_pop($tags);
		
		$node = $this->selectNode($tags, $root);
		return $node->removeAttribute($attname);
	}
	
	//Returns right node based on path tags
	protected function selectNode($tags,$root)
	{
		foreach($tags as $tag)
		{
			$node = $root->getElementsByTagName($tag)->item(0);
			if(empty($node))
			{
				$newnode = $this->xml->createElement($tag);
				$root = $root->appendChild($newnode);
			}
			else
				$root = $node;
		}
		return $root;
	}
	
	//Saves xml content into file
	public function save()
	{
		$path = simtoToolsCore::preparePath($this->filepath);
		if($path)
		{
			if(simtoToolsCore::saveToFile($path,$this->show()))
				return true;
		}
		
		return false;
	}

	//Returns xml in string
	public function show()
	{
		return $this->xml->saveXML();
	}
}
?>