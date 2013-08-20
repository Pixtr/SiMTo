<?php

/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(basename($_SERVER['PHP_SELF']) == 'base.defs.php') die('Restricted area! You cannot load this page directly.');
	
//Project ID
define('PR_ID', 'core');
	
//Project under construction
define('PR_UNDER', FALSE);
	
//Error handeling level
define('PR_ERRLVL', '1');
	
//Error handeling level shown to user
define('PR_UERRLVL', '4');
	
//Show errors to user
define('PR_ERRSHOW', TRUE);
	
//History of changes
define('PR_HISTORY', TRUE);
	
//Page generation time
define('PR_PAGETIME', TRUE);

//Default language
define('PR_LANG', 'en');

//Default language
define('PR_DBASE', 'databasename');
	
	
?>