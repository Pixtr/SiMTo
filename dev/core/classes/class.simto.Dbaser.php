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
	
	public function __construct()
	{
		
	}
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoDbaser::$inst)
			simtoDbaser::$inst = new simtoDbaser();
	
		return simtoDbaser::$inst;
	}
	
	//Create connection to database
	public function connect($dbase)
	{
		
	}
	
	//Destroy connection
	public function disconnect()
	{
		
	}
	
	//Add data to temporaly row in array
	public function addCell($col,$data)
	{
		
	}
	
	//Insterts temporaly row to database
	public function insert($table)
	{
		
	}
	
	//Update data from row in table
	public function update($table,$row)
	{
		
	}
	
	//Delete row from table
	public function delete($row)
	{
		
	}
	
	//Protection from SQL injection
	protected function sqlInject()
	{
		
	}
}


?>