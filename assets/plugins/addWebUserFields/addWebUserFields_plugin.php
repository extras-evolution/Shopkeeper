<?php

/***********************************
http://modx-shopkeeper.ru/
Andchir (http://wdevblog.net.ru)
---------------------------------
addWebUserFields 1.5.2
plugin for MODx (1.x.x) + Shopkeeper (0.9.x)
---------------------------------
System Events:
OnWUsrFormRender, OnWebSaveUser
***********************************/

defined('IN_MANAGER_MODE') or die();

$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$p_table = $dbprefix."web_user_additdata";

define('AWUF_PATH',MODX_BASE_PATH."assets/plugins/addWebUserFields/");

if(file_exists(AWUF_PATH."lang/".$manager_language.".php")){
  require AWUF_PATH."lang/".$manager_language.".php";
}

$e = &$modx->Event;
$output = '';

if($e->name == 'OnWUsrFormRender'){
  
  $userid = isset($id) ? $id : 0;
  $hide_fields = isset($hide_fields) ? $hide_fields : '';
  $hide_fields_arr = strlen($hide_fields)>0 ? explode(',',$hide_fields) : array();
  
  //check table in DB
  if($modx->db->getRecordCount($modx->db->query("show tables from $dbname like '$p_table'"))==0){
    $sql[] = "CREATE TABLE `$p_table` (`id` int(11) NOT NULL AUTO_INCREMENT, `webuser` INT(11) NOT NULL, `setting_name` VARCHAR(50) NOT NULL default '', `setting_value` TEXT, PRIMARY KEY (`id`))";
    foreach ($sql as $line){
      $modx->db->query($line);
    }
  }
  
  $result = $modx->db->select("*", $p_table, "webuser = '$userid'", "id ASC");
  
  //show additional fields
  if($modx->db->getRecordCount($result) > 0){
    
    //in Backend
    if(IN_MANAGER_MODE==true && !isset($tpl)){
    
      $output .= '
        <div class="sectionHeader">'.$langTxt['addit_fields'].'</div>
        <div class="sectionBody">
        <table border="0" cellspacing="0" cellpadding="3">
      ';
      while($row = $modx->db->getRow($result,'assoc')){
        if(!in_array($row['setting_name'],$hide_fields_arr)){
          if(strpos($row['setting_name'],'__checkbox')===false){
            $output .= "\n<tr>\n<td>".(isset($langTxt[$row['setting_name']]) ? $langTxt[$row['setting_name']] : $row['setting_name']).":</td>\n";
            $output .= '<td><input class="inputBox" type="text" name="addit__'.$row['setting_name'].'" value="'.$row['setting_value'].'" /></td></tr>';
          }else{
            
            $output .= "\n<tr>\n<td>".(isset($langTxt[$row['setting_name']]) ? $langTxt[$row['setting_name']] : $row['setting_name']).":</td>\n";
            $checked = $row['setting_value']==1 ? 'checked="checked"' : '';
            $output .= '
            <td>
              <input type="checkbox" name="addit__'.$row['setting_name'].'" value="1" '.$checked.' />
              <input type="hidden" name="addit__'.$row['setting_name'].'_h" value="0" />
            </td>
            </tr>';
            
          }
        }
      }
      $output .= "\n</table>\n</div>\n";
    
    //in Frontend
    }else{
      
      if(isset($tpl)){
        while($row = $modx->db->getRow($result,'assoc')){
          if(!in_array($row['setting_name'],$hide_fields_arr)){
            $chunk = $tpl; 
            $chunk = str_replace("[+field_caption+]", (isset($langTxt[$row['setting_name']]) ? $langTxt[$row['setting_name']] : $row['setting_name']), $chunk);
            $chunk = str_replace("[+field_name+]", "addit__".$row['setting_name'], $chunk);
            if(strpos($row['setting_name'],'__checkbox')===false){
              $chunk = str_replace("[+field_type+]", "text", $chunk);
              $chunk = str_replace("[+field_value+]", $row['setting_value'], $chunk);
            }else{
              $chunk = str_replace("[+field_type+]", "checkbox", $chunk);
              $checked = $row['setting_value']==1 ? 'checked="checked"' : '';
              $chunk = str_replace("[+field_value+]", '1" '.$checked.' /><input type="hidden" name="addit__'.$row['setting_name'].'_h" value="0" ', $chunk);
            }
            $output .= $chunk;
          }
        }
      }
      
    }
    
  }
  
  //set placeholders
  foreach($_POST as $key => $value){
    if(strpos($key,'addit__')!==false){
      $modx->setPlaceholder($key,$value);
    }
  }
  
  $e->output($output);

}

if($e->name == 'OnWebSaveUser'){
  
  $mode = isset($mode) ? $mode : '';
  $userid = isset($userid) ? $userid : 0;
  
  //insert/update fields
  foreach($_POST as $key => $value){
    if(strpos($key,'addit__')!==false){
      $field_name = str_replace('addit__','',$key);
      //if checkbox
      if(strpos($key, "__checkbox")!==false){
        if(substr($field_name, -2)=='_h'){
          if(isset($_POST[str_replace('_h','',$key)])){
            continue;
          }
          $field_name = str_replace('_h','',$field_name);
        }
      }
      if($mode=='upd')
        $result = $modx->db->update("setting_value = '".$modx->db->escape($value)."'", $p_table , "webuser = '$userid' AND setting_name = '$field_name'");
      else if($mode=='new')
        $modx->db->insert("VALUES(NULL,'$userid','$field_name','".$modx->db->escape($value)."')", $p_table);
    }
  }
  
}



?>