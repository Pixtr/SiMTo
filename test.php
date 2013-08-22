
<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
<?php
include('functions.php');

echo 'Hash:'.hash('crc32', 'save').'<br>'."\n";
echo 'Hash:'.hash('crc32', 'Dlouhá věta s diakritikou, bla bla bla.').'<br>'."\n";

cat('cat1');
t('neco');

cat('cat2');
t('tady');

$string = file_get_contents('functions.php',true);
echo $string;
$file = explode("\n", $string);

print_r($file);

echo implode("\n",$file);

$array['tady']['neco']['je'] = 'hodnota';
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

//$clear = clearArr($word_cat);
//print_r($clear);

//Directory separator
define('DS', DIRECTORY_SEPARATOR);
include('dev/core/classes/class.simto.Tools.php');

$path = simtoToolsCore::preparePath('C:\DISC\SkyDrive\CleverOn\www\SiMTo\test\core\classes\neco.php');
echo $path;

echo simtoToolsCore::arrayToString($word_cat,'array','export');
echo simtoToolsCore::arrayToString($array,'array','export');



echo array_search('halo2',$word_cat);



$doc = new DOMDocument;
$doc->load('dev/config/dbase.sett.xml');
$xpath = new DOMXPath($doc);

$query = 'dbases/dbase';
//$result = $xpath->query($query);
$result = $doc->getElementsByTagName('dbase');
$result = $result->item(0)->getElementsByTagName('path');
//foreach($result as $res)
	//$result1[] = $res->nodeValue;
print_r($result);

//echo gettype($result);


include('dev/core/classes/class.simto.XML.php');
include('dev/core/classes/class.simto.Tools.php');

$obj = new simtoXMLCore('dev/config/dbasename.sett.xml');
$obj2 = new simtoXMLCore('dev/config/dbase.sett.xml');

$type = $obj->find(array('c' => 'table[name="tab_name"]/columns/column/settings[name="column_name"]', 'w' => '..', 'r' => 'n'));
//$type = $obj->nodeValue('tag',false);
print_r($obj->show());

$start = $obj->find(array(	'c' => 'table[name="tab_name"]/columns/column', 
							'w' => '..', 
							'r' => 'n'))->item(0);
//$start = $start->item(0);
$obj->addNode('table/name','neco');
print_r($obj->show());

$obj2->importNode($type->item(0), '');
print_r($obj2->show());

$obj->setAttr('table', 'instaled=1');
print_r($obj->show());

$oldnode = $obj->deleteNode('table/columns');
print_r($obj->show());
print_r($oldnode);

$oldatt = $obj->deleteAttr('table/instaled');
print_r($obj->show());
print_r($oldatt);

$obj->filePath(dirname(__FILE__).'\test.xml');
$obj->save();

//$res = $obj->find(array('c' => '', 'w' => '../../column', 'r' => 'c'));

//$res = $obj->nodeValue('tag',false);
//print_r($res);
?>