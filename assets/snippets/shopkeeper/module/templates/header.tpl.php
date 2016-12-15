<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  lang="en" xml:lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="media/style/<?php echo $theme; ?>/style.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo SHOPKEEPER_PATH; ?>module/js/colorpicker/farbtastic.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo SHOPKEEPER_PATH; ?>module/js/colorbox/colorbox.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo SHOPKEEPER_PATH; ?>module/js/datepicker/smoothness/jquery.ui.all.css" />
  <style type="text/css">
    .but-link {padding:2px 0 2px 20px; background-repeat:no-repeat; background-position:left top;}
    .order-table {border-collapse:collapse;}
    .order-table th, .order-table td {padding:2px 5px; border:1px solid #888;}
    .order-table th {background-color:#E4E4E4;}
    .order-table th select, .order-table th input {font-weight:normal;}
    .pages {padding:5px 0;}
    table input {margin:2px 0;}
    li {margin:10px 0 0 0;}
    th.header {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/sort.gif); cursor: pointer; font-weight: bold; background-repeat: no-repeat; background-position: center left; padding-left: 15px;}
    th.headerSortUp {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/asc.gif); background-color: #D0D0D0;}
    th.headerSortDown {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/desc.gif); background-color: #D0D0D0;}
    .colorwell {border: 2px solid #fff; width: 75px; text-align: center; cursor: pointer;}
    form input.editable {border:1px solid #fff; background:none #eee; cursor:pointer;}
  </style>
  <script src="<?php echo SHOPKEEPER_PATH; ?>js/jquery-1.4.2.min.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/jquery.tablesorter.min.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/colorpicker/farbtastic.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/datepicker/jquery.ui.core.min.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/datepicker/jquery.ui.datepicker-ru.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/datepicker/jquery.ui.datepicker.min.js" type="text/javascript"></script>
  <script type="text/javascript">
  var colorBoxOpt = {iframe:true, innerWidth:700, innerHeight:400, opacity:0.5};
  $.fn.tabs = function(){
    var parent = $(this);
    var tabNav = $('div.tab-row',this);
    var tabContent = $('div.tab-page',this);
    $('h2.tab',tabNav).each(function(i){
      $(this).click(function(){
        $('h2.tab',tabNav).removeClass('selected');
        $('h2.tab',tabNav).eq(i).addClass('selected');
        tabContent.hide();
        tabContent.eq(i).show();
        return false;
      });
    });
  }
  var tree = false;
  
  function postForm(action, id, value){
    document.module.action.value=action;
    if (id != null) document.module.item_id.value=id;
    if (value != null) document.module.item_val.value=value;
      document.module.submit();
  }
  
  function checkAll(elem){
    if(elem.checked==true){
      $("form input:checkbox[name='check[]']").attr("checked","checked");
    }else{
      $("form input:checkbox[name='check[]']").removeAttr("checked");
    }
  }
  
  $(document).bind('ready',function(){
      $("#ordersTable").tablesorter({sortList: [[1,0]], headers: {0:{sorter: false}, 9:{sorter: false}}});
      $("a.iframe").colorbox(colorBoxOpt);
      $("a.ajax").colorbox({innerWidth:700, innerHeight:400});
      $("#tabs").tabs();
      setTimeout(function(){
        $('#notifyBlock').slideUp(700);
      },5000);
      $('input.datepicker').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd.mm.yy'
      });
      $('input.editable').each(function(){
          $(this)
          .focus(function(){$(this).removeClass('editable');})
          .blur(function(){$(this).addClass('editable');});
      });
    } 
  );
  </script>
</head>
<body>

<br />
<div class="sectionHeader">Shopkeeper - <?php if($action=='catalog'){echo $langTxt['catalog_mod'];}else{echo $langTxt['modTitle'];} ?></div>

<div class="sectionBody" style="min-height:250px;">


