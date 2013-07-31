
<form name="module" action="<?php echo $mod_page; ?>" method="post">
<input name="action" type="hidden" value="" />
<input name="item_id" type="hidden" value="" />
<input name="item_val" type="hidden" value="" />

<h2><?php echo $langTxt['descTitle']; ?></h2>

<div><?php echo $langTxt['orderDate']; ?>: <b><?php echo $data['date']; ?></b></div>


<br />

<div class="dynamic-tab-pane-control" id="tabs">

<div class="tab-row">
  <h2 class="tab selected"><span><?php echo $langTxt['includes']; ?></span></h2>
  <h2 class="tab"><span><?php echo $langTxt['edit']; ?></span></h2>
  <h2 class="tab"><span><?php echo $langTxt['contact']; ?></span></h2>
</div>

<!-- \\\tab content 1\\\ -->
<div class="tab-page">

  <div style="padding:20px;">
    <ul>
      <?php echo $orderDataList; ?>
    </ul>
  </div>

</div>
<!-- ///tab content 1/// -->

<!-- \\\tab content 2\\\ -->
<div class="tab-page" style="display:none;">
  

  <div style="padding:20px;">
  
  <table>
  
    <?php foreach($data['purchases'] as $i => $dataArray): ?>
    
    <?php list($id, $count, $price, $name) = $dataArray; ?>
    
    <tr>
      <td>
        <input type="hidden" name="p_id[]" value="<?php echo $id; ?>" />
        <?php echo $id; ?>
      </td>
      <td>
        <input style="width:270px" type="text" name="p_name[]" value="<?php echo htmlspecialchars($name); ?>" />
      </td>
      <td>
        x
        <input style="width:30px" type="text" name="p_count[]" value="<?php echo $count; ?>" />
      </td>
      <td>
        <input style="width:70px" type="text" name="p_price[]" value="<?php echo $price; ?>" />
        &nbsp;
        <?php echo $data['currency']; ?>
        &nbsp;
      </td>
      <td>
        &nbsp;<label><input type="checkbox" name="allow_<?php echo $i; ?>" value="1"<?php if(in_array($i,$p_allowed)): ?> checked="checked"<?php endif; ?> /> <?php echo $langTxt['can_order']; ?></label>
      </td>
      <td>
        &nbsp;<label><input type="checkbox" name="delete_<?php echo $i; ?>" value="1" /> <?php echo $langTxt['delete']; ?></label>
      </td>
    </tr>
    
      <?php if(!empty($data['addit_params'][$i])): ?>
        <tr>
        	<td></td>
        	<td>
        	  <?php for($ii=0;$ii<count($data['addit_params'][$i]);$ii++):
                  list($a_name,$a_price) = $data['addit_params'][$i][$ii];
            ?>
        	    <input style="width:180px" type="text" name="a_name_<?php echo $id."_".$i; ?>[]" value="<?php echo $a_name; ?>" />
              <input style="width:80px" type="text" name="a_price_<?php echo $id."_".$i; ?>[]" value="<?php echo $a_price; ?>" />
              <br />
        	    
        	  <?php endfor; ?>
          </td>
        	<td></td>
        	<td></td>
        	<td></td>
          <td></td>
        </tr>
      <?php endif; ?>
      
      <tr><td colspan="6"><hr /></td></tr>
    
    <?php endforeach; ?>
    
  </table>
  
  <br />
  <h3><?php echo $langTxt['add_to_order']; ?></h3>
  
  <table>
    <tr>
        <td>
            <?php echo $langTxt['id']; ?>:
            <br />
            <input style="width:80px" type="text" name="add_prod_id" value="" />
        </td>
        <td>
            <?php echo $langTxt['count']; ?>:
            <br />
            <input style="width:80px" type="text" name="add_prod_count" value="" />
        </td>
        <td>
            <?php echo $langTxt['prod_price']; ?>:
            <br />
            <input style="width:80px" type="text" name="add_prod_price" value="" />
        </td>
        <td>
            <?php echo $langTxt['prod_params']; ?>:
            <br />
            <input style="width:270px" type="text" name="add_prod_params" value="" />
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <?php echo $langTxt['prod_params_help']; ?>
        </td>
    </tr>
  </table>
  
  </div>

  
</div>
<!-- ///tab content 2/// -->

<!-- \\\tab content 3\\\ -->
<div class="tab-page" style="display:none;">
  
  <b><?php echo $langTxt['email']; ?></b>:<br />
  <input type="text" name="email" value="<?php echo $data['email']; ?>" /><br />
  
  <br />
  <b><?php echo $langTxt['phone']; ?></b>:<br />
  <input type="text" name="phone" value="<?php echo $data['phone']; ?>" /><br />
  
  <br />
  <b><?php echo $langTxt['payment']; ?></b>:<br />
  <input type="text" name="payment" value="<?php echo $data['payment']; ?>" /><br />
  
  <br />
  <b><?php echo $langTxt['tracking_num']; ?></b>:<br />
  <input type="text" name="tracking_num" value="<?php echo $data['tracking_num']; ?>" /><br />
  
  <br />
  <b><?php echo $langTxt['note']; ?></b>:<br />
  <textarea name="note" cols="40" rows="5"  style="height:60px"><?php echo $data['note']; ?></textarea><br />
  
  <br />
  <b><?php echo $langTxt['contact']; ?></b>:<br />
  <!--textarea name="short_txt" cols="40" rows="5"  style="height:60px"><?php echo $data['short_txt']; ?></textarea-->
  <div>
      <?php echo $contactsInfo; ?>
  </div>

</div>
<!-- ///tab content 3/// -->



</div>


<?php echo $langTxt['sumTotal'].": <b>".$shkm->numberFormat($data['price'])."</b> ".$data['currency']; ?>


<br /><br clear="all" />

<ul class="actionButtons">
    <li><a href="#" onclick="postForm('save_purchases',<?php echo $data['id']; ?>,1);return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/save.gif" alt="">&nbsp; <?php echo $langTxt['accept_to_pay']; ?></a></li>
    <li><a href="#" onclick="postForm('save_purchases',<?php echo $data['id']; ?>,null);return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/ed_save.gif" alt="">&nbsp; <?php echo $langTxt['save']; ?></a></li>
    <li><a href="<?php echo $mod_page; ?>"><img src="<?php echo SHOPKEEPER_PATH; ?>/style/default/img/cancel.gif" alt="">&nbsp; <?php echo $langTxt['back']; ?></a></li>

    <?php if(isset($plugin['OnSHKOrderDescRender'])) echo $plugin['OnSHKOrderDescRender']; ?>

</ul>

</form>