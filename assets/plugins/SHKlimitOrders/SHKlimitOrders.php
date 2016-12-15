<?php

/*
 *
 * SHKlimitOrders 1.0 plugin for Shopkeeper >= 0.9.6 beta6
 * http://modx-shopkeeper.ru
 * 
 * Ограничение числа товаров для 
 * В текущей версии поддерживаются товары, которые есть в единственном экзе
 * 
 * Системные события: OnSHKFrontendInit, OnSHKcartLoad
 * 
 * Конфигурация:
 * &button_selector=Селектор кнопки;string;button &button_dis_class=CSS-класс неактивной кнопки;string;disabled &button_before=Текст активной кнопки;string;В корзину &button_after=Текст НЕактивной кнопки;string;Уже в корзине
 * 
 */

if(!isset($button_selector)) $button_selector = 'button';
if(!isset($button_before)) $button_before = 'В корзину';
if(!isset($button_after)) $button_after = 'Уже в корзине';
if(!isset($button_dis_class)) $button_dis_class = 'disabled';

$e = &$modx->Event;

$output = "";

//////////////////////////////////////
//OnSHKFrontendInit
//////////////////////////////////////
if ($e->name == 'OnSHKFrontendInit'){
            
  $output .= <<< OUT
  <script type="text/javascript">
  function SHKlimitOrders(SHKids){
    var SHKparent = jQuery(shkOptions.stuffCont);
    var stuffContForm = jQuery('form',SHKparent);
    jQuery.each(stuffContForm,function(ii){
     var SHKcurId_arr = (jQuery('input[name=shk-id]',this).val()).split('__');
     var SHKcurId = SHKcurId_arr[0];
     var SHKcurPrice = jQuery('input[name=shk-price]',this).val();//jQuery('.shk-price',this).text();
     //var SHKinventory = jQuery('input[name=inventory]',this).size()>0 ? parseInt(jQuery('input[name=inventory]',this).val()) : 0;
     //alert(SHKinventory);
     if(jQuery.inArray(SHKcurId+'_'+SHKcurPrice,SHKids)>-1){
       jQuery('{$button_selector}',this)
       .text('{$button_after}')
       .attr('disabled','disabled')
       .addClass('{$button_dis_class}');
     }else{
       jQuery('{$button_selector}',this)
       .text('{$button_before}')
       .removeAttr('disabled')
       .removeClass('{$button_dis_class}')
       .bind('click',function(){
         var SHKbutton = $(this);
         setTimeout(function(){
           if($('#confirmButton').size()>0){
             $('#confirmButton').bind('click',function(){
               SHKbutton.attr('disabled','disabled');
             });
           }else{
             SHKbutton.attr('disabled','disabled');
           }
         },10);
       });
     }
    });
  }
  </script>
OUT;
  
}
//////////////////////////////////////


//////////////////////////////////////
//OnSHKcartLoad
//////////////////////////////////////
if ($e->name == 'OnSHKcartLoad'){
  
  $p_ids = array();
  
  if(!empty($_SESSION['purchases'])){
    $purchases = unserialize($_SESSION['purchases']);
    foreach($purchases as $key => $value){
      $p_ids[] = "'$value[0]_$value[2]'";
    }
  }
  
  $p_ids_list = implode(',',$p_ids);
  
  $output .= '
  <script type="text/javascript">
    SHKlimitOrders(['.$p_ids_list.']);
  </script>
  ';
    
}
//////////////////////////////////////


$e->output($output);


?>