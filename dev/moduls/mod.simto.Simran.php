<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Modul Simran for scramblering words using random generation
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

	//Encrypt and decrypt string
	class modSimran implements simtoIScrambler
	{
		//Stored instance of class object
		protected static $inst;
		
		private $salt;
		private $key;
		
		public function __construct($key){
			//Load modul settings of database
			
			
			if(!empty($key))
			{
				$this->key = $key;
			}
			else
			{
				$this->key = $statickey;
			}
			
			$this->salt();
		}
		
		//Get instance object from class
		public static function getInst($key)
		{
			if (!modSimran::$inst)
				modSimran::$inst = new modSimran($key);
		
			return modSimran::$inst;
		}
		
		//Creates encrypted password
		public function hide($pass){
			$new_pass = '';
			if(!empty($pass)){
				$new_pass = $this->encrypt($pass);
				$new_pass = $this->encrypt($new_pass.$this->salt);
			}
			return $new_pass;
		}
		
		//Shows encrypted password
		public function show($pass){
			$new_pass = '';
			if(!empty($pass)){
				$new_pass = $this->decrypt($pass);
				$new_pass = str_replace($this->salt,"", $new_pass);
				$new_pass = $this->decrypt($new_pass);
			}
			return $new_pass;
		}
		
		//Creates salt from key
		private function salt(){
			$salt = hash('crc32',$this->key);
			$this->salt = $salt;
		}
		
		//Encrypts string
		private function encrypt($data){
			$numb = array(65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,
					88,89,90,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,
					115,116,117,118,119,120,121,122,63,36,38,35,33);
			$chars = str_split($data);
			$new_string = '';
			for($i=0; $i < count($chars); $i++){
				$asci1 = ord($chars[$i]);
				$rand = rand(0,(255-$asci1));
				while(!in_array($asci1 + $rand,$numb)){
					$rand = rand(0,(255-$asci1));
				}
				$asci2 = $asci1 + $rand;
				$new_string .= (string)$rand.chr($asci2);
			}
			
			return $new_string;
		}
		//Decrypts string
		private function decrypt($data){
			$chars = str_split($data);
			$rand = '';
			$new_string = '';
			for($i=0; $i < count($chars); $i++){
				if(is_numeric($chars[$i]) && (($rand.$chars[$i])<255)){
					$rand .= $chars[$i];
				}else{
					$asci = ord($chars[$i]);
					$new_string .= chr($asci-$rand);
					$rand = '';
				}
			}
			
			return $new_string;
		}
	}
?>