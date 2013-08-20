<?php
function tCat($string)
{
	simtoTranslator::getInst()->wordCategory($string);
}


function t($string, $id = '', $cat = '')
{
	simtoTranslator::getInst()->translate($string, $id, $cat);
}

function tDB($id = '')
{
	simtoTranslator::getInst()->translateDb($id);
}

?>