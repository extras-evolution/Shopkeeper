
<form action="<?php echo $mod_page; ?>" name="module" method="post">
<input name="action" type="hidden" value="" />
<input name="item_id" type="hidden" value="" />
<input name="item_val" type="hidden" value="" />

<div style="float:right;"><?php echo $conf_shk_version; ?></div>

<h2><?php echo $langTxt['configTitle']; ?></h2>


<div class="dynamic-tab-pane-control" id="tabs">

<div class="tab-row">
  <h2 class="tab selected"><span><?php echo $langTxt['basic_settings']; ?></span></h2>
  <h2 class="tab"><span><?php echo $langTxt['templates']; ?></span></h2>
</div>

<!-- \\\tab content 1\\\ -->
<div class="tab-page">

  <table>
  <col width="320" />
  <col width="10" />
  <col width="440" />
  <tr>
    <td valign="top">
      <b><?php echo $langTxt['perpage']; ?></b>:<br />
      <input type="text" name="perpage" value="<?php echo $conf_perpage; ?>" /><br />
      <br />
      <b><?php echo $langTxt['currency']; ?></b>:<br />
      <input type="text" name="currency" value="<?php echo $conf_currency; ?>" /><br />
      <br />
      <b><?php echo $langTxt['conf_inventory']; ?></b>:<br />
      <input type="text" name="inventory" value="<?php echo $conf_inventory; ?>" /><br />
      <br />
      <b><?php echo $langTxt['conf_catalog']; ?></b>:<br />
      <input type="text" name="catalog_id" value="<?php echo $conf_catalog; ?>" /><br />
      <br />
      <b><?php echo $langTxt['conf_phase_days']; ?></b>:<br />
      <input type="text" name="phase_days" value="<?php echo $conf_phase_days; ?>" /><br />
      <br />
      <b><?php echo $langTxt['pricetv']; ?></b>:<br />
      <input type="text" name="pricetv" value="<?php echo $conf_pricetv; ?>" /><br />
      <br />
      <b><?php echo $langTxt['informing']; ?></b>:<br />
      <div><input type="checkbox" name="informing1" id="informing1" value="1"<?php if(!empty($conf_informing1) && $conf_informing1): ?> checked="checked"<?php endif; ?> /> <label for="informing1"><?php echo $langTxt['infg_status']; ?></label><br />
    </td>
    <td>&nbsp;</td>
    <td valign="top">
      <div id="picker" style="float: right;"></div>
      <b><?php echo $langTxt['phaseColors']; ?></b>:<br />
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[0]; ?>" /> - <?php echo $langTxt['phase1']; ?></div>
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[1]; ?>" /> - <?php echo $langTxt['phase2']; ?></div>
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[2]; ?>" /> - <?php echo $langTxt['phase3']; ?></div>
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[3]; ?>" /> - <?php echo $langTxt['phase4']; ?></div>
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[4]; ?>" /> - <?php echo $langTxt['phase5']; ?></div>
      <div><input class="colorwell" type="text" name="color[]" value="<?php echo $phaseColor[5]; ?>" /> - <?php echo $langTxt['phase6']; ?></div>
      <div><input type="checkbox" name="colorDefault" id="colorDefault" value="1" /> <label for="colorDefault"><?php echo $langTxt['colorDefault']; ?></label>
    </td>
  </tr>
  </table>

</div>
<!-- ///tab content 1/// -->

<!-- \\\tab content 2\\\ -->
<div class="tab-page" style="display:none;">


  <table>
  <col width="320" />
  <col width="10" />
  <col width="440" />
  <tr>
    <td valign="top">
    
      <b><?php echo $langTxt['template']; ?></b>:<br />
      <textarea name="template" cols="40" rows="5" style="height:90px"><?php echo $conf_template; ?></textarea><br />
      
      <br />
      <b><?php echo $langTxt['small_template']; ?></b>:<br />
      <textarea name="small_template" cols="40" rows="5" style="height:60px"><?php echo $conf_small_template; ?></textarea><br />
      
      <br />
      <h3><?php echo $langTxt['tpl_mail_status']; ?></h3>
      
      <b><?php echo $langTxt['phase2']; ?></b>:<br />
      <textarea name="tpl_mail_status" cols="40" rows="3" style="height:35px"><?php echo $conf_tpl_mail_status; ?></textarea><br />
      
      <br />
      <b><?php echo $langTxt['phase3']; ?></b>:<br />
      <textarea name="tpl_mail_shipped" cols="40" rows="3" style="height:35px"><?php echo $conf_tpl_mail_shipped; ?></textarea><br />
      
      <br />
      <b><?php echo $langTxt['phase5']; ?></b>:<br />
      <textarea name="tpl_mail_canceled" cols="40" rows="3" style="height:35px"><?php echo $conf_tpl_mail_canceled; ?></textarea><br />
    </td>
    <td>&nbsp;</td>
    <td valign="top">
      
      
      
    </td>
  </tr>
  </table>

</div>
<!-- ///tab content 2/// -->

</div>

<br /><br />

<ul class="actionButtons" style="width:200px; float:right; text-align:right;">
    <li><a href="#" onclick="if(confirm('<?php echo $langTxt['confirm']; ?>')){postForm('uninstall',null,null)};return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/m_delete.gif" alt="">&nbsp; <?php echo $langTxt['uninstallMod']; ?></a></li>
</ul>

<ul class="actionButtons">
    <li><a href="#" onclick="postForm('save_config',null,null);return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/ed_save.gif" alt="">&nbsp; <?php echo $langTxt['save']; ?></a></li>
    <li><a href="<?php echo $mod_page; ?>"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/cancel.gif" alt="">&nbsp; <?php echo $langTxt['back']; ?></a></li>
</ul>

<br /><br />


<script type="text/javascript" charset="utf-8">
  var f = $.farbtastic('#picker');
  var p = $('#picker').hide();
  var selected;
  $('input.colorwell')
  .each(function(){f.linkTo(this);})
  .focus(function(){
    if(selected){
      $(selected).css('border-color','#fff').removeClass('colorwell-selected');
    }
    f.linkTo(this);
    p.show();
    $(selected = this).css('border-color','#000').addClass('colorwell-selected');
  })
  .blur(function(){$('#picker').hide();});
</script>

</form>