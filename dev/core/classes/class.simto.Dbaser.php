<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(!defined('PR')) die('Restricted area! You cannot load this page directly.');

class simtoDbaserCore implements simtoICore
{
	//Stored instance of class object
	protected static $inst;
	
	//Connection to database
	protected $mysql;
	
	//MySQL variables
	protected $hostname;
	protected $username;
	protected $password;
	protected $dbase;		//Selected database
	
	//Temporaly row
	protected $row = array();
	
	//Query results
	protected $result;		//Query result
	protected $result_arr;	//Result array
	protected $num_rows;	//Number of result rows
	protected $aff_rows;	//Number of affected rows
	protected $errors;		//Error list
	protected $query;		//Last sql query
	
	//Objects of settings
	protected $dbase_sett;	//Databases settings
	protected $table_sett;	//Tables settings

	public function __construct()
	{
		$this->hostname = SIMTO_DBSERVER;
		$this->username = SIMTO_DBLOGIN;
		$this->password = SIMTO_DBPASS;
		$this->connect();
		$this->dbase_sett = new simtoXML(SIMTO_ROOT.DS.'config'.DS.'dbase'.DS.'dbases.settings.xml');
		$this->dbUse(PR_DBASE);
	}
	
	public function __destruct()
	{
		if ($this->mysql)
			$this->disconnect();
	}
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoDbaser::$inst)
			simtoDbaser::$inst = new simtoDbaser();
	
		return simtoDbaser::$inst;
	}
	
	//Create connection to database
	public function connect()
	{
		$this->disconnect();
		
		$this->mysql = mysqli_connect($this->hostname,$this->username,$this->password);
		
		//error handeling
		if(!$this->mysql)
			return false;
		else
			return true;	
	}
	
	//Destroy connection
	public function disconnect()
	{
		if($this->mysql)
			mysqli_close($this->mysql);
	}
	
	//Database selection
	public function dbUse($dbase)
	{
		if(!mysqli_select_db($dbase, $this->mysql))
			//TODO: error handeling
			return false;
		else
		{	
			$this->dbase = $dbase;
			$dbase_path = $this->dbase_sett->find(array('c' => '/dbases/dbase[name="'.$dbase.'"]','w' => 'path'));
			if(!empty($dbase_path))
				$this->table_sett = new simtoXML($dbase_path);
			else
			{
				$this->table_sett = new simtoXML(SIMTO_ROOT.DS.'config'.DS.'dbase'.DS.$dbase.'.sett.xml');
				$this->dbase_sett->addNode(); 	//TODO: přidat adresu do nastavení database
			}
				//TODO: exception
				
			return true;
		}
	}
	
	//Adds data to temporaly row in array
	public function addCell($col,$data = '')
	{
		$this->row[$col] = $data;
	}
	
	//Adds whole temporaly row from array
	public function addRow($data = array())
	{	
		$this->row = $data;
	}
	
	//Clears temporaly row
	public function clearRow()
	{
		$this->row = array();
	}
	
	//Clears all results
	public function clearResults()
	{
		$this->result = '';
		$this->result_arr = array();
		$this->num_rows = '';
		$this->aff_rows = array();
		$this->errors = array();
	}
	
	//Clears all, results, temporaly row
	public function clearAll()
	{
		$this->clearRow();
		$this->clearResults();
	}
	
	//Inserts temporaly row to database
	public function insert($table,$row = '')
	{		
		if(empty($table))
			return false;
		
		$code = 'INSERT INTO '.$table.' (';
		
		if(empty($row))
			$row = $this->row;
		
		$i = 0; $cols = ''; $vals = '';
		foreach($row as $col => $val)
		{
			if($i > 0)
			{
				$cols .= ',';
				$vals .= ',';
			}	
			$cols .= $col;
			$vals .= $this->rightValue($table,$col,$val);
			$i++;
		}
		
		$code .= $cols.') VALUES('.$vals.');';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Updates data from row in table
	public function update($table,$options = array(),$row = '')
	{	
		if(empty($table))
			return false;
		
		$options = array_merge(array(
				'where' => '',
				'order' => '',
				'limit' => '',
				'asc' => ''
		),$options);
		
		$code = 'UPDATE '.$table.' SET ';
		
		if(empty($row))
			$row = $this->row;
		
		$i = 0; $cols = ''; $vals = '';
		foreach($row as $col => $val)
		{
			if($i > 0)
				$code .= ',';
			
			$code .= $col.'='.$this->rightValue($table,$col,$val);
			$i++;
		}
		
		if(!empty($options['where']))
			$options['where'] = ' WHERE '.$options['where'];
		
		if(!empty($options['order']))
			$options['order'] = ' ORDER BY '.$options['order'].' '.$options['asc'];
		
		if(!empty($options['limit']))
			$options['limit'] = ' LIMIT '.$options['limit'];
		
		$code .= $options['where'].$options['order'].$options['limit'].';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Deletes row from table
	public function delete($table,$options = array())
	{
		if(empty($table))
			return false;
		
		$options = array_merge(array(
				'where' => '',
				'order' => '',
				'limit' => '',
				'asc' => ''
		),$options);
		
		$code = 'DELETE FROM '.$table;
		
		if(!empty($options['where']))
			$options['where'] = ' WHERE '.$options['where'];
		
		if(!empty($options['order']))
			$options['order'] = ' ORDER BY '.$options['order'].' '.$options['asc'];
		
		if(!empty($options['limit']))
			$options['limit'] = ' LIMIT '.$options['limit'];
		
		$code .= $options['where'].$options['order'].$options['limit'].';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Simple select of rows from table
	public function select($table,$cols = '*',$options = array())
	{
		$options = array_merge(array(
				'where' => '',
				'group' => '',
				'ascgpr' => '',
				'order' => '',
				'limit' => '',
				'ascord' => ''
		),$options);
		
		$code = 'SELECT ';
		
		if(is_array($cols))
		{
			$i = 0; $colums = '';
			foreach($cols as $key => $val)
			{
				if($i > 0)
					$colums .= ',';
				
				if(is_numeric($key))
					$colums .= $val;
				else 
					$colums .= $key.' AS '.$val;
				
				$i++;
			}	
		}
		else 
			$colums = $cols;
		
		$code .= $colums.' FROM '.$table;
		
		if(!empty($options['where']))
			$options['where'] = ' WHERE '.$options['where'];
		
		if(!empty($options['group']))
			$options['group'] = ' GROUP BY '.$options['group'].' '.$options['ascgrp'];
		
		if(!empty($options['order']))
			$options['order'] = ' ORDER BY '.$options['order'].' '.$options['ascord'];
		
		if(!empty($options['limit']))
			$options['limit'] = ' LIMIT '.$options['limit'];
		
		$code .= $options['where'].$options['group'].$options['order'].$options['limit'].';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}

	//Creates new database
	public function addDb($name = '')
	{
		if(empty($name))
			return t('Database name can not be empty.','adddbemptyname','Dbaser/Database');
		
		$code = 'SHOW DATABASES LIKE "'.$name.'";';
		$this->execudeSQL($code);
		
		if($this->num_rows < 1)
		{
			$code = 'CREATE DATABASE IF NOT EXISTS '.$name;
		
			$this->query = $code;
		
			if($this->execudeSQL($code))
			{
				$search = array('c' => 'dbases/dbase[name="'.$name.'"]', 'w' => '..', 'r' => 'b');
				if(!$this->dbase_sett->find($search))
				{
					$start = $this->dbase_sett->addNode('dbases/dbase');
					$this->dbase_sett->addNode('name',$name,$start);
					$this->dbase_sett->addNode('tagname','Database '.$name.' title',$start);
					$this->dbase_sett->addAttr('tagname',array('langid' => '', 'langcat' => 'dbases/dbname'),$start);
					$this->dbase_sett->addNode('description','Database '.$name.' description',$start);
					$this->dbase_sett->addAttr('description',array('langid' => '', 'langcat' => 'dbases/dbdesc'),$start);
					$this->dbase_sett->addNode('path',SIMTO_ROOT.DS.'config'.DS.'dbase'.DS.$name.'.sett.xml',$start);
				}
				
				return true;
			}
			else
				return false;
		}
		else
		{
			$search = array('c' => 'dbases/dbase[name="'.$name.'"]', 'w' => '..', 'r' => 'b');
			if(!$this->dbase_sett->find($search))
			{
				$start = $this->dbase_sett->addNode('dbases/dbase');
				$this->dbase_sett->addNode('name',$name,$start);
				$this->dbase_sett->addNode('tagname','Database '.$name.' title',$start);
				$this->dbase_sett->addAttr('tagname',array('langid' => '', 'langcat' => 'dbases/dbname'),$start);
				$this->dbase_sett->addNode('description','Database '.$name.' description',$start);
				$this->dbase_sett->addAttr('description',array('langid' => '', 'langcat' => 'dbases/dbdesc'),$start);
				$this->dbase_sett->addNode('path',SIMTO_ROOT.DS.'config'.DS.'dbase'.DS.$name.'.sett.xml',$start);
			}
			
			return t('Database ;#name#'.$name.'#name#; already exists.','adddbnameexists','Dbaser/Database');
		}
	}
	
	//Deletes whole database
	public function delDb($name = '')
	{
		if(empty($name))
			return false;
		
		$code = 'DROP DATABASE IF EXISTS '.$name;
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Creates whole table
	public function addTable($name = '',$cols = array())
	{
		if(empty($name))
			return false;
		
		$code = 'CREATE TABLE IF NOT EXISTS '.$name.' (';
		
		if(is_array($cols))
		{
			$i = 0; $table = '';
			foreach($cols as $key => $val)
			{
				if($i > 0)
					$table .= ',';
				
				$table .= $key.' '.$val;
				$i++;
			}
		}
		else
			$table = $cols;
		
		$code .= $table.')';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Deletes whole table
	public function delTable($table = '')
	{
		if(empty($table))
			return false;
		
		if(is_array($table))
		{
			$i = 0; $tables = '';
			foreach($table as $key => $val)
			{
				if($i > 0)
					$tables .= ',';
				
				$tables .= $val;
				$i++;
			}		
		}
		else 
			$tables = $table;
		
		$code = 'DROP TABLE IF EXISTS '.$tables.';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Creates new column to table
	public function addCol($table = '',$col = '',$pos = '')
	{
		if(empty($table) || empty($col))
			return false;
		
		$code = 'ALTER TABLE '.$table;
		
		if(is_array($col))
		{
			$i = 0; $cols = '';
			foreach($col as $name => $opt)
			{
				if($i > 0)
					$cols .= ',';
				
				$cols .= ' ADD COLUMN '.$name.' '.$opt;
				
				if(!empty($pos[$name]) && is_array($pos))
					$cols .= ' '.$pos[$name];
				
				$i++;
			}
		}
		else 
		{
			$cols = ' ADD COLUMN '.$col;
			if(!empty($pos) && is_array($pos))
				$cols .= ' '.$pos;
		}
		
		$code .= $cols.';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Delete column from table
	public function delCol($table = '',$col = '')
	{
		if(empty($table) || empty($col))
			return false;
		
		$code = 'ALTER TABLE '.$table;
		
		if(is_array($col))
		{
			$i = 0; $cols = '';
			foreach($col as $key => $name)
			{
				if($i > 0)
					$cols .= ',';
		
				$cols .= ' DROP COLUMN '.$name;
				$i++;
			}
		}
		else
			$cols = ' DROP COLUMN '.$col;
		
		$code .= $cols.';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Modify column in table
	public function changeCol($table = '',$col = '',$change = '')
	{
		if(empty($table) || empty($col) || empty($change))
			return false;
		
		$code = 'ALTER TABLE '.$table;
		
		if(is_array($col) && is_array($change))
		{
			$i = 0; $cols = '';
			foreach($col as $old => $new)
			{
				if($i > 0)
					$cols .= ',';
		
				$cols .= ' CHANGE '.$old.' '.$new.' '.$change[$old];
				$i++;
			}
		}
		elseif(!is_array($col) && !is_array($change))
			$cols = ' CHANGE '.$col.' '.$change;
		else 
			return false;
		
		$code .= $cols.';';
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
	}
	
	//Executes mysql code
	public function execudeSQL($code = '')
	{
		if($this->result = mysqli_query($this->mysql,$code))
		{
			$this->num_rows = @mysqli_num_rows($this->result);
			$this->aff_rows	= @mysqli_affected_rows($this->mysql);
		
			if($this->num_rows > 0)
				$this->result_arr = @mysqli_fetch_assoc($this->result);
			else
				$this->result_arr = array();
		}
		else
		{
			$this->errors = @mysqli_error_list($this->mysql);
			return false;
		}
		
		$this->errors = @mysqli_error_list($this->mysql);
		return true;
	}

	//Protection from SQL injection
	public function safeData($data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $val)
			{
				if(is_array($val))
					$this->safeData($data[$key]);
				else
					$data[$key] = mysqli_real_escape_string($this->mysql,$data);
			}
		}
		else
			$data = mysqli_real_escape_string($this->mysql,$data);
		
		return $data;
	}
	
	//Strips data of html tags
	public function htmlStrip($data,$allowed = '')
	{
		if(is_array($data))
		{
			foreach($data as $key => $val)
			{
				if(is_array($val))
					$this->htmlStrip($val,$allowed);
				else
					$data[$key] = strip_tags($data[$key],$allowed);	
			}
		}
		else 
			$data = strip_tags($data,$allowed);
		
		return $data;
	}	
	
	//Returns query results
	public function resOut()
	{
		return $this->result_arr;
	}
	
	//Returns number of rows
	public function numOut()
	{
		return $this->num_rows;
	}
	
	//Returns number of affected rows
	public function affOut()
	{
		return $this->aff_rows;
	}
	
	//Returns errors
	public function errOut()
	{
		return $this->errors;
	}
	
	//Returns last sql code
	public function sqlOut()
	{
		return $this->query;
	}
	
	//Return last ID
	public function lastId()
	{
		return mysqli_insert_id($this->mysql);
	}
	
	//Returns edited value by columns settings
	protected function rightValue($table,$col,$value)
	{
		$this->table_sett->find(array('c' => 'table[name="'.$table.'"]/columns/column/settings[name="'.$col.'"]',
				'w' => '..', 'r' => 'c'));
		
		$tran = $this->table_sett->nodeValue('@translate');
		if($tran == 1)
		{
			$tid = simtoTranslator::getInst()->createDbWord($value,$table.'/'.$col);
			$value = '"'.$tid.'"';
			return $value;
		}
		
		$scram = $this->table_sett->nodeValue('@scrambler');
		if($scram != 0 && simtoScrambler::getInst()->useModul($scram))
		{
			$pass = simtoScrambler::getInst()->encrypt($value);
			$value = '"'.$pass.'"';
			return $value;
		}
		
		$html = $this->table_sett->nodeValue('security/html/strip');
		if($html == 1)
		{
			$tags = $this->table_sett->nodeValue('security/html/tags/tag',false);
			$tags = $tags[0];
			$allowed = '';
			foreach($tags as $tag)
				$allowed .= '<'.$tag.'>';
			$value = $this->htmlStrip($value,$allowed);
			
		}
		
		$safe = $this->table_sett->nodeValue('security/safe');
		if($safe == 1)
			$value = $this->safeData($value);
		
		$ctype = $this->table_sett->nodeValue('security/type');
		if($ctype == 1)
		{
			$ttype = $this->table_sett->nodeValue('settings/type');
			$dtype = $this->dbase_sett->find(array('s' => 'types', 'c' => 'type[name="'.$ttype.'"]', 'w' => 'dtype'));
			switch($dtype)
			{
				case 'string':
					$value = '"'.$value.'"';
				break;
				
				case 'boolean':
				case 'numeric':
					if(!is_numeric($value))
						$value = 0;
						//TODO: exception
				break;
			}
		}	
		
		return $value;
	}
	
	//Returns true if column is language column
	public function isLang($table,$col)
	{
		$this->table_sett->find(array('c' => 'table[name="'.$table.'"]/columns/column/settings[name="'.$col.'"]',
				'w' => '..', 'r' => 'c'));
		
		$tran = $this->table_sett->nodeValue('@translate');
		if($tran == 1)
			return true;
		else
			return false;
	}
	
	//Returns name of scrambler modul if column is scrambler column
	public function isScram($table,$col)
	{
		$this->table_sett->find(array('c' => 'table[name="'.$table.'"]/columns/column/settings[name="'.$col.'"]',
				'w' => '..', 'r' => 'c'));
		
		$scram = $this->table_sett->nodeValue('@scrambler');
		if($scram != 0)
			return $scram;
		else 
			return false;
	}
	
	//Instalation of tables
	public function instal()
	{
		
	}

	//Checks if settings of tables are correct
	public function tableSettingsCheck()
	{
		
	}
}
?>