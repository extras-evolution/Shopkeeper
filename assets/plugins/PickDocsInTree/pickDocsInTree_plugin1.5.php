<?php

//***********************************
// PickDocsInTree plugin v1.5 for MODx 1.x
//***********************************
// Andchir  http://wdevblog.net.ru
//***********************************
// Description: Selection of documents from a tree to text field of TV-variable.
// Supported TV Input Types: Text, Listbox(Multi-Select)
// For Listbox: Widget - Delimited List, Delimiter - ,
// Configuration: &tv_name=TV name(s);string;docs
// System Events: OnDocFormRender,OnDocFormSave
// Ditto example:
// [[Ditto? &documents=`[*popular*]`&tpl=`promo`]]
//***********************************

$lang_choose = "Выбрать";
$lang_stop = "Стоп";

if(!isset($tv_name)) $tv_name = "docs";

$e = &$modx->Event;

if ($e->name == 'OnDocFormRender'){
  $tv_name_arr = explode(',',$tv_name);
  $tvVar = '';
  foreach ($tv_name_arr as $value) {
    $tvVar .= ",'$value'";
  }
  $tvVar = substr($tvVar, 1);
  $result = $modx->db->select("id,type", $modx->getFullTableName('site_tmplvars'), "name IN ($tvVar)");
  $tvIds = '';

  while ($row = $modx->db->getRow($result)){
    $tvIds .=  ",".$row['id'];
  }
  
  $tvIds = substr($tvIds, 1);

$output = <<< OUT
<!-- PickDocInTree -->
<script type="text/javascript">
  var tvVarIds = [$tvIds];
  var tvActiveId = 0;
  var pickButton = new Array();
  function setMoveValue(pId, pName){
    if($(tvActiveId).tagName == 'INPUT'){
      $(tvActiveId).value += $(tvActiveId).value=='' ? pId : ','+pId;
    }else if($(tvActiveId).tagName == 'SELECT'){
      var slOption = new Element('option',{'value':pId});
      $(tvActiveId).adopt(slOption.appendText(pName));
      //$(tvActiveId).innerHTML += '<option value="'+pId+'">'+pName+'</option>';
    }
  }
  function setFieldAction(active,elem){
    if(tvActiveId != 0 && tvActiveId != active){
      $(tvActiveId).getNext().childNodes[0].nodeValue = "$lang_choose";
    }
    if(elem.childNodes[0].nodeValue=="$lang_choose"){
      tvActiveId = active;
      elem.childNodes[0].nodeValue = "$lang_stop";
      parent.tree.ca = "move";
    }else{
      tvActiveId = 0;
      parent.tree.ca = "open";
      elem.childNodes[0].nodeValue = "$lang_choose";
    }
  }
  
  window.onDomReady(function(){
    for (var i=0;i<tvVarIds.length;i++){
      if($('tv'+tvVarIds[i])!=null){
        var fieldId = 'tv'+tvVarIds[i];
        var pickDocField = $(fieldId);
        var pickButton = new Element('button',{
          'type':'button',
          'class': 'pickInTreeButton',
          'events':{'click':function(){
              setFieldAction(this.getPrevious().id,this);
              return false;
            }
          }
        });
        pickDocField.getParent()
        .adopt(pickButton.appendText('$lang_choose'));
      }
    }
  });
</script>
<!-- /PickDocInTree -->
OUT;
  
  $e->output("\n\n".$output."\n\n");
  
}

if ($e->name == 'OnDocFormSave'){
  global $tmplvars;
  $dbprefix = $modx->db->config['table_prefix'];
  $tv_name_arr = explode(',',$tv_name);
  $tvVar = array();
  foreach ($tv_name_arr as $key => $value) {
    $tvVar[] = "'$value'";
  }
  $query = $modx->db->select("id,type", $modx->getFullTableName('site_tmplvars'), "name IN (".implode(",",$tvVar).")");
  while ($row = $modx->db->getRow($query)){
    $id = $row['id'];
    if($row['type'] == "listbox-multiple" && !empty($tmplvars[$id][1])){
      $tvIds = str_replace("||",",",$tmplvars[$id][1]);
      $modx->db->update("elements = '@SELECT pagetitle, id FROM ".$dbprefix."site_content WHERE id IN ($tvIds) ORDER BY createdon'", $modx->getFullTableName('site_tmplvars'), "id = $id");
    }
  }
}




?>