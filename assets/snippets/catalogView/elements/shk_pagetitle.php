<?php

/*

Example:

[!include? &file=`assets/snippets/catalogView/elements/shk_pagetitle.php`&out_prefix=` | `!]

*/

$output = '';

$get_param = isset($get_param) ? $get_param : 'p';
$out_prefix = isset($out_prefix) ? $out_prefix : '';

if(isset($_GET[$get_param]) && is_numeric($_GET[$get_param])){
    
    $p_id = $modx->db->escape($_GET[$get_param]);
    $pagetitle = $modx->db->getValue($modx->db->select('pagetitle',$modx->getFullTableName('catalog'),"id = '$p_id'"));
    if($pagetitle) $output = $out_prefix.$pagetitle;

}

return $output;

?>