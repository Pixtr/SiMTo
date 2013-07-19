<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(basename($_SERVER['PHP_SELF']) == 'core.defs.php') die('Restricted area! You cannot load this page directly.');


//Page restriction
define('PR', TRUE);

//Directory separator
define('DS', DIRECTORY_SEPARATOR);

//Application temporary folder
define('SIMTO_TEMP', 'temp_folder');

//Utilization of database server
define('SIMTO_MYSQL', TRUE);

//Database server
define('SIMTO_DBSERVER', 'localhost');

//Database login
define('SIMTO_DBLOGIN', 'Encrypted login');

//Databse password
define('SIMTO_DBPASS', 'Encrypted password');

//Default language
define('SIMTO_LANG', 'en');

?>