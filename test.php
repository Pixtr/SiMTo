
<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
<?php
require('dev/simto.php');
simtoCore::getInst(dirname(__FILE__).'\dev')->connect();

//include('functions.php');

echo 'Hash:'.hash('crc32', 'save').'<br>'."\n";
echo 'Hash:'.hash('crc32', 'Dlouhá věta s diakritikou, bla bla bla.').'<br>'."\n";



$string = file_get_contents('functions.php',true);
echo $string;
$file = explode("\n", $string);

print_r($file);

echo implode("\n",$file);

$array['tady']['neco']['je'] = 'hodnota/hjj\zghhh';
$array['tady1']['neco1']['je1'] = 'hodnota1';

	
echo key($array);
$string = 'tento text může být jakýkoliv';
$cat = 'další jakýkoliv text';
$cat1 = '';
if(!empty($cat))
	$cat1 = ",'".$cat."'";
$id = 'idcko';
$text = 'Tohle je nepodstatná čás"t textu'." t(".$string.",'',".$cat."); pokračování v nepodstatné části textu.\n";
echo preg_replace('/"/','\"',$text);
echo preg_replace("/\bt\((.*)\)[;|\.]/","t('".$string."','".$id."'".$cat1.");",$text);
echo preg_replace("/\bt\((['|\"].*['|\"])(,.*)(,.*)\);/","t($1,'".$id."'$3);",$text);
echo preg_replace("/\bt\((['|\"].*['|\"])(,*['|\"].*['|\"])*(,.*)*\);/","t($1,'".$id."'$3);",$text);
//echo eregi_replace("^t\([a-z0-9]*\)(;|\.\s+)","^t\([a-z0-9]*'|\",'".$id."'[a-z0-9]*\)",' t("tady jsem, halo");');


$word_cat['tady']['neco']['je'] = 'halo';
$word_cat['tady']['neco1']['je2'] = 'halo2';
$word_cat['tady']['neco'][] = 'tady';
$word_cat['tady']['neco'][] = 'tady1';
$word_cat['tady'][] = 'neco';
$cat = array('halo');
$word_cat = array_merge($word_cat,$cat);
echo key($word_cat);

print_r($word_cat);



function clearArr($array)
{
	foreach($array as $key => $value)
		if(is_array($value))
			$array[$key] =clearArr($value);
		else
			$array[$key] = '';
		
	return $array;
}


$path = simtoTools::preparePath('C:\DISC\SkyDrive\CleverOn\www\SiMTo\test\core\classes\neco.php');
echo $path."\n";

echo simtoTools::arrayToString($word_cat,'array','build');
echo simtoTools::arrayToString($array,'array','export');



echo array_search('halo2',$word_cat);



$doc = new DOMDocument;
$doc->load('dev/config/dbase/dbases.settings.xml');
$xpath = new DOMXPath($doc);

$query = 'dbases/dbase';
//$result = $xpath->query($query);
$result = $doc->getElementsByTagName('dbase');
$result = $result->item(0)->getElementsByTagName('path');
//foreach($result as $res)
	//$result1[] = $res->nodeValue;
print_r($result);

//echo gettype($result);


//include('dev/core/classes/class.simto.XML.php');
//include('dev/core/classes/class.simto.Translator.php');

$obj = new simtoXML('dev/config/dbase/dbasename.sett.xml');
$obj2 = new simtoXML('dev/config/dbase/dbases.settings.xml');

$type = $obj->find(array('c' => 'table[name="tab_name"]/columns/column/settings[name="column_name"]', 'w' => '..', 'r' => 'n'));
//$type = $obj->nodeValue('tag',false);
print_r($obj->show());
$obj->clearCnode();
print_r($obj->nodeValue('name',false));

$start = $obj->find(array(	'c' => 'table[name="tab_name"]/columns/column', 
							'w' => 'tagname', 
							'r' => 'n'));

print_r($obj->addNode('table/name',''));
print_r($obj->show());

$obj2->importNode($type->item(0), '');
print_r($obj2->show());

$obj->setAttr('table', 'instaled=1');
print_r($obj->show());

//$oldnode = $obj->deleteNode('table/columns');
//print_r($obj->show());
//print_r($oldnode);

//$oldatt = $obj->deleteAttr('table/instaled');
//print_r($obj->show());
//print_r($oldatt);

function xmlzmena($node)
{
	$node = $node->item(0);
	$node->setAttribute('langid','zmena');
	//return $node;
}

xmlzmena($start);

$obj->filePath(dirname(__FILE__).'\test\test.xml');
$obj->save();

//$res = $obj->find(array('c' => '', 'w' => '../../column', 'r' => 'c'));

//$res = $obj->nodeValue('tag',false);
//print_r($res);

$string = 'Tady neco je ;#name#Jmeno#name#; a tady taky neco je ;#name1#Jmeno2#name1#; jeste neco ;#Jmeno3#;.';
$variables = array();
		if (preg_match_all('/\;\#(?<id>[a-z][a-z0-9_]*?)\#/', $string, $m))
		{
			print_r($m);
			foreach($m['id'] as $tag)
			{
				$pattern = '/\;\#'.$tag.'\#(?<var>[A-Za-z][a-z0-9_]*?)\#'.$tag.'\#\;/';
				if(preg_match_all($pattern, $string, $n))
				{
					print_r($n);
					$variables[$tag] = $n['var'][0];
					$string = preg_replace($pattern, ';#'.$tag.'#;',$string);
				}
			}
		}
print_r($variables);
echo $string."\n";

foreach($variables as $tag => $var)
	$string = preg_replace('/\;\#'.$tag.'\#\;/',$var,$string);

$string = preg_replace('/\;\#[A-Za-z][a-z0-9_]*\#\;/','',$string);

echo $string;

$array = array();
$array['cat1']['cat2']['id'] = 'neco';
$array2['cat1']['cat3']['id'] = 'neco jineho';
/*$keys = array('cat1','cat2','id');
foreach($keys as $key)
{
	$array = $array[$key];
	print_r($array);
}*/

$array3 = array_replace_recursive($array,$array2);
print_r($array3);
tCat('Test');
t('ahoj','a8e895058cccdac3');
?>