<?php

/**
 * SaveToSHK plugin 1.1
 */

if(!isset($template)) $template = 0;
if(!isset($tv_price)) $tv_price = 0;

$langTxt = array("saved"=>"Товар сохранен","updated"=>"Товар обновлен");

$e = &$modx->Event;
$output = '';

switch($e->name) {

#######################################################

  case 'OnBeforeDocFormSave':
    
    if(!empty($_POST['to_shk'])){

        global $tmplvars, $pagetitle, $alias, $parent, $introtext, $content, $template, $published, $menuindex;
        
        //insert
        if($mode=='new' && $_POST['to_shk']==4){
          
          //$price = isset($tmplvars[$tv_price][1]) ? $tmplvars[$tv_price][1] : '';
          
          $sql = "INSERT INTO ".$modx->getFullTableName('catalog')."
          (pagetitle, alias, published, parent, isfolder, introtext, content, template, menuindex, createdon, hidemenu)
          VALUES('".$pagetitle."','".$alias."','1','".$parent."','0','".$introtext."','".$content."','".$template."','".$menuindex."','" . time() . "','0')";
          $rs = $modx->db->query($sql);
          
          $docId = $modx->db->getInsertId();
          
          $tvChanges = array();
      		foreach ($tmplvars as $field => $value) {
      			if (is_array($value)) {
      				$tvId = $value[0];
      				$tvVal = $value[1];
      				$tvChanges[] = array('tmplvarid' => $tvId, 'contentid' => $docId, 'value' => $modx->db->escape($tvVal));
      			}
      		}
      		if (!empty($tvChanges)) {
      			foreach ($tvChanges as $tv) {
      				$rs = $modx->db->insert($tv, $modx->getFullTableName('catalog_tmplvar_contentvalues'));
      			}
      		}
          
          echo $langTxt['saved'];
          
        //update
        }else if($mode=='upd' || $_POST['to_shk']==27){
          
          //$price = isset($tmplvars[$tv_price][1]) ? $tmplvars[$tv_price][1] : '';
          
          $updateArr = array(
            'pagetitle' => $pagetitle,
            'alias' => $alias,
            'parent' => $parent,
            'introtext' => $introtext,
            'content' => $content,
            'template' => $template,
            'published' => $published,
            'menuindex' => $menuindex
          );
          $modx->db->update($updateArr,$modx->getFullTableName('catalog'),"id = '$id'");
          
          //save TVs
          /*
          $query1 = "
            SELECT tv.id, tv.name FROM ".$modx->getFullTableName('site_tmplvars')." tv
            LEFT JOIN ".$modx->getFullTableName('site_tmplvar_templates')." stvt ON stvt.tmplvarid = tv.id
            WHERE stvt.templateid = '$template'
          ";
          $allTVs = array();
          $tv_names_res = $modx->db->query($query1);
          while($row = $modx->db->getRow($tv_names_res)){
            $allTVs[$row['id']] = $row['name']; 
          }
          unset($row);
          */
          
          //save all TVs
          $deleteTVs = array();
          foreach ($tmplvars as $field => $value) {
      			if (!is_array($value)) {
              
              //$tvId = $value;
              //$modx->db->delete($modx->getFullTableName('catalog_tmplvar_contentvalues'),"contentid = '$id' AND tmplvarid = '$tvId'");
              $deleteTVs[] = $value;
              
            }else{
      				$tvId = $value[0];
      				$tvVal = $value[1];
              //if(strlen($tvVal)==0) continue;
              $recordCount = $modx->db->getValue($modx->db->select("count(*)",$modx->getFullTableName('catalog_tmplvar_contentvalues'), "contentid = '$id' AND tmplvarid = '$tvId'"));
              if($recordCount>0){
                $rs = $modx->db->update(array('value'=>$modx->db->escape($tvVal)), $modx->getFullTableName('catalog_tmplvar_contentvalues'), "contentid = '$id' AND tmplvarid = '$tvId'");
              }else{
                $rs = $modx->db->insert(array('tmplvarid' => $tvId, 'contentid' => $id, 'value' => $modx->db->escape($tvVal)), $modx->getFullTableName('catalog_tmplvar_contentvalues'));
              }
      			}
      		}
          
          if(count($deleteTVs)>0){
            $modx->db->delete($modx->getFullTableName('catalog_tmplvar_contentvalues'),"contentid = '$id' AND tmplvarid IN (".implode(',',$deleteTVs).")");
          }
          
          echo $langTxt['updated'];
          
        }
        echo '<script type="text/javascript">setTimeout(function(){parent.location.reload();},500);</script>';
        exit;

    }
  break;

#######################################################

  case 'OnDocFormPrerender':
    
    if(!empty($_GET['to_shk'])){
      
      global $id, $content, $tbl_site_tmplvar_contentvalues;
      
      if($template) $content['template'] = $template;
      
      $jsScript = '
        <script type="text/javascript">
        function SHKreloadOnClick(elem){
          elem.removeEvents("click").removeProperty("onclick")
          .addEvent("click",function(){
            parent.location.reload();
            return false;
          });
        }
        window.addEvent("domready", function(){
          
          var SHKparentField = $$("input[name=parent]");
          var SHKparent = SHKparentField.getParent("td");
          var SHKicons = SHKparent.getElements("img");
          SHKicons[0].setStyle("display","none");
          
          SHKparentField.setProperty("name","parent_null");
          var SHKparentField2 = new Element("input",{type:"text",name:"parent",value:SHKparentField.getProperty("value"),styles:{width:"30px"}});
          SHKparent.adopt(SHKparentField2);
          
          SHKreloadOnClick($("Button4").getFirst("a"));
          $$("#stay, #Button2, #Button5, span.and").setStyle("display","none");
        });
        </script>
      ';
      
      //update
      if($_REQUEST['a']==27){
        
        $_REQUEST['a'] = 4; //hack

        if(/*$id==0 && */isset($_REQUEST['update'])) $id = $_REQUEST['update'];
        
        $result = $modx->db->select("*",$modx->getFullTableName('catalog'),"id = '$id'");
        if($modx->db->getRecordCount($result)>0){
          $content = $modx->db->getRow($result);
        }
        
        $content['type'] = 'document';
        $content['richtext'] = 1;
        
        $output .= '<input type="hidden" name="to_shk" value="27" />'."\n".$jsScript;
        
        $tbl_site_tmplvar_contentvalues = $modx->getFullTableName('catalog_tmplvar_contentvalues');
        
      }else{
        
        $output .= '<input type="hidden" name="to_shk" value="4" />'."\n".$jsScript;
      
      }
      
    }
    
  break;

#######################################################

  default:
  break;
}


$e->output($output);

?>