<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

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
	
	//Temporaly row
	protected $row = array();
	
	//Query results
	protected $result;		//Query result
	protected $result_arr;	//Result array
	protected $num_rows;	//Number of result rows
	protected $aff_rows;	//Number of affected rows
	protected $errors;		//Error list
	protected $query;		//Last sql query
	
	

	public function __construct()
	{
		$this->hostname = SIMTO_DBSERVER;
		$this->username = SIMTO_DBLOGIN;
		$this->password = SIMTO_DBPASS;
		$this->connect();
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
			//error handeling
			return false;
		else
			return true;
	}
	
	//Adds data to temporaly row in array ($html - strip(false/true/'<allowed tags>'))
	public function addCell($col = '',$data = '',$html = false)
	{
		if(empty($col))
			return false;
		
		$data = $this->safeData($data);
		
		if($html !== false)
			$data = $this->htmlStrip($data,$html);
		
		$this->row[$col] = $data;
	}
	
	//Adds whole temporaly row from array ($html - strip(false/true/'<allowed tags>'))
	public function addRow($data = array(),$html = false)
	{
		$data = $this->safeData($data);
	
		if($html !== false)
			$data = $this->htmlStrip($data,$html);
	
		foreach($data as $col => $val)
			$this->row[$col] = $val;
	}
	
	//Clears temporaly row
	public function clearRow()
	{
		$this->row = array();
	}
	
	//Clears all results
	public function clearResults()
	{
		unset($this->result);
		unser($this->result_arr);
		unset($this->num_rows);
		unset($this->aff_rows);
		unset($this->errors);
	}
	
	//Clears all, results, temporaly row
	public function clearAll()
	{
		$this->clearRow();
		$this->clearResults();
	}
	
	//Inserts temporaly row to database
	public function insert($table = '')
	{
		if(empty($table))
			return false;
		
		$code = 'INSERT INTO '.$table.' (';
		
		$i = 0; $cols = ''; $vals = '';
		foreach($this->row as $col => $val)
		{
			if($i > 0)
			{
				$cols .= ',';
				$vals .= ',';
			}	
			$cols .= $col;
			$vals .= $val;
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
	public function update($table = '',$options = array())
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
		
		$i = 0; $cols = ''; $vals = '';
		foreach($this->row as $col => $val)
		{
			if($i > 0)
				$code .= ',';
			
			$code .= $col.'='.$val;
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
	public function delete($table = '',$options = array())
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
	public function select($table = '',$cols = '*',$options = array())
	{
		if(empty($table))
			return false;
		
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
			return false;
		
		$code = 'CREATE DATABASE IF NOT EXISTS '.$name;
		
		$this->query = $code;
		
		if($this->execudeSQL($code))
			return true;
		else
			return false;
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
	public function lastID()
	{
		return mysqli_insert_id($this->mysql);
	}
	
}
?>