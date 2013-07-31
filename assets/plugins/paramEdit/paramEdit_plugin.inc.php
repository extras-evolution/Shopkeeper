<?php

//***********************************
//paramEdit v1.7 plugin for MODx 1.0.x and Shopkeeper
//***********************************
//Andchir  http://wdevblog.net.ru
//***********************************
// Configuration (рус.):
// &tv_ids=ID TV-параметров;string; &f_width=Ширина полей;string;180 &f_rows=Число строк в полях;string;1 &f_number=Число полей в руду;string;2 &f_rows=Число строк в полях;string;1 &default_price=Цена по умолчанию;string;
// System Events: OnDocFormRender
//***********************************

defined('IN_MANAGER_MODE') or die();

$e = &$modx->Event;

if(!isset($tv_ids)) $tv_ids = '';
if(!isset($f_width)) $f_width = 180;
if(!isset($f_rows)) $f_rows = 1;
if(!isset($f_number)) $f_number = 2;
if(!isset($pl_name)) $pl_name = str_replace(' ','_',$modx->Event->activePlugin);
if(!isset($default_price)) $default_price = '';

$f_height = $f_rows*16;
$modx_version = $modx->config['settings_version'];

global $tmplvars;

if ($e->name == 'OnDocFormRender') {

$output = <<< OUT

<!-- paramEdit -->
<script type="text/javascript">

function {$pl_name}setParamString(parentId,fieldId){
  var fieldsArr = $(parentId).getElements('textarea');
  var outString = '';
  for (var i=0;i<fieldsArr.length;i++){
    outString += fieldsArr[i].value;
    if((i+1)%{$f_number}==0){
      outString += '||';
    }else{
      outString += '==';
    }
  }
  if(outString)
    $(fieldId).value = outString.slice(0,-2);
  else
    $(fieldId).value = '';
}

function {$pl_name}removeFields(parentId,fieldId){
  var fieldsArr = $$('#'+parentId+' *');
  for (var i=fieldsArr.length-({$f_number}+1);i<fieldsArr.length;i++){
    fieldsArr[i].parentNode.removeChild(fieldsArr[i]);
  }
  {$pl_name}setParamString(parentId,fieldId);
}

function {$pl_name}addFields(parentId,fieldId,index,valArr){
  var br = new Element('br');
  var {$pl_name}f_width_arr = [$f_width];
  var main_width = 0;
  for(var i=0;i<{$f_number};i++){
    var {$pl_name}f_width = typeof({$pl_name}f_width_arr[i])!='undefined' ? {$pl_name}f_width_arr[i] : {$pl_name}f_width_arr[{$pl_name}f_width_arr.length-1];
    main_width += {$pl_name}f_width+8;
    if(i==1){
        var f_value = typeof(valArr)!='undefined' && typeof(valArr[i])!='undefined' ? valArr[i] : '{$default_price}';
    }else{
        var f_value = typeof(valArr)!='undefined' && typeof(valArr[i])!='undefined' ? valArr[i] : '';
    }
    var dataField = new Element('textarea',{
      'styles': {'width':{$pl_name}f_width+'px','height':'{$f_height}px','resize':'none'},
      'value': f_value,
      'events': {'keyup': function(){{$pl_name}setParamString('{$pl_name}editor'+index,''+fieldId+'');}}
    });
    $(parentId).adopt(dataField);
  }
  $(parentId).adopt(br).setStyle('width', main_width+'px');
  {$pl_name}setParamString(parentId,fieldId);
}

function {$pl_name}addEditorFields(fieldId,index){
  var curValue = $(fieldId).value;
  $(fieldId).setStyle('display', 'none');
  curValueArr = curValue.split('||');
  var parentDiv = new Element('div',{id:'{$pl_name}editor'+index});
  $(fieldId).getParent().adopt(parentDiv);
  var eButton1 = new Element('button',{
    'type': 'button', 'styles': {'width': '30px'},
    'events': {'click': function(){
      {$pl_name}removeFields('{$pl_name}editor'+index,''+fieldId+'');
    }}
  }).appendText('-');
  var eButton2 = new Element('button',{
    'type': 'button', 'styles': {'width': '30px'},
    'events': {'click': function(){
      {$pl_name}addFields('{$pl_name}editor'+index,''+fieldId+'',''+index+'');
    }}
  }).appendText('+');
  $(fieldId).getParent().adopt(eButton1,eButton2);
  for (var i=0;i<curValueArr.length;i++){
    if(curValueArr[i]=='') break;
    var varArr = curValueArr[i].split('==');
    {$pl_name}addFields('{$pl_name}editor'+index,''+fieldId+'',''+index+'',varArr);
  }
}

window.addEvent('domready', function(){
  var {$pl_name}editTVids = [{$tv_ids}];
  for (var i=0;i<{$pl_name}editTVids.length;i++){
    if($('tv'+{$pl_name}editTVids[i])!=null){
      var fieldId = 'tv'+{$pl_name}editTVids[i];
      {$pl_name}addEditorFields(fieldId,i);
    }
  }
});
</script>
<!-- /paramEdit -->

OUT;

$e->output($output);

}


?>