<?php

//***********************************
//snippet for MODx 1.x
//***********************************

/*
Код сниппета:
return require MODX_BASE_PATH."assets/plugins/attachFiles/attachFiles_snippet.inc.php";
*/

defined('IN_PARSER_MODE') or die();

if(!isset($docId)) $docId = $modx->documentIdentifier;
if(!isset($tvName)) $tvName = 'param';
if(!isset($tpl)) $tpl = '';
if(!isset($postName)) $postName = 'none';

if(!function_exists('fetchTpl')){
function fetchTpl($tpl){
	global $modx;
  $template = "";
	if(substr($tpl, 0, 6) == "@FILE:"){
	  $tpl_file = MODX_BASE_PATH . substr($tpl, 6);
		$template = file_get_contents($tpl_file);
	}else if(substr($tpl, 0, 6) == "@CODE:"){
		$template = substr($tpl, 6);
	}else if($modx->getChunk($tpl) != ""){
		$template = $modx->getChunk($tpl);
	}else{
		$template = false;
	}
	return $template;
}
}

$TVout = $modx->getTemplateVars(array($tvName),"id",$docId);

if(!empty($TVout[0]['value'])){
  
  $rowChunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', fetchTpl($tpl));
  $output .= $rowChunk[0];
  $fields = explode('||',$TVout[0]['value']);
  
  foreach($fields as $key => $val){
    $row = explode('==',$val);
    $pl_names = array();
    $pl_values = array();
    foreach($row as $k => $v){
      $pl_names[] = '[+field'.($k+1).'+]';
      $pl_values[] = $v;
      $pl_names[] = '[+selected'.($k+1).'+]';
      if(isset($_POST[$postName])){
          $pl_values[] = $_POST[$postName] == $v ? ' selected="selected"' : '';
      }else{
          $pl_values[] = isset($modx->placeholders['form_'.$postName]) && $modx->placeholders['form_'.$postName] == $v ? ' selected="selected"' : '';
      }
    }
    $output .= str_replace($pl_names,$pl_values,$rowChunk[1]);
  }
  
  $output .= $rowChunk[2];

}

return $output;

?>
