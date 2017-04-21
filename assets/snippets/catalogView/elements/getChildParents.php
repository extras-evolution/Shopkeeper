<?php

/*

Выборка дочерних документов по ID родителя верхнего уровня.
Вызывать только кэшированным.

*/

if(!isset($parent)) $parent = $modx->documentIdentifier;
if(!isset($onlyFolders)) $onlyFolders = false;
if(!isset($depth)) $depth = 10;

$output = '';

if(!function_exists('getChildIds')){
function getChildIds($id,$children=array(),$parents){
  global $modx;
  foreach ($modx->documentMap as $mapEntry){
      if(isset($mapEntry[$id]) && in_array($mapEntry[$id],$parents)){
          $childId = $mapEntry[$id];
          $children[] = $childId;
          $children = $children + getChildIds($childId,$children,$parents);
      }
  }
  return $children;
}
}     

if(!function_exists('getParentList')){
function getParentList(){
	global $modx;
	$kids = array();
	foreach ($modx->documentMap as $null => $document) {
    foreach ($document as $parent => $id) {
			$kids[$parent][] = $id;
		}    
	}    
	$parents = array_keys($kids);      
	return $parents;
}
}


if($onlyFolders){
  $allParents = getParentList();
  $childParents = array();
  $childParents = getChildIds($parent,array(),$allParents);
}else{
  //$childParents = array_values($modx->getChildIds($parent));
  $childParents = $modx->getChildIds($parent,$depth);
}


return implode(',',$childParents);


?>