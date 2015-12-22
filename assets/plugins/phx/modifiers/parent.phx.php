<?php

/* Retrived from http://wiki.modxcms.com/index.php/PHx/CustomModifiers
    * description: get specified document field from parent document (id)
    * usage: [+variable:parent=`field`+]
    * defaults to pagetitle 
*/	
	
$field = (strlen($options)>0) ? $options : 'pagetitle';
if (!is_array($parent = $modx->getParent($output,1,$field)))  $parent = $modx->getParent($output,0,$field);
return $parent[$field];

?>