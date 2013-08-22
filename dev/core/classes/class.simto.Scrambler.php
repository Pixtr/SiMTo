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
	
	//Sets decryption method and checks if modul exists (returns true or false if don't)
	public function useModul($modul)
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
		
		//Find how many numbers, lower, upper and other characters password has
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
			
		//Calculate strength of password
		//Calculate how many variates are in password
		$co = 0;
		if($numb_c == 0){ $numb_c = ''; }else{ $co++; }
		if($lower_c == 0){ $lower_c = ''; }else{ $co++; }
		if($upper_c == 0){ $upper_c = ''; }else{ $co++; }
		
		//Logarithm for calculation of strength
		if($len != 0){
			$str = $numb_c * $lower_c * $upper_c * $co;
			$str = $str + $other_c;
			$str = $str / $len;
			$str = $str + $len;
		}
		
		if($res = 'text')
		{
			//Default text results
			$default = array();
			$default[0] = t('No password','0res','Scrambler/Passlength');
			$default[1] = t('Very weak','1res','Scrambler/Passlength');
			$default[5] = t('Weak','5res','Scrambler/Passlength');
			$default[9] = t('Normal','9res','Scrambler/Passlength');
			$default[14] = t('Strong','14res','Scrambler/Passlength');
			$default[20] = t('Very strong','20res','Scrambler/Passlength');
			$default[30] = t('Optimal','30res','Scrambler/Passlength');
			$default[999] = t('Unbreakable','999res','Scrambler/Passlength');
			
			//Finding of special settings for text result
			$lines = array();
			simtoDbaser::getInst()->dbUse(PR_DBASE);
			$options = array('where' => 'pr_id = "'.PR_ID.'" AND class = "strpass"');
			if(simtoDbaser::getInst()->select('tab_scrambler','pr_id,class,setting,item',$options))
			{
				$rows = simtoDbaser::getInst()->resOut();
				foreach($rows as $row)
					$lines[$row['setting']] = tDB($row['item']);
			}
			
			if(empty($lines))
				$lines = $default;
			
			$result = '';
			ksort($lines);
			foreach($lines as $key => $line)
				if($key <= $str)
					$result = $line;
		}
		else
			$result = $str;
			
		return $result;
	}

}
?>