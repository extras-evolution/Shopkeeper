<?php
//*************************************/
//paramEditFull - удобное добавление и
//редактирование дополнительных 
//параметров у товаров.
//*************************************/
//Код плагина - require_once MODX_BASE_PATH."assets/plugins/paramEditFull/paramEditFull.php";
//Конфигурация - &tv_names=TV names;string;Имена TV &inp_width=Input Width;string;200 &loadJquery=Load JQuery;list;true,false;false
//Системные события - OnDocFormRender
//oleg.39style@gmail.com 2011
//*************************************/

defined('IN_MANAGER_MODE') or die();

if (!isset($tv_names))
    $tv_names = '';
if (!isset($inp_width))
    $inp_width = '250';
if (!isset($loadJquery))
    $loadJquery = 'false';    
$e = &$modx->Event;

if ($e->name == 'OnDocFormRender') {

    $tv_name_arr = explode(',', $tv_names);
    $tvVar = '';
    foreach ($tv_name_arr as $value) {
        $tvVar .= ",'" . $value . "'";
    }
    $tvVar = substr($tvVar, 1);
    $result = $modx->db->select("id,type", $modx->getFullTableName('site_tmplvars'),
        "name IN (" . $tvVar . ")");
    $tvIds = '';

    while ($row = $modx->db->getRow($result)) {
        $tvIds .= $row['type'] == "listbox-multiple" ? ",'" . $row['id'] . "[]'" : "," .
            $row['id'];
    }

    $tvIds = substr($tvIds, 1);

    $output = "";
    
    $loadJQ = ($loadJquery != 'false') ? "<script type='text/javascript' src='../assets/plugins/paramEditFull/jquery-1.5.1.min.js'></script>" : "";
    
    if ($e->name == 'OnDocFormRender') {
        $output .= "
    
    ".$loadJQ."
    <script type='text/javascript' src='../assets/plugins/paramEditFull/jquery-ui-1.8.13.custom.min.js'></script>
    <style type='text/css'>
        .li-iu-sorttable{
            height:26px;
            width:300px;
            list-style:none;
        }
        #liEditParamField{
            padding-left:0;
            list-style:none;
            height:26px;
        }
        #inputParamEdit{
           margin:0px 4px 0px 0; 
        }
    </style>
    <script type='text/javascript'>
    var imgAdd = 'media/style/MODxCarbon/images/icons/add.png';
    var imgDel = 'media/style/MODxCarbon/images/icons/delete.png';
    var imgDrag = '/assets/plugins/paramEditFull/drag-default.gif';
    var input_width = '".$inp_width."'+'px';
    
    var tv_name = [".$tvIds."];
    
    jQuery.noConflict();
    
    (function($) {
        
    
      function addAddField(id,rel){
            var idtv = $('li.'+id).length;
            var n = 0;
            $('li.'+id).each(function(){
                if(parseInt($(this).attr('rel')) > n) n = $(this).attr('rel');
            })
            
        idtv = parseInt(n)+1;
        var t_out = '<li id=\'liEditParamField\' class=\"'+id+'\" rel=\"'+idtv+'\"><input onchange=\'documentDirty=true;\' style=\"width:'+input_width+'\" id=\'inputParamEdit\' type=\'text\' value=\'\' class=\'text\'/><input onchange=\'documentDirty=true;\' style=\"width:'+input_width+'\" id=\'inputParamEdit\' type=\'text\' value=\'\' class=\'text\' /><span idtv=\"'+idtv+'\" class=\"addField\" rel=\"'+id+'\"><img title=\'добавить\' style=\'cursor:pointer;margin-right:2px;\' src=\"'+imgAdd+'\" /></span><span class=\'delField\'><img title=\'удалить\' style=\'cursor:pointer;margin-right:2px;\' src=\"'+imgDel+'\" /></span><span><img title=\'переместить\' class=\'spanDragUi\' style=\'cursor:move;margin-right:2px;\' src=\"'+imgDrag+'\" /></span></li>';
        $($('li.'+id+'[rel='+rel+']').after(t_out));
        refreshTvFilds();
        return false;
     
    }   
    
    function refreshTvFilds(){
        for(var a = 0; a<tv_name.length; a++ ){
            var outInp = '';
            var inputLen = $('li.'+'.tv'+tv_name[a]).length;
            $('li.'+'.tv'+tv_name[a]).each(function(){
                var inputAr = $(this).find('input');
                var inputRazd = (inputLen == 1 && (inputAr.eq(0).val() == '' && inputAr.eq(1).val() == '')) ? '' : '==';
                outInp += inputAr.eq(0).val()+inputRazd+inputAr.eq(1).val()+'||';
            })
          $('#tv'+tv_name[a]).val(outInp.slice(0,-2));  
        }
    }
        
        var sortstr = '';
        for(var u =0; u < tv_name.length; u++){
            sortstr += '#ultv'+tv_name[u]+',';
        }
        var sortstr = sortstr.slice(0,-1);
        
        setTimeout(
            function(){
            $(sortstr).sortable({
                placeholder: 'li-iu-sorttable',
                opacity: 0.6,
                axis: 'y',
                helper: 'clone',
                handle : '.spanDragUi',
                stop: function(){
                   refreshTvFilds(); 
                }
            })
        },700)
        
       $('.addField').live('click',
        function(){
            addAddField($(this).attr('rel'),$(this).attr('idtv'));
        });
        
        $('.delField').live('click',
        function(){
            if($(this).parents('ul').find('li').length > 1){
            $(this).parent().remove();
            refreshTvFilds();
            }
        });
        
        $('#inputParamEdit').live('change',
        function(){
           refreshTvFilds();
           return false;
        }).change();
        
      for(var a = 0; a<tv_name.length;a++){  
          
        if(tv_name[a] != null){    
              var tvv = 'tv'+tv_name[a];  
              
              var tv = $('#'+tvv).val();
              $('#'+tvv).css({'display':'none'}); // TODO
              tv_split = tv.split('||');
              var t_out = '<ul id=\"ul'+tvv+'\" style=\'margin-left:0;padding-left:0;\'>';
              for(var i = 0; i <= (tv_split.length - 1); i++){
                var tv_split_s = tv_split[i].split('==');
                if(tv_split_s[1] == null) tv_split_s[1] = ''; 
                t_out += '<li id=\'liEditParamField\' class=\"'+tvv+'\" rel=\"'+i+'\"><input style=\"width:'+input_width+'\" id=\'inputParamEdit\' type=\'text\' value=\''+tv_split_s[0] + '\' class=\'text\'/><input style=\"width:'+input_width+'\" id=\'inputParamEdit\' type=\'text\' value=\''+tv_split_s[1] + '\' class=\'text\'/><span idtv=\"'+i+'\" class=\"addField\" rel=\"'+tvv+'\" class=\'\'><img title=\'добавить\' style=\'cursor:pointer;margin-right:2px;\' src=\"'+imgAdd+'\" /></span><span class=\'delField\'><img title=\'удалить\' style=\'cursor:pointer;margin-right:2px;\' src=\"'+imgDel+'\" /></span><span><img title=\'переместить\' class=\'spanDragUi\' style=\'cursor:move;margin-right:2px;\' src=\"'+imgDrag+'\" /></span></li>';
              
              }
              t_out += '</ul>';
              $($('#'+tvv).after(t_out));
              
          }
      }

})(jQuery);

</script>";
    }

    $e->output($output);
    $modx->clearCache();
}
?>