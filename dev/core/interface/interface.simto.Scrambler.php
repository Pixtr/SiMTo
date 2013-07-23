<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

Interface simtoIScrambler{
	static function getInst($key);
	
	public function hide($pass);
	
	public function show($pass);
}

?>