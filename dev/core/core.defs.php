<?php
//Page restriction
if(basename($_SERVER['PHP_SELF']) == 'core.defs.php') die('Restricted area! You cannot load this page directly.');


//Page restriction
define('PR', TRUE);

//Directory separator
define('DS', DIRECTORY_SEPARATOR);

//Application temporary folder
define('CPCT_TEMP', 'temp_folder');

//Utilization of database server
define('CPCT_MYSQL', TRUE);

//Database server
define('CPCT_DBSERVER', 'localhost');

//Database login
define('CPCT_DBLOGIN', 'Encrypted login');

//Databse password
define('CPCT_DBPASS', 'Encrypted password');

//Default language
define('CPCT_LANG', 'en');

?>