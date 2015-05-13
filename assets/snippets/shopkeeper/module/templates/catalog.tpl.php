
<script type="text/javascript">
parent.tree.ca = "open";
function setMoveValue(pId, pName){
  $('#docId').val(pId);
  $('#categoryName')
  .text(pName)
  .attr({'style':'','href':'<?php echo $mod_page; ?>&action=catalog&category='+pId});
  $('#getCategory').next('small').remove();
}
function setFieldAction(elem){
  if(parent.tree.ca != "move"){
    parent.tree.ca = "move";
    elem.src = "<?php echo SHOPKEEPER_PATH; ?>/style/default/img/layout_go.png";
    elem.title = "<?php echo $langTxt['stopget_category']; ?>";
  }else{
    parent.tree.ca = "open";
    elem.src = "<?php echo SHOPKEEPER_PATH; ?>/style/default/img/application_double.png";
    elem.title = "<?php echo $langTxt['get_category']; ?>";
  }
}
$(document).bind('ready',function(){
    if($('#categoryName').text()=='') $('#getCategory').after('<small>&nbsp; <?php echo $langTxt['no_category']; ?></small>');
    $(document).bind('cbox_closed', function(){
      window.location.reload();
    });
});
</script>



<div style="min-height: 300px">

<br />

<form name="module" action="<?php $catalog_mod_page; ?>" method="post">
  <input name="action" type="hidden" value="" />
  <input name="item_id" type="hidden" value="" />
  <input name="item_val" type="hidden" value="" />
  <div>
      <img style="cursor:pointer; vertical-align: middle;" id="getCategory" src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/application_double.png" width="16" height="16" alt="" onclick="setFieldAction(this);return false" title="<?php echo $langTxt['get_category']; ?>" />
      &nbsp;
      <a style="<?php if($categoryId!=0): ?>font-weight:bold; font-size:120%;text-decoration:none;<?php endif; ?>" id="categoryName" href="<?php echo $mod_page; ?>&amp;action=catalog&amp;category=<?php echo $categoryId; ?>" title="<?php echo $langTxt['goto_category']; ?>"><?php echo $category['pagetitle']; ?></a>
  </div>
  <?php if($categoryId!=0): ?>
    <br />
    <ul class="actionButtons">
        <li><a href="index.php?a=4&pid=<?php echo $categoryId; ?>&to_shk=1" onclick="$.fn.colorbox.init();$.fn.colorbox($.extend(colorBoxOpt,{href:this.href})); return false;"><?php echo $langTxt['new_product']; ?></a></li>
    </ul>
  <?php endif; ?>
</form>

<br />

<form id="catalogSaveForm" action="<?php $catalog_mod_page; ?>" method="post">
    <input name="action" type="hidden" value="change_price" />
    <input name="item_val" type="hidden" value="" />
    <?php echo $data_table; ?>
    <br />
    <?php echo $pagination; ?>
    
    <br />
    <!--button type="submit" name="change_price"><?php echo $langTxt['save']; ?></button-->


    <ul class="actionButtons">
        <li><a href="#" onclick="document.getElementById('catalogSaveForm').submit();return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/ed_save.gif" alt="">&nbsp; <?php echo $langTxt['save']; ?></a></li>
        <li><a href="<?php echo $mod_page; ?>"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/cancel.gif" alt="">&nbsp; <?php echo $langTxt['back']; ?></a></li>
    </ul>

</form>

</div>
