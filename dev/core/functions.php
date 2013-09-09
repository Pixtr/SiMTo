<?php
function tCat($string)
{
	simtoTranslator::getInst()->wordCategory($string);
}


function t($string, $id = '', $cat = '')
{
	print simtoTranslator::getInst()->translate($string, $id, $cat);
}

function tDB($id = '')
{
	print simtoTranslator::getInst()->translateDb($id);
}

?>