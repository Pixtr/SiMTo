<?php
/* 2013 CleverOn SiMTo - Site Management Tool
 *
 * Author: CleverOn Group
 * Proprietary software license
 * All rights reserved for CleverOn Group
 */

//Page restriction
if(!PR) die('Restricted area! You cannot load this page directly.');

class simtoScramblerCore implements simtoICore
{
	//Stored instance of class object
	protected static $inst;
	
	public function __construct(){
		
	}
	
	
	//Get instance object from class
	public static function getInst()
	{
		if (!simtoScrambler::$inst)
			simtoScrambler::$inst = new simtoScrambler();
	
		return simtoScrambler::$inst;
	}
	
	
	//Scrambler encrypts word into password
	public function encrypt($pass)
	{
		
	}
	
	//Scrambler decrypts password back to word
	public function decrypt($pass)
	{
		
	}
	
	//Scrambler change method of decryption on every saved password
	public function change()
	{
		
	}
	
	
	//Mesure strength of password (password,option if result should be text or number)
	public function passStrength($pass,$res = 'text'){
		$numb = array('1','2','3','4','5','6','7','8','9','0');
		$lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$pass_arr = str_split($pass);
			
		$len = strlen($pass);
		$numb_c = 0;
		$lower_c = 0;
		$upper_c = 0;
		$other_c = 0;
		$str = 0;
			
		for($i=0; $i<$len; $i++){
			if(in_array($pass_arr[$i],$lower)){
				$lower_c++;
			}elseif(in_array($pass_arr[$i],$upper)){
				$upper_c++;
			}elseif(in_array($pass_arr[$i],$numb)){
				$numb_c++;
			}else{
				$other_c++;
			}
		}
			
			
		$co = 0;
		if($numb_c == 0){ $numb_c = ''; }else{ $co++; }
		if($lower_c == 0){ $lower_c = ''; }else{ $co++; }
		if($upper_c == 0){ $upper_c = ''; }else{ $co++; }
			
		if($len != 0){
			$str = $numb_c * $lower_c * $upper_c * $co;
			$str = $str + $other_c;
			$str = $str / $len;
			$str = $str + $len;
		}
		
		if($res != 'text'){
			return $str;
		}
			
		if($str == 0){ return 'No password';
		}elseif(($str > 0) && ($str < 5)){ return 'Very weak';
		}elseif(($str >= 5) && ($str < 9)){ return 'Weak';
		}elseif(($str >= 9) && ($str < 14)){ return 'Normal';
		}elseif(($str >= 14) && ($str < 20)){ return 'Strong';
		}elseif(($str >= 20) && ($str < 30)){ return 'Very strong';
		}elseif(($str >= 30)){ return 'Optimal'; }
	}
}



?>